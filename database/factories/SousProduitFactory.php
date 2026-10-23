<?php

namespace Database\Factories;

use App\Models\Produit;
use App\Models\SousProduit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SousProduit>
 */
class SousProduitFactory extends Factory
{
    protected $model = SousProduit::class;

    public function definition(): array
    {
        return [
            'produit_id' => Produit::factory(),
            'code' => strtoupper(fake()->unique()->lexify('SP???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
