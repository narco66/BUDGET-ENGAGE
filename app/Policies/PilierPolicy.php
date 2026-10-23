<?php

namespace App\Policies;

use App\Models\Pilier;

class PilierPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'piliers.manage';
    }
}
