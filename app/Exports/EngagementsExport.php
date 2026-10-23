<?php

namespace App\Exports;

use App\Models\Engagement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EngagementsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Engagement>  $engagements
     */
    public function __construct(
        protected Collection $engagements
    ) {}

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
        return [
            'Numero',
            'Statut',
            'Exercice',
            'Ligne budgetaire',
            'Montant',
            'Date engagement',
            'Createur',
            'Score risque',
        ];
    }

    /**
     * @param  Engagement  $engagement
     * @return array<int, string>
     */
    public function map($engagement): array
    {
        $status = $engagement->statut instanceof \BackedEnum
            ? $engagement->statut->value
            : (string) $engagement->statut;

        return [
            $engagement->numero,
            $status,
            (string) ($engagement->exerciceBudgetaire?->annee ?? ''),
            (string) ($engagement->ligneBudgetaire?->code ?? ''),
            number_format((float) $engagement->montant, 2, '.', ''),
            $engagement->date_engagement?->format('Y-m-d') ?? '',
            (string) ($engagement->createur?->name ?? ''),
            $engagement->score_risque !== null ? (string) $engagement->score_risque : '',
        ];
    }
}
