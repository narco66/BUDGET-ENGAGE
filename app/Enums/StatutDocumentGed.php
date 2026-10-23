<?php

namespace App\Enums;

enum StatutDocumentGed: string
{
    case Brouillon = 'brouillon';
    case Depose = 'depose';
    case EnValidation = 'en_validation';
    case Valide = 'valide';
    case Rejete = 'rejete';
    case Signe = 'signe';
    case Publie = 'publie';
    case Archive = 'archive';
    case Expire = 'expire';
    case Supprime = 'supprime';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Depose => 'Déposé',
            self::EnValidation => 'En cours de validation',
            self::Valide => 'Validé',
            self::Rejete => 'Rejeté',
            self::Signe => 'Signé',
            self::Publie => 'Publié',
            self::Archive => 'Archivé',
            self::Expire => 'Expiré',
            self::Supprime => 'Supprimé',
        };
    }

    /** Classe de badge CSS cohérente avec la charte CEEAC. */
    public function badge(): string
    {
        return match ($this) {
            self::Brouillon => 'badge-neutral',
            self::Depose => 'badge-info',
            self::EnValidation => 'badge-warning',
            self::Valide, self::Signe, self::Publie => 'badge-success',
            self::Rejete, self::Supprime => 'badge-danger',
            self::Archive => 'badge-neutral',
            self::Expire => 'badge-warning',
        };
    }
}
