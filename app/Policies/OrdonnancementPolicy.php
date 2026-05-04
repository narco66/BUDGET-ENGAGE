<?php

namespace App\Policies;

use App\Models\Ordonnancement;
use App\Models\User;

class OrdonnancementPolicy
{
    public function view(User $user, Ordonnancement $ordonnancement): bool
    {
        $ordonnancement->loadMissing('liquidation.engagement');

        return $user->can('view', $ordonnancement->liquidation->engagement);
    }

    public function addPaiement(User $user, Ordonnancement $ordonnancement): bool
    {
        if (! $user->can('execution.payment')) {
            return false;
        }

        $ordonnancement->loadMissing('liquidation.engagement');

        return EngagementPolicy::allowsExecutionRecording($ordonnancement->liquidation->engagement);
    }
}
