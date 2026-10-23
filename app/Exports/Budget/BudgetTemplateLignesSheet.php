<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetTemplateLignesSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<int, string|int|float>>  $rows
     */
    public function __construct(private array $rows = []) {}

    public function title(): string
    {
        return 'Lignes Budget';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Section', 'Niveau', 'Categorie', 'Code', 'Code parent', 'Libelle', 'Montant total', 'Montant CEEAC', 'Montant PTF', 'Source'];
    }

    /**
     * @return array<int, array<int, string|int|float>>
     */
    public function array(): array
    {
        if ($this->rows !== []) {
            return $this->rows;
        }

        return [
            ['recette', 'titre', 'recette_interne', 'R-INT', '', 'RECETTES INTERNES', 26_275_514_803, 26_275_514_803, 0, 'Budget 2026'],
            ['recette', 'ligne', 'recette_interne', '72103', '7210', 'Republique du Cameroun', 2_890_306_628, 2_890_306_628, 0, 'Budget 2026'],
            ['depense', 'titre', 'fonctionnement', 'D-FONC', '', 'DEPENSES DE FONCTIONNEMENT', 13_677_514_803, 13_677_514_803, 0, 'Budget 2026'],
            ['depense', 'chapitre', 'personnel', '66', 'D-FONC', 'DEPENSES DE PERSONNEL', 11_586_264_803, 11_586_264_803, 0, 'Budget 2026'],
            ['depense', 'ligne', 'personnel', '66101', '6610', 'Salaire de base des fonctionnaires', 5_377_302_285, 5_377_302_285, 0, 'Budget 2026'],
        ];
    }
}
