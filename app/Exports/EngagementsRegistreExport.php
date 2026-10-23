<?php

namespace App\Exports;

use App\Models\Engagement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EngagementsRegistreExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Engagement>  $engagements
     */
    public function __construct(protected Collection $engagements)
    {
    }

    /**
     * @return Collection<int, Engagement>
     */
    public function collection(): Collection
    {
        return $this->engagements;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Numero', 'Type ERP', 'Type', 'Exercice', 'Departement', 'Objet', 'Montant', 'Statut', 'Date', 'Mode'];
    }

    /**
     * @param  Engagement  $engagement
     * @return array<int, string>
     */
    public function map($engagement): array
    {
        $statut = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : (string) $engagement->statut;

        return [
            (string) $engagement->numero,
            (string) ($engagement->engagementType?->libelle ?? ''),
            (string) $engagement->type_engagement,
            (string) ($engagement->exerciceBudgetaire?->annee ?? ''),
            (string) ($engagement->departement?->libelle ?? ''),
            (string) $engagement->objet,
            number_format((float) $engagement->montant, 2, '.', ''),
            $statut,
            $engagement->date_engagement?->format('Y-m-d') ?? '',
            (string) ($engagement->mode_creation ?? ''),
        ];
    }
}
