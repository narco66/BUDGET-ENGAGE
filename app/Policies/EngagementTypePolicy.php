<?php

namespace App\Policies;

use App\Models\EngagementType;
use App\Models\User;

class EngagementTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('engagement.view') || $user->can('engagement.admin');
    }

    public function view(User $user, EngagementType $type): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('engagement.admin');
    }

    public function update(User $user, EngagementType $type): bool
    {
        return $user->can('engagement.admin');
    }

    public function delete(User $user, EngagementType $type): bool
    {
        return $user->can('engagement.admin');
    }

    public function restore(User $user, EngagementType $type): bool
    {
        return $user->can('engagement.admin');
    }

    public function forceDelete(User $user, EngagementType $type): bool
    {
        return false;
    }
}
