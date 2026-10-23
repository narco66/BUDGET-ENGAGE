<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaiementRapprochement extends Model
{
    use SoftDeletes;

    protected $table = 'paiement_rapprochements';

    protected $fillable = [
        'paiement_id', 'paiement_banque_id', 'reference_releve', 'date_operation',
        'montant_releve', 'montant_paiement', 'ecart', 'statut', 'mode', 'source_import',
        'valide', 'valide_par', 'valide_at', 'observation', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_operation' => 'date',
            'montant_releve' => 'decimal:2',
            'montant_paiement' => 'decimal:2',
            'ecart' => 'decimal:2',
            'valide' => 'boolean',
            'valide_at' => 'datetime',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function banque(): BelongsTo
    {
        return $this->belongsTo(PaiementBanque::class, 'paiement_banque_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
