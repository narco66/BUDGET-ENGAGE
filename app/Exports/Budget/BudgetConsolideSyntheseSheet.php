<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetConsolideSyntheseSheet implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private array $data, private int $annee) {}

    public function title(): string
    {
        return 'Synthese consolidee';
    }

    /**
     * @return array<int, array<int, string|float>>
     */
    public function array(): array
    {
        $d = $this->data;

        return [
            ["ETAT CONSOLIDE PAP / BUDGET / EXECUTION — {$this->annee}"],
            [''],
            ['Indicateur', 'Montant (FCFA)'],
            ['PAP programme (total)', (float) $d['papTotal']],
            ['Ligne PAP du budget', (float) $d['papLigneBudget']],
            ['Ecart PAP / budget', (float) $d['papLigneBudget'] - (float) $d['papTotal']],
            ['Budget total des depenses', (float) $d['totalDepenses']],
            ['Credits ouverts', (float) $d['creditsOuverts']],
            ['Engage', (float) $d['engage']],
            ['Paye', (float) $d['paye']],
            ['Taux engagement (%)', (float) $d['tauxEngagement']],
        ];
    }
}
