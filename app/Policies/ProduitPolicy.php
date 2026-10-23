<?php

namespace App\Policies;

class ProduitPolicy extends RbmEntityPolicy
{
    protected function managePermission(): string
    {
        return 'produits.manage';
    }
}
