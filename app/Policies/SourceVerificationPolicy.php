<?php

namespace App\Policies;

class SourceVerificationPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'indicateurs.manage';
    }
}
