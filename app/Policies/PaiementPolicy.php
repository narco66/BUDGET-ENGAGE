<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    public function view(User $user, Paiement $paiement): bool
    {
        $paiement->loadMissing('ordonnancement.liquidation.engagement');

        return $user->can('view', $paiement->ordonnancement->liquidation->engagement);
    }
}
