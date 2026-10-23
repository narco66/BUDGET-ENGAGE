<?php

namespace App\Enums;

enum ModeCreationLiquidation: string
{
    case Automatique = 'automatique';
    case Manuel = 'manuel';
    case Complementaire = 'complementaire';
    case Correctif = 'correctif';

    public function libelle(): string
    {
        return match ($this) {
            self::Automatique => 'Génération automatique',
            self::Manuel => 'Création manuelle',
            self::Complementaire => 'Liquidation complémentaire',
            self::Correctif => 'Liquidation corrective',
        };
    }

    /** Modes exigeant une justification obligatoire. */
    public function exigeJustification(): bool
    {
        return in_array($this, [self::Manuel, self::Correctif], true);
    }
}
