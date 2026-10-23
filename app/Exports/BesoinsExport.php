<?php

namespace App\Exports;

use App\Models\ExpressionBesoin;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BesoinsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, ExpressionBesoin>  $besoins
     */
    public function __construct(protected Collection $besoins)
    {
    }

    /**
     * @return Collection<int, ExpressionBesoin>
     */
    public function collection(): Collection
    {
        return $this->besoins;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Reference', 'Type', 'Objet', 'Exercice', 'Statut', 'Montant', 'Departement', 'Cree par', 'Date'];
    }

    /**
     * @param  ExpressionBesoin  $besoin
     * @return array<int, string>
     */
    public function map($besoin): array
    {
        return [
            (string) $besoin->reference,
            $besoin->type_besoin->value,
            (string) $besoin->objet,
            (string) ($besoin->exerciceBudgetaire?->annee ?? ''),
            $besoin->statut->value,
            number_format((float) $besoin->montant_total, 2, '.', ''),
            (string) ($besoin->departement?->libelle ?? ''),
            (string) ($besoin->createur?->name ?? ''),
            $besoin->date_besoin?->format('Y-m-d') ?? '',
        ];
    }
}
