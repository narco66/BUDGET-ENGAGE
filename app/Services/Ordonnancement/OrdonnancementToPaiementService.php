<?php

namespace App\Services\Ordonnancement;

use App\Enums\StatutOrdonnancement;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\User;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transmission d'un ordonnancement mandaté vers le module Paiement. Reprend
 * informations, montant, imputation et pièces GED. Empêche toute double
 * transmission (jalon transmis_paiement_at + statut).
 */
class OrdonnancementToPaiementService
{
    private const SCALE = 2;

    public function __construct(
        private readonly EngagementExecutionService $execution,
        private readonly OrdonnancementAuditService $audit,
    ) {
    }

    public function transmettre(Ordonnancement $ordonnancement, User $user): Paiement
    {
        if (! ($ordonnancement->statut?->estTransmissible() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un ordonnancement mandaté peut être transmis au paiement.'),
            ]);
        }

        if ($ordonnancement->transmis_paiement_at !== null) {
            throw ValidationException::withMessages([
                'statut' => __('Cet ordonnancement a déjà été transmis au paiement.'),
            ]);
        }

        return DB::transaction(function () use ($ordonnancement, $user): Paiement {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement->refresh();
            $ordonnancement->loadMissing(['gedAttachments', 'lignes']);

            $dejaPaye = (string) $ordonnancement->paiements()->sum('montant_paye');
            $reste = bcsub((string) $ordonnancement->montant_ordonnance, $dejaPaye, self::SCALE);

            if (bccomp($reste, '0', self::SCALE) <= 0) {
                throw ValidationException::withMessages([
                    'montant' => __('L’ordonnancement est déjà intégralement payé.'),
                ]);
            }

            $paiement = $this->execution->recordPaiement($ordonnancement, [
                'engagement_imputation_id' => $ordonnancement->engagement_imputation_id,
                'montant_paye' => $reste,
                'date_paiement' => now()->toDateString(),
                'mode_paiement' => $ordonnancement->lignes->first()?->mode_paiement ?? 'virement',
                'reference_paiement' => 'Transmission '.$ordonnancement->numero,
            ], $user);

            foreach ($ordonnancement->gedAttachments as $attachment) {
                $paiement->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_ordonnancement', 'created_by' => $user->id],
                );
            }

            $ordonnancement->update([
                'statut' => StatutOrdonnancement::TransmisPaiement,
                'transmis_paiement_at' => now(),
                'transmis_paiement_par' => $user->id,
                'montant_transmis_paiement' => $reste,
            ]);

            $this->audit->record($ordonnancement, $user, 'transmission_paiement', 'Transmission au paiement.', [
                'paiement_id' => $paiement->id,
                'montant' => $reste,
            ]);

            $id = $ordonnancement->id;
            $numero = (string) $ordonnancement->numero;
            DB::afterCommit(function () use ($user, $id, $numero): void {
                BudgetJournal::record($user, "Transmission de l’ordonnancement {$numero} au paiement.", Ordonnancement::find($id), [
                    'ordonnancement_id' => $id,
                ]);
            });

            return $paiement;
        });
    }
}
