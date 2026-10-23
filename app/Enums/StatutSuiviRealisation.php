<?php

namespace App\Enums;

enum StatutSuiviRealisation: string
{
    case Brouillon = 'brouillon';
    case Soumis = 'soumis';
    case Valide = 'valide';
    case Rejete = 'rejete';
}
