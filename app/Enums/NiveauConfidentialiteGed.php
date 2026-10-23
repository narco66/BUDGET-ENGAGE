<?php

namespace App\Enums;

enum NiveauConfidentialiteGed: string
{
    case Public = 'public';
    case Interne = 'interne';
    case Restreint = 'restreint';
    case Confidentiel = 'confidentiel';
    case TresConfidentiel = 'tres_confidentiel';

    public function libelle(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Interne => 'Interne',
            self::Restreint => 'Restreint',
            self::Confidentiel => 'Confidentiel',
            self::TresConfidentiel => 'Très confidentiel',
        };
    }

    /** Niveau de sensibilité croissant (0 = public, 4 = très confidentiel). */
    public function rang(): int
    {
        return match ($this) {
            self::Public => 0,
            self::Interne => 1,
            self::Restreint => 2,
            self::Confidentiel => 3,
            self::TresConfidentiel => 4,
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Public => 'badge-success',
            self::Interne => 'badge-info',
            self::Restreint => 'badge-warning',
            self::Confidentiel, self::TresConfidentiel => 'badge-danger',
        };
    }
}
