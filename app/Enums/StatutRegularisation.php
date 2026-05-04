<?php

namespace App\Enums;

enum StatutRegularisation: string
{
    case EnAttenteRegularisation = 'en_attente_regularisation';
    case EnCoursRegularisation = 'en_cours_regularisation';
    case Regularise = 'regularise';
    case NonRegularise = 'non_regularise';
}
