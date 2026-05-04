<?php

namespace App\Policies;

use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class LigneBudgetairePolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function view(User $user, LigneBudgetaire $ligneBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function create(User $user): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function update(User $user, LigneBudgetaire $ligneBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function delete(User $user, LigneBudgetaire $ligneBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function restore(User $user, LigneBudgetaire $ligneBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function forceDelete(User $user, LigneBudgetaire $ligneBudgetaire): bool
    {
        return false;
    }
}
