<?php

namespace App\Http\Controllers\Audit;

use App\Enums\StatutEngagement;
use App\Enums\StatutRegularisation;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditOverviewController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('audit.view') || $request->user()->can('audit.alerts.manage'),
            403
        );

        $exercice = ExerciceBudgetaire::query()
            ->where('est_actif', true)
            ->orderByDesc('annee')
            ->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();

        $riskThreshold = 60;

        $urgences = Engagement::query()
            ->with(['exerciceBudgetaire', 'ligneBudgetaire'])
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->where('is_urgence', true)
            ->where(function ($q): void {
                $q->whereNull('statut_regularisation')
                    ->orWhereNotIn('statut_regularisation', [StatutRegularisation::Regularise]);
            })
            ->latest('updated_at')
            ->limit(25)
            ->get();

        $highRisk = Engagement::query()
            ->with(['exerciceBudgetaire', 'ligneBudgetaire'])
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->whereNotNull('score_risque')
            ->where('score_risque', '>=', $riskThreshold)
            ->orderByDesc('score_risque')
            ->limit(25)
            ->get();

        $blockedValidation = Engagement::query()
            ->with(['exerciceBudgetaire'])
            ->when($exercice, fn ($q) => $q->where('exercice_budgetaire_id', $exercice->id))
            ->where('statut', StatutEngagement::EnValidation)
            ->orderBy('updated_at')
            ->limit(15)
            ->get();

        return view('audit.index', compact(
            'exercice',
            'urgences',
            'highRisk',
            'blockedValidation',
            'riskThreshold'
        ));
    }
}
