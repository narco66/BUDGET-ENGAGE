<?php

namespace App\Http\Controllers\Reports;

use App\Enums\StatutEngagement;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutionReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeReportAccess($request);

        $limitedScope = $this->usesLimitedScope($request->user());

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        $base = Engagement::query()
            ->whereIn('statut', [
                StatutEngagement::Approuve,
                StatutEngagement::Liquide,
                StatutEngagement::Ordonnance,
                StatutEngagement::Paye,
                StatutEngagement::Cloture,
            ]);

        if ($limitedScope) {
            $base->where('created_by', $request->user()->id);
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $base->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $countEngagements = (clone $base)->count();
        $sumMontantEngagements = (clone $base)->sum('montant');

        $sumLiquide = Liquidation::query()
            ->whereHas('engagement', function ($q) use ($base): void {
                $q->whereIn('id', (clone $base)->select('id'));
            })
            ->sum('montant_liquide');

        $sumOrdonnance = Ordonnancement::query()
            ->whereHas('liquidation.engagement', function ($q) use ($base): void {
                $q->whereIn('id', (clone $base)->select('id'));
            })
            ->sum('montant_ordonnance');

        $sumPaye = Paiement::query()
            ->whereHas('ordonnancement.liquidation.engagement', function ($q) use ($base): void {
                $q->whereIn('id', (clone $base)->select('id'));
            })
            ->sum('montant_paye');

        $engagements = (clone $base)
            ->with([
                'exerciceBudgetaire',
                'ligneBudgetaire',
                'createur',
                'liquidations.ordonnancements.paiements',
            ])
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        foreach ($engagements as $engagement) {
            $tl = '0';
            $to = '0';
            $tp = '0';
            foreach ($engagement->liquidations as $liq) {
                $tl = bcadd($tl, (string) $liq->montant_liquide, 2);
                foreach ($liq->ordonnancements as $ord) {
                    $to = bcadd($to, (string) $ord->montant_ordonnance, 2);
                    foreach ($ord->paiements as $pay) {
                        $tp = bcadd($tp, (string) $pay->montant_paye, 2);
                    }
                }
            }
            $engagement->report_total_liquide = $tl;
            $engagement->report_total_ordonnance = $to;
            $engagement->report_total_paye = $tp;
        }

        return view('reports.execution.index', [
            'exercices' => $exercices,
            'engagements' => $engagements,
            'countEngagements' => $countEngagements,
            'sumMontantEngagements' => $sumMontantEngagements,
            'sumLiquide' => $sumLiquide,
            'sumOrdonnance' => $sumOrdonnance,
            'sumPaye' => $sumPaye,
            'limitedScope' => $limitedScope,
            'filters' => [
                'exercice_budgetaire_id' => $request->input('exercice_budgetaire_id'),
            ],
        ]);
    }

    protected function authorizeReportAccess(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user->can('reports.view')
                || $user->can('reports.view_limited')
                || $user->can('exercises.manage')
                || $user->can('audit.view'),
            403
        );
    }

    protected function usesLimitedScope(?\Illuminate\Contracts\Auth\Authenticatable $user): bool
    {
        if (! $user instanceof \App\Models\User) {
            return false;
        }

        if ($user->can('reports.view') || $user->can('exercises.manage') || $user->can('audit.view')) {
            return false;
        }

        return $user->can('reports.view_limited');
    }
}
