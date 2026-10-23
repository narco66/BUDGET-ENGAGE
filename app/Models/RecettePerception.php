<?php

namespace App\Models;

use App\Enums\StatutRecettePerception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecettePerception extends Model
{
    use SoftDeletes;

    protected $table = 'recettes_perceptions';

    protected $fillable = [
        'exercice_budgetaire_id',
        'budget_ligne_id',
        'numero',
        'date_perception',
        'montant',
        'reference',
        'source_emetteur',
        'mode_reglement',
        'statut',
        'observation',
        'created_by',
        'validated_by',
        'validated_at',
        'cancelled_at',
        'cancelled_by',
        'motif_annulation',
    ];

    protected function casts(): array
    {
        return [
            'date_perception' => 'date',
            'montant' => 'decimal:2',
            'statut' => StatutRecettePerception::class,
            'validated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function budgetLigne(): BelongsTo
    {
        return $this->belongsTo(BudgetLigne::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
