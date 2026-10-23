<?php

namespace Database\Factories;

use App\Models\Indicateur;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Indicateur>
 */
class IndicateurFactory extends Factory
{
    protected $model = Indicateur::class;

    public function definition(): array
    {
        return [
            'type_indicateur_id' => TypeIndicateur::factory(),
            'unite_mesure_id' => UniteMesure::factory(),
            'code' => strtoupper(fake()->unique()->lexify('IND???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
