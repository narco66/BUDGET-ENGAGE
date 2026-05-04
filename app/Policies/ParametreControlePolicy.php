<?php

namespace App\Policies;

use App\Models\ParametreControle;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class ParametreControlePolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesParameters($user);
    }

    public function view(User $user, ParametreControle $parametreControle): bool
    {
        return $this->managesParameters($user);
    }

    public function create(User $user): bool
    {
        return $this->managesParameters($user);
    }

    public function update(User $user, ParametreControle $parametreControle): bool
    {
        return $this->managesParameters($user);
    }

    public function delete(User $user, ParametreControle $parametreControle): bool
    {
        return $this->managesParameters($user);
    }

    public function restore(User $user, ParametreControle $parametreControle): bool
    {
        return false;
    }

    public function forceDelete(User $user, ParametreControle $parametreControle): bool
    {
        return false;
    }
}
