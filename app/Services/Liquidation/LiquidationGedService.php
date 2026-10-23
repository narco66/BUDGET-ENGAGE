<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use App\Models\LiquidationDocument;
use App\Models\GedDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Gestion documentaire de la liquidation. Toute pièce est stockée dans la GED
 * (aucun document hors GED), rattachée par lien polymorphe, et classée dans
 * liquidation_documents en « spécifique » (propre à la liquidation) ou
 * « hérité » (provenant de l'engagement / de l'expression de besoins).
 */
class LiquidationGedService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    /**
     * Dépôt d'une pièce spécifique à la liquidation (facture, PV, décompte…).
     */
    public function deposer(
        Liquidation $liquidation,
        UploadedFile $file,
        User $user,
        ?string $titre = null,
        ?string $typePiece = null,
        ?int $typeDocumentId = null,
        string $confidentialite = 'interne',
    ): GedDocument {
        return DB::transaction(function () use ($liquidation, $file, $user, $titre, $typePiece, $typeDocumentId, $confidentialite): GedDocument {
            $liquidation->loadMissing('engagement');

            $document = $this->ged->store([
                'titre' => $titre ?: $file->getClientOriginalName(),
                'type_document_id' => $typeDocumentId,
                'niveau_confidentialite' => $confidentialite,
                'module_source' => 'liquidation',
                'module_source_id' => $liquidation->id,
                'exercice_budgetaire_id' => $liquidation->exercice_budgetaire_id ?? $liquidation->engagement?->exercice_budgetaire_id,
                'departement_id' => $liquidation->engagement?->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $liquidation->gedAttachments()->firstOrCreate(
                ['ged_document_id' => $document->id],
                ['role' => 'piece_liquidation', 'created_by' => $user->id],
            );

            $this->enregistrerDocument($liquidation, $document->id, 'specifique', $typePiece, 'liquidation', $user);

            return $document;
        });
    }

    /**
     * Hérite des pièces GED de l'engagement (et du besoin lié) vers la liquidation.
     */
    public function heriterDepuisEngagement(Liquidation $liquidation, User $user): int
    {
        $liquidation->loadMissing('engagement.gedAttachments');
        $engagement = $liquidation->engagement;

        if ($engagement === null) {
            return 0;
        }

        $count = 0;

        DB::transaction(function () use ($liquidation, $engagement, $user, &$count): void {
            foreach ($engagement->gedAttachments as $attachment) {
                $liquidation->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_engagement', 'created_by' => $user->id],
                );

                $this->enregistrerDocument($liquidation, $attachment->ged_document_id, 'herite', null, 'engagement', $user);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Inscrit (ou met à jour) une pièce dans le registre documentaire de la
     * liquidation sans la dupliquer.
     */
    public function enregistrerDocument(
        Liquidation $liquidation,
        ?int $gedDocumentId,
        string $categorie,
        ?string $typePiece,
        ?string $source,
        User $user,
    ): LiquidationDocument {
        return LiquidationDocument::query()->updateOrCreate(
            [
                'liquidation_id' => $liquidation->id,
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
