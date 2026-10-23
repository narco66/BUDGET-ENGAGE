<?php

namespace App\Models;

use App\Enums\BeneficiaireType;
use App\Enums\ModeCreationOrdonnancement;
use App\Enums\StatutOrdonnancement;
use App\Models\Concerns\HasGedDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ordonnancement extends Model
{
    use HasGedDocuments;
    use SoftDeletes;

    protected $fillable = [
        'liquidation_id',
        'engagement_imputation_id',
        'ordonnancement_type_id',
        'parent_ordonnancement_id',
        'exercice_budgetaire_id',
        'fournisseur_id',
        'montant_ordonnance',
        'date_ordonnancement',
        'reference',
        'numero',
        'mode_creation',
        'statut',
        'objet',
        'devise',
        'justification',
        'beneficiaire_type',
        'beneficiaire_id',
        'montant_liquide_ref',
        'reste_a_ordonnancer',
        'taux_ordonnancement',
        'montant_transmis_paiement',
        'submitted_at',
        'controle_budgetaire_at',
        'controle_comptable_at',
        'controle_financier_at',
        'visa_ordonnateur_at',
        'valide_at',
        'mandate_at',
        'transmis_paiement_at',
        'mandat_numero',
        'visa_numero',
        'visa_hash',
        'transmis_paiement_par',
        'validated_by',
        'commentaire',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'motif_annulation',
    ];

    protected function casts(): array
    {
        return [
            'montant_ordonnance' => 'decimal:2',
            'montant_liquide_ref' => 'decimal:2',
            'reste_a_ordonnancer' => 'decimal:2',
            'taux_ordonnancement' => 'decimal:2',
            'montant_transmis_paiement' => 'decimal:2',
            'date_ordonnancement' => 'date',
            'statut' => StatutOrdonnancement::class,
            'mode_creation' => ModeCreationOrdonnancement::class,
            'beneficiaire_type' => BeneficiaireType::class,
            'submitted_at' => 'datetime',
            'controle_budgetaire_at' => 'datetime',
            'controle_comptable_at' => 'datetime',
            'controle_financier_at' => 'datetime',
            'visa_ordonnateur_at' => 'datetime',
            'valide_at' => 'datetime',
            'mandate_at' => 'datetime',
            'transmis_paiement_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function engagementImputation(): BelongsTo
    {
        return $this->belongsTo(EngagementImputation::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(OrdonnancementType::class, 'ordonnancement_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_ordonnancement_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_ordonnancement_id');
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(OrdonnancementLigne::class)->orderBy('ordre');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(OrdonnancementValidation::class)->orderBy('ordre');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrdonnancementDocument::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(OrdonnancementVersion::class)->orderByDesc('numero_version');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(OrdonnancementHistorique::class)->latest();
    }

    public function mandats(): HasMany
    {
        return $this->hasMany(OrdonnancementMandat::class);
    }

    public function mandat(): HasOne
    {
        return $this->hasOne(OrdonnancementMandat::class)->latestOfMany();
    }

    public function ordresPaiement(): HasMany
    {
        return $this->hasMany(OrdonnancementOrdrePaiement::class);
    }

    public function calculs(): HasMany
    {
        return $this->hasMany(OrdonnancementCalcul::class)->latest();
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function transmetteurPaiement(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmis_paiement_par');
    }

    public function annulateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function beneficiaire(): ?Model
    {
        return match ($this->beneficiaire_type) {
            BeneficiaireType::Fournisseur => $this->beneficiaire_id ? Fournisseur::find($this->beneficiaire_id) : null,
            BeneficiaireType::Agent => $this->beneficiaire_id ? Agent::find($this->beneficiaire_id) : null,
            default => $this->fournisseur,
        };
    }
}
