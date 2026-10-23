<?php

namespace Tests\Feature\Engagements;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\User;
use App\Services\EngagementWorkflowService;
use Database\Seeders\ParametresControleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowDelegatedThresholdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ParametresControleSeeder::class);
    }

    public function test_delegated_validation_is_blocked_above_threshold(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        $engagement = Engagement::factory()->create([
            'created_by' => User::factory()->create()->id,
            'statut' => StatutEngagement::Brouillon,
            'montant' => 6_000_000,
        ]);

        $workflow = app(EngagementWorkflowService::class);
        $workflow->submitForValidation($engagement, $user);

        $steps = config('engagement_workflow.steps', []);
        $blocked = false;
        foreach ($steps as $index => $_step) {
            try {
                $workflow->processDecision($engagement->fresh(), $user, DecisionWorkflow::Valide, 'ok');
            } catch (\Illuminate\Validation\ValidationException $e) {
                if ($index === count($steps) - 1) {
                    $blocked = true;
                    break;
                }
                throw $e;
            }
        }

        $this->assertTrue($blocked);
        $this->assertSame(StatutEngagement::EnValidation, $engagement->fresh()->statut);
    }
}
