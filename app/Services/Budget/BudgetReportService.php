<?php

namespace App\Services\Budget;

use App\Models\AlerteBudgetaire;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use Illuminate\Support\Collection;

/**
 * Agregats du budget (recettes / depenses) et suivi de l'execution via les lignes operationnelles.
 */
class BudgetReportService
{
    public function __construct(
        private readonly BudgetRestesService $restes,
    ) {
    }

    /**
     * Synthese recettes / depenses + execution.
     *
     * @return array<string, float|int>
     */
    public function synthese(ExerciceBudgetaire $exercice): array
    {
        $titres = BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('niveau', 'titre')
            ->get(['section', 'montant_total', 'montant_ceeac', 'montant_ptf']);

        $recettes = (float) $titres->where('section', 'recette')->sum('montant_total');
        $depenses = (float) $titres->where('section', 'depense')->sum('montant_total');
        $ceeac = (float) $titres->where('section', 'depense')->sum('montant_ceeac');
        $ptf = (float) $titres->where('section', 'depense')->sum('montant_ptf');

        $ligneIds = BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->whereNotNull('ligne_budgetaire_id')
            ->pluck('ligne_budgetaire_id')
            ->unique();

        $exec = LigneBudgetaire::query()
            ->whereIn('id', $ligneIds)
            ->selectRaw('COALESCE(SUM(montant_revise),0) r, COALESCE(SUM(montant_engage),0) e, COALESCE(SUM(montant_liquide),0) l, COALESCE(SUM(montant_ordonnance),0) o, COALESCE(SUM(montant_paye),0) p')
            ->first();

        $creditsOuverts = (float) ($exec->r ?? 0);
        $engage = (float) ($exec->e ?? 0);
        $liquide = (float) ($exec->l ?? 0);
        $ordonnance = (float) ($exec->o ?? 0);
        $paye = (float) ($exec->p ?? 0);
        $restesSynth = $this->restes->syntheseExercice($exercice);

        return [
            'montant_recettes' => $recettes,
            'montant_depenses' => $depenses,
            'montant_ceeac' => $ceeac,
            'montant_ptf' => $ptf,
            'equilibre' => $recettes - $depenses,
            'nb_lignes_operationnelles' => $ligneIds->count(),
            'credits_ouverts' => $creditsOuverts,
            'montant_engage' => $engage,
            'montant_liquide' => $liquide,
            'montant_ordonnance' => $ordonnance,
            'montant_paye' => $paye,
            'disponible' => $creditsOuverts - $engage,
            'reste_a_engager' => $restesSynth['reste_a_engager'],
            'reste_a_liquider' => $restesSynth['reste_a_liquider'],
            'reste_a_ordonnancer' => $restesSynth['reste_a_ordonnancer'],
            'reste_a_payer' => $restesSynth['reste_a_payer'],
            'taux_engagement' => $creditsOuverts > 0 ? round($engage / $creditsOuverts * 100, 1) : 0.0,
            'taux_liquidation' => $creditsOuverts > 0 ? round($liquide / $creditsOuverts * 100, 1) : 0.0,
            'taux_ordonnancement' => $creditsOuverts > 0 ? round($ordonnance / $creditsOuverts * 100, 1) : 0.0,
            'taux_paiement' => $creditsOuverts > 0 ? round($paye / $creditsOuverts * 100, 1) : 0.0,
        ];
    }

    /**
     * Execution par categorie de depense (titre/chapitre regroupe par categorie).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function executionParCategorie(ExerciceBudgetaire $exercice): Collection
    {
        $rows = BudgetLigne::query()
            ->where('budget_lignes.exercice_budgetaire_id', $exercice->id)
            ->where('budget_lignes.section', 'depense')
            ->whereNotNull('budget_lignes.ligne_budgetaire_id')
            ->join('lignes_budgetaires', 'lignes_budgetaires.id', '=', 'budget_lignes.ligne_budgetaire_id')
            ->get([
                'budget_lignes.categorie as categorie',
                'lignes_budgetaires.montant_revise as revise',
                'lignes_budgetaires.montant_engage as engage',
                'lignes_budgetaires.montant_liquide as liquide',
                'lignes_budgetaires.montant_ordonnance as ordonnance',
                'lignes_budgetaires.montant_paye as paye',
            ]);

        return $this->buildCategorieRows($rows);
    }

    /**
     * Detail par ligne operationnelle issue du budget.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function executionLignes(ExerciceBudgetaire $exercice): Collection
    {
        return BudgetLigne::query()
            ->where('budget_lignes.exercice_budgetaire_id', $exercice->id)
            ->where('budget_lignes.section', 'depense')
            ->whereNotNull('budget_lignes.ligne_budgetaire_id')
            ->join('lignes_budgetaires', 'lignes_budgetaires.id', '=', 'budget_lignes.ligne_budgetaire_id')
            ->orderBy('budget_lignes.code')
            ->get([
                'budget_lignes.code as code',
                'budget_lignes.categorie as categorie',
                'lignes_budgetaires.id as ligne_id',
                'lignes_budgetaires.libelle as libelle',
                'lignes_budgetaires.montant_revise as ouvert',
                'lignes_budgetaires.montant_engage as engage',
                'lignes_budgetaires.montant_liquide as liquide',
                'lignes_budgetaires.montant_ordonnance as ordonnance',
                'lignes_budgetaires.montant_paye as paye',
            ])
            ->map(function ($r) {
                $ouvert = (float) $r->ouvert;
                $engage = (float) $r->engage;
                $liquide = (float) $r->liquide;
                $ordonnance = (float) $r->ordonnance;
                $paye = (float) $r->paye;

                return [
                    'code' => $r->code,
                    'categorie' => $r->categorie ?: 'autre',
                    'libelle' => (string) $r->libelle,
                    'credits_ouverts' => $ouvert,
                    'montant_engage' => $engage,
                    'montant_liquide' => $liquide,
                    'montant_ordonnance' => $ordonnance,
                    'montant_paye' => $paye,
                    'disponible' => $ouvert - $engage,
                    'reste_a_liquider' => max(0, $engage - $liquide),
                    'reste_a_ordonnancer' => max(0, $liquide - $ordonnance),
                    'reste_a_payer' => max(0, $ordonnance - $paye),
                    'taux_engagement' => $ouvert > 0 ? round($engage / $ouvert * 100, 1) : 0.0,
                ];
            });
    }

    /**
     * Alertes de seuil actives, triees par gravite puis taux decroissant.
     *
     * @return Collection<int, AlerteBudgetaire>
     */
    public function alertesActives(ExerciceBudgetaire $exercice): Collection
    {
        $rang = ['depassement' => 3, 'critique' => 2, 'avertissement' => 1];

        return AlerteBudgetaire::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->actives()
            ->get()
            ->sortByDesc(fn (AlerteBudgetaire $a) => [$rang[$a->niveau] ?? 0, (float) $a->taux])
            ->values();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function buildCategorieRows(Collection $rows): Collection
    {
        return $rows->groupBy('categorie')->map(function (Collection $g, $cat) {
            $ouvert = (float) $g->sum('revise');
            $engage = (float) $g->sum('engage');
            $liquide = (float) $g->sum('liquide');
            $ordonnance = (float) $g->sum('ordonnance');
            $paye = (float) $g->sum('paye');

            return [
                'categorie' => $cat ?: 'autre',
                'libelle' => ucfirst(str_replace('_', ' ', (string) ($cat ?: 'autre'))),
                'credits_ouverts' => $ouvert,
                'montant_engage' => $engage,
                'montant_liquide' => $liquide,
                'montant_ordonnance' => $ordonnance,
                'montant_paye' => $paye,
                'disponible' => $ouvert - $engage,
                'reste_a_liquider' => max(0, $engage - $liquide),
                'reste_a_ordonnancer' => max(0, $liquide - $ordonnance),
                'reste_a_payer' => max(0, $ordonnance - $paye),
                'taux_engagement' => $ouvert > 0 ? round($engage / $ouvert * 100, 1) : 0.0,
            ];
        })->sortByDesc('credits_ouverts')->values();
    }
}
