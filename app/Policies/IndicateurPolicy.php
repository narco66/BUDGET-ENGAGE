<?php

namespace App\Policies;

class IndicateurPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'indicateurs.manage';
    }
}
