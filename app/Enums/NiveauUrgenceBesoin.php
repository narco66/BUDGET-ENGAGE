<?php

namespace App\Enums;

enum NiveauUrgenceBesoin: string
{
    case Normal = 'normal';
    case Urgent = 'urgent';
    case TresUrgent = 'tres_urgent';

    public function libelle(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Urgent => 'Urgent',
            self::TresUrgent => 'Très urgent',
        };
    }
}
