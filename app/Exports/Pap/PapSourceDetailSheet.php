<?php

namespace App\Exports\Pap;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PapSourceDetailSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  Lignes a plat (pilier/axe/produit).
     */
    public function __construct(private array $rows) {}

    public function title(): string
    {
        return 'Detail par source';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Niveau', 'Code', 'Libelle', 'CEEAC (FCFA)', 'PTF (FCFA)', 'Total (FCFA)', 'Part CEEAC (%)', 'Part PTF (%)'];
    }

    /**
     * @return array<int, array<int, string|float|int>>
     */
    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $r) {
            $total = (float) $r['montant_total'];
            $ceeac = (float) $r['montant_ceeac'];
            $ptf = (float) $r['montant_ptf'];
            $data[] = [
                $r['niveau'], $r['code'], $r['libelle'], $ceeac, $ptf, $total,
                $total > 0 ? round($ceeac / $total * 100, 1) : 0,
                $total > 0 ? round($ptf / $total * 100, 1) : 0,
            ];
        }

        return $data;
    }
}
