<?php

namespace App\Policies;

class CadreLogiquePolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'cadres_logiques.manage';
    }
}
