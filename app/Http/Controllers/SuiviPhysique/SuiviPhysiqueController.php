<?php

namespace App\Http\Controllers\SuiviPhysique;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuiviPhysique\StoreSuiviPhysiqueRealisationRequest;
use App\Models\Activite;
use App\Models\ExerciceBudgetaire;
use App\Models\SuiviPhysiqueRealisation;
use App\Services\SuiviPhysique\SuiviPhysiqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuiviPhysiqueController extends Controller
{
    public function __construct(private readonly SuiviPhysiqueService $suivi)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SuiviPhysiqueRealisation::class);

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exercice = $exercices->firstWhere('id', (int) $request->input('exercice_budgetaire_id'))
            ?? $exercices->firstWhere('est_actif', true)
            ?? $exercices->first();

        $trimestre = $request->filled('trimestre') ? (int) $request->input('trimestre') : null;
        $synthese = $exercice ? $this->suivi->syntheseExercice($exercice) : null;
        $parActivite = $exercice ? $this->suivi->parActivite($exercice, $trimestre) : collect();

        $realisations = $exercice
            ? SuiviPhysiqueRealisation::query()
                ->with(['activite', 'createur'])
                ->where('exercice_budgetaire_id', $exercice->id)
                ->when($trimestre, fn ($q) => $q->where('trimestre', $trimestre))
                ->latest('periode_fin')
                ->limit(50)
                ->get()
            : collect();

        return view('suivi-physique.index', compact('exercices', 'exercice', 'trimestre', 'synthese', 'parActivite', 'realisations'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SuiviPhysiqueRealisation::class);

        return view('suivi-physique.create', [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'activites' => Activite::query()->where('statut', 'actif')->orderBy('code')->get(),
            'exerciceId' => (int) ($request->input('exercice_budgetaire_id') ?: ExerciceBudgetaire::query()->where('est_actif', true)->value('id')),
        ]);
    }

    public function store(StoreSuiviPhysiqueRealisationRequest $request): RedirectResponse
    {
        $realisation = $this->suivi->enregistrer($request->validated(), $request->user());

        return redirect()
            ->route('suivi-physique.show', $realisation)
            ->with('status', __('Réalisation physique enregistrée.'));
    }

    public function show(SuiviPhysiqueRealisation $suivi_physique): View
    {
        $this->authorize('view', $suivi_physique);
        $suivi_physique->load(['activite', 'exerciceBudgetaire', 'createur', 'validateur']);

        return view('suivi-physique.show', ['realisation' => $suivi_physique]);
    }

    public function validateRealisation(Request $request, SuiviPhysiqueRealisation $suivi_physique): RedirectResponse
    {
        $this->authorize('validate', $suivi_physique);
        $this->suivi->valider($suivi_physique, $request->user());

        return redirect()
            ->route('suivi-physique.show', $suivi_physique)
            ->with('status', __('Réalisation validée.'));
    }
}
