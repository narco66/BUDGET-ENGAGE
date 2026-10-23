<?php

namespace App\Services\Ged;

use App\Enums\StatutDocumentGed;
use App\Enums\StatutSignatureGed;
use App\Models\GedDocument;
use App\Models\GedSignature;
use App\Models\User;
use App\Notifications\GedDocumentSignedNotification;
use App\Notifications\GedSignatureRequestedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Signature électronique interne (scellé cryptographique).
 *
 * Le scellé est un HMAC-SHA256 calculé sur l'empreinte SHA-256 du document,
 * l'identité du signataire, l'horodatage et la référence documentaire.
 * L'architecture est volontairement isolée pour permettre une bascule
 * ultérieure vers un prestataire de signature qualifiée (eIDAS) sans refonte.
 */
class GedSignatureService
{
    public const ALGORITHME = 'HMAC-SHA256';

    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    /**
     * Crée une demande de signature pour un signataire habilité.
     */
    public function request(GedDocument $document, User $signataire, User $demandeur): GedSignature
    {
        if ($document->hash_sha256 === null) {
            throw ValidationException::withMessages([
                'signature' => __('Le document ne dispose pas encore de fichier à signer.'),
            ]);
        }

        return DB::transaction(function () use ($document, $signataire, $demandeur): GedSignature {
            $signature = $document->signatures()->create([
                'signataire_id' => $signataire->id,
                'demande_par' => $demandeur->id,
                'statut' => StatutSignatureGed::EnAttente->value,
                'algorithme' => self::ALGORITHME,
            ]);

            $document->update(['signature_status' => StatutSignatureGed::EnAttente->value]);

            $this->documents->journal($document, $demandeur, 'demande_signature', null, [
                'signataire_id' => $signataire->id,
            ]);

            DB::afterCommit(fn () => $signataire->notify(new GedSignatureRequestedNotification($document)));

            return $signature;
        });
    }

    /**
     * Appose la signature : scelle le document et le verrouille.
     */
    public function sign(GedSignature $signature, User $user, ?string $adresseIp = null, ?string $userAgent = null): void
    {
        if ($signature->statut === StatutSignatureGed::Signe) {
            throw ValidationException::withMessages(['signature' => __('Cette demande est déjà signée.')]);
        }

        $document = $signature->document;

        if ($document->hash_sha256 === null) {
            throw ValidationException::withMessages(['signature' => __('Empreinte du document indisponible.')]);
        }

        DB::transaction(function () use ($signature, $document, $user, $adresseIp, $userAgent): void {
            $horodatage = now();
            $hashSigne = $document->hash_sha256;
            $scelle = $this->seal($hashSigne, $user->id, $horodatage->timestamp, (string) $document->reference);

            $signature->update([
                'signataire_id' => $user->id,
                'statut' => StatutSignatureGed::Signe->value,
                'algorithme' => self::ALGORITHME,
                'hash_signe' => $hashSigne,
                'signature_value' => $scelle,
                'certificat' => $this->certificatPayload($document, $user, $horodatage, $scelle),
                'horodatage' => $horodatage,
                'adresse_ip' => $adresseIp,
                'user_agent' => $userAgent,
            ]);

            $document->update([
                'est_signe' => true,
                'signature_status' => StatutSignatureGed::Signe->value,
                'statut' => StatutDocumentGed::Signe->value,
                'est_verrouille' => true,
                'updated_by' => $user->id,
            ]);

            $this->documents->journal($document, $user, 'signature_apposee', null, [
                'signature_uuid' => $signature->uuid,
                'horodatage' => $horodatage->toIso8601String(),
            ]);

            DB::afterCommit(function () use ($document, $user): void {
                $document->loadMissing('createur');
                $document->createur?->notify(new GedDocumentSignedNotification($document, $user->name));
            });
        });
    }

    public function reject(GedSignature $signature, User $user, string $motif): void
    {
        DB::transaction(function () use ($signature, $user, $motif): void {
            $signature->update([
                'statut' => StatutSignatureGed::Rejete->value,
                'motif_rejet' => $motif,
            ]);

            $signature->document->update(['signature_status' => StatutSignatureGed::Rejete->value]);

            $this->documents->journal($signature->document, $user, 'signature_rejetee', null, null, $motif);
        });
    }

    /**
     * Vérifie l'intégrité du document et la validité du scellé.
     *
     * @return array{signe:bool, integrite_fichier:bool, scelle_valide:bool, hash_actuel:?string}
     */
    public function verify(GedSignature $signature): array
    {
        $signature->loadMissing('document');
        $document = $signature->document;

        $signe = $signature->statut === StatutSignatureGed::Signe;
        $hashActuel = $document ? $this->documents->fileHash($document) : null;

        $integriteFichier = $signe
            && $hashActuel !== null
            && hash_equals((string) $signature->hash_signe, (string) $hashActuel);

        $scelleAttendu = $signe
            ? $this->seal(
                (string) $signature->hash_signe,
                (int) $signature->signataire_id,
                (int) $signature->horodatage?->timestamp,
                (string) $document?->reference,
            )
            : null;

        $scelleValide = $signe
            && $scelleAttendu !== null
            && hash_equals((string) $signature->signature_value, $scelleAttendu);

        return [
            'signe' => $signe,
            'integrite_fichier' => $integriteFichier,
            'scelle_valide' => $scelleValide,
            'hash_actuel' => $hashActuel,
        ];
    }

    private function seal(string $hash, int $signataireId, int $timestamp, string $reference): string
    {
        $payload = implode('|', [$hash, $signataireId, $timestamp, $reference]);

        return hash_hmac('sha256', $payload, $this->secret());
    }

    private function secret(): string
    {
        // Clé dérivée de l'APP_KEY de l'application (jamais exposée).
        return hash('sha256', 'ged-signature:'.config('app.key'));
    }

    private function certificatPayload(GedDocument $document, User $user, \DateTimeInterface $horodatage, string $scelle): string
    {
        return json_encode([
            'reference' => $document->reference,
            'document_uuid' => $document->uuid,
            'hash_sha256' => $document->hash_sha256,
            'signataire' => $user->name,
            'signataire_id' => $user->id,
            'horodatage' => $horodatage->format(DATE_ATOM),
            'algorithme' => self::ALGORITHME,
            'scelle' => $scelle,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
