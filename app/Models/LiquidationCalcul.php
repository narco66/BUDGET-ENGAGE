<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidationCalcul extends Model
{
    protected $table = 'liquidation_calculs';

    protected $fillable = [
        'liquidation_id',
        'liquidation_ligne_id',
        'type_calcul',
        'formule',
        'entrees',
        'resultat',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entrees' => 'array',
            'resultat' => 'decimal:2',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(LiquidationLigne::class, 'liquidation_ligne_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
