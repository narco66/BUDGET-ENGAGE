<?php

return [
    'workflow' => [
        'submitted' => 'Engagement :numero transmis en circuit de validation.',
        'step_validated' => 'Étape « :etape » validée sur l’engagement :numero.',
        'approved' => 'Engagement :numero approuvé — montant :montant imputé au montant engagé (ligne #:ligne).',
        'rejected' => 'Engagement :numero rejeté lors de l’étape « :etape ».',
        'returned' => 'Engagement :numero retourné au initiateur après l’étape « :etape ».',
    ],
    'execution' => [
        'liquidation' => 'Liquidation #:id — :montant — engagement :numero.',
        'ordonnancement' => 'Ordonnancement #:id — :montant — engagement :numero.',
        'paiement' => 'Paiement #:id — :montant — engagement :numero.',
    ],
    'cancellation' => [
        'approved_engagement' => 'Annulation de l’engagement approuvé :numero — rollback montant engagé sur la ligne.',
    ],
];
