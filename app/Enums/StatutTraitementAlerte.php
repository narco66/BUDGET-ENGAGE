<?php

namespace App\Enums;

enum StatutTraitementAlerte: string
{
    case Nouvelle = 'nouvelle';
    case EnAnalyse = 'en_analyse';
    case Confirmee = 'confirmee';
    case NonConfirmee = 'non_confirmee';
    case Corrigee = 'corrigee';
    case Cloturee = 'cloturee';
}
