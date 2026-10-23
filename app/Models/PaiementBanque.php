<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementBanque extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'paiement_banques';

    protected $fillable = [
        'code', 'libelle', 'banque', 'agence', 'titulaire', 'iban', 'rib', 'bic_swift',
        'devise', 'solde_initial', 'plafond_decaissement', 'signataires', 'est_actif', 'ordre',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'solde_initial' => 'decimal:2',
            'plafond_decaissement' => 'decimal:2',
            'signataires' => 'array',
            'est_actif' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'paiement_banque_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActives($query)
    {
        return $query->where('est_actif', true);
    }
}
