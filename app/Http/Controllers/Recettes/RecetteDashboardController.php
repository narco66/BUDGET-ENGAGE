<?php

namespace App\Http\Controllers\Recettes;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\RecettePerception;
use App\Services\Recettes\RecetteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecetteDashboardController extends Controller
{
    public function __construct(private readonly RecetteService $recettes)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RecettePerception::class);

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exercice = $this->resolveExercice($request, $exercices);
        $synthese = $exercice ? $this->recettes->syntheseExercice($exercice) : null;
        $rapprochement = $exercice ? $this->recettes->rapprochement($exercice) : collect();

        $perceptions = $exercice
            ? RecettePerception::query()
                ->with(['budgetLigne', 'createur'])
                ->where('exercice_budgetaire_id', $exercice->id)
                ->latest('date_perception')
                ->limit(50)
                ->get()
            : collect();

        return view('recettes.dashboard', compact('exercices', 'exercice', 'synthese', 'rapprochement', 'perceptions'));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ExerciceBudgetaire>  $exercices
     */
    private function resolveExercice(Request $request, $exercices): ?ExerciceBudgetaire
    {
        if ($request->filled('exercice_budgetaire_id')) {
            return $exercices->firstWhere('id', (int) $request->input('exercice_budgetaire_id'));
        }

        return $exercices->firstWhere('est_actif', true) ?? $exercices->first();
    }
}
