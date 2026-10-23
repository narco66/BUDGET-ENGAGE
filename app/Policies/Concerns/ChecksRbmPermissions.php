<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksRbmPermissions
{
    protected function canViewRbm(User $user): bool
    {
        return $user->can('rbm.view')
            || $user->can('rbm.admin')
            || $user->can('rbm.create')
            || $user->can('rbm.update');
    }

    protected function canManageRbm(User $user): bool
    {
        return $user->can('rbm.admin')
            || $user->can('rbm.create')
            || $user->can('rbm.update');
    }

    protected function canCreateRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.create');
    }

    protected function canUpdateRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.update');
    }

    protected function canDeleteRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.delete');
    }

    protected function canArchiveRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.archive') || $user->can('rbm.delete');
    }

    protected function canRestoreRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.restore');
    }

    protected function canExportRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.export') || $user->can('rbm.view');
    }

    protected function canImportRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.import');
    }

    protected function canValidateRbm(User $user): bool
    {
        return $user->can('rbm.admin') || $user->can('rbm.validate');
    }

    protected function canManageEntity(User $user, string $permission): bool
    {
        return $user->can('rbm.admin') || $user->can($permission);
    }
}
