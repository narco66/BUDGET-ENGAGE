<?php

namespace App\Services\Ordonnancement;

use App\Models\GedDocument;
use App\Models\Ordonnancement;
use App\Models\OrdonnancementDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Gestion documentaire de l'ordonnancement. Toute pièce est stockée dans la GED
 * (aucun document hors GED), rattachée par lien polymorphe, et classée dans
 * ordonnancement_documents en « spécifique » ou « hérité » (de la liquidation /
 * de l'engagement / du besoin via la chaîne).
 */
class OrdonnancementGedService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    public function deposer(
        Ordonnancement $ordonnancement,
        UploadedFile $file,
        User $user,
        ?string $titre = null,
        ?string $typePiece = null,
        ?int $typeDocumentId = null,
        string $confidentialite = 'interne',
    ): GedDocument {
        return DB::transaction(function () use ($ordonnancement, $file, $user, $titre, $typePiece, $typeDocumentId, $confidentialite): GedDocument {
            $ordonnancement->loadMissing('liquidation.engagement');
            $engagement = $ordonnancement->liquidation?->engagement;

            $document = $this->ged->store([
                'titre' => $titre ?: $file->getClientOriginalName(),
                'type_document_id' => $typeDocumentId,
                'niveau_confidentialite' => $confidentialite,
                'module_source' => 'ordonnancement',
                'module_source_id' => $ordonnancement->id,
                'exercice_budgetaire_id' => $ordonnancement->exercice_budgetaire_id ?? $engagement?->exercice_budgetaire_id,
                'departement_id' => $engagement?->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $ordonnancement->gedAttachments()->firstOrCreate(
                ['ged_document_id' => $document->id],
                ['role' => 'piece_ordonnancement', 'created_by' => $user->id],
            );

            $this->enregistrerDocument($ordonnancement, $document->id, 'specifique', $typePiece, 'ordonnancement', $user);

            return $document;
        });
    }

    /**
     * Hérite des pièces GED de la liquidation (qui porte déjà la chaîne
     * engagement / besoin) vers l'ordonnancement.
     */
    public function heriterDepuisLiquidation(Ordonnancement $ordonnancement, User $user): int
    {
        $ordonnancement->loadMissing('liquidation.gedAttachments');
        $liquidation = $ordonnancement->liquidation;

        if ($liquidation === null) {
            return 0;
        }

        $count = 0;

        DB::transaction(function () use ($ordonnancement, $liquidation, $user, &$count): void {
            foreach ($liquidation->gedAttachments as $attachment) {
                $ordonnancement->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_liquidation', 'created_by' => $user->id],
                );

                $this->enregistrerDocument($ordonnancement, $attachment->ged_document_id, 'herite', null, 'liquidation', $user);
                $count++;
            }
        });

        return $count;
    }

    public function enregistrerDocument(
        Ordonnancement $ordonnancement,
        ?int $gedDocumentId,
        string $categorie,
        ?string $typePiece,
        ?string $source,
        User $user,
    ): OrdonnancementDocument {
        return OrdonnancementDocument::query()->updateOrCreate(
            [
                'ordonnancement_id' => $ordonnancement->id,
                'ged_document_id' => $gedDocumentId,
            ],
            [
                'categorie' => $categorie,
                'type_piece' => $typePiece,
                'source' => $source,
                'created_by' => $user->id,
            ],
        );
    }
}
