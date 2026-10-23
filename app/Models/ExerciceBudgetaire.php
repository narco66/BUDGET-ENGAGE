<?php

namespace App\Models;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\Concerns\HasGedDocuments;
use Database\Factories\ExerciceBudgetaireFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciceBudgetaire extends Model
{
    /** @use HasFactory<ExerciceBudgetaireFactory> */
    use HasFactory;

    use HasGedDocuments;

    protected $table = 'exercices_budgetaires';

    protected $fillable = [
        'annee',
        'date_debut',
        'date_fin',
        'statut',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'statut' => StatutExerciceBudgetaire::class,
            'est_actif' => 'boolean',
        ];
    }

    public function lignesBudgetaires(): HasMany
    {
        return $this->hasMany(LigneBudgetaire::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    /** @deprecated Table imports_budget — migré vers {@see imports()} */
    public function importsBudget(): HasMany
    {
        return $this->hasMany(ImportBudget::class);
    }

    public function salairesChargesPersonnels(): HasMany
    {
        return $this->hasMany(SalaireChargePersonnel::class);
    }

    public function papLignesBudgetaires(): HasMany
    {
        return $this->hasMany(PapLigneBudgetaire::class);
    }

    public function papImports(): HasMany
    {
        return $this->hasMany(PapImport::class);
    }

    public function budgetLignes(): HasMany
    {
        return $this->hasMany(BudgetLigne::class);
    }

    /**
     * Imports unifiés : nomenclature 2026 (+ enregistrements legacy migrés).
     */
    public function imports(): HasMany
    {
        return $this->hasMany(BudgetImport::class);
    }

    /** @deprecated Utiliser {@see imports()} */
    public function budgetImports(): HasMany
    {
        return $this->imports();
    }

    public function scopeOuvert($query)
    {
        return $query->where('statut', StatutExerciceBudgetaire::Ouvert);
    }
}
