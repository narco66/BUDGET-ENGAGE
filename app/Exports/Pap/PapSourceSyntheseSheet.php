<?php

namespace App\Exports\Pap;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PapSourceSyntheseSheet implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<string, mixed>  $synthese
     */
    public function __construct(private array $synthese, private int $annee) {}

    public function title(): string
    {
        return 'Synthese';
    }

    /**
     * @return array<int, array<int, string|float>>
     */
    public function array(): array
    {
        $total = (float) $this->synthese['montant_total'];
        $ceeac = (float) $this->synthese['montant_ceeac'];
        $ptf = (float) $this->synthese['montant_ptf'];

        return [
            ['REPARTITION PAR SOURCE DE FINANCEMENT — PAP '.$this->annee],
            [''],
            ['Source', 'Montant (FCFA)', 'Part (%)'],
            ['CEEAC', $ceeac, $total > 0 ? round($ceeac / $total * 100, 1) : 0],
            ['PTF (Partenaires Techniques et Financiers)', $ptf, $total > 0 ? round($ptf / $total * 100, 1) : 0],
            ['TOTAL', $total, 100],
        ];
    }
}
