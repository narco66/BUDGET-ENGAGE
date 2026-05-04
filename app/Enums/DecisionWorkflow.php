<?php

namespace App\Enums;

enum DecisionWorkflow: string
{
    case EnAttente = 'en_attente';
    case Valide = 'valide';
    case Rejete = 'rejete';
    case Retourne = 'retourne';
}
