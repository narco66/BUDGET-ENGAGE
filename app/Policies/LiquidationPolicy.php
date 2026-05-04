<?php

namespace App\Policies;

use App\Models\Liquidation;
use App\Models\User;

class LiquidationPolicy
{
    public function view(User $user, Liquidation $liquidation): bool
    {
        return $user->can('view', $liquidation->engagement);
    }

    public function addOrdonnancement(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('execution.liquidation')) {
            return false;
        }

        $liquidation->loadMissing('engagement');

        return EngagementPolicy::allowsExecutionRecording($user, $liquidation->engagement);
    }
}
