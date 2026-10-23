<?php

namespace App\Services\Rbm;

use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\PapLigneBudgetaire;
use Illuminate\Support\Collection;

/**
 * Agrege les donnees du PAP et confronte la programmation budgetaire (lignes budgetaires liees).
 */
class PapBudgetService
{
    /**
     * Synthese globale du PAP d'un exercice.
     *
     * @return array<string, float|int>
     */
    public function synthese(ExerciceBudgetaire $exercice): array
    {
        $piliers = PapLigneBudgetaire::query()
            ->pourExercice($exercice->id)
            ->niveau('pilier')
            ->get(['montant_total', 'montant_ceeac', 'montant_ptf']);

        $compte = PapLigneBudgetaire::query()
            ->pourExercice($exercice->id)
            ->selectRaw('niveau, count(*) as n')
            ->groupBy('niveau')
            ->pluck('n', 'niveau');

        $budgete = (float) LigneBudgetaire::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->whereNotNull('pap_ligne_budgetaire_id')
            ->sum('montant_revise');

        $total = (float) $piliers->sum('montant_total');

        return [
            'montant_total' => $total,
            'montant_ceeac' => (float) $piliers->sum('montant_ceeac'),
            'montant_ptf' => (float) $piliers->sum('montant_ptf'),
            'nb_piliers' => (int) ($compte['pilier'] ?? 0),
            'nb_axes' => (int) ($compte['axe'] ?? 0),
            'nb_produits' => (int) ($compte['produit'] ?? 0),
            'nb_sous_produits' => (int) ($compte['sous_produit'] ?? 0),
            'nb_lignes' => (int) $compte->sum(),
            'montant_budgete' => $budgete,
            'nb_lignes_liees' => (int) LigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->whereNotNull('pap_ligne_budgetaire_id')
                ->count(),
            'taux_couverture' => $total > 0 ? round($budgete / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * Etat de programmation par pilier : montant PAP vs montant budgete (lignes liees).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function programmationParPilier(ExerciceBudgetaire $exercice): Collection
    {
        $piliers = PapLigneBudgetaire::query()
            ->pourExercice($exercice->id)
            ->niveau('pilier')
            ->orderBy('ordre')
            ->get();

        $budgeteParPilier = $this->budgeteParPrefixePilier($exercice);

        return $piliers->map(function (PapLigneBudgetaire $p) use ($budgeteParPilier) {
            $prefixe = substr($p->code, 0, 3);
            $budgete = (float) ($budgeteParPilier[$prefixe] ?? 0);
            $total = (float) $p->montant_total;

            return [
                'code' => $p->code,
                'libelle' => $p->libelle,
                'montant_total' => $total,
                'montant_ceeac' => (float) $p->montant_ceeac,
                'montant_ptf' => (float) $p->montant_ptf,
                'montant_budgete' => $budgete,
                'ecart' => $total - $budgete,
                'taux_couverture' => $total > 0 ? round($budgete / $total * 100, 1) : 0.0,
            ];
        });
    }

    /**
     * Ventilation hierarchique pilier -> axe -> produit, avec montant PAP et montant budgete.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function programmationHierarchie(ExerciceBudgetaire $exercice): Collection
    {
        $budgete = $this->budgeteParPrefixe($exercice);

        $lignes = PapLigneBudgetaire::query()
            ->pourExercice($exercice->id)
            ->whereIn('niveau', ['pilier', 'axe', 'produit'])
            ->orderBy('ordre')
            ->get(['code', 'libelle', 'niveau', 'parent_code', 'montant_total', 'montant_ceeac', 'montant_ptf']);

        $axesParParent = $lignes->where('niveau', 'axe')->groupBy('parent_code');
        $produitsParParent = $lignes->where('niveau', 'produit')->groupBy('parent_code');

        $build = fn (PapLigneBudgetaire $n): array => $this->nodeRow($n, $budgete);

        return $lignes->where('niveau', 'pilier')->values()->map(function (PapLigneBudgetaire $pilier) use ($axesParParent, $produitsParParent, $build) {
            $row = $build($pilier);
            $row['axes'] = ($axesParParent[$pilier->code] ?? collect())->map(function (PapLigneBudgetaire $axe) use ($produitsParParent, $build) {
                $axeRow = $build($axe);
                $axeRow['produits'] = ($produitsParParent[$axe->code] ?? collect())->map($build)->values();

                return $axeRow;
            })->values();

            return $row;
        });
    }

    /**
     * Ventilation a plat (pour export Excel/PDF) : une ligne par pilier/axe/produit.
     *
     * @return array<int, array<string, mixed>>
     */
    public function programmationFlat(ExerciceBudgetaire $exercice): array
    {
        $rows = [];
        foreach ($this->programmationHierarchie($exercice) as $pilier) {
            $rows[] = $this->stripChildren($pilier, 'Pilier');
            foreach ($pilier['axes'] as $axe) {
                $rows[] = $this->stripChildren($axe, 'Axe');
                foreach ($axe['produits'] as $produit) {
                    $rows[] = $this->stripChildren($produit, 'Produit');
                }
            }
        }

        return $rows;
    }

    /**
     * Classe une ligne selon sa source de financement.
     */
    public static function classifySource(float $ceeac, float $ptf): string
    {
        if ($ceeac > 0 && $ptf > 0) {
            return 'Mixte';
        }
        if ($ceeac > 0) {
            return 'CEEAC';
        }
        if ($ptf > 0) {
            return 'PTF';
        }

        return 'Néant';
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function stripChildren(array $node, string $niveauLabel): array
    {
        unset($node['axes'], $node['produits']);
        $node['niveau'] = $niveauLabel;
        $node['source'] = self::classifySource((float) $node['montant_ceeac'], (float) $node['montant_ptf']);

        return $node;
    }

    /**
     * @param  array<string, float>  $budgete
     * @return array<string, mixed>
     */
    private function nodeRow(PapLigneBudgetaire $n, array $budgete): array
    {
        $total = (float) $n->montant_total;
        $b = (float) ($budgete[$n->code] ?? 0);

        return [
            'code' => $n->code,
            'libelle' => $n->libelle,
            'montant_total' => $total,
            'montant_ceeac' => (float) $n->montant_ceeac,
            'montant_ptf' => (float) $n->montant_ptf,
            'montant_budgete' => $b,
            'ecart' => $total - $b,
            'taux_couverture' => $total > 0 ? round($b / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * Somme des montants revises des lignes budgetaires liees, groupee par prefixe pilier (3 chiffres).
     *
     * @return array<string, float>
     */
    private function budgeteParPrefixePilier(ExerciceBudgetaire $exercice): array
    {
        $byPrefix = $this->budgeteParPrefixe($exercice);
        $result = [];
        foreach ($byPrefix as $code => $montant) {
            if (strlen($code) === 3) {
                $result[$code] = $montant;
            }
        }

        return $result;
    }

    /**
     * Etat consolide PAP <-> Budget <-> Execution, par pilier.
     * Programme (PAP) vs budgete (lignes liees) vs engage/paye (execution).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function consolideParPilier(ExerciceBudgetaire $exercice): Collection
    {
        $piliers = PapLigneBudgetaire::query()
            ->pourExercice($exercice->id)
            ->niveau('pilier')
            ->orderBy('ordre')
            ->get();

        $lignes = LigneBudgetaire::query()
            ->where('lignes_budgetaires.exercice_budgetaire_id', $exercice->id)
            ->whereNotNull('lignes_budgetaires.pap_ligne_budgetaire_id')
            ->join('pap_lignes_budgetaires', 'pap_lignes_budgetaires.id', '=', 'lignes_budgetaires.pap_ligne_budgetaire_id')
            ->get([
                'pap_lignes_budgetaires.code as pap_code',
                'lignes_budgetaires.montant_revise as revise',
                'lignes_budgetaires.montant_engage as engage',
                'lignes_budgetaires.montant_paye as paye',
            ]);

        $parPrefixe = [];
        foreach ($lignes as $l) {
            $p = substr((string) $l->pap_code, 0, 3);
            $parPrefixe[$p] ??= ['budgete' => 0.0, 'engage' => 0.0, 'paye' => 0.0];
            $parPrefixe[$p]['budgete'] += (float) $l->revise;
            $parPrefixe[$p]['engage'] += (float) $l->engage;
            $parPrefixe[$p]['paye'] += (float) $l->paye;
        }

        return $piliers->map(function (PapLigneBudgetaire $p) use ($parPrefixe) {
            $prefixe = substr($p->code, 0, 3);
            $agg = $parPrefixe[$prefixe] ?? ['budgete' => 0.0, 'engage' => 0.0, 'paye' => 0.0];
            $pap = (float) $p->montant_total;
            $budgete = (float) $agg['budgete'];

            return [
                'code' => $p->code,
                'libelle' => $p->libelle,
                'pap_total' => $pap,
                'budgete' => $budgete,
                'engage' => (float) $agg['engage'],
                'paye' => (float) $agg['paye'],
                'ecart_pap_budget' => $pap - $budgete,
                'taux_budgetisation' => $pap > 0 ? round($budgete / $pap * 100, 1) : 0.0,
                'taux_engagement' => $budgete > 0 ? round((float) $agg['engage'] / $budgete * 100, 1) : 0.0,
            ];
        });
    }

    /**
     * Somme des montants revises des lignes budgetaires liees, ventilee sur tous les prefixes
     * ancetres (pilier 3, axe 4, produit 5 chiffres) du code PAP rattache.
     *
     * @return array<string, float>
     */
    private function budgeteParPrefixe(ExerciceBudgetaire $exercice): array
    {
        $lignes = LigneBudgetaire::query()
            ->where('lignes_budgetaires.exercice_budgetaire_id', $exercice->id)
            ->whereNotNull('pap_ligne_budgetaire_id')
            ->join('pap_lignes_budgetaires', 'pap_lignes_budgetaires.id', '=', 'lignes_budgetaires.pap_ligne_budgetaire_id')
            ->get(['lignes_budgetaires.montant_revise as montant_revise', 'pap_lignes_budgetaires.code as pap_code']);

        $result = [];
        foreach ($lignes as $ligne) {
            $code = (string) $ligne->pap_code;
            $montant = (float) $ligne->montant_revise;
            foreach ([3, 4, 5] as $len) {
                if (strlen($code) >= $len) {
                    $prefixe = substr($code, 0, $len);
                    $result[$prefixe] = ($result[$prefixe] ?? 0) + $montant;
                }
            }
        }

        return $result;
    }
}
