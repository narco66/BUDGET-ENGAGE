<?php

namespace App\Enums;

enum StatutSignatureGed: string
{
    case NonSigne = 'non_signe';
    case EnAttente = 'en_attente';
    case Signe = 'signe';
    case Rejete = 'rejete';

    public function libelle(): string
    {
        return match ($this) {
            self::NonSigne => 'Non signé',
            self::EnAttente => 'En attente de signature',
            self::Signe => 'Signé',
            self::Rejete => 'Signature rejetée',
        };
    }
}
