<?php

return [
    'steps' => [
        'validation_service' => 'Validation — niveau service',
        'validation_direction' => 'Validation — niveau direction',
        'validation_departement' => 'Validation — niveau département',
        'controle_budgetaire' => 'Contrôle budgétaire',
        'controle_financier' => 'Contrôle financier',
        'visa_ordonnateur' => 'Visa ordonnateur',
    ],

    'decisions' => [
        'en_attente' => 'En attente',
        'valide' => 'Validé',
        'rejete' => 'Rejeté',
        'retourne' => 'Retourné',
    ],

    'messages' => [
        'validated' => 'Étape validée.',
        'rejected' => 'Engagement rejeté.',
        'returned' => 'Engagement retourné au initiateur.',
        'approved' => 'Engagement approuvé — le montant a été imputé sur la ligne (montant engagé).',
    ],
];
