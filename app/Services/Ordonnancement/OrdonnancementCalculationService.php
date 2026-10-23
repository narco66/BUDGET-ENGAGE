<?php

namespace App\Services\Ordonnancement;

use App\Models\Ordonnancement;
use App\Models\OrdonnancementCalcul;
use App\Models\OrdonnancementLigne;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de calcul de l'ordonnancement.
 *
 *   Montant ordonnançable = Montant liquidé − Montants déjà ordonnancés
 *
 * Le total à ordonnancer est porté par les lignes (le montant_ordonnance de
 * l'en-tête reste 0 tant que le mandat n'est pas généré, afin de ne pas fausser
 * les sommes de la chaîne d'exécution). Précision bcmath, calculs journalisés.
 */
class OrdonnancementCalculationService
{
    private const SCALE = 2;

    public function recompute(Ordonnancement $ordonnancement, ?User $user = null): Ordonnancement
    {
        return DB::transaction(function () use ($ordonnancement, $user): Ordonnancement {
            $ordonnancement->loadMissing('lignes');

            $totalLiquide = '0';
            $totalOrdonnance = '0';
            $totalReste = '0';

            foreach ($ordonnancement->lignes as $ligne) {
                $reste = $this->recomputeLigne($ligne);
                $totalLiquide = bcadd($totalLiquide, (string) $ligne->montant_liquide, self::SCALE);
                $totalOrdonnance = bcadd($totalOrdonnance, (string) $ligne->montant_ordonnance, self::SCALE);
                $totalReste = bcadd($totalReste, $reste, self::SCALE);
            }

            $taux = bccomp($totalLiquide, '0', self::SCALE) > 0
                ? bcmul(bcdiv($totalOrdonnance, $totalLiquide, 6), '100', self::SCALE)
                : '0';

            // montant_ordonnance (en-tête) reste 0 jusqu'au mandat : non écrit ici.
            $ordonnancement->fill([
                'montant_liquide_ref' => $totalLiquide,
                'reste_a_ordonnancer' => $totalReste,
                'taux_ordonnancement' => $taux,
            ]);
            $ordonnancement->save();

            $this->journaliser($ordonnancement, 'montant_ordonnancable', [
                'total_liquide' => $totalLiquide,
                'total_ordonnance' => $totalOrdonnance,
                'reste' => $totalReste,
            ], $totalOrdonnance, $user);

            return $ordonnancement;
        });
    }

    public function recomputeLigne(OrdonnancementLigne $ligne): string
    {
        $reste = bcsub((string) $ligne->montant_liquide, (string) $ligne->montant_ordonnance, self::SCALE);
        $ligne->reste_a_ordonnancer = $reste;
        $ligne->save();

        return $reste;
    }

    /** Total à ordonnancer = somme des montant_ordonnance des lignes. */
    public function totalAOrdonnancer(Ordonnancement $ordonnancement): string
    {
        $ordonnancement->loadMissing('lignes');

        $total = '0';
        foreach ($ordonnancement->lignes as $ligne) {
            $total = bcadd($total, (string) $ligne->montant_ordonnance, self::SCALE);
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $entrees
     */
    private function journaliser(Ordonnancement $ordonnancement, string $type, array $entrees, string $resultat, ?User $user): void
    {
        OrdonnancementCalcul::query()->create([
            'ordonnancement_id' => $ordonnancement->id,
            'type_calcul' => $type,
            'formule' => 'Ordonnançable = Liquidé − déjà ordonnancé',
            'entrees' => $entrees,
            'resultat' => $resultat,
            'created_by' => $user?->id,
        ]);
    }
}
