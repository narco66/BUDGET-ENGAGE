<?php

namespace App\Models;

use App\Enums\BeneficiaireType;
use App\Enums\ModeCreationPaiement;
use App\Enums\StatutPaiement;
use App\Models\Concerns\HasGedDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use HasGedDocuments;
    use SoftDeletes;

    protected $fillable = [
        'ordonnancement_id',
        'engagement_imputation_id',
        'paiement_type_id',
        'parent_paiement_id',
        'paiement_lot_id',
        'paiement_banque_id',
        'exercice_budgetaire_id',
        'fournisseur_id',
        'montant_paye',
        'date_paiement',
        'reference_paiement',
        'mode_paiement',
        'numero',
        'mode_creation',
        'statut',
        'objet',
        'justification',
        'beneficiaire_type',
        'beneficiaire_id',
        'devise',
        'devise_paiement',
        'taux_change',
        'ecart_change',
        'frais_bancaires',
        'montant_ordonnance_ref',
        'reste_a_payer',
        'taux_paiement',
        'montant_transmis_banque',
        'priorite',
        'date_prevue',
        'date_valeur',
        'reference_bancaire',
        'numero_cheque',
        'numero_swift',
        'programmed_at',
        'controle_tresorerie_at',
        'controle_comptable_at',
        'validation_financiere_at',
        'validation_signataires_at',
        'transmis_banque_at',
        'execute_at',
        'rapproche_at',
        'cloture_at',
        'preuve_numero',
        'quittance_numero',
        'preuve_hash',
        'validated_by',
        'executed_by',
        'transmis_banque_par',
        'commentaire',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'motif_annulation',
    ];

    protected function casts(): array
    {
        return [
            'montant_paye' => 'decimal:2',
            'frais_bancaires' => 'decimal:2',
            'taux_change' => 'decimal:6',
            'ecart_change' => 'decimal:6',
            'montant_ordonnance_ref' => 'decimal:2',
            'reste_a_payer' => 'decimal:2',
            'taux_paiement' => 'decimal:2',
            'montant_transmis_banque' => 'decimal:2',
            'date_paiement' => 'date',
            'date_prevue' => 'date',
            'date_valeur' => 'date',
            'statut' => StatutPaiement::class,
            'mode_creation' => ModeCreationPaiement::class,
            'beneficiaire_type' => BeneficiaireType::class,
            'programmed_at' => 'datetime',
            'controle_tresorerie_at' => 'datetime',
            'controle_comptable_at' => 'datetime',
            'validation_financiere_at' => 'datetime',
            'validation_signataires_at' => 'datetime',
            'transmis_banque_at' => 'datetime',
            'execute_at' => 'datetime',
            'rapproche_at' => 'datetime',
            'cloture_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function engagementImputation(): BelongsTo
    {
        return $this->belongsTo(EngagementImputation::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PaiementType::class, 'paiement_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_paiement_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_paiement_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(PaiementLot::class, 'paiement_lot_id');
    }

    public function banque(): BelongsTo
    {
        return $this->belongsTo(PaiementBanque::class, 'paiement_banque_id');
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
        return $this->hasMany(PaiementLigne::class)->orderBy('ordre');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(PaiementValidation::class)->orderBy('ordre');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PaiementDocument::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PaiementVersion::class)->orderByDesc('numero_version');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(PaiementHistorique::class)->latest();
    }

    public function rejets(): HasMany
    {
        return $this->hasMany(PaiementRejet::class)->latest();
    }

    public function rapprochements(): HasMany
    {
        return $this->hasMany(PaiementRapprochement::class);
    }

    public function ecritures(): HasMany
    {
        return $this->hasMany(PaiementEcritureComptable::class);
    }

    public function calculs(): HasMany
    {
        return $this->hasMany(PaiementCalcul::class)->latest();
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function executeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function annulateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
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
