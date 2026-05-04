<?php

namespace App\Models;

use App\Enums\DecisionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationWorkflow extends Model
{
    protected $table = 'validations_workflow';

    protected $fillable = [
        'engagement_id',
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
            'decision' => DecisionWorkflow::class,
            'date_decision' => 'datetime',
            'est_validation_deleguee' => 'boolean',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
