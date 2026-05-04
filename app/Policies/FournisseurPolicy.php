<?php

namespace App\Policies;

use App\Models\Fournisseur;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class FournisseurPolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, Fournisseur $fournisseur): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, Fournisseur $fournisseur): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, Fournisseur $fournisseur): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, Fournisseur $fournisseur): bool
    {
        return $this->managesReferentials($user);
    }

    public function forceDelete(User $user, Fournisseur $fournisseur): bool
    {
        return false;
    }
}
