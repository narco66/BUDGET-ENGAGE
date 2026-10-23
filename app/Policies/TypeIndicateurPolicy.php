<?php

namespace App\Policies;

class TypeIndicateurPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'indicateurs.manage';
    }
}
