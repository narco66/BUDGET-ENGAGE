<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Exports\LiquidationReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\Ordonnancement;
use App\Services\Ordonnancement\OrdonnancementReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fabrique des 40+ états réglementaires d'ordonnancement (PDF / Excel / CSV)
 * depuis un registre déclaratif unique. L'export Excel générique
 * (LiquidationReportExport) est réutilisé car indépendant du module.
 */
class OrdonnancementReportController extends Controller
{
    public function __construct(private readonly OrdonnancementReportingService $reporting)
    {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Ordonnancement::class);

        return response()->view('ordonnancements.reports.index', [
            'reports' => $this->reporting->availableReports(),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
        ]);
    }

    public function show(Request $request, string $rapport, string $format): Response|BinaryFileResponse|StreamedResponse
    {
        abort_unless($request->user()?->can('ordonnancement.export') || $request->user()?->can('ordonnancement.view'), 403);

        $report = $this->reporting->buildReport($rapport, $request->only('exercice_budgetaire_id'));
        $slug = 'etat-ordonnancements-'.$rapport;

        return match ($format) {
            'pdf' => Pdf::loadView('ordonnancements.exports.rapport', [
                'report' => $report,
                'generatedAt' => now(),
            ])->download($slug.'.pdf'),
            'xlsx' => \Maatwebsite\Excel\Facades\Excel::download(
                new LiquidationReportExport($report['columns'], $report['rows'], $report['title']),
                $slug.'.xlsx',
            ),
            'csv' => $this->streamCsv($report, $slug),
            default => abort(404),
        };
    }

    /**
     * @param  array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, total: ?string}  $report
     */
    private function streamCsv(array $report, string $slug): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $out = fopen('php://output', 'wb');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, $report['columns'], ';');
            foreach ($report['rows'] as $row) {
                fputcsv($out, $row, ';');
            }
            if ($report['total'] !== null) {
                fputcsv($out, ['TOTAL', $report['total']], ';');
            }
            fclose($out);
        }, $slug.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
