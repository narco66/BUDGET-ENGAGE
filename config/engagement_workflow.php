<?php

/**
 * Étapes du circuit de validation des engagements (ordre = ordre métier).
 * Chaque étape est associée à une permission Spatie (ou plusieurs avec permissions_any).
 */
return [
    'steps' => [
        [
            'key' => 'validation_service',
            'permission' => 'workflow.validate_service',
        ],
        [
            'key' => 'validation_direction',
            'permission' => 'workflow.validate_direction',
        ],
        [
            'key' => 'validation_departement',
            'permission' => 'workflow.validate_department',
        ],
        [
            'key' => 'controle_budgetaire',
            'permission' => 'workflow.budget_control',
        ],
        [
            'key' => 'controle_financier',
            'permission' => 'workflow.financial_control',
        ],
        [
            'key' => 'visa_ordonnateur',
            'permissions_any' => [
                'workflow.ordonnateur',
                'workflow.ordonnateur_delegue',
            ],
        ],
    ],
];
