<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementLot extends Model
{
    use SoftDeletes;

    protected $table = 'paiement_lots';

    protected $fillable = [
        'numero', 'libelle', 'date_lot', 'statut', 'paiement_banque_id',
        'montant_total', 'devise', 'observation', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_lot' => 'date',
            'montant_total' => 'decimal:2',
        ];
    }

    public function banque(): BelongsTo
    {
        return $this->belongsTo(PaiementBanque::class, 'paiement_banque_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'paiement_lot_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
