<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class ServicePolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, Service $service): bool
    {
        return $this->managesReferentials($user);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return false;
    }
}
