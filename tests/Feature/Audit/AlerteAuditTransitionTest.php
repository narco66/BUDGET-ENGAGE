<?php

namespace Tests\Feature\Audit;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\User;
use App\Services\EngagementCancellationService;
use App\Services\EngagementWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlerteAuditTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_rejected_transition_creates_audit_alert(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $workflow = app(EngagementWorkflowService::class);
        $workflow->submitForValidation($engagement, $user);
        $workflow->processDecision($engagement, $user, DecisionWorkflow::Rejete, 'dossier incomplet');

        $this->assertDatabaseHas('alertes_audit', [
            'engagement_id' => $engagement->id,
            'code_alerte' => 'TRANSITION_STATUT_SENSIBLE',
            'type_alerte' => 'transition_statut',
            'niveau_risque' => 'eleve',
            'score_risque' => 75,
            'statut_traitement' => 'nouvelle',
        ]);
    }

    public function test_cancel_approved_transition_creates_critical_audit_alert(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
            'montant' => 1_500_000,
        ]);

        $workflow = app(EngagementWorkflowService::class);
        $workflow->submitForValidation($engagement, $user);

        foreach (config('engagement_workflow.steps', []) as $_step) {
            $workflow->processDecision($engagement, $user, DecisionWorkflow::Valide, 'ok');
        }

        app(EngagementCancellationService::class)->cancelApproved(
            $engagement->fresh(),
            'Annulation après revue',
            $user
        );

        $this->assertDatabaseHas('alertes_audit', [
            'engagement_id' => $engagement->id,
            'code_alerte' => 'TRANSITION_STATUT_SENSIBLE',
            'type_alerte' => 'transition_statut',
            'niveau_risque' => 'critique',
            'score_risque' => 90,
            'statut_traitement' => 'nouvelle',
        ]);
    }
}
