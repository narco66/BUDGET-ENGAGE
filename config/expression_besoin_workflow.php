<?php

/*
|--------------------------------------------------------------------------
| Circuit de validation des expressions de besoins
|--------------------------------------------------------------------------
| Étapes ordonnées du workflow. Chaque étape référence la permission Spatie
| qui habilite à la traiter. Un utilisateur disposant de
| `expression_besoins.admin` peut traiter n'importe quelle étape.
*/

return [
    'steps' => [
        [
            'key' => 'validation_service',
            'libelle' => 'Validation Chef de Service',
            'permission' => 'workflow.validate_service',
        ],
        [
            'key' => 'validation_direction',
            'libelle' => 'Validation Directeur',
            'permission' => 'workflow.validate_direction',
        ],
        [
            'key' => 'validation_departement',
            'libelle' => 'Validation Département',
            'permission' => 'workflow.validate_department',
        ],
        [
            'key' => 'controle_budgetaire',
            'libelle' => 'Contrôle Budgétaire',
            'permission' => 'workflow.budget_control',
        ],
        [
            'key' => 'validation_finale',
            'libelle' => 'Validation Finale',
            'permission' => 'workflow.ordonnateur',
        ],
    ],
];
