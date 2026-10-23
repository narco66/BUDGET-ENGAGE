<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\AlerteBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Services\Budget\BudgetAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetAlerteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:budget.alertes');
    }

    public function historique(Request $request): View
    {
        $exercice = $this->resolveExercice($request);

        return view('budget.alertes.historique', [
            'exercice' => $exercice,
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'alertes' => $exercice
                ? AlerteBudgetaire::query()->with('ligneBudgetaire', 'traitePar')
                    ->where('exercice_budgetaire_id', $exercice->id)
                    ->whereIn('statut', ['traitee', 'resolue'])
                    ->latest('updated_at')->paginate(30)
                : AlerteBudgetaire::query()->whereRaw('1 = 0')->paginate(30),
        ]);
    }

    public function traiter(Request $request, AlerteBudgetaire $alerte, BudgetAlertService $service): RedirectResponse
    {
        $commentaire = $request->validate(['commentaire' => ['nullable', 'string', 'max:1000']])['commentaire'] ?? null;
        $service->traiter($alerte, $request->user(), $commentaire);

        return back()->with('status', __('Alerte de la ligne :code marquée comme traitée.', ['code' => $alerte->code]));
    }

    public function restaurer(Request $request, AlerteBudgetaire $alerte, BudgetAlertService $service): RedirectResponse
    {
        $service->restaurer($alerte, $request->user());

        return back()->with('status', __('Alerte de la ligne :code restaurée (rouverte).', ['code' => $alerte->code]));
    }

    private function resolveExercice(Request $request): ?ExerciceBudgetaire
    {
        $id = $request->integer('exercice_budgetaire_id');

        return ($id > 0 ? ExerciceBudgetaire::query()->find($id) : null)
            ?? ExerciceBudgetaire::query()->where('est_actif', true)->orderByDesc('annee')->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();
    }
}
