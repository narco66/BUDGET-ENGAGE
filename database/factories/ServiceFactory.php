<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'direction_id' => Direction::factory(),
            'code' => strtoupper(fake()->unique()->lexify('SRV???')),
            'libelle' => 'Service '.fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'statut' => 'actif',
        ];
    }
}
