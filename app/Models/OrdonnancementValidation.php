<?php

namespace App\Models;

use App\Enums\DecisionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdonnancementValidation extends Model
{
    protected $table = 'ordonnancement_validations';

    protected $fillable = [
        'ordonnancement_id',
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

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
