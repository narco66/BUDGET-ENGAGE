<?php

namespace App\Http\Controllers\Reports;

use App\Exports\EngagementsExport;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function engagementPdf(Request $request, Engagement $engagement): Response
    {
        abort_unless($request->user()?->can('view', $engagement), 403);

        $engagement->load([
            'exerciceBudgetaire',
            'ligneBudgetaire.natureDepense',
            'createur',
            'piecesJustificatives',
            'validationsWorkflow.validateur',
        ]);

        $pdf = Pdf::loadView('exports.engagement_pdf', [
            'engagement' => $engagement,
            'generatedAt' => now(),
        ]);

        $fileName = "engagement-{$engagement->numero}.pdf";

        return $pdf->download($fileName);
    }

    public function engagementsExcel(Request $request): BinaryFileResponse
    {
        abort_unless(
            $request->user()->can('reports.view')
            || $request->user()->can('reports.view_limited')
            || $request->user()->can('audit.view')
            || $request->user()->can('exercises.manage'),
            403
        );

        $query = Engagement::query()
            ->with(['exerciceBudgetaire', 'ligneBudgetaire', 'createur'])
            ->orderByDesc('created_at');

        if (
            $request->user()->can('reports.view_limited')
            && ! $request->user()->can('reports.view')
            && ! $request->user()->can('audit.view')
            && ! $request->user()->can('exercises.manage')
        ) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $engagements = $query->get();

        return Excel::download(
            new EngagementsExport($engagements),
            'engagements-export.xlsx'
        );
    }

    public function engagementsCsv(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()->can('reports.view')
            || $request->user()->can('reports.view_limited')
            || $request->user()->can('audit.view')
            || $request->user()->can('exercises.manage'),
            403
        );

        $query = Engagement::query()
            ->with(['exerciceBudgetaire', 'ligneBudgetaire', 'createur'])
            ->orderByDesc('created_at');

        if (
            $request->user()->can('reports.view_limited')
            && ! $request->user()->can('reports.view')
            && ! $request->user()->can('audit.view')
            && ! $request->user()->can('exercises.manage')
        ) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $engagements = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="engagements-export.csv"',
        ];

        return response()->stream(function () use ($engagements): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }
            fputcsv($handle, ['Numero', 'Statut', 'Exercice', 'Ligne budgetaire', 'Montant', 'Date engagement', 'Createur', 'Score risque']);
            foreach ($engagements as $engagement) {
                $status = $engagement->statut instanceof \BackedEnum
                    ? $engagement->statut->value
                    : (string) $engagement->statut;
                fputcsv($handle, [
                    $engagement->numero,
                    $status,
                    $engagement->exerciceBudgetaire?->annee,
                    $engagement->ligneBudgetaire?->code,
                    number_format((float) $engagement->montant, 2, '.', ''),
                    $engagement->date_engagement?->format('Y-m-d'),
                    $engagement->createur?->name,
                    $engagement->score_risque,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
