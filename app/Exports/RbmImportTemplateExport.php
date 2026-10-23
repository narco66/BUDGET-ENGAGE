<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RbmImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Code pilier',
            'Libelle pilier',
            'Code axe',
            'Libelle axe',
            'Code produit',
            'Libelle produit',
            'Code sous-produit',
            'Libelle sous-produit',
            'Code activite',
            'Libelle activite',
            'Code tache',
            'Libelle tache',
            'Code indicateur',
            'Libelle indicateur',
            'Code type indicateur',
            'Code unite mesure',
            'Ordre',
            'Statut',
        ];
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        return [
            [
                'P1',
                'Pilier institutionnel',
                'A1',
                'Axe strategique exemple',
                'PR1',
                'Produit exemple',
                'SP1',
                'Sous-produit exemple',
                'AC1',
                'Activite exemple',
                'T1',
                'Tache exemple',
                'IND1',
                'Indicateur exemple',
                'QUANT',
                'PCT',
                1,
                'actif',
            ],
        ];
    }
}
