<?php

namespace Database\Factories;

use App\Models\Departement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departement>
 */
class DepartementFactory extends Factory
{
    protected $model = Departement::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('DEP???')),
            'libelle' => fake()->company().' — Département',
            'description' => fake()->optional()->sentence(),
            'statut' => 'actif',
        ];
    }
}
