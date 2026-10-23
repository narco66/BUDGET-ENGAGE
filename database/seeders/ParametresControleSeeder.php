<?php

namespace Database\Seeders;

use App\Models\ParametreControle;
use Illuminate\Database\Seeder;

class ParametresControleSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['cle' => 'seuil_ordonnateur_delegue', 'valeur' => '5000000', 'type_valeur' => 'decimal', 'description' => 'Montant maximum validable par ordonnateur délégué'],
            ['cle' => 'delai_regularisation_urgence_jours', 'valeur' => '10', 'type_valeur' => 'integer', 'description' => 'Délai maximal de régularisation (jours)'],
            ['cle' => 'marge_proximite_seuil_pourcentage', 'valeur' => '10', 'type_valeur' => 'integer', 'description' => 'Marge de détection des montants proches du seuil (%)'],
            ['cle' => 'seuil_consommation_budget_alerte', 'valeur' => '80', 'type_valeur' => 'integer', 'description' => 'Pourcentage de consommation déclenchant une alerte'],
            ['cle' => 'seuil_consommation_budget_critique', 'valeur' => '95', 'type_valeur' => 'integer', 'description' => 'Pourcentage de consommation déclenchant une alerte critique'],
            ['cle' => 'nombre_urgences_repetitives_periode', 'valeur' => '3', 'type_valeur' => 'integer', 'description' => 'Nombre d’urgences similaires déclenchant une alerte'],
            ['cle' => 'periode_detection_fractionnement_jours', 'valeur' => '30', 'type_valeur' => 'integer', 'description' => 'Fenêtre d’analyse des fractionnements (jours)'],
        ];

        foreach ($defaults as $row) {
            ParametreControle::query()->firstOrCreate(
                ['cle' => $row['cle']],
                [
                    'valeur' => $row['valeur'],
                    'type_valeur' => $row['type_valeur'],
                    'description' => $row['description'],
                    'actif' => true,
                ]
            );
        }
    }
}
