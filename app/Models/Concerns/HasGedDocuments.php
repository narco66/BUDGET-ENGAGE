<?php

namespace App\Models\Concerns;

use App\Models\GedDocument;
use App\Models\GedDocumentAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Permet à n'importe quel modèle métier (Engagement, Liquidation,
 * Ordonnancement, Paiement, LigneBudgetaire, ExerciceBudgetaire…) de référencer
 * des documents GED via la table pivot polymorphe `ged_document_attachments`,
 * sans aucune modification de schéma sur la table métier elle-même.
 */
trait HasGedDocuments
{
    public function gedAttachments(): MorphMany
    {
        return $this->morphMany(GedDocumentAttachment::class, 'attachable');
    }

    public function gedDocuments(): MorphToMany
    {
        return $this->morphToMany(
            GedDocument::class,
            'attachable',
            'ged_document_attachments',
            'attachable_id',
            'ged_document_id'
        )->withPivot(['role'])->withTimestamps();
    }
}
