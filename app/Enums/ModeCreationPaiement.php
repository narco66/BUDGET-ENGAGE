<?php

namespace App\Enums;

enum ModeCreationPaiement: string
{
    case Automatique = 'automatique';
    case Manuel = 'manuel';
    case Partiel = 'partiel';
    case Groupe = 'groupe';
    case Rectificatif = 'rectificatif';
    case Annulation = 'annulation';

    public function libelle(): string
    {
        return match ($this) {
            self::Automatique => 'Génération automatique',
            self::Manuel => 'Création manuelle',
            self::Partiel => 'Paiement partiel',
            self::Groupe => 'Paiement groupé',
            self::Rectificatif => 'Paiement rectificatif',
            self::Annulation => 'Paiement d’annulation',
        };
    }

    /** Modes exigeant une justification obligatoire. */
    public function exigeJustification(): bool
    {
        return in_array($this, [self::Manuel, self::Rectificatif, self::Annulation], true);
    }
}
