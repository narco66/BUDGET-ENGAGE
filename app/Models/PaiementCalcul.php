<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementCalcul extends Model
{
    protected $table = 'paiement_calculs';

    protected $fillable = [
        'paiement_id', 'paiement_ligne_id', 'type_calcul', 'formule', 'entrees', 'resultat', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entrees' => 'array',
            'resultat' => 'decimal:2',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(PaiementLigne::class, 'paiement_ligne_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
