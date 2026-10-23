<?php

namespace App\Enums;

enum StatutRecettePerception: string
{
    case Brouillon = 'brouillon';
    case Valide = 'valide';
    case Annule = 'annule';
}
