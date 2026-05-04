<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Direction>
 */
class DirectionFactory extends Factory
{
    protected $model = Direction::class;

    public function definition(): array
    {
        return [
            'departement_id' => Departement::factory(),
            'code' => strtoupper(fake()->unique()->lexify('DIR???')),
            'libelle' => 'Direction '.fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'statut' => 'actif',
        ];
    }
}
