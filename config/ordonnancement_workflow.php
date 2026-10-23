<?php

/**
 * Circuit de validation paramétrable de l'Ordonnancement.
 *
 * Brouillon → Soumission → Contrôle budgétaire → Contrôle comptable →
 * Contrôle financier → Visa ordonnateur → Ordonnancement validé.
 * La génération du mandat et la transmission au paiement relèvent de
 * OrdonnancementService / OrdonnancementToPaiementService.
 */
return [
    'steps' => [
        [
            'key' => 'controle_budgetaire',
            'permissions_any' => ['ordonnancement.validate', 'workflow.budget_control'],
            'statut' => 'controle_budgetaire',
            'jalon' => 'controle_budgetaire_at',
        ],
        [
            'key' => 'controle_comptable',
            'permissions_any' => ['ordonnancement.validate'],
            'statut' => 'controle_comptable',
            'jalon' => 'controle_comptable_at',
        ],
        [
            'key' => 'controle_financier',
            'permissions_any' => ['ordonnancement.validate', 'workflow.financial_control'],
            'statut' => 'controle_financier',
            'jalon' => 'controle_financier_at',
        ],
        [
            'key' => 'visa_ordonnateur',
            'permissions_any' => [
                'ordonnancement.validate',
                'workflow.ordonnateur',
                'workflow.ordonnateur_delegue',
            ],
            'statut' => 'visa_ordonnateur',
            'jalon' => 'visa_ordonnateur_at',
        ],
    ],
];
