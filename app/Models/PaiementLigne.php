<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementLigne extends Model
{
    use SoftDeletes;

    protected $table = 'paiement_lignes';

    protected $fillable = [
        'paiement_id', 'ordonnancement_ligne_id', 'liquidation_ligne_id', 'engagement_imputation_id',
        'ligne_budgetaire_id', 'source_financement_id', 'activite_id', 'tache_id', 'pap_ligne_budgetaire_id',
        'centre_cout', 'centre_responsabilite', 'designation',
        'montant_ordonnance', 'montant_paye', 'reste_a_payer',
        'devise', 'taux_change', 'frais_bancaires', 'reference_bancaire', 'observation', 'ordre',
    ];

    protected function casts(): array
    {
        return [
            'montant_ordonnance' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'reste_a_payer' => 'decimal:2',
            'taux_change' => 'decimal:6',
            'frais_bancaires' => 'decimal:2',
            'ordre' => 'integer',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function ordonnancementLigne(): BelongsTo
    {
        return $this->belongsTo(OrdonnancementLigne::class);
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
}
