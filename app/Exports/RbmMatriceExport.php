<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RbmMatriceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  array<int, array<string, string>>  $rows
     */
    public function __construct(
        protected array $rows
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Pilier', 'Axe', 'Produit', 'Sous-produit', 'Activité', 'Tâche', 'Indicateurs'];
    }

    /**
     * @param  array<string, string>  $row
     * @return array<int, string>
     */
    public function map($row): array
    {
        return [
            $row['pilier'],
            $row['axe'],
            $row['produit'],
            $row['sous_produit'],
            $row['activite'],
            $row['tache'],
            $row['indicateurs'],
        ];
    }
}
