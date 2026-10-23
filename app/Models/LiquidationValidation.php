<?php

namespace App\Models;

use App\Enums\DecisionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidationValidation extends Model
{
    protected $table = 'liquidation_validations';

    protected $fillable = [
        'liquidation_id',
        'etape',
        'ordre',
        'validateur_id',
        'decision',
        'commentaire',
        'date_decision',
        'est_validation_deleguee',
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

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
