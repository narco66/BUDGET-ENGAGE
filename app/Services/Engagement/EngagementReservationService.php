<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\EngagementReservationBudgetaire;
use Illuminate\Support\Facades\DB;

/**
 * Registre explicite des réservations de crédit. La source de vérité reste
 * `lignes_budgetaires.montant_engage` (gérée par EngagementWorkflowService) ;
 * ce service ne fait qu'enregistrer la traçabilité détaillée, déclenché par
 * les événements d'approbation et d'annulation (aucune écriture budgétaire).
 */
class EngagementReservationService
{
    /**
     * Enregistre la réservation de crédit d'un engagement approuvé (idempotent :
     * ne fait rien si une réservation active existe déjà).
     */
    public function recordForEngagement(Engagement $engagement): void
    {
        if ($engagement->reservationsBudgetaires()->where('sens', 'reservation')->where('statut', 'active')->exists()) {
            return;
        }

        $engagement->loadMissing('imputations');

        $lignes = $engagement->imputations->isNotEmpty()
            ? $engagement->imputations->map(fn ($i) => ['ligne_budgetaire_id' => $i->ligne_budgetaire_id, 'montant_impute' => $i->montant_impute])
            : collect([['ligne_budgetaire_id' => $engagement->ligne_budgetaire_id, 'montant_impute' => $engagement->montant]]);

        DB::transaction(function () use ($engagement, $lignes): void {
            foreach ($lignes as $ligne) {
                if ($ligne['ligne_budgetaire_id'] === null) {
                    continue;
                }
                EngagementReservationBudgetaire::query()->create([
                    'engagement_id' => $engagement->id,
                    'ligne_budgetaire_id' => $ligne['ligne_budgetaire_id'],
                    'exercice_budgetaire_id' => $engagement->exercice_budgetaire_id,
                    'montant_reserve' => $ligne['montant_impute'],
                    'sens' => 'reservation',
                    'statut' => 'active',
                    'date_reservation' => now(),
                ]);
            }
        });
    }

    /**
     * Libère les réservations actives d'un engagement (annulation).
     */
    public function releaseForEngagement(Engagement $engagement): void
    {
        $actives = $engagement->reservationsBudgetaires()
            ->where('sens', 'reservation')->where('statut', 'active')->get();

        if ($actives->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($engagement, $actives): void {
            foreach ($actives as $reservation) {
                $reservation->update(['statut' => 'liberee']);

                EngagementReservationBudgetaire::query()->create([
                    'engagement_id' => $engagement->id,
                    'ligne_budgetaire_id' => $reservation->ligne_budgetaire_id,
                    'exercice_budgetaire_id' => $engagement->exercice_budgetaire_id,
                    'montant_reserve' => $reservation->montant_reserve,
                    'sens' => 'liberation',
                    'statut' => 'liberee',
                    'date_reservation' => now(),
                ]);
            }
        });
    }
}
