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

class ExecutionChainTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Engagement $engagement;

    private LigneBudgetaire $ligne;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('administrateur_general');

        $exercice = ExerciceBudgetaire::factory()->create();
        $this->ligne = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create([
            'montant_vote' => 1_000_000,
            'montant_engage' => 500_000,
        ]);

        $this->engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'ligne_budgetaire_id' => $this->ligne->id,
            'montant' => 500_000,
            'statut' => StatutEngagement::Approuve,
        ]);

        $this->engagement->imputations()->create([
            'ligne_budgetaire_id' => $this->ligne->id,
            'montant_impute' => 500_000,
        ]);
    }

    public function test_chaine_complete_liquidation_ordonnancement_paiement(): void
    {
        $service = app(EngagementExecutionService::class);

        $liquidation = $service->recordLiquidation($this->engagement, [
            'montant_liquide' => 500_000,
            'date_liquidation' => now()->toDateString(),
            'reference' => 'LIQ-E2E',
        ], $this->user);

        $this->assertSame(StatutEngagement::Liquide, $this->engagement->fresh()->statut);
        $this->assertSame('500000.00', number_format((float) $this->ligne->fresh()->montant_liquide, 2, '.', ''));

        $ordonnancement = $service->recordOrdonnancement($liquidation, [
            'montant_ordonnance' => 500_000,
            'date_ordonnancement' => now()->toDateString(),
        ], $this->user);

        $this->assertSame(StatutEngagement::Ordonnance, $this->engagement->fresh()->statut);

        $paiement = $service->recordPaiement($ordonnancement, [
            'montant_paye' => 500_000,
            'date_paiement' => now()->toDateString(),
            'mode_paiement' => 'virement',
        ], $this->user);

        $this->assertSame(StatutEngagement::Paye, $this->engagement->fresh()->statut);
        $this->assertSame('500000.00', number_format((float) $this->ligne->fresh()->montant_paye, 2, '.', ''));
        $this->assertNotNull($paiement->id);
    }

    public function test_liquidation_partielle_ne_passe_pas_en_statut_liquide(): void
    {
        $service = app(EngagementExecutionService::class);

        $service->recordLiquidation($this->engagement, [
            'montant_liquide' => 200_000,
            'date_liquidation' => now()->toDateString(),
        ], $this->user);

        $this->assertSame(StatutEngagement::Approuve, $this->engagement->fresh()->statut);
        $this->assertSame('200000.00', number_format((float) $this->ligne->fresh()->montant_liquide, 2, '.', ''));
    }

    public function test_rollback_paiement_redescend_le_statut(): void
    {
        $service = app(EngagementExecutionService::class);

        $liq = $service->recordLiquidation($this->engagement, [
            'montant_liquide' => 500_000,
            'date_liquidation' => now()->toDateString(),
        ], $this->user);

        $ord = $service->recordOrdonnancement($liq, [
            'montant_ordonnance' => 500_000,
            'date_ordonnancement' => now()->toDateString(),
        ], $this->user);

        $pay = $service->recordPaiement($ord, [
            'montant_paye' => 500_000,
            'date_paiement' => now()->toDateString(),
        ], $this->user);

        $this->assertSame(StatutEngagement::Paye, $this->engagement->fresh()->statut);

        $service->cancelPaiement($pay, $this->user, 'Erreur de saisie');

        $this->assertSame(StatutEngagement::Ordonnance, $this->engagement->fresh()->statut);
        $this->assertSame('0.00', number_format((float) $this->ligne->fresh()->montant_paye, 2, '.', ''));
        $this->assertSoftDeleted($pay);
    }

    public function test_module_execution_accessible(): void
    {
        $service = app(EngagementExecutionService::class);
        $liq = $service->recordLiquidation($this->engagement, [
            'montant_liquide' => 100_000,
            'date_liquidation' => now()->toDateString(),
        ], $this->user);

        $this->actingAs($this->user)
            ->get(route('execution.dashboard'))
            ->assertOk();

        $this->actingAs($this->user)
            ->get(route('execution.liquidations.show', $liq))
            ->assertOk();
    }

    public function test_annulation_liquidation_impossible_si_ordonnancements(): void
    {
        $service = app(EngagementExecutionService::class);
        $liq = $service->recordLiquidation($this->engagement, [
            'montant_liquide' => 500_000,
            'date_liquidation' => now()->toDateString(),
        ], $this->user);

        $service->recordOrdonnancement($liq, [
            'montant_ordonnance' => 100_000,
            'date_ordonnancement' => now()->toDateString(),
        ], $this->user);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->cancelLiquidation($liq->fresh(), $this->user);
    }
}
