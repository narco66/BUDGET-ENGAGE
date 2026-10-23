<?php

namespace App\Enums;

enum StatutPaiement: string
{
    case Brouillon = 'brouillon';
    case Programme = 'programme';
    case ControleTresorerie = 'controle_tresorerie';
    case ControleComptable = 'controle_comptable';
    case ValidationFinanciere = 'validation_financiere';
    case ValidationSignataires = 'validation_signataires';
    case Valide = 'valide';
    case PretBanque = 'pret_banque';
    case TransmisBanque = 'transmis_banque';
    case Execute = 'execute';
    case Echoue = 'echoue';
    case Rapproche = 'rapproche';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Annule = 'annule';
    case Cloture = 'cloture';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Programme => 'Programmé',
            self::ControleTresorerie => 'Contrôle trésorerie',
            self::ControleComptable => 'Contrôle comptable',
            self::ValidationFinanciere => 'Validation financière',
            self::ValidationSignataires => 'Validation signataires',
            self::Valide => 'Validé',
            self::PretBanque => 'Prêt pour banque',
            self::TransmisBanque => 'Transmis à la banque',
            self::Execute => 'Exécuté',
            self::Echoue => 'Échoué',
            self::Rapproche => 'Rapproché',
            self::Retourne => 'Retourné',
            self::Rejete => 'Rejeté',
            self::Annule => 'Annulé',
            self::Cloture => 'Clôturé',
        };
    }

    /** Programmé ou dans un des contrôles/validations du circuit. */
    public function estEnCircuit(): bool
    {
        return in_array($this, [
            self::Programme,
            self::ControleTresorerie,
            self::ControleComptable,
            self::ValidationFinanciere,
            self::ValidationSignataires,
        ], true);
    }

    public function estModifiable(): bool
    {
        return in_array($this, [self::Brouillon, self::Retourne], true);
    }

    /** Validé : peut être préparé/transmis à la banque. */
    public function estTransmissibleBanque(): bool
    {
        return in_array($this, [self::Valide, self::PretBanque], true);
    }

    /** Transmis à la banque : l'exécution (décaissement) peut être confirmée. */
    public function estExecutable(): bool
    {
        return $this === self::TransmisBanque;
    }

    /** Exécuté : éligible au rapprochement bancaire. */
    public function estRapprochable(): bool
    {
        return $this === self::Execute;
    }

    /** Rapproché : clôturable. */
    public function estCloturable(): bool
    {
        return $this === self::Rapproche;
    }
}
