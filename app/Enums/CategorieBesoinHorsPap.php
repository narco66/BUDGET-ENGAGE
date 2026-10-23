<?php

namespace App\Enums;

enum CategorieBesoinHorsPap: string
{
    case FonctionnementAdministratif = 'fonctionnement_administratif';
    case Maintenance = 'maintenance';
    case Informatique = 'informatique';
    case RessourcesHumaines = 'ressources_humaines';
    case Logistique = 'logistique';
    case Securite = 'securite';
    case Urgence = 'urgence';
    case Imprevu = 'imprevu';
    case Representation = 'representation';
    case Communication = 'communication';
    case InvestissementInstitutionnel = 'investissement_institutionnel';
    case Autres = 'autres';

    public function libelle(): string
    {
        return match ($this) {
            self::FonctionnementAdministratif => 'Fonctionnement administratif',
            self::Maintenance => 'Maintenance',
            self::Informatique => 'Informatique',
            self::RessourcesHumaines => 'Ressources humaines',
            self::Logistique => 'Logistique',
            self::Securite => 'Sécurité',
            self::Urgence => 'Urgence',
            self::Imprevu => 'Imprévu',
            self::Representation => 'Représentation',
            self::Communication => 'Communication',
            self::InvestissementInstitutionnel => 'Investissement institutionnel',
            self::Autres => 'Autres',
        };
    }
}
