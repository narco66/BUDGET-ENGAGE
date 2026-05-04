<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Liquidation extends Model
{
    protected $fillable = [
        'engagement_id',
        'montant_liquide',
        'date_liquidation',
        'reference',
        'commentaire',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant_liquide' => 'decimal:2',
            'date_liquidation' => 'date',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ordonnancements(): HasMany
    {
        return $this->hasMany(Ordonnancement::class);
    }
}
