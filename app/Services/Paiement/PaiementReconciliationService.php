<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementBanque;
use App\Models\PaiementRapprochement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de rapprochement bancaire : import des lignes de relevé (Excel / CSV /
 * MT940 / CAMT.053 — déjà normalisées en tableau), rapprochement automatique par
 * montant + référence, rapprochement manuel et détection des écarts.
 */
class PaiementReconciliationService
{
    private const SCALE = 2;

    public function __construct(private readonly PaiementAuditService $audit)
    {
    }

    /**
     * Importe des lignes de relevé et tente un rapprochement automatique.
     *
     * @param  array<int, array{date_operation: string, montant: float|string, reference?: string}>  $lignes
     * @return Collection<int, PaiementRapprochement>
     */
    public function importerReleve(PaiementBanque $banque, array $lignes, string $source, User $user): Collection
    {
        return DB::transaction(function () use ($banque, $lignes, $source, $user): Collection {
            $resultats = collect();

            foreach ($lignes as $ligne) {
                $montant = (string) ($ligne['montant'] ?? '0');
                $reference = $ligne['reference'] ?? null;

                $paiement = $this->trouverPaiementCorrespondant($banque, $montant, $reference);

                $rapprochement = PaiementRapprochement::query()->create([
                    'paiement_id' => $paiement?->id,
                    'paiement_banque_id' => $banque->id,
                    'reference_releve' => $reference,
                    'date_operation' => $ligne['date_operation'] ?? now()->toDateString(),
                    'montant_releve' => $montant,
                    'montant_paiement' => $paiement?->montant_paye,
                    'ecart' => $paiement !== null ? bcsub($montant, (string) $paiement->montant_paye, self::SCALE) : null,
                    'statut' => $paiement !== null ? 'rapproche' : 'non_rapproche',
                    'mode' => 'auto',
                    'source_import' => $source,
                    'created_by' => $user->id,
                ]);

                $resultats->push($rapprochement);
            }

            return $resultats;
        });
    }

    /**
     * Rapprochement manuel d'une ligne de relevé avec un paiement.
     */
    public function rapprocherManuel(PaiementRapprochement $rapprochement, Paiement $paiement, User $user): PaiementRapprochement
    {
        $ecart = bcsub((string) $rapprochement->montant_releve, (string) $paiement->montant_paye, self::SCALE);

        $rapprochement->update([
            'paiement_id' => $paiement->id,
            'montant_paiement' => $paiement->montant_paye,
            'ecart' => $ecart,
            'statut' => bccomp($ecart, '0', self::SCALE) === 0 ? 'rapproche' : 'ecart',
            'mode' => 'manuel',
        ]);

        $this->audit->record($paiement, $user, 'rapprochement', 'Rapprochement bancaire manuel.', [
            'rapprochement_id' => $rapprochement->id,
            'ecart' => $ecart,
        ]);

        return $rapprochement;
    }

    public function valider(PaiementRapprochement $rapprochement, User $user): PaiementRapprochement
    {
        $rapprochement->update([
            'valide' => true,
            'valide_par' => $user->id,
            'valide_at' => now(),
        ]);

        return $rapprochement;
    }

    private function trouverPaiementCorrespondant(PaiementBanque $banque, string $montant, ?string $reference): ?Paiement
    {
        return Paiement::query()
            ->where('paiement_banque_id', $banque->id)
            ->where('statut', 'execute')
            ->whereNull('cancelled_at')
            ->where('montant_paye', $montant)
            ->when($reference !== null, fn ($q) => $q->where(function ($b) use ($reference): void {
                $b->where('reference_bancaire', $reference)
                    ->orWhere('numero', $reference)
                    ->orWhere('reference_paiement', $reference);
            }))
            ->whereDoesntHave('rapprochements', fn ($q) => $q->where('statut', 'rapproche'))
            ->first();
    }
}
