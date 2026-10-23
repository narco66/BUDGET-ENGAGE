<?php

namespace App\Models;

use App\Enums\DecisionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementValidation extends Model
{
    protected $table = 'paiement_validations';

    protected $fillable = [
        'paiement_id', 'etape', 'ordre', 'validateur_id', 'decision',
        'commentaire', 'date_decision', 'est_validation_deleguee',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'decision' => DecisionWorkflow::class,
            'date_decision' => 'datetime',
            'est_validation_deleguee' => 'boolean',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
