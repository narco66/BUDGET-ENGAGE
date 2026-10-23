<?php

namespace App\Policies;

class SousProduitPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'sous_produits.manage';
    }
}
