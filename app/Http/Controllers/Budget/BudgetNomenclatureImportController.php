<?php

namespace App\Http\Controllers\Budget;

use App\Exports\BudgetConsolidatedExport;
use App\Exports\BudgetExecutionExport;
use App\Exports\BudgetImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreBudgetImportRequest;
use App\Models\BudgetImport;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\PapLigneBudgetaire;
use App\Services\Budget\BudgetImportService;
use App\Services\Budget\BudgetReportService;
use App\Services\Budget\BudgetSyncService;
use App\Services\Rbm\PapBudgetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetNomenclatureImportController extends Controller
{
    public function __construct(private BudgetImportService $service)
    {
        $this->middleware('permission:budget.view|budget.import.run|budget.replace|budget.restore');
        $this->middleware('permission:budget.import.run|budget.replace')->only(['simulate', 'store']);
        $this->middleware('permission:budget.restore')->only('restore');
        $this->middleware('permission:budget.sync')->only('synchronize');
    }

    public function synchronize(Request $request, BudgetSyncService $sync): RedirectResponse
    {
        $exercice = $this->resolveExercice($request);
        if ($exercice === null) {
            return back()->with('error', __('Exercice introuvable.'));
        }

        $result = $sync->synchronize($exercice, $request->user());

        return redirect()
            ->route('budget.nomenclature.synthese', ['exercice_budgetaire_id' => $exercice->id])
            ->with('status', __(':c ligne(s) créée(s), :u mise(s) à jour — :m FCFA de crédits ouverts générés depuis le budget.', [
                'c' => $result['creations'],
                'u' => $result['mises_a_jour'],
                'm' => number_format($result['montant'], 0, ',', ' '),
            ]));
    }

    public function index(): View
    {
        return view('budget.nomenclature.index', [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'modes' => BudgetImportService::MODES,
            'imports' => BudgetImport::query()->with('user', 'exerciceBudgetaire')->latest()->limit(10)->get(),
            'lignesActuelles' => BudgetLigne::query()->count(),
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new BudgetImportTemplateExport, 'modele-import-budget.xlsx');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        $headings = ['Section', 'Niveau', 'Categorie', 'Code', 'Code parent', 'Libelle', 'Montant total', 'Montant CEEAC', 'Montant PTF', 'Source'];
        $examples = [
            ['recette', 'titre', 'recette_interne', 'R-INT', '', 'RECETTES INTERNES', 26275514803, 26275514803, 0, 'Budget 2026'],
            ['depense', 'titre', 'fonctionnement', 'D-FONC', '', 'DEPENSES DE FONCTIONNEMENT', 13677514803, 13677514803, 0, 'Budget 2026'],
            ['depense', 'ligne', 'personnel', '66101', '6610', 'Salaire de base des fonctionnaires', 5377302285, 5377302285, 0, 'Budget 2026'],
        ];

        return response()->streamDownload(function () use ($headings, $examples): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $headings);
            foreach ($examples as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'modele-import-budget.csv', ['Content-Type' => 'text/csv']);
    }

    public function simulate(StoreBudgetImportRequest $request): View
    {
        [$rows, $meta] = $this->resolveRowsAndMeta($request);
        $exercice = ExerciceBudgetaire::query()->findOrFail($request->integer('exercice_budgetaire_id'));

        $import = $this->service->run($rows, $exercice, 'simulation', $request->user(), $meta);

        return view('budget.nomenclature.result', ['import' => $import, 'simulation' => true]);
    }

    public function store(StoreBudgetImportRequest $request): View
    {
        [$rows, $meta] = $this->resolveRowsAndMeta($request);
        $exercice = ExerciceBudgetaire::query()->findOrFail($request->integer('exercice_budgetaire_id'));

        $import = $this->service->run($rows, $exercice, (string) $request->input('mode'), $request->user(), $meta);

        return view('budget.nomenclature.result', ['import' => $import, 'simulation' => false]);
    }

    public function history(): View
    {
        return view('budget.nomenclature.history', [
            'imports' => BudgetImport::query()->with('user', 'exerciceBudgetaire')->latest()->paginate(20),
        ]);
    }

    public function show(BudgetImport $budgetImport): View
    {
        $budgetImport->load('user', 'exerciceBudgetaire', 'restoredFrom');

        return view('budget.nomenclature.show', ['import' => $budgetImport]);
    }

    public function synthese(Request $request, BudgetReportService $report): View
    {
        $exercice = $this->resolveExercice($request);
        $lignes = $exercice
            ? BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->where('niveau', 'titre')->orderBy('ordre')->get()
            : collect();

        $recettes = $lignes->where('section', 'recette');
        $depenses = $lignes->where('section', 'depense');
        $papLigne = $exercice
            ? BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->where('categorie', 'pap')->first()
            : null;
        $papTotal = $exercice
            ? (float) PapLigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->where('niveau', 'pilier')->sum('montant_total')
            : 0.0;

        return view('budget.nomenclature.synthese', [
            'exercice' => $exercice,
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'recettes' => $recettes,
            'depenses' => $depenses,
            'totalRecettes' => $recettes->sum('montant_total'),
            'totalDepenses' => $depenses->sum('montant_total'),
            'totalCeeac' => $depenses->sum('montant_ceeac'),
            'totalPtf' => $depenses->sum('montant_ptf'),
            'papLigne' => $papLigne,
            'papTotal' => $papTotal,
            'execution' => $exercice ? $report->synthese($exercice) : null,
            'executionParCategorie' => $exercice ? $report->executionParCategorie($exercice) : collect(),
            'alertes' => $exercice ? $report->alertesActives($exercice) : collect(),
        ]);
    }

    public function consolide(Request $request, PapBudgetService $pap, BudgetReportService $report): View
    {
        $exercice = $this->resolveExercice($request);
        $data = $this->consolideData($exercice, $pap, $report);

        return view('budget.nomenclature.consolide', array_merge($data, [
            'exercice' => $exercice,
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
        ]));
    }

    public function exportConsolideExcel(Request $request, PapBudgetService $pap, BudgetReportService $report): BinaryFileResponse
    {
        $exercice = $this->resolveExercice($request);
        abort_if($exercice === null, 404);
        $data = $this->consolideData($exercice, $pap, $report);

        return Excel::download(
            new BudgetConsolidatedExport($this->consolideSynthese($data), $data['consolide']->all(), $exercice->annee),
            "etat-consolide-pap-budget-{$exercice->annee}.xlsx"
        );
    }

    public function exportConsolidePdf(Request $request, PapBudgetService $pap, BudgetReportService $report): Response
    {
        $exercice = $this->resolveExercice($request);
        abort_if($exercice === null, 404);
        $data = $this->consolideData($exercice, $pap, $report);

        $pdf = Pdf::loadView('budget.nomenclature.consolide_pdf', [
            'exercice' => $exercice,
            'synthese' => $this->consolideSynthese($data),
            'piliers' => $data['consolide'],
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("etat-consolide-pap-budget-{$exercice->annee}.pdf");
    }

    /**
     * @return array<string, mixed>
     */
    private function consolideData(?ExerciceBudgetaire $exercice, PapBudgetService $pap, BudgetReportService $report): array
    {
        return [
            'papTotal' => $exercice
                ? (float) PapLigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->where('niveau', 'pilier')->sum('montant_total')
                : 0.0,
            'papLigneBudget' => $exercice
                ? BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->where('categorie', 'pap')->first()
                : null,
            'totalDepenses' => $exercice
                ? (float) BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->where('section', 'depense')->where('niveau', 'titre')->sum('montant_total')
                : 0.0,
            'consolide' => $exercice ? $pap->consolideParPilier($exercice) : collect(),
            'execution' => $exercice ? $report->synthese($exercice) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function consolideSynthese(array $data): array
    {
        return [
            'papTotal' => (float) $data['papTotal'],
            'papLigneBudget' => (float) ($data['papLigneBudget']?->montant_total ?? 0),
            'totalDepenses' => (float) $data['totalDepenses'],
            'creditsOuverts' => (float) ($data['execution']['credits_ouverts'] ?? 0),
            'engage' => (float) ($data['execution']['montant_engage'] ?? 0),
            'paye' => (float) ($data['execution']['montant_paye'] ?? 0),
            'tauxEngagement' => (float) ($data['execution']['taux_engagement'] ?? 0),
        ];
    }

    public function exportExecutionExcel(Request $request, BudgetReportService $report): BinaryFileResponse
    {
        $exercice = $this->resolveExercice($request);
        abort_if($exercice === null, 404);

        return Excel::download(
            new BudgetExecutionExport(
                $report->synthese($exercice),
                $report->executionParCategorie($exercice)->all(),
                $report->executionLignes($exercice)->all(),
                $exercice->annee,
            ),
            "execution-budget-{$exercice->annee}.xlsx"
        );
    }

    public function exportExecutionPdf(Request $request, BudgetReportService $report): Response
    {
        $exercice = $this->resolveExercice($request);
        abort_if($exercice === null, 404);

        $pdf = Pdf::loadView('budget.nomenclature.execution_pdf', [
            'exercice' => $exercice,
            'synthese' => $report->synthese($exercice),
            'categories' => $report->executionParCategorie($exercice),
            'lignes' => $report->executionLignes($exercice),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("execution-budget-{$exercice->annee}.pdf");
    }

    public function restore(BudgetImport $budgetImport): RedirectResponse
    {
        if ($budgetImport->mode !== 'remplacement' || ($budgetImport->snapshot === null || $budgetImport->snapshot === '')) {
            return back()->with('error', __('Cet import ne dispose pas de sauvegarde restaurable.'));
        }

        $restore = $this->service->restore($budgetImport, request()->user());

        return redirect()
            ->route('budget.nomenclature.show', $restore)
            ->with('status', __('Donnees budgetaires restaurees a partir de l\'import #:id.', ['id' => $budgetImport->id]));
    }

    private function resolveExercice(Request $request): ?ExerciceBudgetaire
    {
        $id = $request->integer('exercice_budgetaire_id');

        return ($id > 0 ? ExerciceBudgetaire::query()->find($id) : null)
            ?? ExerciceBudgetaire::query()->whereHas('budgetLignes')->orderByDesc('annee')->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, mixed>}
     */
    private function resolveRowsAndMeta(StoreBudgetImportRequest $request): array
    {
        $meta = [
            'version' => (string) ($request->input('version') ?: '1.0'),
            'adresse_ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        if ($request->input('source') === 'fichier' && $request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $checksum = hash_file('sha256', $file->getRealPath());
            $path = $file->store('budget-imports');

            $meta = array_merge($meta, [
                'fichier_original' => $file->getClientOriginalName(),
                'chemin_fichier' => $path,
                'checksum' => $checksum,
                'taille' => $file->getSize(),
                'source_label' => 'Fichier importe : '.$file->getClientOriginalName(),
            ]);

            $rawRows = Excel::toArray([], Storage::path($path))[0] ?? [];

            return [$this->service->parseSpreadsheet($rawRows), $meta];
        }

        $meta['source_label'] = 'Document officiel Budget 2026';

        return [$this->service->rowsFromDataset(), $meta];
    }
}
