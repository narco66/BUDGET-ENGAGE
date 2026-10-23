<?php

namespace Database\Factories;

use App\Models\UniteMesure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UniteMesure>
 */
class UniteMesureFactory extends Factory
{
    protected $model = UniteMesure::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('UNT???')),
            'libelle' => fake()->word(),
            'symbole' => fake()->optional()->lexify('??'),
            'description' => null,
            'ordre' => 0,
            'statut' => 'actif',
        ];
    }
}
