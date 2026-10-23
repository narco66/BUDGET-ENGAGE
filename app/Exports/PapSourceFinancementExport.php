<?php

namespace App\Exports;

use App\Exports\Pap\PapSourceDetailSheet;
use App\Exports\Pap\PapSourceSyntheseSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PapSourceFinancementExport implements WithMultipleSheets
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  Lignes a plat (pilier/axe/produit).
     * @param  array<string, mixed>  $synthese
     */
    public function __construct(private array $rows, private array $synthese, private int $annee) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new PapSourceSyntheseSheet($this->synthese, $this->annee),
            new PapSourceDetailSheet($this->rows),
        ];
    }
}
