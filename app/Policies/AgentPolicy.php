<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class AgentPolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, Agent $agent): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, Agent $agent): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, Agent $agent): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, Agent $agent): bool
    {
        return $this->managesReferentials($user);
    }

    public function forceDelete(User $user, Agent $agent): bool
    {
        return false;
    }
}
