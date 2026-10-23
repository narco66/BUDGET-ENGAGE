<?php

namespace App\Policies;

class UniteMesurePolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'indicateurs.manage';
    }
}
