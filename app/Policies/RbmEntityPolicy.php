<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksRbmPermissions;
use Illuminate\Database\Eloquent\Model;

abstract class RbmEntityPolicy
{
    use ChecksRbmPermissions;

    abstract protected function managePermission(): string;

    public function viewAny(User $user): bool
    {
        return $this->canViewRbm($user)
            || $this->canManageEntity($user, $this->managePermission());
    }

    public function view(User $user, Model $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canCreateRbm($user)
            || $this->canManageEntity($user, $this->managePermission());
    }

    public function update(User $user, Model $model): bool
    {
        return $this->canUpdateRbm($user)
            || $this->canManageEntity($user, $this->managePermission());
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canArchiveRbm($user)
            || $this->canManageEntity($user, $this->managePermission());
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->canRestoreRbm($user);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
