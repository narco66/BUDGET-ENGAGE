<?php

namespace App\Http\Controllers\Rbm;

use App\Exports\PapImportTemplateExport;
use App\Exports\PapProgrammationExport;
use App\Exports\PapSourceFinancementExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rbm\StorePapImportRequest;
use App\Models\ExerciceBudgetaire;
use App\Models\PapImport;
use App\Models\PapLigneBudgetaire;
use App\Services\Rbm\PapBudgetService;
use App\Services\Rbm\PapImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PapImportController extends Controller
{
    public function __construct(private PapImportService $service)
    {
        $this->middleware('permission:pap.view|pap.import|pap.replace|rbm.admin');
        $this->middleware('permission:pap.import|pap.replace|rbm.admin')->only(['simulate', 'store']);
        $this->middleware('permission:pap.restore|rbm.admin')->only('restore');
    }

    public function index(): View
    {
        return view('rbm.pap-import.index', [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'modes' => PapImportService::MODES,
            'imports' => PapImport::query()->with('user', 'exerciceBudgetaire')->latest()->limit(10)->get(),
            'lignesActuelles' => PapLigneBudgetaire::query()->count(),
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new PapImportTemplateExport, 'modele-import-pap.xlsx');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        $headings = ['Code', 'Libelle', 'Niveau', 'Code parent', 'Montant total', 'Montant CEEAC', 'Montant PTF', 'Source'];
        $examples = [
            ['201', 'PILIER 1 - INTEGRATION POLITIQUE, PAIX ET SECURITE', 'pilier', '', 1750000000, 1750000000, 0, 'PAP 2026'],
            ['2011', 'AXE 1 : RENFORCER LA GOUVERNANCE POLITIQUE', 'axe', '201', 655000000, 655000000, 0, 'PAP 2026'],
            ['20111', 'Fonctionnement du Comite des Sages', 'produit', '2011', 150000000, 150000000, 0, 'PAP 2026'],
        ];

        return response()->streamDownload(function () use ($headings, $examples): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $headings);
            foreach ($examples as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'modele-import-pap.csv', ['Content-Type' => 'text/csv']);
    }

    public function simulate(StorePapImportRequest $request): View
    {
        [$rows, $meta] = $this->resolveRowsAndMeta($request);
        $exercice = ExerciceBudgetaire::query()->findOrFail($request->integer('exercice_budgetaire_id'));

        $import = $this->service->run($rows, $exercice, 'simulation', $request->user(), $meta);

        return view('rbm.pap-import.result', ['import' => $import, 'simulation' => true]);
    }

    public function store(StorePapImportRequest $request): View
    {
        [$rows, $meta] = $this->resolveRowsAndMeta($request);
        $exercice = ExerciceBudgetaire::query()->findOrFail($request->integer('exercice_budgetaire_id'));

        $import = $this->service->run($rows, $exercice, (string) $request->input('mode'), $request->user(), $meta);

        return view('rbm.pap-import.result', ['import' => $import, 'simulation' => false]);
    }

    public function programmation(Request $request, PapBudgetService $budget): View
    {
        $exercice = $this->resolveProgrammationExercice($request);

        $niveau = (string) $request->input('niveau', 'tous');
        if (! in_array($niveau, ['tous', 'pilier', 'axe', 'produit'], true)) {
            $niveau = 'tous';
        }

        $source = (string) $request->input('source', 'tous');
        if (! in_array($source, ['tous', 'ceeac', 'ptf', 'mixte'], true)) {
            $source = 'tous';
        }

        $afficheListe = $niveau !== 'tous' || $source !== 'tous';

        $synthese = $exercice ? $budget->synthese($exercice) : null;
        $hierarchie = $exercice && ! $afficheListe ? $budget->programmationHierarchie($exercice) : collect();

        $lignesFiltrees = collect();
        if ($exercice && $afficheListe) {
            $niveauLabel = ['pilier' => 'Pilier', 'axe' => 'Axe', 'produit' => 'Produit'];
            $sourceLabel = ['ceeac' => 'CEEAC', 'ptf' => 'PTF', 'mixte' => 'Mixte'];

            $lignesFiltrees = collect($budget->programmationFlat($exercice))
                ->when($niveau !== 'tous', fn ($c) => $c->where('niveau', $niveauLabel[$niveau]))
                ->when($source !== 'tous', fn ($c) => $c->where('source', $sourceLabel[$source]))
                ->values();
        }

        return view('rbm.pap-import.programmation', [
            'exercice' => $exercice,
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'synthese' => $synthese,
            'hierarchie' => $hierarchie,
            'niveau' => $niveau,
            'source' => $source,
            'afficheListe' => $afficheListe,
            'lignesFiltrees' => $lignesFiltrees,
        ]);
    }

    public function exportProgrammationExcel(Request $request, PapBudgetService $budget): BinaryFileResponse
    {
        $exercice = $this->resolveProgrammationExercice($request);
        abort_if($exercice === null, 404);

        return Excel::download(
            new PapProgrammationExport(
                $budget->programmationFlat($exercice),
                $budget->synthese($exercice),
                $exercice->annee,
            ),
            "programmation-pap-{$exercice->annee}.xlsx"
        );
    }

    public function exportProgrammationPdf(Request $request, PapBudgetService $budget): Response
    {
        $exercice = $this->resolveProgrammationExercice($request);
        abort_if($exercice === null, 404);

        $pdf = Pdf::loadView('rbm.pap-import.programmation_pdf', [
            'exercice' => $exercice,
            'synthese' => $budget->synthese($exercice),
            'hierarchie' => $budget->programmationHierarchie($exercice),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("programmation-pap-{$exercice->annee}.pdf");
    }

    public function exportSourceFinancement(Request $request, PapBudgetService $budget): BinaryFileResponse
    {
        $exercice = $this->resolveProgrammationExercice($request);
        abort_if($exercice === null, 404);

        return Excel::download(
            new PapSourceFinancementExport(
                $budget->programmationFlat($exercice),
                $budget->synthese($exercice),
                $exercice->annee,
            ),
            "repartition-source-financement-pap-{$exercice->annee}.xlsx"
        );
    }

    private function resolveProgrammationExercice(Request $request): ?ExerciceBudgetaire
    {
        $exerciceId = $request->integer('exercice_budgetaire_id');

        return ($exerciceId > 0 ? ExerciceBudgetaire::query()->find($exerciceId) : null)
            ?? ExerciceBudgetaire::query()->whereHas('papLignesBudgetaires')->orderByDesc('annee')->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();
    }

    public function history(): View
    {
        return view('rbm.pap-import.history', [
            'imports' => PapImport::query()->with('user', 'exerciceBudgetaire')->latest()->paginate(20),
        ]);
    }

    public function show(PapImport $papImport): View
    {
        $papImport->load('user', 'exerciceBudgetaire', 'cadreLogique', 'restoredFrom');

        return view('rbm.pap-import.show', ['import' => $papImport]);
    }

    public function restore(PapImport $papImport): RedirectResponse
    {
        if ($papImport->mode !== 'remplacement' || ($papImport->snapshot === null || $papImport->snapshot === '')) {
            return back()->with('error', __('Cet import ne dispose pas de sauvegarde restaurable.'));
        }

        $restore = $this->service->restore($papImport, request()->user());

        return redirect()
            ->route('rbm.pap-import.show', $restore)
            ->with('status', __('Donnees PAP restaurees a partir de l\'import #:id.', ['id' => $papImport->id]));
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, mixed>}
     */
    private function resolveRowsAndMeta(StorePapImportRequest $request): array
    {
        $meta = [
            'version' => (string) ($request->input('version') ?: '1.0'),
            'adresse_ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ];

        if ($request->input('source') === 'fichier' && $request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $checksum = hash_file('sha256', $file->getRealPath());
            $path = $file->store('pap-imports');

            $meta = array_merge($meta, [
                'fichier_original' => $file->getClientOriginalName(),
                'chemin_fichier' => $path,
                'checksum' => $checksum,
                'taille' => $file->getSize(),
                'source_label' => 'Fichier importe : '.$file->getClientOriginalName(),
            ]);

            $rawRows = Excel::toArray([], Storage::path($path))[0] ?? [];
            $rows = $this->service->parseSpreadsheet($rawRows);

            return [$rows, $meta];
        }

        $meta['source_label'] = 'Document officiel PAP 2026';

        return [$this->service->rowsFromDataset(), $meta];
    }
}
