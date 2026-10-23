<?php

namespace Tests\Feature\Execution;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Services\EngagementExecutionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiImputationExecutionTraceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_liquidation_updates_only_targeted_budget_line_from_imputation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        $exercice = ExerciceBudgetaire::factory()->create();
        $ligneA = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create();
        $ligneB = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create();

        $engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'ligne_budgetaire_id' => $ligneA->id,
            'montant' => 300000,
            'statut' => StatutEngagement::Approuve,
        ]);

        $impA = $engagement->imputations()->create([
            'ligne_budgetaire_id' => $ligneA->id,
            'montant_impute' => 100000,
        ]);
        $impB = $engagement->imputations()->create([
            'ligne_budgetaire_id' => $ligneB->id,
            'montant_impute' => 200000,
        ]);

        $service = app(EngagementExecutionService::class);
        $liquidation = $service->recordLiquidation($engagement, [
            'engagement_imputation_id' => $impB->id,
            'montant_liquide' => 150000,
            'date_liquidation' => now()->toDateString(),
            'reference' => 'LIQ-001',
        ], $user);

        $this->assertSame($impB->id, (int) $liquidation->engagement_imputation_id);
        $this->assertSame('0.00', number_format((float) $ligneA->fresh()->montant_liquide, 2, '.', ''));
        $this->assertSame('150000.00', number_format((float) $ligneB->fresh()->montant_liquide, 2, '.', ''));
        $this->assertNotNull($impA->fresh());
    }
}
