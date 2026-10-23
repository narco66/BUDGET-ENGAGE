<?php

namespace App\Models;

use App\Enums\TypeReceptionLiquidation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidationReception extends Model
{
    use SoftDeletes;

    protected $table = 'liquidation_receptions';

    protected $fillable = [
        'liquidation_id',
        'type_reception',
        'date_reception',
        'numero_pv',
        'commission',
        'responsable_id',
        'conforme',
        'reserves',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'type_reception' => TypeReceptionLiquidation::class,
            'date_reception' => 'date',
            'conforme' => 'boolean',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
