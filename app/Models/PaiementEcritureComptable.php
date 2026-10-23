<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementEcritureComptable extends Model
{
    use SoftDeletes;

    protected $table = 'paiement_ecritures_comptables';

    protected $fillable = [
        'paiement_id', 'numero_ecriture', 'date_ecriture', 'journal', 'compte',
        'libelle', 'debit', 'credit', 'devise', 'lettrage', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
