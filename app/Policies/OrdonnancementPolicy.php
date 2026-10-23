<?php

namespace App\Policies;

use App\Models\Ordonnancement;
use App\Models\User;

class OrdonnancementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('execution.liquidation')
            || $user->can('execution.payment')
            || $user->can('ordonnancement.view');
    }

    public function view(User $user, Ordonnancement $ordonnancement): bool
    {
        if ($user->can('ordonnancement.view')) {
            return true;
        }

        $ordonnancement->loadMissing('liquidation.engagement');

        return $ordonnancement->liquidation?->engagement
            ? $user->can('view', $ordonnancement->liquidation->engagement)
            : false;
    }

    // --- Abilities du module Ordonnancement ERP (gate sur permissions ordonnancement.*) ---

    public function create(User $user): bool
    {
        return $user->can('ordonnancement.create');
    }

    public function generer(User $user): bool
    {
        return $user->can('ordonnancement.generate') || $user->can('ordonnancement.create');
    }

    /** Modification autorisée uniquement en brouillon / retourné, sans paiement. */
    public function modifier(User $user, Ordonnancement $ordonnancement): bool
    {
        if (! $user->can('ordonnancement.update')) {
            return false;
        }

        return ($ordonnancement->statut?->estModifiable() ?? true)
            && ! $ordonnancement->paiements()->exists();
    }

    public function soumettre(User $user, Ordonnancement $ordonnancement): bool
    {
        return $user->can('ordonnancement.update')
            && ($ordonnancement->statut?->estModifiable() ?? true);
    }

    public function decider(User $user, Ordonnancement $ordonnancement): bool
    {
        return $user->can('ordonnancement.validate');
    }

    public function mandater(User $user, Ordonnancement $ordonnancement): bool
    {
        return ($user->can('ordonnancement.validate') || $user->can('ordonnancement.admin'))
            && ($ordonnancement->statut?->estMandatable() ?? false);
    }

    public function transmettre(User $user, Ordonnancement $ordonnancement): bool
    {
        return $user->can('ordonnancement.transfer_paiement')
            && ($ordonnancement->statut?->estTransmissible() ?? false);
    }

    public function annuler(User $user, Ordonnancement $ordonnancement): bool
    {
        if (! $user->can('ordonnancement.cancel')) {
            return false;
        }

        return ! $ordonnancement->paiements()->exists();
    }

    public function gererTypes(User $user): bool
    {
        return $user->can('ordonnancement.admin');
    }

    public function update(User $user, Ordonnancement $ordonnancement): bool
    {
        if (! $user->can('execution.liquidation') && ! $user->can('execution.payment')) {
            return false;
        }

        $ordonnancement->loadMissing('liquidation.engagement');

        return EngagementPolicy::allowsExecutionRecording($ordonnancement->liquidation->engagement)
            && ! $ordonnancement->paiements()->exists();
    }

    public function cancel(User $user, Ordonnancement $ordonnancement): bool
    {
        if (! $user->can('execution.liquidation') && ! $user->can('execution.payment')) {
            return false;
        }

        $ordonnancement->loadMissing('liquidation.engagement');

        if ($ordonnancement->liquidation->engagement->statut === \App\Enums\StatutEngagement::Cloture) {
            return false;
        }

        return ! $ordonnancement->paiements()->exists();
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
