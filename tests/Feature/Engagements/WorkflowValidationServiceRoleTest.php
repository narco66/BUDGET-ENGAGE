<?php

namespace Tests\Feature\Engagements;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\User;
use App\Services\EngagementWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowValidationServiceRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_responsable_service_can_validate_first_workflow_step(): void
    {
        $initiator = User::factory()->create();
        $initiator->assignRole('agent_initiateur');

        $responsableService = User::factory()->create();
        $responsableService->assignRole('responsable_service');

        $engagement = Engagement::factory()->create([
            'created_by' => $initiator->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $workflow = app(EngagementWorkflowService::class);
        $workflow->submitForValidation($engagement, $initiator);

        $firstStep = $workflow->currentPendingStep($engagement->fresh());
        $this->assertNotNull($firstStep);
        $this->assertSame('validation_service', $firstStep->etape);
        $this->assertTrue($workflow->userCanValidateStep($responsableService, $firstStep));

        $workflow->processDecision($engagement->fresh(), $responsableService, DecisionWorkflow::Valide, 'validation service ok');

        $nextStep = $workflow->currentPendingStep($engagement->fresh());
        $this->assertNotNull($nextStep);
        $this->assertSame('validation_direction', $nextStep->etape);
    }
}
