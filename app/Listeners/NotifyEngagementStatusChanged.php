<?php

namespace App\Listeners;

use App\Events\EngagementStatusChanged;
use App\Models\Engagement;
use App\Models\User;
use App\Notifications\EngagementStatusChangedNotification;

class NotifyEngagementStatusChanged
{
    public function handle(EngagementStatusChanged $event): void
    {
        $engagement = Engagement::query()->find($event->engagementId);
        if ($engagement === null) {
            return;
        }

        $notifiables = User::query()
            ->where(function ($query): void {
                $query->permission('audit.view')
                    ->orWhere->permission('workflow.budget_control')
                    ->orWhere->permission('workflow.financial_control')
                    ->orWhere->permission('exercises.manage');
            })
            ->get();

        foreach ($notifiables as $user) {
            $user->notify(new EngagementStatusChangedNotification($engagement, $event->from, $event->to));
        }
    }
}
