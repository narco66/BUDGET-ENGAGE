<?php

namespace App\Enums;

enum BeneficiaireType: string
{
    case Fournisseur = 'fournisseur';
    case Agent = 'agent';
    case Autre = 'autre';
}
