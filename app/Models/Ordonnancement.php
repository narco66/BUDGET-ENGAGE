<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ordonnancement extends Model
{
    protected $fillable = [
        'liquidation_id',
        'montant_ordonnance',
        'date_ordonnancement',
        'reference',
        'commentaire',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant_ordonnance' => 'decimal:2',
            'date_ordonnancement' => 'date',
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

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
