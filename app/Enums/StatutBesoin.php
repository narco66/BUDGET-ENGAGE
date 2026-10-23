<?php

namespace App\Enums;

enum StatutBesoin: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case EnValidation = 'en_validation';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Valide = 'valide';
    case Converti = 'converti';
    case Annule = 'annule';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Soumis => 'Soumis',
            self::EnValidation => 'En cours de validation',
            self::Retourne => 'Retourné',
            self::Rejete => 'Rejeté',
            self::Valide => 'Validé',
            self::Converti => 'Converti en engagement',
            self::Annule => 'Annulé',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Brouillon => 'badge-neutral',
            self::Soumis => 'badge-info',
            self::EnValidation => 'badge-warning',
            self::Retourne => 'badge-warning',
            self::Rejete, self::Annule => 'badge-danger',
            self::Valide => 'badge-success',
            self::Converti => 'badge-success',
        };
    }

    /** Le besoin est-il modifiable par son auteur ? */
    public function estModifiable(): bool
    {
        return in_array($this, [self::Brouillon, self::Retourne], true);
    }

    /** Soumis ou en cours de validation hiérarchique. */
    public function estEnCircuitValidation(): bool
    {
        return in_array($this, [self::Soumis, self::EnValidation], true);
    }

    /** Peut être annulé avant conversion en engagement. */
    public function estAnnulable(): bool
    {
        return in_array($this, [self::Brouillon, self::Retourne, self::Soumis, self::EnValidation], true);
    }
}
