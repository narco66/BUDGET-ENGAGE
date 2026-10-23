<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlerteBudgetaire extends Model
{
    protected $table = 'alertes_budgetaires';

    protected $fillable = [
        'exercice_budgetaire_id',
        'ligne_budgetaire_id',
        'code',
        'type',
        'niveau',
        'seuil',
        'taux',
        'montant_engage',
        'plafond',
        'message',
        'statut',
        'detectee_le',
        'traitee_par',
        'traitee_le',
        'commentaire_traitement',
    ];

    protected function casts(): array
    {
        return [
            'taux' => 'decimal:2',
            'montant_engage' => 'decimal:2',
            'plafond' => 'decimal:2',
            'detectee_le' => 'datetime',
            'traitee_le' => 'datetime',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }

    public function scopeActives($query)
    {
        return $query->where('statut', 'nouvelle');
    }
}
