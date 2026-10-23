<?php

namespace App\Http\Controllers\Liquidation;

use App\Exports\LiquidationReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fabrique des 40+ états réglementaires de liquidation. Un même jeu de rapports
 * (LiquidationReportingService::availableReports) est restitué en PDF, Excel ou
 * CSV via une route paramétrée — pas de duplication par format.
 */
class LiquidationReportController extends Controller
{
    public function __construct(private readonly LiquidationReportingService $reporting)
    {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', Liquidation::class);

        return response()->view('liquidations.reports.index', [
            'reports' => $this->reporting->availableReports(),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
        ]);
    }

    public function show(Request $request, string $rapport, string $format): Response|BinaryFileResponse|StreamedResponse
    {
        abort_unless($request->user()?->can('liquidation.export') || $request->user()?->can('liquidation.view'), 403);

        $report = $this->reporting->buildReport($rapport, $request->only('exercice_budgetaire_id'));
        $slug = 'etat-liquidations-'.$rapport;

        return match ($format) {
            'pdf' => Pdf::loadView('liquidations.exports.rapport', [
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
            fprintf($out, "\xEF\xBB\xBF"); // BOM UTF-8 (Excel)
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
