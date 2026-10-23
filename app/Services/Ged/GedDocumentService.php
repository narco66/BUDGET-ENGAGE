<?php

namespace App\Services\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\StatutDocumentGed;
use App\Models\GedDocument;
use App\Models\GedJournal;
use App\Models\GedTypeDocument;
use App\Models\GedVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service central de gestion des fichiers documentaires GED :
 * stockage sécurisé (disque privé), calcul SHA-256, codification automatique
 * de la référence, versioning sans suppression physique, journalisation.
 */
class GedDocumentService
{
    public const DISK = 'local'; // storage/app/private

    /**
     * Dépose un nouveau document avec son fichier (version 1).
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, UploadedFile $file, User $user): GedDocument
    {
        return DB::transaction(function () use ($data, $file, $user): GedDocument {
            $type = isset($data['type_document_id'])
                ? GedTypeDocument::query()->find($data['type_document_id'])
                : null;

            $annee = $this->resolveYear($data['date_document'] ?? null);
            $stored = $this->storeFile($file, $data['module_source'] ?? null, $annee);

            $data['reference'] = $this->generateReference($type, $data['module_source'] ?? null, $annee);
            $data['statut'] = StatutDocumentGed::Depose->value;
            $data['niveau_confidentialite'] = $data['niveau_confidentialite']
                ?? ($type?->niveau_confidentialite_defaut?->value ?? NiveauConfidentialiteGed::Interne->value);
            $data['version_courante'] = 1;
            $data['date_depot'] = now();
            $data['created_by'] = $user->id;
            $data = array_merge($data, $stored);

            $document = GedDocument::query()->create($data);

            GedVersion::query()->create([
                'ged_document_id' => $document->id,
                'numero_version' => 1,
                'chemin_fichier' => $stored['chemin_fichier'],
                'nom_original' => $stored['nom_original'],
                'nom_stocke' => $stored['nom_stocke'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'taille' => $stored['taille'],
                'hash_sha256' => $stored['hash_sha256'],
                'commentaire' => 'Version initiale (dépôt).',
                'statut' => $document->statut->value,
                'created_by' => $user->id,
            ]);

            $this->journal($document, $user, 'depot', null, [
                'reference' => $document->reference,
                'hash_sha256' => $document->hash_sha256,
            ]);

            return $document;
        });
    }

    /**
     * Ajoute une nouvelle version de fichier à un document existant.
     * Les anciennes versions ne sont jamais supprimées physiquement.
     */
    public function addVersion(GedDocument $document, UploadedFile $file, ?string $commentaire, User $user): GedVersion
    {
        return DB::transaction(function () use ($document, $file, $commentaire, $user): GedVersion {
            $document = GedDocument::query()->whereKey($document->id)->lockForUpdate()->firstOrFail();

            $annee = $this->resolveYear($document->date_document);
            $stored = $this->storeFile($file, $document->module_source, $annee);
            $numero = (int) $document->version_courante + 1;

            $version = GedVersion::query()->create([
                'ged_document_id' => $document->id,
                'numero_version' => $numero,
                'chemin_fichier' => $stored['chemin_fichier'],
                'nom_original' => $stored['nom_original'],
                'nom_stocke' => $stored['nom_stocke'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'taille' => $stored['taille'],
                'hash_sha256' => $stored['hash_sha256'],
                'commentaire' => $commentaire,
                'statut' => $document->statut?->value,
                'created_by' => $user->id,
            ]);

            $document->update(array_merge($stored, [
                'version_courante' => $numero,
                'updated_by' => $user->id,
            ]));

            $this->journal($document, $user, 'nouvelle_version', null, [
                'numero_version' => $numero,
                'hash_sha256' => $stored['hash_sha256'],
            ]);

            return $version;
        });
    }

    /**
     * Stocke physiquement le fichier et retourne ses métadonnées.
     *
     * @return array{chemin_fichier:string, nom_original:string, nom_stocke:string, extension:string, mime_type:string, taille:int, hash_sha256:string}
     */
    public function storeFile(UploadedFile $file, ?string $module, int $annee): array
    {
        $moduleSlug = Str::slug($module ?: 'general');
        $repertoire = sprintf('ged/%d/%s/%s', $annee, now()->format('m'), $moduleSlug);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $nomStocke = Str::uuid()->toString().'.'.$extension;
        $hash = hash_file('sha256', $file->getRealPath());

        $chemin = $file->storeAs($repertoire, $nomStocke, self::DISK);

        return [
            'chemin_fichier' => $chemin,
            'nom_original' => $file->getClientOriginalName(),
            'nom_stocke' => $nomStocke,
            'extension' => $extension,
            'mime_type' => $file->getClientMimeType(),
            'taille' => (int) $file->getSize(),
            'hash_sha256' => $hash,
        ];
    }

    /**
     * Codification documentaire : CEEAC/GED/{MODULE}/{TYPE}/{ANNEE}/{NUMERO}.
     */
    public function generateReference(?GedTypeDocument $type, ?string $module, int $annee): string
    {
        $moduleCode = strtoupper(Str::slug($module ?: 'doc', '_'));
        $typeCode = strtoupper((string) ($type?->prefixe_reference ?: $type?->code ?: 'DOC'));

        $prefixe = sprintf('CEEAC/GED/%s/%s/%d/', $moduleCode, $typeCode, $annee);

        $seq = GedDocument::withTrashed()
            ->where('reference', 'like', $prefixe.'%')
            ->count() + 1;

        do {
            $reference = $prefixe.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $seq++;
        } while (GedDocument::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    public function fileExists(GedDocument $document): bool
    {
        return $document->chemin_fichier !== null
            && Storage::disk(self::DISK)->exists($document->chemin_fichier);
    }

    public function absolutePath(string $chemin): string
    {
        return Storage::disk(self::DISK)->path($chemin);
    }

    /**
     * Empreinte SHA-256 recalculée sur le fichier physique de la version courante.
     * Retourne null si le fichier est absent (utile pour la vérification d'intégrité).
     */
    public function fileHash(GedDocument $document): ?string
    {
        if (! $this->fileExists($document)) {
            return null;
        }

        return hash_file('sha256', $this->absolutePath($document->chemin_fichier));
    }

    private function resolveYear(mixed $date): int
    {
        if ($date instanceof \DateTimeInterface) {
            return (int) $date->format('Y');
        }

        if (is_string($date) && $date !== '') {
            return (int) date('Y', strtotime($date));
        }

        return (int) now()->format('Y');
    }

    /**
     * @param  array<string, mixed>|null  $anciennes
     * @param  array<string, mixed>|null  $nouvelles
     */
    public function journal(?GedDocument $document, User $user, string $action, ?array $anciennes = null, ?array $nouvelles = null, ?string $commentaire = null): void
    {
        GedJournal::query()->create([
            'ged_document_id' => $document?->id,
            'user_id' => $user->id,
            'action' => $action,
            'module_source' => $document?->module_source,
            'anciennes_valeurs' => $anciennes,
            'nouvelles_valeurs' => $nouvelles,
            'commentaire' => $commentaire,
            'adresse_ip' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
