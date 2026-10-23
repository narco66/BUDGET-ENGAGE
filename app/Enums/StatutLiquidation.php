<?php

namespace App\Enums;

enum StatutLiquidation: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case ControleTechnique = 'controle_technique';
    case ControleAdministratif = 'controle_administratif';
    case ControleBudgetaire = 'controle_budgetaire';
    case ControleFinancier = 'controle_financier';
    case Valide = 'valide';
    case Certifie = 'certifie';
    case TransmisOrdonnancement = 'transmis_ordonnancement';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Annule = 'annule';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Soumis => 'Soumise',
            self::ControleTechnique => 'Contrôle technique',
            self::ControleAdministratif => 'Contrôle administratif',
            self::ControleBudgetaire => 'Contrôle budgétaire',
            self::ControleFinancier => 'Contrôle financier',
            self::Valide => 'Validée',
            self::Certifie => 'Certifiée',
            self::TransmisOrdonnancement => 'Transmise à l’ordonnancement',
            self::Retourne => 'Retournée',
            self::Rejete => 'Rejetée',
            self::Annule => 'Annulée',
        };
    }

    /** Soumise ou dans un des contrôles du circuit. */
    public function estEnCircuit(): bool
    {
        return in_array($this, [
            self::Soumis,
            self::ControleTechnique,
            self::ControleAdministratif,
            self::ControleBudgetaire,
            self::ControleFinancier,
        ], true);
    }

    /** Modifiable uniquement tant que non engagée dans le circuit/validée. */
    public function estModifiable(): bool
    {
        return in_array($this, [self::Brouillon, self::Retourne], true);
    }

    /** Certifiée : prête à être transmise à l’ordonnancement. */
    public function estCertifiable(): bool
    {
        return $this === self::Valide;
    }

    public function estTransmissible(): bool
    {
        return $this === self::Certifie;
    }
}
