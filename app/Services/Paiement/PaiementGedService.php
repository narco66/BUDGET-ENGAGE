<?php

namespace App\Services\Paiement;

use App\Models\GedDocument;
use App\Models\Paiement;
use App\Models\PaiementDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Gestion documentaire du paiement. Toute pièce est stockée dans la GED (aucun
 * document hors GED), rattachée par lien polymorphe, et classée dans
 * paiement_documents en « spécifique » ou « hérité » (de l'ordonnancement et de
 * la chaîne amont).
 */
class PaiementGedService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    public function deposer(
        Paiement $paiement,
        UploadedFile $file,
        User $user,
        ?string $titre = null,
        ?string $typePiece = null,
        ?int $typeDocumentId = null,
        string $confidentialite = 'interne',
    ): GedDocument {
        return DB::transaction(function () use ($paiement, $file, $user, $titre, $typePiece, $typeDocumentId, $confidentialite): GedDocument {
            $paiement->loadMissing('ordonnancement.liquidation.engagement');
            $engagement = $paiement->ordonnancement?->liquidation?->engagement;

            $document = $this->ged->store([
                'titre' => $titre ?: $file->getClientOriginalName(),
                'type_document_id' => $typeDocumentId,
                'niveau_confidentialite' => $confidentialite,
                'module_source' => 'paiement',
                'module_source_id' => $paiement->id,
                'exercice_budgetaire_id' => $paiement->exercice_budgetaire_id ?? $engagement?->exercice_budgetaire_id,
                'departement_id' => $engagement?->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $paiement->gedAttachments()->firstOrCreate(
                ['ged_document_id' => $document->id],
                ['role' => 'piece_paiement', 'created_by' => $user->id],
            );

            $this->enregistrerDocument($paiement, $document->id, 'specifique', $typePiece, 'paiement', $user);

            return $document;
        });
    }

    /**
     * Hérite des pièces GED de l'ordonnancement (qui porte déjà toute la chaîne).
     */
    public function heriterDepuisOrdonnancement(Paiement $paiement, User $user): int
    {
        $paiement->loadMissing('ordonnancement.gedAttachments');
        $ordonnancement = $paiement->ordonnancement;

        if ($ordonnancement === null) {
            return 0;
        }

        $count = 0;

        DB::transaction(function () use ($paiement, $ordonnancement, $user, &$count): void {
            foreach ($ordonnancement->gedAttachments as $attachment) {
                $paiement->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_ordonnancement', 'created_by' => $user->id],
                );

                $this->enregistrerDocument($paiement, $attachment->ged_document_id, 'herite', null, 'ordonnancement', $user);
                $count++;
            }
        });

        return $count;
    }

    public function enregistrerDocument(
        Paiement $paiement,
        ?int $gedDocumentId,
        string $categorie,
        ?string $typePiece,
        ?string $source,
        User $user,
    ): PaiementDocument {
        return PaiementDocument::query()->updateOrCreate(
            [
                'paiement_id' => $paiement->id,
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
