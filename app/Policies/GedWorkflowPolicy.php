<?php

namespace App\Policies;

use App\Models\GedWorkflow;
use App\Models\User;

class GedWorkflowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ged.view');
    }

    public function view(User $user, GedWorkflow $workflow): bool
    {
        return $user->can('ged.view');
    }

    public function create(User $user): bool
    {
        return $user->can('ged.admin');
    }

    public function update(User $user, GedWorkflow $workflow): bool
    {
        return $user->can('ged.admin');
    }

    public function delete(User $user, GedWorkflow $workflow): bool
    {
        return $user->can('ged.admin');
    }

    public function restore(User $user, GedWorkflow $workflow): bool
    {
        return $user->can('ged.admin');
    }

    public function forceDelete(User $user, GedWorkflow $workflow): bool
    {
        return false;
    }
}
