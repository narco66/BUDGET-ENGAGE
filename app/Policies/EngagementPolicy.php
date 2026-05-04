<?php

namespace App\Policies;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\User;
use App\Services\EngagementWorkflowService;

class EngagementPolicy
{
    /** @var list<string> */
    protected const SEE_ALL_PERMISSIONS = [
        'exercises.manage',
        'reports.view',
        'workflow.validate_service',
        'workflow.validate_direction',
        'workflow.validate_department',
        'workflow.budget_control',
        'workflow.financial_control',
        'workflow.ordonnateur',
        'workflow.ordonnateur_delegue',
        'execution.liquidation',
        'execution.payment',
        'audit.view',
    ];

    public static function seesAllEngagements(User $user): bool
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
        return $user->can('engagements.create')
            || $user->can('engagements.submit')
            || $user->can('reports.view_limited')
            || self::seesAllEngagements($user);
    }

    public function view(User $user, Engagement $engagement): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if (self::seesAllEngagements($user)) {
            return true;
        }

        return $engagement->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('engagements.create');
    }

    public function update(User $user, Engagement $engagement): bool
    {
        if (! in_array($engagement->statut, [StatutEngagement::Brouillon, StatutEngagement::Retourne], true)) {
            return false;
        }

        if ($engagement->created_by === $user->id && $user->can('engagements.create')) {
            return true;
        }

        return $user->can('exercises.manage');
    }

    public function delete(User $user, Engagement $engagement): bool
    {
        if ($engagement->statut !== StatutEngagement::Brouillon) {
            return false;
        }

        if ($engagement->created_by === $user->id && $user->can('engagements.create')) {
            return true;
        }

        return $user->can('exercises.manage');
    }

    public function submit(User $user, Engagement $engagement): bool
    {
        if (! $user->can('engagements.submit')) {
            return false;
        }

        if (! in_array($engagement->statut, [StatutEngagement::Brouillon, StatutEngagement::Retourne], true)) {
            return false;
        }

        if ($engagement->created_by === $user->id) {
            return true;
        }

        return $user->can('exercises.manage');
    }

    public function decideWorkflow(User $user, Engagement $engagement): bool
    {
        if ($engagement->statut !== StatutEngagement::EnValidation) {
            return false;
        }

        $service = app(EngagementWorkflowService::class);
        $step = $service->currentPendingStep($engagement);

        return $step !== null && $service->userCanValidateStep($user, $step);
    }

    public function createLiquidation(User $user, Engagement $engagement): bool
    {
        if (! $user->can('execution.liquidation')) {
            return false;
        }

        return self::allowsExecutionRecording($engagement);
    }

    public function cancelApproved(User $user, Engagement $engagement): bool
    {
        if (! $user->can('exercises.manage')) {
            return false;
        }

        if ($engagement->statut !== StatutEngagement::Approuve) {
            return false;
        }

        return ! $engagement->liquidations()->exists();
    }

    /**
     * Engagement dans un état où l’on peut encore saisir liquidation / ordonnancement / paiement.
     */
    public static function allowsExecutionRecording(Engagement $engagement): bool
    {
        return ! in_array($engagement->statut, [
            StatutEngagement::Annule,
            StatutEngagement::Rejete,
            StatutEngagement::Brouillon,
            StatutEngagement::Soumis,
            StatutEngagement::EnValidation,
            StatutEngagement::Retourne,
            StatutEngagement::Paye,
            StatutEngagement::Cloture,
        ], true);
    }

    public function restore(User $user, Engagement $engagement): bool
    {
        return false;
    }

    public function forceDelete(User $user, Engagement $engagement): bool
    {
        return false;
    }
}
