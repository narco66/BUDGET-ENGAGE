<?php

namespace App\Models;

use App\Enums\CategorieBesoinHorsPap;
use App\Enums\NiveauUrgenceBesoin;
use App\Enums\PrioriteBesoin;
use App\Enums\StatutBesoin;
use App\Enums\TypeBesoin;
use App\Models\Concerns\GeneratesGedUuid;
use App\Models\Concerns\HasGedDocuments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpressionBesoin extends Model
{
    use GeneratesGedUuid;
    use HasFactory;
    use HasGedDocuments;
    use SoftDeletes;

    protected $table = 'expression_besoins';

    protected $fillable = [
        'uuid',
        'reference',
        'exercice_budgetaire_id',
        'type_besoin',
        'structure_demandeuse',
        'departement_id',
        'direction_id',
        'service_id',
        'demandeur_id',
        'date_besoin',
        'objet',
        'description',
        'priorite',
        'niveau_urgence',
        'justification',
        'objectif_recherche',
        'consequences_non_realisation',
        'observations',
        'pilier_id',
        'axe_strategique_id',
        'produit_id',
        'sous_produit_id',
        'activite_id',
        'tache_id',
        'indicateur_id',
        'responsable_activite',
        'categorie_hors_pap',
        'sous_categorie',
        'nature_besoin',
        'centre_cout',
        'centre_responsabilite',
        'montant_total',
        'statut',
        'version_revision',
        'derniere_revision_at',
        'derniere_revision_motif',
        'submitted_at',
        'validated_at',
        'rejected_at',
        'motif_rejet',
        'engagement_id',
        'converted_at',
        'converted_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type_besoin' => TypeBesoin::class,
            'statut' => StatutBesoin::class,
            'priorite' => PrioriteBesoin::class,
            'niveau_urgence' => NiveauUrgenceBesoin::class,
            'categorie_hors_pap' => CategorieBesoinHorsPap::class,
            'montant_total' => 'decimal:2',
            'version_revision' => 'integer',
            'date_besoin' => 'date',
            'derniere_revision_at' => 'datetime',
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'rejected_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    /* Relations — structure / acteurs */

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

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* Relations — rattachement PAP (RBM) */

    public function pilier(): BelongsTo
    {
        return $this->belongsTo(Pilier::class);
    }

    public function axeStrategique(): BelongsTo
    {
        return $this->belongsTo(AxeStrategique::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function sousProduit(): BelongsTo
    {
        return $this->belongsTo(SousProduit::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }

    public function indicateur(): BelongsTo
    {
        return $this->belongsTo(Indicateur::class);
    }

    /* Relations — contenu & cycle */

    public function lignes(): HasMany
    {
        return $this->hasMany(ExpressionBesoinLigne::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ExpressionBesoinValidation::class)->orderBy('ordre');
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function convertisseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /* Scopes */

    public function scopePap(Builder $query): Builder
    {
        return $query->where('type_besoin', TypeBesoin::Pap->value);
    }

    public function scopeHorsPap(Builder $query): Builder
    {
        return $query->where('type_besoin', TypeBesoin::HorsPap->value);
    }

    public function estPap(): bool
    {
        return $this->type_besoin === TypeBesoin::Pap;
    }
}
