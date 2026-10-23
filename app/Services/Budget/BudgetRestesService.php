<?php

namespace App\Services\Budget;

use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use Illuminate\Support\Collection;

/**
 * Calcul institutionnel des restes à engager, liquider, ordonnancer et payer.
 *
 * Chaîne : plafond → engagé → liquidé → ordonnancé → payé.
 */
class BudgetRestesService
{
    public function plafond(LigneBudgetaire $ligne): string
    {
        $revise = (float) $ligne->montant_revise;

        return number_format($revise > 0 ? $revise : (float) $ligne->montant_vote, 2, '.', '');
    }

    /**
     * @return array{
     *     plafond: string,
     *     montant_engage: string,
     *     montant_liquide: string,
     *     montant_ordonnance: string,
     *     montant_paye: string,
     *     credit_disponible: string,
     *     reste_a_engager: string,
     *     reste_a_liquider: string,
     *     reste_a_ordonnancer: string,
     *     reste_a_payer: string,
     *     taux_engagement: float,
     *     taux_liquidation: float,
     *     taux_ordonnancement: float,
     *     taux_paiement: float
     * }
     */
    public function restesPourLigne(LigneBudgetaire $ligne): array
    {
        $plafond = $this->plafond($ligne);
        $engage = $this->fmt($ligne->montant_engage);
        $liquide = $this->fmt($ligne->montant_liquide);
        $ordonnance = $this->fmt($ligne->montant_ordonnance);
        $paye = $this->fmt($ligne->montant_paye);

        $resteEngager = bcsub($plafond, $engage, 2);
        $resteLiquider = bcsub($engage, $liquide, 2);
        $resteOrdonnancer = bcsub($liquide, $ordonnance, 2);
        $restePayer = bcsub($ordonnance, $paye, 2);

        return [
            'plafond' => $plafond,
            'montant_engage' => $engage,
            'montant_liquide' => $liquide,
            'montant_ordonnance' => $ordonnance,
            'montant_paye' => $paye,
            'credit_disponible' => $resteEngager,
            'reste_a_engager' => $resteEngager,
            'reste_a_liquider' => $this->nonNegatif($resteLiquider),
            'reste_a_ordonnancer' => $this->nonNegatif($resteOrdonnancer),
            'reste_a_payer' => $this->nonNegatif($restePayer),
            'taux_engagement' => $this->taux($engage, $plafond),
            'taux_liquidation' => $this->taux($liquide, $plafond),
            'taux_ordonnancement' => $this->taux($ordonnance, $plafond),
            'taux_paiement' => $this->taux($paye, $plafond),
        ];
    }

    /**
     * Synthèse consolidée pour un exercice (toutes les lignes opérationnelles).
     *
     * @return array<string, float|string>
     */
    public function syntheseExercice(ExerciceBudgetaire $exercice): array
    {
        $lignes = LigneBudgetaire::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->get();

        $totaux = [
            'plafond' => '0',
            'montant_engage' => '0',
            'montant_liquide' => '0',
            'montant_ordonnance' => '0',
            'montant_paye' => '0',
            'reste_a_engager' => '0',
            'reste_a_liquider' => '0',
            'reste_a_ordonnancer' => '0',
            'reste_a_payer' => '0',
        ];

        foreach ($lignes as $ligne) {
            $r = $this->restesPourLigne($ligne);
            foreach (array_keys($totaux) as $key) {
                $totaux[$key] = bcadd($totaux[$key], $r[$key], 2);
            }
        }

        $count = max(1, $lignes->count());

        return [
            'nb_lignes' => $lignes->count(),
            'plafond' => (float) $totaux['plafond'],
            'montant_engage' => (float) $totaux['montant_engage'],
            'montant_liquide' => (float) $totaux['montant_liquide'],
            'montant_ordonnance' => (float) $totaux['montant_ordonnance'],
            'montant_paye' => (float) $totaux['montant_paye'],
            'reste_a_engager' => (float) $totaux['reste_a_engager'],
            'reste_a_liquider' => (float) $totaux['reste_a_liquider'],
            'reste_a_ordonnancer' => (float) $totaux['reste_a_ordonnancer'],
            'reste_a_payer' => (float) $totaux['reste_a_payer'],
            'taux_engagement' => $this->taux($totaux['montant_engage'], $totaux['plafond']),
            'taux_liquidation' => $this->taux($totaux['montant_liquide'], $totaux['plafond']),
            'taux_ordonnancement' => $this->taux($totaux['montant_ordonnance'], $totaux['plafond']),
            'taux_paiement' => $this->taux($totaux['montant_paye'], $totaux['plafond']),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function lignesExercice(ExerciceBudgetaire $exercice, ?string $search = null): Collection
    {
        $query = LigneBudgetaire::query()
            ->with(['departement', 'natureDepense'])
            ->where('exercice_budgetaire_id', $exercice->id)
            ->orderBy('code');

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function (LigneBudgetaire $ligne): array {
            $restes = $this->restesPourLigne($ligne);

            return array_merge([
                'id' => $ligne->id,
                'code' => $ligne->code,
                'libelle' => $ligne->libelle,
                'departement' => $ligne->departement?->libelle,
            ], $restes);
        });
    }

    private function fmt(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function nonNegatif(string $value): string
    {
        return bccomp($value, '0', 2) < 0 ? '0.00' : $value;
    }

    private function taux(string $numerateur, string $denominateur): float
    {
        if (bccomp($denominateur, '0', 2) <= 0) {
            return 0.0;
        }

        return round(((float) $numerateur / (float) $denominateur) * 100, 1);
    }
}
