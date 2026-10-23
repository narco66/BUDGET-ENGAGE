<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidationVersion extends Model
{
    protected $table = 'liquidation_versions';

    protected $fillable = [
        'liquidation_id',
        'numero_version',
        'snapshot',
        'motif',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'numero_version' => 'integer',
            'snapshot' => 'array',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
