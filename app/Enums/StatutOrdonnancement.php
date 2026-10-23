<?php

namespace App\Enums;

enum StatutOrdonnancement: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case ControleBudgetaire = 'controle_budgetaire';
    case ControleComptable = 'controle_comptable';
    case ControleFinancier = 'controle_financier';
    case VisaOrdonnateur = 'visa_ordonnateur';
    case Valide = 'valide';
    case Mandate = 'mandate';
    case TransmisPaiement = 'transmis_paiement';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Annule = 'annule';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Soumis => 'Soumis',
            self::ControleBudgetaire => 'Contrôle budgétaire',
            self::ControleComptable => 'Contrôle comptable',
            self::ControleFinancier => 'Contrôle financier',
            self::VisaOrdonnateur => 'Visa ordonnateur',
            self::Valide => 'Validé',
            self::Mandate => 'Mandat généré',
            self::TransmisPaiement => 'Transmis au paiement',
            self::Retourne => 'Retourné',
            self::Rejete => 'Rejeté',
            self::Annule => 'Annulé',
        };
    }

    /** Soumis ou dans un des contrôles/visa du circuit. */
    public function estEnCircuit(): bool
    {
        return in_array($this, [
            self::Soumis,
            self::ControleBudgetaire,
            self::ControleComptable,
            self::ControleFinancier,
            self::VisaOrdonnateur,
        ], true);
    }

    public function estModifiable(): bool
    {
        return in_array($this, [self::Brouillon, self::Retourne], true);
    }

    /** Validé : un mandat peut être généré. */
    public function estMandatable(): bool
    {
        return $this === self::Valide;
    }

    /** Mandaté : transmissible au paiement. */
    public function estTransmissible(): bool
    {
        return $this === self::Mandate;
    }
}
