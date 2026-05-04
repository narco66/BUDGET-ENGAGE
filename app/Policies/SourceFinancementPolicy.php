<?php

namespace App\Policies;

use App\Models\SourceFinancement;
use App\Models\User;
use App\Policies\Concerns\ChecksManagePermissions;

class SourceFinancementPolicy
{
    use ChecksManagePermissions;

    public function viewAny(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function view(User $user, SourceFinancement $sourceFinancement): bool
    {
        return $this->managesReferentials($user);
    }

    public function create(User $user): bool
    {
        return $this->managesReferentials($user);
    }

    public function update(User $user, SourceFinancement $sourceFinancement): bool
    {
        return $this->managesReferentials($user);
    }

    public function delete(User $user, SourceFinancement $sourceFinancement): bool
    {
        return $this->managesReferentials($user);
    }

    public function restore(User $user, SourceFinancement $sourceFinancement): bool
    {
        return false;
    }

    public function forceDelete(User $user, SourceFinancement $sourceFinancement): bool
    {
        return false;
    }
}
