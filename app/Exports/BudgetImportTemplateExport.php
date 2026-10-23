<?php

namespace App\Exports;

use App\Exports\Budget\BudgetTemplateInstructionsSheet;
use App\Exports\Budget\BudgetTemplateLignesSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BudgetImportTemplateExport implements WithMultipleSheets
{
    /**
     * @param  array<int, array<int, string|int|float>>  $lignes
     */
    public function __construct(private array $lignes = []) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new BudgetTemplateInstructionsSheet,
            new BudgetTemplateLignesSheet($this->lignes),
        ];
    }
}
