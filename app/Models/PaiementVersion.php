<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementVersion extends Model
{
    protected $table = 'paiement_versions';

    protected $fillable = [
        'paiement_id', 'numero_version', 'snapshot', 'motif', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'numero_version' => 'integer',
            'snapshot' => 'array',
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
