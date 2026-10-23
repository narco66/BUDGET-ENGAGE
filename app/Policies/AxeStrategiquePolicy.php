<?php

namespace App\Policies;

class AxeStrategiquePolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'axes.manage';
    }
}
