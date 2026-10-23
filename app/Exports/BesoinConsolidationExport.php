<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BesoinConsolidationExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, object>  $lignes
     */
    public function __construct(protected Collection $lignes)
    {
    }

    public function collection(): Collection
    {
        return $this->lignes;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Exercice', 'Nombre', 'Montant demande', 'Montant valide', 'Montant converti', 'Dont PAP', 'Dont Hors PAP'];
    }

    /**
     * @param  object  $ligne
     * @return array<int, string>
     */
    public function map($ligne): array
    {
        return [
            (string) $ligne->annee,
            (string) $ligne->nb,
            number_format((float) $ligne->demande, 2, '.', ''),
            number_format((float) $ligne->valide, 2, '.', ''),
            number_format((float) $ligne->converti, 2, '.', ''),
            number_format((float) $ligne->pap, 2, '.', ''),
            number_format((float) $ligne->hors_pap, 2, '.', ''),
        ];
    }
}
