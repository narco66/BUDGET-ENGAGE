<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RbmReferentialExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $columns
     */
    public function __construct(
        protected string $modelClass,
        protected array $columns
    ) {}

    public function collection(): Collection
    {
        return $this->modelClass::query()->orderBy('code')->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->columns;
    }
}
