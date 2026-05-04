<?php

namespace App\Enums;

enum StatutImportBudget: string
{
    case Importe = 'importe';
    case EnValidation = 'en_validation';
    case Valide = 'valide';
    case Rejete = 'rejete';
    case Annule = 'annule';
}
