<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdonnancementLigne extends Model
{
    use SoftDeletes;

    protected $table = 'ordonnancement_lignes';

    protected $fillable = [
        'ordonnancement_id',
        'liquidation_ligne_id',
        'engagement_imputation_id',
        'ligne_budgetaire_id',
        'source_financement_id',
        'activite_id',
        'tache_id',
        'pap_ligne_budgetaire_id',
        'centre_cout',
        'centre_responsabilite',
        'designation',
        'montant_liquide',
        'montant_ordonnance',
        'reste_a_ordonnancer',
        'mode_paiement',
        'echeance',
        'banque',
        'compte_bancaire',
        'observation',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'montant_liquide' => 'decimal:2',
            'montant_ordonnance' => 'decimal:2',
            'reste_a_ordonnancer' => 'decimal:2',
            'echeance' => 'date',
            'ordre' => 'integer',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function liquidationLigne(): BelongsTo
    {
        return $this->belongsTo(LiquidationLigne::class);
    }

    public function engagementImputation(): BelongsTo
    {
        return $this->belongsTo(EngagementImputation::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function sourceFinancement(): BelongsTo
    {
        return $this->belongsTo(SourceFinancement::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }

    public function papLigne(): BelongsTo
    {
        return $this->belongsTo(PapLigneBudgetaire::class, 'pap_ligne_budgetaire_id');
    }
}
