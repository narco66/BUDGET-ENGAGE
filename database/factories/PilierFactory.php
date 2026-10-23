<?php

namespace Database\Factories;

use App\Models\Pilier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pilier>
 */
class PilierFactory extends Factory
{
    protected $model = Pilier::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('PIL???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
