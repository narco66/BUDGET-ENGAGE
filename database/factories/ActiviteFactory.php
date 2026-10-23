<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\SousProduit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activite>
 */
class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    public function definition(): array
    {
        return [
            'sous_produit_id' => SousProduit::factory(),
            'code' => strtoupper(fake()->unique()->lexify('ACT???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
