<?php

namespace App\Exports;

use App\Exports\Budget\BudgetConsolidePilierSheet;
use App\Exports\Budget\BudgetConsolideSyntheseSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BudgetConsolidatedExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $synthese
     * @param  array<int, array<string, mixed>>  $piliers
     */
    public function __construct(private array $synthese, private array $piliers, private int $annee) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new BudgetConsolideSyntheseSheet($this->synthese, $this->annee),
            new BudgetConsolidePilierSheet($this->piliers),
        ];
    }
}
