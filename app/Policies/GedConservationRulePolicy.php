<?php

namespace App\Policies;

use App\Models\GedConservationRule;
use App\Models\User;

class GedConservationRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ged.view');
    }

    public function view(User $user, GedConservationRule $rule): bool
    {
        return $user->can('ged.view');
    }

    public function create(User $user): bool
    {
        return $user->can('ged.admin');
    }

    public function update(User $user, GedConservationRule $rule): bool
    {
        return $user->can('ged.admin');
    }

    public function delete(User $user, GedConservationRule $rule): bool
    {
        return $user->can('ged.admin');
    }

    public function restore(User $user, GedConservationRule $rule): bool
    {
        return $user->can('ged.admin');
    }

    public function forceDelete(User $user, GedConservationRule $rule): bool
    {
        return false;
    }
}
