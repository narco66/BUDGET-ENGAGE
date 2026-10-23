<?php

namespace Database\Seeders;

use App\Models\AxeStrategique;
use App\Models\Pilier;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use Illuminate\Database\Seeder;

class RbmReferentialSeeder extends Seeder
{
    public function run(): void
    {
        $pilier = Pilier::query()->firstOrCreate(
            ['code' => 'P1'],
            [
                'libelle' => 'Pilier institutionnel CEEAC',
                'description' => 'Référentiel de démonstration RBM/GAR',
                'ordre' => 1,
                'statut' => 'actif',
            ]
        );

        AxeStrategique::query()->firstOrCreate(
            ['pilier_id' => $pilier->id, 'code' => 'A1'],
            [
                'libelle' => 'Gouvernance et intégration régionale',
                'ordre' => 1,
                'statut' => 'actif',
            ]
        );

        TypeIndicateur::query()->firstOrCreate(
            ['code' => 'QUANT'],
            ['libelle' => 'Quantitatif', 'ordre' => 1, 'statut' => 'actif']
        );

        UniteMesure::query()->firstOrCreate(
            ['code' => 'PCT'],
            ['libelle' => 'Pourcentage', 'symbole' => '%', 'ordre' => 1, 'statut' => 'actif']
        );
    }
}
