<?php

namespace Database\Factories;

use App\Enums\BeneficiaireType;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Engagement>
 */
class EngagementFactory extends Factory
{
    protected $model = Engagement::class;

    public function definition(): array
    {
        $exercice = ExerciceBudgetaire::factory();

        return [
            'numero' => 'ENG-'.fake()->unique()->numerify('######'),
            'exercice_budgetaire_id' => $exercice,
            'ligne_budgetaire_id' => LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire'),
            'departement_id' => null,
            'direction_id' => null,
            'service_id' => null,
            'type_engagement' => fake()->randomElement(['achat', 'prestation', 'mission', 'salaire', 'charge']),
            'beneficiaire_type' => BeneficiaireType::Autre,
            'beneficiaire_id' => null,
            'beneficiaire_autre' => fake()->company(),
            'objet' => fake()->sentence(12),
            'montant' => fake()->randomFloat(2, 10_000, 5_000_000),
            'date_engagement' => fake()->date(),
            'statut' => StatutEngagement::Brouillon,
            'created_by' => User::factory(),
            'is_urgence' => false,
            'justification_urgence' => null,
            'statut_regularisation' => null,
            'regularisation_deadline' => null,
            'engagement_urgence_parent_id' => null,
            'score_risque' => null,
        ];
    }
}
