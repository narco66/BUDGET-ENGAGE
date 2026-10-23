<?php

namespace App\Exports\Pap;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PapTemplateLignesSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<int, string|int|float>>  $rows  Donnees reelles (optionnel) sinon exemples.
     */
    public function __construct(private array $rows = []) {}

    public function title(): string
    {
        return 'Lignes PAP';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Code',
            'Libelle',
            'Niveau',
            'Code parent',
            'Montant total',
            'Montant CEEAC',
            'Montant PTF',
            'Source',
        ];
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
            ['201', 'PILIER 1 - INTEGRATION POLITIQUE, PAIX ET SECURITE', 'pilier', '', 1_750_000_000, 1_750_000_000, 0, 'PAP 2026'],
            ['2011', 'AXE 1 : RENFORCER LA GOUVERNANCE POLITIQUE', 'axe', '201', 655_000_000, 655_000_000, 0, 'PAP 2026'],
            ['20111', 'Fonctionnement du Comite des Sages', 'produit', '2011', 150_000_000, 150_000_000, 0, 'PAP 2026'],
            ['201111', 'Exemple de sous-produit', 'sous_produit', '20111', 150_000_000, 150_000_000, 0, 'PAP 2026'],
        ];
    }
}
