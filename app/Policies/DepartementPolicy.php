<?php

namespace App\Policies;

use App\Models\Departement;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class DepartementPolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, Departement $departement): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, Departement $departement): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, Departement $departement): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, Departement $departement): bool
    {
        return $this->managesReferentials($user);
    }

    public function forceDelete(User $user, Departement $departement): bool
    {
        return false;
    }
}
