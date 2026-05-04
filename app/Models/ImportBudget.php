<?php

namespace App\Models;

use App\Enums\StatutImportBudget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBudget extends Model
{
    protected $table = 'imports_budget';

    protected $fillable = [
        'exercice_budgetaire_id',
        'fichier_original',
        'chemin_fichier',
        'statut',
        'total_lignes',
        'lignes_valides',
        'lignes_rejetees',
        'rapport_erreurs',
        'imported_by',
        'validated_by',
        'imported_at',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutImportBudget::class,
            'rapport_erreurs' => 'array',
            'imported_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function importateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
