<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PapProgrammationExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  Lignes a plat (pilier/axe/produit).
     * @param  array<string, mixed>  $synthese
     */
    public function __construct(private array $rows, private array $synthese, private int $annee) {}

    public function title(): string
    {
        return 'Programmation PAP '.$this->annee;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Niveau', 'Code', 'Libelle', 'Montant total (FCFA)', 'CEEAC', 'PTF',
            'Budgete', 'Ecart', 'Couverture (%)',
        ];
    }

    /**
     * @return array<int, array<int, string|float|int>>
     */
    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $r) {
            $data[] = [
                $r['niveau'], $r['code'], $r['libelle'],
                $r['montant_total'], $r['montant_ceeac'], $r['montant_ptf'],
                $r['montant_budgete'], $r['ecart'], $r['taux_couverture'],
            ];
        }

        $data[] = ['', '', 'TOTAL', $this->synthese['montant_total'], $this->synthese['montant_ceeac'],
            $this->synthese['montant_ptf'], $this->synthese['montant_budgete'],
            $this->synthese['montant_total'] - $this->synthese['montant_budgete'], $this->synthese['taux_couverture']];

        return $data;
    }
}
