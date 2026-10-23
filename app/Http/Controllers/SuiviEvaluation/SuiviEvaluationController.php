<?php

namespace App\Http\Controllers\SuiviEvaluation;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuiviEvaluation\StoreIndicateurRealisationRequest;
use App\Models\ExerciceBudgetaire;
use App\Models\Indicateur;
use App\Models\IndicateurRealisation;
use App\Services\SuiviEvaluation\SuiviEvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuiviEvaluationController extends Controller
{
    public function __construct(private readonly SuiviEvaluationService $suivi)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IndicateurRealisation::class);

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exercice = $exercices->firstWhere('id', (int) $request->input('exercice_budgetaire_id'))
            ?? $exercices->firstWhere('est_actif', true)
            ?? $exercices->first();

        $trimestre = (int) ($request->input('trimestre') ?: now()->quarter);

        $synthese = $exercice ? $this->suivi->syntheseTrimestre($exercice, $trimestre) : null;
        $rapport = $exercice ? $this->suivi->rapportTrimestriel($exercice, $trimestre) : collect();

        return view('suivi-evaluation.index', compact('exercices', 'exercice', 'trimestre', 'synthese', 'rapport'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', IndicateurRealisation::class);

        return view('suivi-evaluation.create', [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'indicateurs' => Indicateur::query()->where('statut', 'actif')->orderBy('code')->get(),
            'exerciceId' => (int) ($request->input('exercice_budgetaire_id') ?: ExerciceBudgetaire::query()->where('est_actif', true)->value('id')),
        ]);
    }

    public function store(StoreIndicateurRealisationRequest $request): RedirectResponse
    {
        $realisation = $this->suivi->enregistrer($request->validated(), $request->user());

        return redirect()
            ->route('suivi-evaluation.show', $realisation)
            ->with('status', __('Réalisation indicateur enregistrée.'));
    }

    public function show(IndicateurRealisation $suivi_evaluation): View
    {
        $this->authorize('view', $suivi_evaluation);
        $suivi_evaluation->load(['indicateur.uniteMesure', 'indicateur.typeIndicateur', 'exerciceBudgetaire', 'createur', 'validateur']);

        return view('suivi-evaluation.show', ['realisation' => $suivi_evaluation]);
    }

    public function validateRealisation(Request $request, IndicateurRealisation $suivi_evaluation): RedirectResponse
    {
        $this->authorize('validate', $suivi_evaluation);
        $this->suivi->valider($suivi_evaluation, $request->user());

        return redirect()
            ->route('suivi-evaluation.show', $suivi_evaluation)
            ->with('status', __('Réalisation validée.'));
    }
}
