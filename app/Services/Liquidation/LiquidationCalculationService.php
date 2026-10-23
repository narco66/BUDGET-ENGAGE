<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use App\Models\LiquidationCalcul;
use App\Models\LiquidationLigne;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de calcul financier de la liquidation.
 *
 *   Montant Net = Brut − Avances − Acomptes − Retenues − Garanties − Pénalités
 *                 ± Révision de prix + Taxes
 *
 * Calculs réalisés ligne par ligne puis agrégés en en-tête. Chaque opération est
 * historisée dans liquidation_calculs (audit / reconstitution). Précision bcmath.
 */
class LiquidationCalculationService
{
    private const SCALE = 2;

    /**
     * Recalcule l'intégralité d'une liquidation (lignes + en-tête) et journalise.
     */
    public function recompute(Liquidation $liquidation, ?User $user = null): Liquidation
    {
        return DB::transaction(function () use ($liquidation, $user): Liquidation {
            $liquidation->loadMissing(['lignes', 'engagement']);

            $totaux = [
                'montant_brut' => '0',
                'total_avances' => '0',
                'total_acomptes' => '0',
                'total_retenues' => '0',
                'total_garanties' => '0',
                'total_penalites' => '0',
                'total_taxes' => '0',
                'montant_revision_prix' => '0',
                'montant_net' => '0',
            ];

            foreach ($liquidation->lignes as $ligne) {
                $net = $this->recomputeLigne($ligne, $liquidation, $user);

                $totaux['montant_brut'] = bcadd($totaux['montant_brut'], (string) $ligne->montant_brut, self::SCALE);
                $totaux['total_avances'] = bcadd($totaux['total_avances'], (string) $ligne->montant_avances, self::SCALE);
                $totaux['total_acomptes'] = bcadd($totaux['total_acomptes'], (string) $ligne->montant_acomptes, self::SCALE);
                $totaux['total_retenues'] = bcadd($totaux['total_retenues'], (string) $ligne->montant_retenues, self::SCALE);
                $totaux['total_garanties'] = bcadd($totaux['total_garanties'], (string) $ligne->montant_garanties, self::SCALE);
                $totaux['total_penalites'] = bcadd($totaux['total_penalites'], (string) $ligne->montant_penalites, self::SCALE);
                $totaux['total_taxes'] = bcadd($totaux['total_taxes'], (string) $ligne->montant_taxes, self::SCALE);
                $totaux['montant_revision_prix'] = bcadd($totaux['montant_revision_prix'], (string) $ligne->montant_revision_prix, self::SCALE);
                $totaux['montant_net'] = bcadd($totaux['montant_net'], $net, self::SCALE);
            }

            $montantEngagement = (string) ($liquidation->engagement?->montant ?? '0');
            $taux = bccomp($montantEngagement, '0', self::SCALE) > 0
                ? bcmul(bcdiv($totaux['montant_net'], $montantEngagement, 6), '100', self::SCALE)
                : '0';

            $liquidation->fill(array_merge($totaux, [
                'taux_liquidation' => $taux,
            ]));
            $liquidation->save();

            $this->journaliser($liquidation, null, 'montant_net', $totaux, $totaux['montant_net'], $user, $this->formuleNet());

            return $liquidation;
        });
    }

    /**
     * Recalcule une ligne et retourne son montant net (chaîne bcmath).
     *
     * @return string
     */
    public function recomputeLigne(LiquidationLigne $ligne, Liquidation $liquidation, ?User $user = null): string
    {
        // Brut : quantité réalisée × prix unitaire si disponibles, sinon valeur fournie.
        if ($ligne->quantite_realisee !== null && $ligne->prix_unitaire !== null) {
            $ligne->montant_brut = bcmul((string) $ligne->quantite_realisee, (string) $ligne->prix_unitaire, self::SCALE);
        }

        $net = (string) $ligne->montant_brut;
        $net = bcsub($net, (string) $ligne->montant_avances, self::SCALE);
        $net = bcsub($net, (string) $ligne->montant_acomptes, self::SCALE);
        $net = bcsub($net, (string) $ligne->montant_retenues, self::SCALE);
        $net = bcsub($net, (string) $ligne->montant_garanties, self::SCALE);
        $net = bcsub($net, (string) $ligne->montant_penalites, self::SCALE);
        $net = bcadd($net, (string) $ligne->montant_revision_prix, self::SCALE);
        $net = bcadd($net, (string) $ligne->montant_taxes, self::SCALE);

        $ligne->montant_net = $net;
        $ligne->save();

        return $net;
    }

    /**
     * Taux de liquidation = montant net / montant engagement × 100.
     */
    public function tauxLiquidation(Liquidation $liquidation): string
    {
        $montantEngagement = (string) ($liquidation->engagement?->montant ?? '0');
        if (bccomp($montantEngagement, '0', self::SCALE) <= 0) {
            return '0';
        }

        return bcmul(bcdiv((string) $liquidation->montant_net, $montantEngagement, 6), '100', self::SCALE);
    }

    /**
     * @param  array<string, mixed>  $entrees
     */
    private function journaliser(
        Liquidation $liquidation,
        ?int $ligneId,
        string $type,
        array $entrees,
        string $resultat,
        ?User $user,
        string $formule,
    ): void {
        LiquidationCalcul::query()->create([
            'liquidation_id' => $liquidation->id,
            'liquidation_ligne_id' => $ligneId,
            'type_calcul' => $type,
            'formule' => $formule,
            'entrees' => $entrees,
            'resultat' => $resultat,
            'created_by' => $user?->id,
        ]);
    }

    private function formuleNet(): string
    {
        return 'Net = Brut − Avances − Acomptes − Retenues − Garanties − Pénalités ± Révision + Taxes';
    }
}
