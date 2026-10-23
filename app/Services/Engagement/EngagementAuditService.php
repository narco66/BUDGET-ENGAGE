<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Journal d'audit dédié au module Engagement (log `engagement`), en complément
 * du journal budgétaire global (BudgetJournal). Alimente la timeline de la fiche.
 */
class EngagementAuditService
{
    public const LOG_NAME = 'engagement';

    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(Engagement $engagement, ?User $user, string $action, ?string $description = null, array $properties = []): void
    {
        $logger = activity(self::LOG_NAME)
            ->performedOn($engagement)
            ->withProperties(array_merge(['action' => $action, 'numero' => $engagement->numero], $properties));

        if ($user !== null) {
            $logger->causedBy($user);
        }

        $logger->log($description ?? $action);
    }

    /**
     * @return Collection<int, Activity>
     */
    public function timeline(Engagement $engagement): Collection
    {
        return Activity::query()
            ->where('log_name', self::LOG_NAME)
            ->where('subject_type', $engagement->getMorphClass())
            ->where('subject_id', $engagement->id)
            ->with('causer')
            ->latest()
            ->get();
    }
}
