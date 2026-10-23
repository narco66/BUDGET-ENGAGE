<?php

namespace App\Services\Ged;

use App\Enums\StatutDocumentGed;
use App\Models\GedArchive;
use App\Models\GedDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Archivage électronique : verrouillage en lecture seule, journal d'archivage
 * et restauration contrôlée. Un document à conservation obligatoire ne peut
 * pas être restauré (ni détruit).
 */
class GedArchiveService
{
    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    public function archive(GedDocument $document, User $user, ?string $motif = null): GedArchive
    {
        if ($document->est_archive) {
            throw ValidationException::withMessages(['archive' => __('Document déjà archivé.')]);
        }

        return DB::transaction(function () use ($document, $user, $motif): GedArchive {
            $annee = $document->date_document?->year ?? (int) now()->year;

            $archive = $document->archive()->create([
                'reference_archive' => $this->referenceArchive($document, $annee),
                'annee' => $annee,
                'module' => $document->module_source,
                'type_document' => $document->typeDocument?->libelle,
                'structure' => $document->departement?->libelle,
                'motif' => $motif,
                'chemin_archive' => $document->chemin_fichier,
                'hash_sha256' => $document->hash_sha256,
                'est_verrouille' => true,
                'archive_par' => $user->id,
                'date_archivage' => now(),
            ]);

            $document->update([
                'est_archive' => true,
                'est_verrouille' => true,
                'date_archivage' => now(),
                'archived_by' => $user->id,
                'statut' => StatutDocumentGed::Archive->value,
                'motif_archivage' => $motif,
                'updated_by' => $user->id,
            ]);

            $this->documents->journal($document, $user, 'archivage', null, [
                'reference_archive' => $archive->reference_archive,
            ], $motif);

            return $archive;
        });
    }

    public function restore(GedDocument $document, User $user): void
    {
        if (! $document->est_archive) {
            throw ValidationException::withMessages(['archive' => __("Ce document n'est pas archivé.")]);
        }

        $document->loadMissing('conservationRule');
        if ($document->conservationRule?->conservation_obligatoire) {
            throw ValidationException::withMessages([
                'archive' => __('Document à conservation obligatoire : restauration interdite.'),
            ]);
        }

        DB::transaction(function () use ($document, $user): void {
            $document->update([
                'est_archive' => false,
                'est_verrouille' => false,
                'statut' => StatutDocumentGed::Valide->value,
                'updated_by' => $user->id,
            ]);

            $document->archive()->latest('id')->first()?->update([
                'restaure_par' => $user->id,
                'date_restauration' => now(),
                'est_verrouille' => false,
            ]);

            $this->documents->journal($document, $user, 'restauration_archive');
        });
    }

    private function referenceArchive(GedDocument $document, int $annee): string
    {
        $base = $document->reference ?: ('DOC-'.$document->id);

        return sprintf('ARCH/%d/%s', $annee, $base);
    }
}
