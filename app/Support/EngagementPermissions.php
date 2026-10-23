<?php

namespace App\Support;

use App\Models\User;
use App\Policies\EngagementPolicy;

/**
 * Pont unifié entre les permissions historiques (engagements.*)
 * et les permissions granulaires ERP (engagement.*).
 */
final class EngagementPermissions
{
    public static function canViewAny(User $user): bool
    {
        return self::canCreate($user)
            || self::canSubmit($user)
            || $user->can('engagement.view')
            || $user->can('reports.view_limited')
            || EngagementPolicy::seesAllEngagements($user);
    }

    public static function canCreate(User $user): bool
    {
        return $user->can('engagements.create') || $user->can('engagement.create');
    }

    public static function canSubmit(User $user): bool
    {
        return $user->can('engagements.submit') || $user->can('engagement.generate');
    }

    public static function canUpdate(User $user): bool
    {
        return self::canCreate($user) || $user->can('engagement.update');
    }

    public static function canExport(User $user): bool
    {
        return $user->can('engagement.export')
            || $user->can('reports.view')
            || $user->can('reports.view_limited');
    }

    public static function canTransferLiquidation(User $user): bool
    {
        return $user->can('engagement.transfer_liquidation')
            || $user->can('execution.liquidation');
    }

    public static function canCancelApproved(User $user): bool
    {
        return $user->can('engagement.cancel') || $user->can('exercises.manage');
    }
}
