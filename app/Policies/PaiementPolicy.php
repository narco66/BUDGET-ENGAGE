<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('execution.payment') || $user->can('paiement.view');
    }

    public function view(User $user, Paiement $paiement): bool
    {
        if ($user->can('paiement.view')) {
            return true;
        }

        $paiement->loadMissing('ordonnancement.liquidation.engagement');

        return $paiement->ordonnancement?->liquidation?->engagement
            ? $user->can('view', $paiement->ordonnancement->liquidation->engagement)
            : false;
    }

    // --- Abilities du module Paiement ERP (gate sur permissions paiement.*) ---

    public function create(User $user): bool
    {
        return $user->can('paiement.create');
    }

    public function generer(User $user): bool
    {
        return $user->can('paiement.generate') || $user->can('paiement.create');
    }

    /** Modification autorisée uniquement en brouillon / retourné. */
    public function modifier(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.update')
            && ($paiement->statut?->estModifiable() ?? true);
    }

    public function programmer(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.update')
            && ($paiement->statut?->estModifiable() ?? true);
    }

    public function decider(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.validate');
    }

    public function transmettre(User $user, Paiement $paiement): bool
    {
        return ($user->can('paiement.execute') || $user->can('paiement.admin'))
            && ($paiement->statut?->estTransmissibleBanque() ?? false);
    }

    public function executer(User $user, Paiement $paiement): bool
    {
        return ($user->can('paiement.execute') || $user->can('paiement.admin'))
            && ($paiement->statut?->estExecutable() ?? false);
    }

    public function signalerRejet(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.reject');
    }

    public function rapprocher(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.reconcile')
            && ($paiement->statut?->estRapprochable() ?? false);
    }

    public function cloturer(User $user, Paiement $paiement): bool
    {
        return ($user->can('paiement.reconcile') || $user->can('paiement.admin'))
            && ($paiement->statut?->estCloturable() ?? false);
    }

    public function annuler(User $user, Paiement $paiement): bool
    {
        return $user->can('paiement.cancel');
    }

    public function gererTypes(User $user): bool
    {
        return $user->can('paiement.admin');
    }

    public function update(User $user, Paiement $paiement): bool
    {
        if (! $user->can('execution.payment')) {
            return false;
        }

        $paiement->loadMissing('ordonnancement.liquidation.engagement');

        return EngagementPolicy::allowsExecutionRecording($paiement->ordonnancement->liquidation->engagement);
    }

    public function cancel(User $user, Paiement $paiement): bool
    {
        if (! $user->can('execution.payment')) {
            return false;
        }

        $paiement->loadMissing('ordonnancement.liquidation.engagement');

        return $paiement->ordonnancement->liquidation->engagement->statut !== \App\Enums\StatutEngagement::Cloture;
    }
}
