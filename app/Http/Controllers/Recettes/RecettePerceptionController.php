<?php

namespace App\Http\Controllers\Recettes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recettes\StoreRecettePerceptionRequest;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\RecettePerception;
use App\Services\Recettes\RecetteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecettePerceptionController extends Controller
{
    public function __construct(private readonly RecetteService $recettes)
    {
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RecettePerception::class);

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exerciceId = (int) ($request->input('exercice_budgetaire_id') ?: $exercices->firstWhere('est_actif', true)?->id);
        $lignesRecette = BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exerciceId)
            ->where('section', 'recette')
            ->where('niveau', 'ligne')
            ->orderBy('code')
            ->get(['id', 'code', 'libelle', 'montant_total']);

        return view('recettes.create', compact('exercices', 'exerciceId', 'lignesRecette'));
    }

    public function store(StoreRecettePerceptionRequest $request): RedirectResponse
    {
        $perception = $this->recettes->enregistrer(
            $request->validated(),
            $request->user(),
            $request->boolean('valider') && $request->user()->can('recettes.validate')
        );

        return redirect()
            ->route('recettes.perceptions.show', $perception)
            ->with('status', __('Perception enregistrée.'));
    }

    public function show(RecettePerception $perception): View
    {
        $this->authorize('view', $perception);
        $perception->load(['exerciceBudgetaire', 'budgetLigne', 'createur', 'validateur']);

        return view('recettes.show', compact('perception'));
    }

    public function validatePerception(Request $request, RecettePerception $perception): RedirectResponse
    {
        $this->authorize('validate', $perception);
        $this->recettes->valider($perception, $request->user());

        return redirect()
            ->route('recettes.perceptions.show', $perception)
            ->with('status', __('Perception validée et rapprochée.'));
    }

    public function cancel(Request $request, RecettePerception $perception): RedirectResponse
    {
        $this->authorize('cancel', $perception);
        $request->validate(['motif_annulation' => ['required', 'string', 'max:500']]);
        $this->recettes->annuler($perception, $request->user(), $request->string('motif_annulation')->toString());

        return redirect()
            ->route('recettes.dashboard', ['exercice_budgetaire_id' => $perception->exercice_budgetaire_id])
            ->with('status', __('Perception annulée.'));
    }
}
