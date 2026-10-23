<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetConsolidePilierSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(private array $rows) {}

    public function title(): string
    {
        return 'Par pilier';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Pilier', 'PAP programme', 'Budgete', 'Taux budgetisation (%)', 'Engage', 'Paye', 'Taux engagement (%)'];
    }

    /**
     * @return array<int, array<int, string|float>>
     */
    public function array(): array
    {
        return array_map(fn (array $r) => [
            $r['code'], $r['libelle'],
            (float) $r['pap_total'], (float) $r['budgete'], (float) $r['taux_budgetisation'],
            (float) $r['engage'], (float) $r['paye'], (float) $r['taux_engagement'],
        ], $this->rows);
    }
}
