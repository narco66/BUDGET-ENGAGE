<?php

/**
 * Circuit de validation paramétrable de la Liquidation.
 *
 * Chaque étape référence soit une permission unique (`permission`), soit un jeu
 * de permissions alternatives (`permissions_any`). L'ordre du tableau définit la
 * séquence. `statut` indique l'état StatutLiquidation porté tant que l'étape est
 * en attente ; `jalon` la colonne timestamp horodatée à la validation de l'étape.
 */
return [
    'steps' => [
        [
            'key' => 'controle_technique',
            'permission' => 'liquidation.validate',
            'statut' => 'controle_technique',
            'jalon' => 'controle_technique_at',
        ],
        [
            'key' => 'controle_administratif',
            'permission' => 'liquidation.validate',
            'statut' => 'controle_administratif',
            'jalon' => 'controle_administratif_at',
        ],
        [
            'key' => 'controle_budgetaire',
            'permission' => 'workflow.budget_control',
            'statut' => 'controle_budgetaire',
            'jalon' => 'controle_budgetaire_at',
        ],
        [
            'key' => 'controle_financier',
            'permission' => 'workflow.financial_control',
            'statut' => 'controle_financier',
            'jalon' => 'controle_financier_at',
        ],
        [
            'key' => 'validation',
            'permissions_any' => [
                'liquidation.validate',
                'workflow.ordonnateur',
                'workflow.ordonnateur_delegue',
            ],
            'statut' => 'controle_financier',
            'jalon' => 'valide_at',
        ],
    ],
];
