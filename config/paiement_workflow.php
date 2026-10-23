<?php

/**
 * Circuit de validation paramétrable du Paiement.
 *
 * Brouillon → Programmation → Contrôle trésorerie → Contrôle comptable →
 * Validation financière → Validation signataires → Validé. L'exécution bancaire,
 * le rapprochement et la clôture relèvent de PaiementService.
 */
return [
    'steps' => [
        [
            'key' => 'controle_tresorerie',
            'permissions_any' => ['paiement.validate', 'workflow.budget_control'],
            'statut' => 'controle_tresorerie',
            'jalon' => 'controle_tresorerie_at',
        ],
        [
            'key' => 'controle_comptable',
            'permissions_any' => ['paiement.validate'],
            'statut' => 'controle_comptable',
            'jalon' => 'controle_comptable_at',
        ],
        [
            'key' => 'validation_financiere',
            'permissions_any' => ['paiement.validate', 'workflow.financial_control'],
            'statut' => 'validation_financiere',
            'jalon' => 'validation_financiere_at',
        ],
        [
            'key' => 'validation_signataires',
            'permissions_any' => [
                'paiement.validate',
                'workflow.ordonnateur',
                'workflow.ordonnateur_delegue',
            ],
            'statut' => 'validation_signataires',
            'jalon' => 'validation_signataires_at',
        ],
    ],
];
