<?php

namespace App\Enums;

enum PrioriteBesoin: string
{
    case Basse = 'basse';
    case Normale = 'normale';
    case Haute = 'haute';
    case Critique = 'critique';

    public function libelle(): string
    {
        return match ($this) {
            self::Basse => 'Basse',
            self::Normale => 'Normale',
            self::Haute => 'Haute',
            self::Critique => 'Critique',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Basse => 'badge-neutral',
            self::Normale => 'badge-info',
            self::Haute => 'badge-warning',
            self::Critique => 'badge-danger',
        };
    }
}
