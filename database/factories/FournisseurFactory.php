<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fournisseur>
 */
class FournisseurFactory extends Factory
{
    protected $model = Fournisseur::class;

    public function definition(): array
    {
        return [
            'raison_sociale' => fake()->company(),
            'sigle' => fake()->optional()->lexify('???'),
            'numero_fiscal' => fake()->optional()->numerify('NIF######'),
            'adresse' => fake()->address(),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'banque' => fake()->randomElement(['BEAC', 'BGFI', 'Ecobank', 'UBA']),
            'compte_bancaire' => fake()->iban(),
            'type_fournisseur' => fake()->randomElement(['PME', 'Grande entreprise', 'ONG']),
            'statut' => 'actif',
        ];
    }
}
