<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementTypeValeur extends Model
{
    protected $table = 'engagement_type_valeurs';

    protected $fillable = [
        'engagement_id',
        'cle',
        'valeur',
        'type_valeur',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }
}
