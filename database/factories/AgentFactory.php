<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'matricule' => fake()->unique()->numerify('MAT####'),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'fonction' => fake()->jobTitle(),
            'grade' => fake()->randomElement(['A4', 'A5', 'A6', 'B3', 'C2']),
            'categorie' => fake()->randomElement(['A', 'B', 'C']),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'banque' => fake()->randomElement(['BEAC', 'BGFI']),
            'compte_bancaire' => fake()->iban(),
            'statut' => 'actif',
        ];
    }
}
