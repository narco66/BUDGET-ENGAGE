<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetExecutionCategorieSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(private array $rows) {}

    public function title(): string
    {
        return 'Par categorie';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Categorie', 'Credits ouverts', 'Engage', 'Paye', 'Disponible', 'Taux engagement (%)'];
    }

    /**
     * @return array<int, array<int, string|float>>
     */
    public function array(): array
    {
        return array_map(fn (array $r) => [
            $r['libelle'], (float) $r['credits_ouverts'], (float) $r['montant_engage'],
            (float) $r['montant_paye'], (float) $r['disponible'], (float) $r['taux_engagement'],
        ], $this->rows);
    }
}
