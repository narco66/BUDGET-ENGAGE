<?php

namespace App\Services\ExpressionBesoin;

use App\Models\ExpressionBesoin;
use App\Models\GedDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Dépôt automatique des pièces justificatives d'un besoin dans la GED, avec
 * rattachement polymorphe. Aucun document n'est stocké hors de la GED.
 */
class ExpressionBesoinGedService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    /**
     * Dépose une pièce dans la GED et la rattache au besoin.
     */
    public function deposer(
        ExpressionBesoin $besoin,
        UploadedFile $file,
        User $user,
        ?string $titre = null,
        ?int $typeDocumentId = null,
        string $confidentialite = 'interne',
    ): GedDocument {
        return DB::transaction(function () use ($besoin, $file, $user, $titre, $typeDocumentId, $confidentialite): GedDocument {
            $document = $this->ged->store([
                'titre' => $titre ?: $file->getClientOriginalName(),
                'type_document_id' => $typeDocumentId,
                'niveau_confidentialite' => $confidentialite,
                'module_source' => 'expression_besoin',
                'module_source_id' => $besoin->id,
                'exercice_budgetaire_id' => $besoin->exercice_budgetaire_id,
                'departement_id' => $besoin->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $besoin->gedAttachments()->create([
                'ged_document_id' => $document->id,
                'role' => 'piece_justificative',
                'created_by' => $user->id,
            ]);

            return $document;
        });
    }
}
