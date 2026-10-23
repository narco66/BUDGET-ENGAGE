<?php

namespace App\Models;

use App\Enums\StatutSuiviRealisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndicateurRealisation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'indicateur_id',
        'exercice_budgetaire_id',
        'trimestre',
        'annee',
        'periode_debut',
        'periode_fin',
        'valeur_cible',
        'valeur_realisee',
        'source_donnee',
        'statut',
        'commentaire',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'valeur_cible' => 'decimal:4',
            'valeur_realisee' => 'decimal:4',
            'statut' => StatutSuiviRealisation::class,
            'validated_at' => 'datetime',
        ];
    }

    public function indicateur(): BelongsTo
    {
        return $this->belongsTo(Indicateur::class);
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function tauxRealisation(): ?float
    {
        if ($this->valeur_cible === null || (float) $this->valeur_cible <= 0) {
            return null;
        }

        return round(((float) $this->valeur_realisee / (float) $this->valeur_cible) * 100, 1);
    }
}
