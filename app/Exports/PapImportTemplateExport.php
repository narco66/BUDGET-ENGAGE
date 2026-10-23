<?php

namespace App\Exports;

use App\Exports\Pap\PapTemplateInstructionsSheet;
use App\Exports\Pap\PapTemplateLignesSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PapImportTemplateExport implements WithMultipleSheets
{
    /**
     * @param  array<int, array<int, string|int|float>>  $lignes  Donnees reelles a pre-remplir (optionnel).
     */
    public function __construct(private array $lignes = []) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new PapTemplateInstructionsSheet,
            new PapTemplateLignesSheet($this->lignes),
        ];
    }
}
