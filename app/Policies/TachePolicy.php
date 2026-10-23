<?php

namespace App\Policies;

class TachePolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'taches.manage';
    }
}
