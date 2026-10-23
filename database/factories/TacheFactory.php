<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\Tache;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tache>
 */
class TacheFactory extends Factory
{
    protected $model = Tache::class;

    public function definition(): array
    {
        return [
            'activite_id' => Activite::factory(),
            'code' => strtoupper(fake()->unique()->lexify('TCH???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
