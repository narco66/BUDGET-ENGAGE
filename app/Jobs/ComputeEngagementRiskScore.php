<?php

namespace App\Jobs;

use App\Models\Engagement;
use App\Services\RiskScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeEngagementRiskScore implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $engagementId
    ) {}

    public function handle(RiskScoringService $riskScoringService): void
    {
        $engagement = Engagement::query()
            ->with('piecesJustificatives')
            ->find($this->engagementId);

        if ($engagement === null) {
            return;
        }

        $engagement->update([
            'score_risque' => $riskScoringService->compute($engagement),
        ]);
    }
}
