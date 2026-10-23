<?php

namespace App\Models;

use App\Enums\BeneficiaireType;
use App\Enums\StatutEngagement;
use App\Enums\StatutRegularisation;
use App\Models\Concerns\HasGedDocuments;
use Database\Factories\EngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Engagement extends Model
{
    /** @use HasFactory<EngagementFactory> */
    use HasFactory;
    use HasGedDocuments;
    use SoftDeletes;

    protected $fillable = [
        'numero',
        'exercice_budgetaire_id',
        'ligne_budgetaire_id',
        'departement_id',
        'direction_id',
        'service_id',
        'type_engagement',
        'engagement_type_id',
        'mode_creation',
        'beneficiaire_type',
        'beneficiaire_id',
        'beneficiaire_autre',
        'objet',
        'montant',
        'date_engagement',
        'statut',
        'created_by',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'transmis_liquidation_at',
        'transmis_par',
        'motif_rejet',
        'motif_annulation',
        'is_urgence',
        'justification_urgence',
        'statut_regularisation',
        'regularisation_deadline',
        'engagement_urgence_parent_id',
        'score_risque',
    ];

    protected function casts(): array
    {
        return [
            'beneficiaire_type' => BeneficiaireType::class,
            'statut' => StatutEngagement::class,
            'statut_regularisation' => StatutRegularisation::class,
            'montant' => 'decimal:2',
            'date_engagement' => 'date',
            'regularisation_deadline' => 'date',
            'is_urgence' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'transmis_liquidation_at' => 'datetime',
        ];
    }

    public function transmetteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmis_par');
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function imputations(): HasMany
    {
        return $this->hasMany(EngagementImputation::class);
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

    public function engagementType(): BelongsTo
    {
        return $this->belongsTo(EngagementType::class, 'engagement_type_id');
    }

    public function typeValeurs(): HasMany
    {
        return $this->hasMany(EngagementTypeValeur::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EngagementVersion::class)->orderByDesc('numero_version');
    }

    public function reservationsBudgetaires(): HasMany
    {
        return $this->hasMany(EngagementReservationBudgetaire::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function engagementUrgenceParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'engagement_urgence_parent_id');
    }

    public function regularisations(): HasMany
    {
        return $this->hasMany(self::class, 'engagement_urgence_parent_id');
    }

    public function validationsWorkflow(): HasMany
    {
        return $this->hasMany(ValidationWorkflow::class)->orderBy('ordre');
    }

    public function piecesJustificatives(): HasMany
    {
        return $this->hasMany(PieceJustificative::class);
    }

    public function liquidations(): HasMany
    {
        return $this->hasMany(Liquidation::class);
    }

    public function expressionBesoin(): HasOne
    {
        return $this->hasOne(ExpressionBesoin::class);
    }

    public function alertesAudit(): HasMany
    {
        return $this->hasMany(AlerteAudit::class);
    }

    public function beneficiaire(): ?Model
    {
        return match ($this->beneficiaire_type) {
            BeneficiaireType::Fournisseur => $this->beneficiaire_id
                ? Fournisseur::find($this->beneficiaire_id)
                : null,
            BeneficiaireType::Agent => $this->beneficiaire_id
                ? Agent::find($this->beneficiaire_id)
                : null,
            default => null,
        };
    }
}
