<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementRejet extends Model
{
    use SoftDeletes;

    protected $table = 'paiement_rejets';

    protected $fillable = [
        'paiement_id', 'motif', 'description', 'date_rejet', 'reference_rejet',
        'traite', 'traite_par', 'traite_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_rejet' => 'date',
            'traite' => 'boolean',
            'traite_at' => 'datetime',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function traiteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
