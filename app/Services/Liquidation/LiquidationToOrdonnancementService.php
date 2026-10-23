<?php

namespace App\Services\Liquidation;

use App\Enums\StatutLiquidation;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\User;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transmission d'une liquidation certifiée vers l'Ordonnancement. Reprend
 * informations, montant, imputation et pièces GED. Empêche toute double
 * transmission (jalon transmis_ordonnancement_at + statut).
 */
class LiquidationToOrdonnancementService
{
    private const SCALE = 2;

    public function __construct(
        private readonly EngagementExecutionService $execution,
        private readonly LiquidationAuditService $audit,
    ) {
    }

    public function transmettre(Liquidation $liquidation, User $user): Ordonnancement
    {
        if (! ($liquidation->statut?->estTransmissible() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seule une liquidation certifiée peut être transmise à l’ordonnancement.'),
            ]);
        }

        if ($liquidation->transmis_ordonnancement_at !== null) {
            throw ValidationException::withMessages([
                'statut' => __('Cette liquidation a déjà été transmise à l’ordonnancement.'),
            ]);
        }

        return DB::transaction(function () use ($liquidation, $user): Ordonnancement {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $liquidation->refresh();
            $liquidation->loadMissing('gedAttachments');

            $dejaOrdonnance = (string) $liquidation->ordonnancements()->sum('montant_ordonnance');
            $reste = bcsub((string) $liquidation->montant_liquide, $dejaOrdonnance, self::SCALE);

            if (bccomp($reste, '0', self::SCALE) <= 0) {
                throw ValidationException::withMessages([
                    'montant' => __('La liquidation est déjà intégralement ordonnancée.'),
                ]);
            }

            $ordonnancement = $this->execution->recordOrdonnancement($liquidation, [
                'engagement_imputation_id' => $liquidation->engagement_imputation_id,
                'montant_ordonnance' => $reste,
                'date_ordonnancement' => now()->toDateString(),
                'reference' => 'Transmission '.$liquidation->numero,
            ], $user);

            foreach ($liquidation->gedAttachments as $attachment) {
                $ordonnancement->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_liquidation', 'created_by' => $user->id],
                );
            }

            $liquidation->update([
                'statut' => StatutLiquidation::TransmisOrdonnancement,
                'transmis_ordonnancement_at' => now(),
                'transmis_ordonnancement_par' => $user->id,
            ]);

            $this->audit->record($liquidation, $user, 'transmission_ordonnancement', 'Transmission à l’ordonnancement.', [
                'ordonnancement_id' => $ordonnancement->id,
                'montant' => $reste,
            ]);

            $liquidationId = $liquidation->id;
            $numero = (string) $liquidation->numero;
            DB::afterCommit(function () use ($user, $liquidationId, $numero): void {
                BudgetJournal::record($user, "Transmission de la liquidation {$numero} à l’ordonnancement.", Liquidation::find($liquidationId), [
                    'liquidation_id' => $liquidationId,
                ]);
            });

            return $ordonnancement;
        });
    }
}
