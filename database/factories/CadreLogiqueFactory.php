<?php

namespace Database\Factories;

use App\Models\CadreLogique;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CadreLogique>
 */
class CadreLogiqueFactory extends Factory
{
    protected $model = CadreLogique::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('CL???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'annee' => (int) fake()->year(),
            'version' => '1.0',
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
