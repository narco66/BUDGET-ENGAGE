<?php

namespace App\Exports;

use App\Models\GedDocument;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GedDocumentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, GedDocument>  $documents
     */
    public function __construct(protected Collection $documents)
    {
    }

    /**
     * @return Collection<int, GedDocument>
     */
    public function collection(): Collection
    {
        return $this->documents;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Reference', 'Titre', 'Type', 'Categorie', 'Statut', 'Confidentialite',
            'Version', 'Date document', 'Date depot', 'Expiration', 'Module', 'SHA-256', 'Depose par',
        ];
    }

    /**
     * @param  GedDocument  $document
     * @return array<int, string>
     */
    public function map($document): array
    {
        return [
            (string) $document->reference,
            (string) $document->titre,
            (string) ($document->typeDocument?->libelle ?? ''),
            (string) ($document->categorie?->libelle ?? ''),
            $document->statut->value,
            $document->niveau_confidentialite->value,
            (string) $document->version_courante,
            $document->date_document?->format('Y-m-d') ?? '',
            $document->date_depot?->format('Y-m-d H:i') ?? '',
            $document->date_expiration?->format('Y-m-d') ?? '',
            (string) ($document->module_source ?? ''),
            (string) ($document->hash_sha256 ?? ''),
            (string) ($document->createur?->name ?? ''),
        ];
    }
}
