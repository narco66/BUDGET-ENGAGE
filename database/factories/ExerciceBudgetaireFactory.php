<?php

namespace Database\Factories;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\ExerciceBudgetaire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciceBudgetaire>
 */
class ExerciceBudgetaireFactory extends Factory
{
    protected $model = ExerciceBudgetaire::class;

    public function definition(): array
    {
        $year = (int) fake()->unique()->numberBetween(2024, 2035);

        return [
            'annee' => $year,
            'date_debut' => $year.'-01-01',
            'date_fin' => $year.'-12-31',
            'statut' => StatutExerciceBudgetaire::Ouvert,
            'est_actif' => true,
        ];
    }
}
