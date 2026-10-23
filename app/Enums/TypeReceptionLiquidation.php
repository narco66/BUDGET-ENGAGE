<?php

namespace App\Enums;

enum TypeReceptionLiquidation: string
{
    case Provisoire = 'provisoire';
    case Definitive = 'definitive';

    public function libelle(): string
    {
        return match ($this) {
            self::Provisoire => 'Réception provisoire',
            self::Definitive => 'Réception définitive',
        };
    }
}
