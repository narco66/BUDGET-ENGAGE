<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreExerciceBudgetaireRequest;
use App\Http\Requests\Budget\UpdateExerciceBudgetaireRequest;
use App\Models\ExerciceBudgetaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExerciceBudgetaireController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ExerciceBudgetaire::class, 'exercice_budgetaire');
    }

    public function index(): View
    {
        $exercices = ExerciceBudgetaire::query()
            ->withCount('lignesBudgetaires')
            ->orderByDesc('annee')
            ->paginate(15);

        return view('budget.exercices.index', compact('exercices'));
    }

    public function create(): View
    {
        return view('budget.exercices.create');
    }

    public function store(StoreExerciceBudgetaireRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['est_actif'] = $request->boolean('est_actif');

        ExerciceBudgetaire::query()->create($data);

        return redirect()->route('budget.exercices.index')->with('status', __('Exercice budgétaire créé.'));
    }

    public function show(ExerciceBudgetaire $exercice_budgetaire): View
    {
        $exercice_budgetaire->loadCount('lignesBudgetaires');

        return view('budget.exercices.show', compact('exercice_budgetaire'));
    }

    public function edit(ExerciceBudgetaire $exercice_budgetaire): View
    {
        return view('budget.exercices.edit', compact('exercice_budgetaire'));
    }

    public function update(UpdateExerciceBudgetaireRequest $request, ExerciceBudgetaire $exercice_budgetaire): RedirectResponse
    {
        $data = $request->validated();
        $data['est_actif'] = $request->boolean('est_actif');

        $exercice_budgetaire->update($data);

        return redirect()->route('budget.exercices.index')->with('status', __('Exercice budgétaire mis à jour.'));
    }

    public function destroy(ExerciceBudgetaire $exercice_budgetaire): RedirectResponse
    {
        $exercice_budgetaire->delete();

        return redirect()->route('budget.exercices.index')->with('status', __('Exercice budgétaire supprimé.'));
    }
}
