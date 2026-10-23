<?php

namespace Database\Factories;

use App\Models\AxeStrategique;
use App\Models\Pilier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AxeStrategique>
 */
class AxeStrategiqueFactory extends Factory
{
    protected $model = AxeStrategique::class;

    public function definition(): array
    {
        return [
            'pilier_id' => Pilier::factory(),
            'code' => strtoupper(fake()->unique()->lexify('AXE???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
