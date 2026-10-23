<?php

namespace Database\Factories;

use App\Models\TypeIndicateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeIndicateur>
 */
class TypeIndicateurFactory extends Factory
{
    protected $model = TypeIndicateur::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('TYP???')),
            'libelle' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'ordre' => 0,
            'statut' => 'actif',
        ];
    }
}
