<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdonnancementOrdrePaiement extends Model
{
    use SoftDeletes;

    protected $table = 'ordonnancement_ordres_paiement';

    protected $fillable = [
        'ordonnancement_id',
        'ordonnancement_mandat_id',
        'numero',
        'montant',
        'date_ordre',
        'mode_paiement',
        'beneficiaire',
        'banque',
        'compte_bancaire',
        'statut',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_ordre' => 'date',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function mandat(): BelongsTo
    {
        return $this->belongsTo(OrdonnancementMandat::class, 'ordonnancement_mandat_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
