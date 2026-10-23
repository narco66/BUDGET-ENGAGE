<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export Excel générique d'un état de liquidation (en-têtes + lignes déjà
 * formatées par LiquidationReportingService::buildReport).
 */
class LiquidationReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string>>  $rows
     */
    public function __construct(
        private array $columns,
        private array $rows,
        private string $title = 'État',
    ) {
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->columns;
    }

    public function title(): string
    {
        return \Illuminate\Support\Str::limit($this->title, 28, '');
    }
}
