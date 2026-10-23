<?php

namespace App\Policies;

use App\Models\Liquidation;
use App\Models\User;

class LiquidationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('execution.liquidation')
            || $user->can('execution.payment')
            || $user->can('liquidation.view');
    }

    public function view(User $user, Liquidation $liquidation): bool
    {
        return $user->can('liquidation.view')
            || $user->can('view', $liquidation->engagement);
    }

    // --- Abilities du module Liquidation ERP (gate sur permissions liquidation.*) ---

    public function create(User $user): bool
    {
        return $user->can('liquidation.create');
    }

    public function generer(User $user): bool
    {
        return $user->can('liquidation.generate') || $user->can('liquidation.create');
    }

    /** Modification autorisée uniquement en brouillon / retournée. */
    public function modifier(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('liquidation.update')) {
            return false;
        }

        return ($liquidation->statut?->estModifiable() ?? true)
            && ! $liquidation->ordonnancements()->exists();
    }

    public function soumettre(User $user, Liquidation $liquidation): bool
    {
        return $user->can('liquidation.update')
            && ($liquidation->statut?->estModifiable() ?? true);
    }

    public function decider(User $user, Liquidation $liquidation): bool
    {
        return $user->can('liquidation.validate');
    }

    public function certifier(User $user, Liquidation $liquidation): bool
    {
        return ($user->can('liquidation.validate') || $user->can('liquidation.admin'))
            && ($liquidation->statut?->estCertifiable() ?? false);
    }

    public function transmettre(User $user, Liquidation $liquidation): bool
    {
        return $user->can('liquidation.transfer_ordonnancement')
            && ($liquidation->statut?->estTransmissible() ?? false);
    }

    public function annuler(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('liquidation.cancel')) {
            return false;
        }

        return ! $liquidation->ordonnancements()->exists();
    }

    public function gererTypes(User $user): bool
    {
        return $user->can('liquidation.admin');
    }

    public function update(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('execution.liquidation')) {
            return false;
        }

        $liquidation->loadMissing('engagement');

        return EngagementPolicy::allowsExecutionRecording($liquidation->engagement)
            && ! $liquidation->ordonnancements()->exists();
    }

    public function cancel(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('execution.liquidation')) {
            return false;
        }

        $liquidation->loadMissing('engagement');

        if ($liquidation->engagement->statut === \App\Enums\StatutEngagement::Cloture) {
            return false;
        }

        return ! $liquidation->ordonnancements()->exists();
    }

    public function addOrdonnancement(User $user, Liquidation $liquidation): bool
    {
        if (! $user->can('execution.liquidation')) {
            return false;
        }

        $liquidation->loadMissing('engagement');

        return EngagementPolicy::allowsExecutionRecording($liquidation->engagement);
    }
}
