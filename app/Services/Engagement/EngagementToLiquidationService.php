<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\Liquidation;
use App\Models\User;
use App\Policies\EngagementPolicy;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transmission d'un engagement approuvé au module Liquidation.
 *
 * RÉUTILISE EngagementExecutionService::recordLiquidation (aucune duplication de
 * la logique de liquidation ni des contrôles budgétaires) : pour chaque
 * imputation, liquide le montant restant. La double transmission est empêchée
 * par le marqueur `transmis_liquidation_at`. Les pièces GED de l'engagement sont
 * reprises sur les liquidations créées.
 */
class EngagementToLiquidationService
{
    public function __construct(
        private readonly EngagementExecutionService $execution,
        private readonly EngagementAuditService $audit,
    ) {
    }

    /**
     * @return array<int, Liquidation>
     */
    public function transmit(Engagement $engagement, User $user): array
    {
        if ($engagement->transmis_liquidation_at !== null) {
            throw ValidationException::withMessages(['transmission' => __('Cet engagement a déjà été transmis à la liquidation.')]);
        }

        if (! EngagementPolicy::allowsExecutionRecording($engagement)) {
            throw ValidationException::withMessages(['transmission' => __('Seul un engagement approuvé peut être transmis à la liquidation.')]);
        }

        $engagement->loadMissing(['imputations', 'gedAttachments']);

        if ($engagement->imputations->isEmpty()) {
            throw ValidationException::withMessages(['transmission' => __('L’engagement ne comporte aucune imputation à transmettre.')]);
        }

        return DB::transaction(function () use ($engagement, $user): array {
            $created = [];

            foreach ($engagement->imputations as $imputation) {
                $sumExisting = (string) Liquidation::query()
                    ->where('engagement_imputation_id', $imputation->id)->sum('montant_liquide');
                $remaining = bcsub((string) $imputation->montant_impute, $sumExisting, 2);

                if (bccomp($remaining, '0', 2) <= 0) {
                    continue;
                }

                $liquidation = $this->execution->recordLiquidation($engagement->fresh(), [
                    'engagement_imputation_id' => $imputation->id,
                    'montant_liquide' => $remaining,
                    'date_liquidation' => now()->toDateString(),
                    'reference' => 'Transmission '.$engagement->numero,
                ], $user);

                // Reprise des pièces GED de l'engagement sur la liquidation.
                foreach ($engagement->gedAttachments as $attachment) {
                    $liquidation->gedAttachments()->firstOrCreate(
                        ['ged_document_id' => $attachment->ged_document_id],
                        ['role' => 'piece_engagement', 'created_by' => $user->id],
                    );
                }

                $created[] = $liquidation;
            }

            $engagement->update([
                'transmis_liquidation_at' => now(),
                'transmis_par' => $user->id,
            ]);

            BudgetJournal::record($user, "Transmission de l’engagement {$engagement->numero} à la liquidation.", $engagement, [
                'engagement_id' => $engagement->id,
                'liquidations' => count($created),
            ]);
            $this->audit->record($engagement, $user, 'transmission_liquidation', 'Transmission à la liquidation ('.count($created).' liquidation(s) générée(s)).');

            return $created;
        });
    }
}
