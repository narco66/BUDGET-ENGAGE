<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirementCredit extends Model
{
    protected $table = 'virements_credits';

    protected $fillable = [
        'exercice_budgetaire_id',
        'ligne_source_id',
        'ligne_destination_id',
        'montant',
        'motif',
        'source_revise_avant',
        'source_revise_apres',
        'destination_revise_avant',
        'destination_revise_apres',
        'statut',
        'user_id',
        'adresse_ip',
        'approuve_par',
        'decision_at',
        'motif_decision',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'source_revise_avant' => 'decimal:2',
            'source_revise_apres' => 'decimal:2',
            'destination_revise_avant' => 'decimal:2',
            'destination_revise_apres' => 'decimal:2',
            'decision_at' => 'datetime',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class, 'ligne_source_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class, 'ligne_destination_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approbateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'propose');
    }
}
