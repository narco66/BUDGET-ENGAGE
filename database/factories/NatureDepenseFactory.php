<?php

namespace Database\Factories;

use App\Models\NatureDepense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NatureDepense>
 */
class NatureDepenseFactory extends Factory
{
    protected $model = NatureDepense::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('NAT###'),
            'libelle' => fake()->randomElement([
                'Biens et services',
                'Transferts courants',
                'Immobilisations',
                'Charges de personnel',
            ]),
            'description' => fake()->optional()->sentence(),
            'statut' => 'actif',
        ];
    }
}
