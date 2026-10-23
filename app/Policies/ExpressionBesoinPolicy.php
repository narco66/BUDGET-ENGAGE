<?php

namespace App\Policies;

use App\Enums\StatutBesoin;
use App\Models\ExpressionBesoin;
use App\Models\User;
use App\Services\ExpressionBesoin\ExpressionBesoinWorkflowService;

class ExpressionBesoinPolicy
{
    /** Permissions donnant une visibilité transverse sur tous les besoins. */
    protected const SEE_ALL_PERMISSIONS = [
        'expression_besoins.admin',
        'expression_besoins.validate',
        'expression_besoins.convert_engagement',
        'reports.view',
        'audit.view',
    ];

    public static function seesAll(User $user): bool
    {
        foreach (self::SEE_ALL_PERMISSIONS as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('expression_besoins.view')
            || $user->can('expression_besoins.create')
            || self::seesAll($user);
    }

    public function view(User $user, ExpressionBesoin $besoin): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return self::seesAll($user)
            || $besoin->created_by === $user->id
            || $besoin->demandeur_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('expression_besoins.create');
    }

    public function update(User $user, ExpressionBesoin $besoin): bool
    {
        if (! $besoin->statut->estModifiable()) {
            return $user->can('expression_besoins.admin');
        }

        if ($besoin->created_by === $user->id && $user->can('expression_besoins.create')) {
            return true;
        }

        return $user->can('expression_besoins.admin');
    }

    public function delete(User $user, ExpressionBesoin $besoin): bool
    {
        if ($besoin->statut !== StatutBesoin::Brouillon) {
            return false;
        }

        if ($besoin->created_by === $user->id && $user->can('expression_besoins.create')) {
            return true;
        }

        return $user->can('expression_besoins.admin');
    }

    public function submit(User $user, ExpressionBesoin $besoin): bool
    {
        if (! $user->can('expression_besoins.submit')) {
            return false;
        }

        if (! in_array($besoin->statut, [StatutBesoin::Brouillon, StatutBesoin::Retourne], true)) {
            return false;
        }

        return $besoin->created_by === $user->id || $user->can('expression_besoins.admin');
    }

    public function validateBesoin(User $user, ExpressionBesoin $besoin): bool
    {
        if (! $besoin->statut->estEnCircuitValidation()) {
            return false;
        }

        $service = app(ExpressionBesoinWorkflowService::class);
        $step = $service->currentPendingStep($besoin);

        return $step !== null && $service->userCanValidate($user, $step);
    }

    public function cancel(User $user, ExpressionBesoin $besoin): bool
    {
        if (! $besoin->statut->estAnnulable()) {
            return false;
        }

        if ($besoin->created_by === $user->id && $user->can('expression_besoins.submit')) {
            return true;
        }

        return $user->can('expression_besoins.admin');
    }

    public function reject(User $user, ExpressionBesoin $besoin): bool
    {
        return $user->can('expression_besoins.reject') || $user->can('expression_besoins.validate');
    }

    public function convert(User $user, ExpressionBesoin $besoin): bool
    {
        return $user->can('expression_besoins.convert_engagement')
            && $besoin->statut === StatutBesoin::Valide
            && $besoin->engagement_id === null;
    }

    public function revise(User $user, ExpressionBesoin $besoin): bool
    {
        return ($user->can('expression_besoins.admin') || $user->can('expression_besoins.validate'))
            && $besoin->statut === StatutBesoin::Valide
            && $besoin->engagement_id === null;
    }

    public function export(User $user): bool
    {
        return $user->can('expression_besoins.export');
    }

    public function restore(User $user, ExpressionBesoin $besoin): bool
    {
        return $user->can('expression_besoins.admin');
    }

    public function forceDelete(User $user, ExpressionBesoin $besoin): bool
    {
        return false;
    }
}
