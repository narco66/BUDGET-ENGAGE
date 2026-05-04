<?php

namespace App\Enums;

enum NiveauRisqueAlerte: string
{
    case Faible = 'faible';
    case Moyen = 'moyen';
    case Eleve = 'eleve';
    case Critique = 'critique';
}
