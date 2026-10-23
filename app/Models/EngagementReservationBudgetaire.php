<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementReservationBudgetaire extends Model
{
    use HasFactory;

    protected $table = 'engagement_reservations_budgetaires';

    protected $fillable = [
        'engagement_id',
        'ligne_budgetaire_id',
        'exercice_budgetaire_id',
        'montant_reserve',
        'sens',
        'statut',
        'reserve_par',
        'date_reservation',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'montant_reserve' => 'decimal:2',
            'date_reservation' => 'datetime',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function reservePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserve_par');
    }
}
