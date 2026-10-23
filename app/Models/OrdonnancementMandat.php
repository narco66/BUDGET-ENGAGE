<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdonnancementMandat extends Model
{
    use SoftDeletes;

    protected $table = 'ordonnancement_mandats';

    protected $fillable = [
        'ordonnancement_id',
        'numero',
        'montant',
        'date_mandat',
        'statut',
        'beneficiaire',
        'banque',
        'compte_bancaire',
        'hash_integrite',
        'observation',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_mandat' => 'date',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function ordresPaiement(): HasMany
    {
        return $this->hasMany(OrdonnancementOrdrePaiement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
