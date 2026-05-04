<?php

namespace App\Enums;

enum StatutEngagement: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case EnValidation = 'en_validation';
    case Retourne = 'retourne';
    case Rejete = 'rejete';
    case Approuve = 'approuve';
    case Annule = 'annule';
    case Liquide = 'liquide';
    case Ordonnance = 'ordonnance';
    case Paye = 'paye';
    case Cloture = 'cloture';
}
