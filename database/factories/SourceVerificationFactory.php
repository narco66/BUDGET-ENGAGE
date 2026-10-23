<?php

namespace Database\Factories;

use App\Models\SourceVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceVerification>
 */
class SourceVerificationFactory extends Factory
{
    protected $model = SourceVerification::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('SRC???')),
            'libelle' => fake()->sentence(2),
            'description' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
