<?php

namespace App\Policies;

use App\Models\ExerciceBudgetaire;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class ExerciceBudgetairePolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function view(User $user, ExerciceBudgetaire $exerciceBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function create(User $user): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function update(User $user, ExerciceBudgetaire $exerciceBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function delete(User $user, ExerciceBudgetaire $exerciceBudgetaire): bool
    {
        return $this->managesBudgetExercises($user);
    }

    public function restore(User $user, ExerciceBudgetaire $exerciceBudgetaire): bool
    {
        return false;
    }

    public function forceDelete(User $user, ExerciceBudgetaire $exerciceBudgetaire): bool
    {
        return false;
    }
}
