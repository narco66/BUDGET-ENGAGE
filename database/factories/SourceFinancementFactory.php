<?php

namespace Database\Factories;

use App\Models\SourceFinancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceFinancement>
 */
class SourceFinancementFactory extends Factory
{
    protected $model = SourceFinancement::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('SRC???')),
            'libelle' => fake()->randomElement([
                'Budget ordinaire',
                'Budget projet',
                'Contribution des États',
                'Financement externe',
            ]),
            'description' => fake()->optional()->sentence(),
            'statut' => 'actif',
        ];
    }
}
