<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementImputation extends Model
{
    protected $fillable = [
        'engagement_id',
        'ligne_budgetaire_id',
        'source_financement_id',
        'activite_id',
        'centre_cout',
        'centre_responsabilite',
        'montant_impute',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'montant_impute' => 'decimal:2',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
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
