<?php

namespace Database\Factories;

use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\SourceFinancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LigneBudgetaire>
 */
class LigneBudgetaireFactory extends Factory
{
    protected $model = LigneBudgetaire::class;

    public function definition(): array
    {
        $vote = fake()->randomFloat(2, 1_000_000, 500_000_000);

        return [
            'exercice_budgetaire_id' => ExerciceBudgetaire::factory(),
            'departement_id' => null,
            'direction_id' => null,
            'service_id' => null,
            'nature_depense_id' => NatureDepense::factory(),
            'source_financement_id' => SourceFinancement::factory(),
            'code' => fake()->unique()->bothify('LB-####'),
            'libelle' => 'Ligne '.fake()->words(3, true),
            'montant_vote' => $vote,
            'montant_revise' => $vote,
            'montant_engage' => 0,
            'montant_liquide' => 0,
            'montant_ordonnance' => 0,
            'montant_paye' => 0,
            'statut' => 'active',
        ];
    }
}
