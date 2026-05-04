<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaireChargePersonnel extends Model
{
    use SoftDeletes;

    protected $table = 'salaires_charges_personnels';

    protected $fillable = [
        'agent_id',
        'exercice_budgetaire_id',
        'mois',
        'montant_salaire',
        'montant_charges',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'montant_salaire' => 'decimal:2',
            'montant_charges' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }
}
