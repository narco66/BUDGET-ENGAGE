<?php

namespace Database\Factories;

use App\Models\CadreLogique;
use App\Models\ChaineResultat;
use App\Models\Indicateur;
use App\Models\Pilier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChaineResultat>
 */
class ChaineResultatFactory extends Factory
{
    protected $model = ChaineResultat::class;

    public function definition(): array
    {
        return [
            'cadre_logique_id' => CadreLogique::factory(),
            'code' => strtoupper(fake()->unique()->lexify('CR???')),
            'libelle' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'niveau' => 'pilier',
            'element_type' => Pilier::class,
            'element_id' => Pilier::factory(),
            'indicateur_id' => Indicateur::factory(),
            'resultat_attendu' => fake()->optional()->sentence(),
            'ordre' => fake()->numberBetween(1, 100),
            'statut' => 'actif',
        ];
    }
}
