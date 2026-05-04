<?php

namespace App\Enums;

enum StatutExerciceBudgetaire: string
{
    case Preparatoire = 'preparatoire';
    case Ouvert = 'ouvert';
    case Suspendu = 'suspendu';
    case Cloture = 'cloture';
    case Archive = 'archive';
}
