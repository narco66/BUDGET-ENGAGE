<?php

namespace App\Http\Controllers;

use App\Enums\StatutEngagement;
use App\Enums\StatutRegularisation;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $exercice = ExerciceBudgetaire::query()
            ->where('est_actif', true)
            ->orderByDesc('annee')
            ->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();

        $budgetRevise = '0';
        $engage = '0';
        $paye = '0';

        if ($exercice) {
            $agg = LigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->selectRaw('COALESCE(SUM(montant_revise), 0) as r, COALESCE(SUM(montant_engage), 0) as e, COALESCE(SUM(montant_paye), 0) as p')
                ->first();
            $budgetRevise = (string) $agg->r;
            $engage = (string) $agg->e;
            $paye = (string) $agg->p;
        }

        $disponible = bcsub($budgetRevise, $engage, 2);

        $recentEngagements = Engagement::query()
            ->with(['exerciceBudgetaire', 'ligneBudgetaire', 'createur'])
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $alertsEnValidation = Engagement::query()
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->where('statut', StatutEngagement::EnValidation)
            ->count();

        $urgencesPending = Engagement::query()
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->where('is_urgence', true)
            ->where(function ($q): void {
                $q->whereNull('statut_regularisation')
                    ->orWhereNotIn('statut_regularisation', [StatutRegularisation::Regularise]);
            })
            ->count();

        $chartMonths = [];
        $start = now()->subMonths(5)->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $chartMonths[] = [
                'label' => $month->translatedFormat('M'),
                'count' => Engagement::query()
                    ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count(),
            ];
        }

        $maxChart = max(array_column($chartMonths, 'count')) ?: 1;

        return view('dashboard', compact(
            'exercice',
            'budgetRevise',
            'engage',
            'paye',
            'disponible',
            'recentEngagements',
            'alertsEnValidation',
            'urgencesPending',
            'chartMonths',
            'maxChart'
        ));
    }
}
