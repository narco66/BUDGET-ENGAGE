<?php

namespace App\Policies;

class ChaineResultatPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'cadres_logiques.manage';
    }
}
