<?php

namespace App\Http\Controllers\Engagements;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\EngagementImputation;
use App\Models\EngagementReservationBudgetaire;
use App\Models\LigneBudgetaire;
use App\Policies\EngagementPolicy;
use App\Services\Budget\CreditAvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EngagementDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('engagement.view') || $request->user()?->can('engagements.create'), 403);

        $base = $this->baseQuery($request);
        $engIds = (clone $base)->pluck('id');

        $kpis = [
            'total' => (clone $base)->count(),
            'approuves' => (clone $base)->where('statut', 'approuve')->count(),
            'en_validation' => (clone $base)->where('statut', 'en_validation')->count(),
            'brouillons' => (clone $base)->where('statut', 'brouillon')->count(),
            'rejetes' => (clone $base)->where('statut', 'rejete')->count(),
            'annules' => (clone $base)->where('statut', 'annule')->count(),
            'montant_engage' => (float) (clone $base)->whereIn('statut', ['approuve', 'liquide', 'ordonnance', 'paye'])->sum('montant'),
            'credits_reserves' => (float) EngagementReservationBudgetaire::query()
                ->whereIn('engagement_id', $engIds)->where('sens', 'reservation')->where('statut', 'active')->sum('montant_reserve'),
        ];

        $parStatut = (clone $base)->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut')->toArray();

        $parType = (clone $base)
            ->leftJoin('engagement_types', 'engagement_types.id', '=', 'engagements.engagement_type_id')
            ->selectRaw("COALESCE(engagement_types.libelle, engagements.type_engagement) as libelle, COUNT(*) as total, SUM(engagements.montant) as montant")
            ->groupByRaw('COALESCE(engagement_types.libelle, engagements.type_engagement)')
            ->orderByDesc('montant')->limit(10)->get();

        $parDepartement = (clone $base)
            ->leftJoin('departements', 'departements.id', '=', 'engagements.departement_id')
            ->selectRaw("COALESCE(departements.libelle, 'Non rattaché') as libelle, COUNT(*) as total, SUM(engagements.montant) as montant")
            ->groupByRaw("COALESCE(departements.libelle, 'Non rattaché')")
            ->orderByDesc('montant')->limit(10)->get();

        $parSource = EngagementImputation::query()
            ->whereIn('engagement_id', $engIds)
            ->join('sources_financement', 'sources_financement.id', '=', 'engagement_imputations.source_financement_id')
            ->selectRaw('sources_financement.libelle as libelle, SUM(engagement_imputations.montant_impute) as montant')
            ->groupBy('sources_financement.libelle')->orderByDesc('montant')->limit(10)->get();

        $parLigne = EngagementImputation::query()
            ->whereIn('engagement_id', $engIds)
            ->join('lignes_budgetaires', 'lignes_budgetaires.id', '=', 'engagement_imputations.ligne_budgetaire_id')
            ->selectRaw('lignes_budgetaires.code as code, SUM(engagement_imputations.montant_impute) as montant')
            ->groupBy('lignes_budgetaires.code')->orderByDesc('montant')->limit(10)->get();

        // Alertes budgétaires : lignes dont le taux d'engagement dépasse 90 %.
        $credits = app(CreditAvailabilityService::class);
        $alertes = LigneBudgetaire::query()
            ->with('exerciceBudgetaire')
            ->where('montant_engage', '>', 0)
            ->get()
            ->map(function (LigneBudgetaire $ligne) use ($credits) {
                $plafond = (float) $credits->plafond($ligne);
                $taux = $plafond > 0 ? ((float) $ligne->montant_engage / $plafond) * 100 : 0;

                return [
                    'code' => $ligne->code, 'libelle' => $ligne->libelle, 'annee' => $ligne->exerciceBudgetaire?->annee,
                    'engage' => (float) $ligne->montant_engage, 'plafond' => $plafond, 'taux' => $taux,
                    'disponible' => (float) $credits->disponibleComptable($ligne),
                ];
            })
            ->filter(fn ($l) => $l['taux'] >= 90)
            ->sortByDesc('taux')
            ->take(10)
            ->values();

        return view('engagements.dashboard', compact(
            'kpis', 'parStatut', 'parType', 'parDepartement', 'parSource', 'parLigne', 'alertes'
        ));
    }

    /**
     * @return Builder<Engagement>
     */
    private function baseQuery(Request $request): Builder
    {
        $query = Engagement::query();

        if (! EngagementPolicy::seesAllEngagements($request->user())) {
            $query->where('engagements.created_by', $request->user()->id);
        }

        return $query;
    }
}
