<?php

namespace App\Exports;

use App\Exports\Budget\BudgetExecutionCategorieSheet;
use App\Exports\Budget\BudgetExecutionLignesSheet;
use App\Exports\Budget\BudgetExecutionSyntheseSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BudgetExecutionExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $synthese
     * @param  array<int, array<string, mixed>>  $categories
     * @param  array<int, array<string, mixed>>  $lignes
     */
    public function __construct(
        private array $synthese,
        private array $categories,
        private array $lignes,
        private int $annee,
    ) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new BudgetExecutionSyntheseSheet($this->synthese, $this->annee),
            new BudgetExecutionCategorieSheet($this->categories),
            new BudgetExecutionLignesSheet($this->lignes),
        ];
    }
}
