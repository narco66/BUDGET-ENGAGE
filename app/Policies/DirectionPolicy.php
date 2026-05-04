<?php

namespace App\Policies;

use App\Models\Direction;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class DirectionPolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, Direction $direction): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, Direction $direction): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, Direction $direction): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, Direction $direction): bool
    {
        return $this->managesReferentials($user);
    }

    public function forceDelete(User $user, Direction $direction): bool
    {
        return false;
    }
}
