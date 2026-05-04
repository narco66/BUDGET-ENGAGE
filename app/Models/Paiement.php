<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'ordonnancement_id',
        'montant_paye',
        'date_paiement',
        'reference_paiement',
        'mode_paiement',
        'commentaire',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant_paye' => 'decimal:2',
            'date_paiement' => 'date',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
