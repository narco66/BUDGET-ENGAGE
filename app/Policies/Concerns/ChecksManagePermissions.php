<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksManagePermissions
{
    protected function managesReferentials(User $user): bool
    {
        return $user->can('referentials.manage');
    }

    protected function managesBudgetExercises(User $user): bool
    {
        return $user->can('exercises.manage');
    }

    protected function managesParameters(User $user): bool
    {
        return $user->can('parameters.manage');
    }
}
