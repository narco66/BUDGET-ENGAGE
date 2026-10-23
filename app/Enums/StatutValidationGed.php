<?php

namespace App\Enums;

enum StatutValidationGed: string
{
    case EnAttente = 'en_attente';
    case Valide = 'valide';
    case Rejete = 'rejete';
    case Retourne = 'retourne';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Valide => 'Validé',
            self::Rejete => 'Rejeté',
            self::Retourne => 'Retourné',
        };
    }
}
