<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidationLigne extends Model
{
    use SoftDeletes;

    protected $table = 'liquidation_lignes';

    protected $fillable = [
        'liquidation_id',
        'engagement_imputation_id',
        'ligne_budgetaire_id',
        'source_financement_id',
        'activite_id',
        'tache_id',
        'pap_ligne_budgetaire_id',
        'centre_cout',
        'centre_responsabilite',
        'designation',
        'quantite_prevue',
        'quantite_realisee',
        'prix_unitaire',
        'montant_brut',
        'montant_avances',
        'montant_acomptes',
        'montant_retenues',
        'montant_garanties',
        'montant_penalites',
        'montant_taxes',
        'montant_revision_prix',
        'montant_net',
        'reste_a_liquider',
        'observation',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'quantite_prevue' => 'decimal:4',
            'quantite_realisee' => 'decimal:4',
            'prix_unitaire' => 'decimal:2',
            'montant_brut' => 'decimal:2',
            'montant_avances' => 'decimal:2',
            'montant_acomptes' => 'decimal:2',
            'montant_retenues' => 'decimal:2',
            'montant_garanties' => 'decimal:2',
            'montant_penalites' => 'decimal:2',
            'montant_taxes' => 'decimal:2',
            'montant_revision_prix' => 'decimal:2',
            'montant_net' => 'decimal:2',
            'reste_a_liquider' => 'decimal:2',
            'ordre' => 'integer',
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
