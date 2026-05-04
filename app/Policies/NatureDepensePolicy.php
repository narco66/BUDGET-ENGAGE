<?php

namespace App\Policies;

use App\Models\NatureDepense;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class NatureDepensePolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, NatureDepense $natureDepense): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, NatureDepense $natureDepense): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, NatureDepense $natureDepense): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, NatureDepense $natureDepense): bool
    {
        return false;
    }

    public function forceDelete(User $user, NatureDepense $natureDepense): bool
    {
        return false;
    }
}
