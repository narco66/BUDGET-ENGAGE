<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementCalcul;
use App\Models\PaiementLigne;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de calcul du paiement.
 *
 *   Montant à payer = Montant ordonnancé − Montants déjà payés
 *   Reste à payer   = Montant ordonnancé − Montant total payé
 *   Taux            = Montant payé / Montant ordonnancé × 100
 *
 * Le montant_paye de l'en-tête reste 0 tant que le paiement n'est pas exécuté
 * (pour ne pas fausser les sommes de la chaîne d'exécution) ; le total à payer
 * est porté par les lignes. Précision bcmath, calculs journalisés.
 */
class PaiementCalculationService
{
    private const SCALE = 2;

    public function recompute(Paiement $paiement, ?User $user = null): Paiement
    {
        return DB::transaction(function () use ($paiement, $user): Paiement {
            $paiement->loadMissing('lignes');

            $totalOrdonnance = '0';
            $totalPaye = '0';
            $totalReste = '0';

            foreach ($paiement->lignes as $ligne) {
                $reste = $this->recomputeLigne($ligne);
                $totalOrdonnance = bcadd($totalOrdonnance, (string) $ligne->montant_ordonnance, self::SCALE);
                $totalPaye = bcadd($totalPaye, (string) $ligne->montant_paye, self::SCALE);
                $totalReste = bcadd($totalReste, $reste, self::SCALE);
            }

            $taux = bccomp($totalOrdonnance, '0', self::SCALE) > 0
                ? bcmul(bcdiv($totalPaye, $totalOrdonnance, 6), '100', self::SCALE)
                : '0';

            // montant_paye (en-tête) reste 0 jusqu'à l'exécution : non écrit ici.
            $paiement->fill([
                'montant_ordonnance_ref' => $totalOrdonnance,
                'reste_a_payer' => $totalReste,
                'taux_paiement' => $taux,
            ]);
            $paiement->save();

            $this->journaliser($paiement, 'montant_a_payer', [
                'total_ordonnance' => $totalOrdonnance,
                'total_a_payer' => $totalPaye,
                'reste' => $totalReste,
            ], $totalPaye, $user);

            return $paiement;
        });
    }

    public function recomputeLigne(PaiementLigne $ligne): string
    {
        $reste = bcsub((string) $ligne->montant_ordonnance, (string) $ligne->montant_paye, self::SCALE);
        $ligne->reste_a_payer = $reste;
        $ligne->save();

        return $reste;
    }

    /** Total à payer = somme des montant_paye des lignes. */
    public function totalAPayer(Paiement $paiement): string
    {
        $paiement->loadMissing('lignes');

        $total = '0';
        foreach ($paiement->lignes as $ligne) {
            $total = bcadd($total, (string) $ligne->montant_paye, self::SCALE);
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $entrees
     */
    private function journaliser(Paiement $paiement, string $type, array $entrees, string $resultat, ?User $user): void
    {
        PaiementCalcul::query()->create([
            'paiement_id' => $paiement->id,
            'type_calcul' => $type,
            'formule' => 'À payer = Ordonnancé − déjà payé',
            'entrees' => $entrees,
            'resultat' => $resultat,
            'created_by' => $user?->id,
        ]);
    }
}
