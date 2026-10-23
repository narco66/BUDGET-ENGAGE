<?php

namespace App\Enums;

enum StatutEngagement: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case EnValidation = 'en_validation';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Approuve = 'approuve';
    case Annule = 'annule';
    case Liquide = 'liquide';
    case Ordonnance = 'ordonnance';
    case Paye = 'paye';
    case Cloture = 'cloture';

    /** Soumis ou en cours de validation hiérarchique. */
    public function estEnCircuitValidation(): bool
    {
        return in_array($this, [self::Soumis, self::EnValidation], true);
    }

    /** Peut être clôturé après paiement intégral. */
    public function estCloturable(): bool
    {
        return $this === self::Paye;
    }
}
