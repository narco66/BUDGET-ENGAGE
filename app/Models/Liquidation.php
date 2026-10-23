<?php

namespace App\Models;

use App\Enums\BeneficiaireType;
use App\Enums\ModeCreationLiquidation;
use App\Enums\StatutLiquidation;
use App\Models\Concerns\HasGedDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liquidation extends Model
{
    use HasGedDocuments;
    use SoftDeletes;

    protected $fillable = [
        'engagement_id',
        'engagement_imputation_id',
        'liquidation_type_id',
        'parent_liquidation_id',
        'exercice_budgetaire_id',
        'fournisseur_id',
        'montant_liquide',
        'date_liquidation',
        'reference',
        'numero',
        'mode_creation',
        'statut',
        'objet',
        'devise',
        'justification',
        'beneficiaire_type',
        'beneficiaire_id',
        'montant_brut',
        'total_avances',
        'total_acomptes',
        'total_retenues',
        'total_garanties',
        'total_penalites',
        'total_taxes',
        'montant_revision_prix',
        'montant_net',
        'taux_liquidation',
        'service_fait_valide',
        'submitted_at',
        'controle_technique_at',
        'controle_administratif_at',
        'controle_budgetaire_at',
        'controle_financier_at',
        'valide_at',
        'certifie_at',
        'transmis_ordonnancement_at',
        'certificat_numero',
        'certificat_hash',
        'transmis_ordonnancement_par',
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
            'montant_liquide' => 'decimal:2',
            'montant_brut' => 'decimal:2',
            'total_avances' => 'decimal:2',
            'total_acomptes' => 'decimal:2',
            'total_retenues' => 'decimal:2',
            'total_garanties' => 'decimal:2',
            'total_penalites' => 'decimal:2',
            'total_taxes' => 'decimal:2',
            'montant_revision_prix' => 'decimal:2',
            'montant_net' => 'decimal:2',
            'taux_liquidation' => 'decimal:2',
            'service_fait_valide' => 'boolean',
            'date_liquidation' => 'date',
            'statut' => StatutLiquidation::class,
            'mode_creation' => ModeCreationLiquidation::class,
            'beneficiaire_type' => BeneficiaireType::class,
            'submitted_at' => 'datetime',
            'controle_technique_at' => 'datetime',
            'controle_administratif_at' => 'datetime',
            'controle_budgetaire_at' => 'datetime',
            'controle_financier_at' => 'datetime',
            'valide_at' => 'datetime',
            'certifie_at' => 'datetime',
            'transmis_ordonnancement_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function engagementImputation(): BelongsTo
    {
        return $this->belongsTo(EngagementImputation::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LiquidationType::class, 'liquidation_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_liquidation_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_liquidation_id');
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
        return $this->hasMany(LiquidationLigne::class)->orderBy('ordre');
    }

    public function serviceFait(): HasOne
    {
        return $this->hasOne(LiquidationServiceFait::class)->latestOfMany();
    }

    public function servicesFaits(): HasMany
    {
        return $this->hasMany(LiquidationServiceFait::class);
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(LiquidationReception::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(LiquidationValidation::class)->orderBy('ordre');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LiquidationDocument::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LiquidationVersion::class)->orderByDesc('numero_version');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(LiquidationHistorique::class)->latest();
    }

    public function calculs(): HasMany
    {
        return $this->hasMany(LiquidationCalcul::class)->latest();
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function transmetteurOrdonnancement(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmis_ordonnancement_par');
    }

    public function annulateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function ordonnancements(): HasMany
    {
        return $this->hasMany(Ordonnancement::class);
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
