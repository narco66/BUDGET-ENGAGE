<?php

namespace App\Models;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\StatutDocumentGed;
use App\Enums\StatutSignatureGed;
use App\Models\Concerns\GeneratesGedUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedDocument extends Model
{
    use GeneratesGedUuid;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_documents';

    protected $fillable = [
        'uuid',
        'reference',
        'titre',
        'description',
        'categorie_id',
        'type_document_id',
        'dossier_id',
        'conservation_rule_id',
        'workflow_id',
        'module_source',
        'module_source_id',
        'exercice_budgetaire_id',
        'departement_id',
        'direction_id',
        'service_id',
        'chemin_fichier',
        'nom_original',
        'nom_stocke',
        'extension',
        'mime_type',
        'taille',
        'hash_sha256',
        'version_courante',
        'statut',
        'niveau_confidentialite',
        'date_document',
        'date_depot',
        'date_expiration',
        'date_archivage',
        'est_archive',
        'est_verrouille',
        'est_signe',
        'signature_status',
        'motif_archivage',
        'ocr_contenu',
        'created_by',
        'updated_by',
        'archived_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutDocumentGed::class,
            'niveau_confidentialite' => NiveauConfidentialiteGed::class,
            'signature_status' => StatutSignatureGed::class,
            'taille' => 'integer',
            'version_courante' => 'integer',
            'date_document' => 'date',
            'date_expiration' => 'date',
            'date_depot' => 'datetime',
            'date_archivage' => 'datetime',
            'est_archive' => 'boolean',
            'est_verrouille' => 'boolean',
            'est_signe' => 'boolean',
        ];
    }

    /* --------------------------------------------------------------------- */
    /* Relations — classement                                                */
    /* --------------------------------------------------------------------- */

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(GedCategorie::class, 'categorie_id');
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(GedTypeDocument::class, 'type_document_id');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(GedDossier::class, 'dossier_id');
    }

    public function conservationRule(): BelongsTo
    {
        return $this->belongsTo(GedConservationRule::class, 'conservation_rule_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(GedWorkflow::class, 'workflow_id');
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function serviceStructure(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /* --------------------------------------------------------------------- */
    /* Relations — contenu documentaire                                      */
    /* --------------------------------------------------------------------- */

    public function versions(): HasMany
    {
        return $this->hasMany(GedVersion::class, 'ged_document_id')->orderByDesc('numero_version');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(GedSignature::class, 'ged_document_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(GedValidation::class, 'ged_document_id')->orderBy('ordre');
    }

    public function metadonnees(): HasMany
    {
        return $this->hasMany(GedMetadonnee::class, 'ged_document_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(GedTag::class, 'ged_document_tags', 'ged_document_id', 'ged_tag_id')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(GedDocumentAttachment::class, 'ged_document_id');
    }

    public function partages(): HasMany
    {
        return $this->hasMany(GedPartage::class, 'ged_document_id');
    }

    public function acces(): HasMany
    {
        return $this->hasMany(GedAcces::class, 'ged_document_id');
    }

    public function archive(): HasMany
    {
        return $this->hasMany(GedArchive::class, 'ged_document_id');
    }

    public function journaux(): HasMany
    {
        return $this->hasMany(GedJournal::class, 'ged_document_id');
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(GedCommentaire::class, 'ged_document_id');
    }

    public function favoris(): HasMany
    {
        return $this->hasMany(GedFavori::class, 'ged_document_id');
    }

    public function numerisations(): HasMany
    {
        return $this->hasMany(GedNumerisation::class, 'ged_document_id');
    }

    /* --------------------------------------------------------------------- */
    /* Relations — audit                                                     */
    /* --------------------------------------------------------------------- */

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function archiveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /* --------------------------------------------------------------------- */
    /* Scopes utilitaires                                                     */
    /* --------------------------------------------------------------------- */

    public function scopeArchives(Builder $query): Builder
    {
        return $query->where('est_archive', true);
    }

    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('est_archive', false);
    }

    public function scopeExpires(Builder $query): Builder
    {
        return $query->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<=', now());
    }

    /**
     * Restreint la requête aux documents visibles par l'utilisateur :
     * tout pour l'administrateur GED, sinon ses propres documents et ceux dont
     * le niveau de confidentialité n'est ni « confidentiel » ni « très confidentiel ».
     * Source unique du cloisonnement (réutilisée par recherche, listings et exports).
     */
    public function scopeVisiblePour(Builder $query, User $user): Builder
    {
        if ($user->can('ged.admin')) {
            return $query;
        }

        $table = $this->getTable();

        return $query->where(function (Builder $b) use ($user, $table): void {
            $b->where($table.'.created_by', $user->id)
                ->orWhereNotIn($table.'.niveau_confidentialite', [
                    NiveauConfidentialiteGed::Confidentiel->value,
                    NiveauConfidentialiteGed::TresConfidentiel->value,
                ]);
        });
    }
}
