<?php

namespace App\Enums;

enum TypeBesoin: string
{
    case Pap = 'pap';
    case HorsPap = 'hors_pap';

    public function libelle(): string
    {
        return match ($this) {
            self::Pap => 'Besoin PAP',
            self::HorsPap => 'Besoin Hors PAP',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pap => 'badge-info',
            self::HorsPap => 'badge-neutral',
        };
    }
}
