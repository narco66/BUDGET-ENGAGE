<?php

namespace Tests\Feature\Engagements;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Jobs\ComputeEngagementRiskScore;
use App\Models\Engagement;
use App\Models\User;
use App\Services\EngagementWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RiskScoringWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_approval_dispatches_risk_scoring_job(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
            'montant' => 15_000_000,
            'is_urgence' => true,
            'type_engagement' => 'salaire',
        ]);

        $service = app(EngagementWorkflowService::class);
        $service->submitForValidation($engagement, $user);

        foreach (config('engagement_workflow.steps', []) as $_step) {
            $service->processDecision($engagement, $user, DecisionWorkflow::Valide, 'ok');
        }

        $this->assertSame(StatutEngagement::Approuve, $engagement->fresh()->statut);

        Bus::assertDispatched(ComputeEngagementRiskScore::class, function (ComputeEngagementRiskScore $job) use ($engagement): bool {
            return $job->engagementId === $engagement->id;
        });
    }
}
