<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetExecutionSyntheseSheet implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<string, mixed>  $synthese
     */
    public function __construct(private array $synthese, private int $annee) {}

    public function title(): string
    {
        return 'Synthese';
    }

    /**
     * @return array<int, array<int, string|float>>
     */
    public function array(): array
    {
        $s = $this->synthese;

        return [
            ["ETAT D'EXECUTION BUDGETAIRE — Budget {$this->annee}"],
            [''],
            ['Indicateur', 'Montant (FCFA)'],
            ['Total recettes', (float) $s['montant_recettes']],
            ['Total depenses', (float) $s['montant_depenses']],
            ['Equilibre (R - D)', (float) $s['equilibre']],
            ['Financement CEEAC', (float) $s['montant_ceeac']],
            ['Financement PTF', (float) $s['montant_ptf']],
            [''],
            ['Credits ouverts', (float) $s['credits_ouverts']],
            ['Engage', (float) $s['montant_engage']],
            ['Liquide', (float) $s['montant_liquide']],
            ['Paye', (float) $s['montant_paye']],
            ['Disponible', (float) $s['disponible']],
            ["Taux d'engagement (%)", (float) $s['taux_engagement']],
            ['Taux de paiement (%)', (float) $s['taux_paiement']],
            ['Lignes operationnelles', (int) $s['nb_lignes_operationnelles']],
        ];
    }
}
