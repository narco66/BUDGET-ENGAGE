<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\GedDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Dépôt automatique des pièces d'un engagement dans la GED, avec rattachement
 * polymorphe. Aucun document n'est stocké hors de la GED.
 */
class EngagementGedService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    public function deposer(
        Engagement $engagement,
        UploadedFile $file,
        User $user,
        ?string $titre = null,
        ?int $typeDocumentId = null,
        string $confidentialite = 'interne',
    ): GedDocument {
        return DB::transaction(function () use ($engagement, $file, $user, $titre, $typeDocumentId, $confidentialite): GedDocument {
            $document = $this->ged->store([
                'titre' => $titre ?: $file->getClientOriginalName(),
                'type_document_id' => $typeDocumentId,
                'niveau_confidentialite' => $confidentialite,
                'module_source' => 'engagement',
                'module_source_id' => $engagement->id,
                'exercice_budgetaire_id' => $engagement->exercice_budgetaire_id,
                'departement_id' => $engagement->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $engagement->gedAttachments()->firstOrCreate(
                ['ged_document_id' => $document->id],
                ['role' => 'piece_engagement', 'created_by' => $user->id],
            );

            return $document;
        });
    }
}
