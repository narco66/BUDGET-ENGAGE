<?php

namespace App\Policies;

class ActivitePolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'activites.manage';
    }
}
