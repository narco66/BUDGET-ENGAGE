<?php

namespace App\Listeners;

use App\Events\EngagementApproved;
use App\Jobs\ComputeEngagementRiskScore;

class DispatchRiskScoring
{
    public function handle(EngagementApproved $event): void
    {
        ComputeEngagementRiskScore::dispatch($event->engagementId);
    }
}
