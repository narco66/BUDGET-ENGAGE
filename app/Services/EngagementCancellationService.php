<?php

namespace App\Services;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EngagementCancellationService
{
    /**
     * Annule un engagement encore au statut « approuvé », sans aucune liquidation :
     * rollback du montant engagé sur la ligne budgétaire.
     */
    public function cancelApproved(Engagement $engagement, string $motif, User $user): void
    {
        DB::transaction(function () use ($engagement, $motif, $user): void {
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();
            $engagement->refresh();

            if ($engagement->statut !== StatutEngagement::Approuve) {
                throw ValidationException::withMessages([
                    'engagement' => __('Seuls les engagements au statut « approuvé » (sans exécution) peuvent être annulés ainsi.'),
                ]);
            }

            if ($engagement->liquidations()->exists()) {
                throw ValidationException::withMessages([
                    'engagement' => __('Impossible d’annuler : des liquidations existent déjà. Traitez d’abord l’exécution ou utilisez un flux métier d’annulation adapté.'),
                ]);
            }

            $numero = $engagement->numero;
            $engagementId = $engagement->id;

            $ligne = LigneBudgetaire::query()
                ->whereKey($engagement->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $nouveauMontantEngage = bcsub((string) $ligne->montant_engage, (string) $engagement->montant, 2);

            if (bccomp($nouveauMontantEngage, '0', 2) < 0) {
                throw ValidationException::withMessages([
                    'engagement' => __('Incohérence : le montant engagé sur la ligne est insuffisant pour annuler cet engagement.'),
                ]);
            }

            $ligne->update([
                'montant_engage' => $nouveauMontantEngage,
            ]);

            $engagement->update([
                'statut' => StatutEngagement::Annule,
                'cancelled_at' => now(),
                'motif_annulation' => $motif,
            ]);

            DB::afterCommit(function () use ($user, $engagementId, $numero, $motif): void {
                BudgetJournal::record($user, __('journal.cancellation.approved_engagement', ['numero' => $numero]), Engagement::find($engagementId), [
                    'engagement_id' => $engagementId,
                    'motif_annulation' => $motif,
                ]);
            });
        });
    }
}
