<?php

namespace App\Enums;

enum ModeCreationOrdonnancement: string
{
    case Automatique = 'automatique';
    case Manuel = 'manuel';
    case Complementaire = 'complementaire';
    case Rectificatif = 'rectificatif';
    case Annulation = 'annulation';

    public function libelle(): string
    {
        return match ($this) {
            self::Automatique => 'Génération automatique',
            self::Manuel => 'Création manuelle',
            self::Complementaire => 'Ordonnancement complémentaire',
            self::Rectificatif => 'Ordonnancement rectificatif',
            self::Annulation => 'Ordonnancement d’annulation',
        };
    }

    /** Modes exigeant une justification obligatoire. */
    public function exigeJustification(): bool
    {
        return in_array($this, [self::Manuel, self::Rectificatif, self::Annulation], true);
    }
}
