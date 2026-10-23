<?php

namespace App\Policies;

use App\Models\RecettePerception;
use App\Models\User;

class RecettePerceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('recettes.view');
    }

    public function view(User $user, RecettePerception $perception): bool
    {
        return $user->can('recettes.view');
    }

    public function create(User $user): bool
    {
        return $user->can('recettes.create');
    }

    public function validate(User $user, RecettePerception $perception): bool
    {
        return $user->can('recettes.validate');
    }

    public function cancel(User $user, RecettePerception $perception): bool
    {
        return $user->can('recettes.validate');
    }
}
