<?php

namespace Tests\Feature\Liquidation;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\User;
use App\Services\Liquidation\LiquidationService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidationHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Engagement $engagement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrateur_general');

        $exercice = ExerciceBudgetaire::factory()->create();
        $ligne = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create(['montant_vote' => 10_000_000]);
        $this->engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'ligne_budgetaire_id' => $ligne->id,
            'montant' => 500_000,
            'statut' => StatutEngagement::Approuve,
        ]);
        $this->engagement->imputations()->create([
            'ligne_budgetaire_id' => $ligne->id,
            'montant_impute' => 500_000,
        ]);
    }

    private function liquidation(): Liquidation
    {
        return app(LiquidationService::class)->genererDepuisEngagement($this->engagement, $this->admin);
    }

    public function test_pages_principales_accessibles(): void
    {
        $this->actingAs($this->admin)->get(route('liquidations.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('liquidations.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('liquidations.dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('liquidations.reports.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('liquidations.show', $this->liquidation()))->assertOk();
    }

    public function test_generation_via_http(): void
    {
        $this->actingAs($this->admin)
            ->post(route('engagements.generer-liquidation', $this->engagement))
            ->assertRedirect();

        $this->assertDatabaseCount('liquidations', 1);
    }

    public function test_store_cree_une_liquidation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('liquidations.store'), [
            'engagement_id' => $this->engagement->id,
            'mode_creation' => 'automatique',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [[
                'designation' => 'Prestation test',
                'ligne_budgetaire_id' => $this->engagement->ligne_budgetaire_id,
                'montant_brut' => 150_000,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('liquidations', [
            'engagement_id' => $this->engagement->id,
            'montant_net' => 150_000,
        ]);
    }

    public function test_permissions_refusees_sans_droit(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent_initiateur'); // ni liquidation.create ni view

        $this->actingAs($agent)->get(route('liquidations.create'))->assertForbidden();
        $this->actingAs($agent)->post(route('liquidations.store'), [
            'engagement_id' => $this->engagement->id,
            'date_liquidation' => now()->toDateString(),
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $this->engagement->ligne_budgetaire_id, 'montant_brut' => 1000]],
        ])->assertForbidden();
    }

    public function test_documents_pdf_telechargeables(): void
    {
        $liq = $this->liquidation();

        foreach (['liquidations.fiche', 'liquidations.bordereau', 'liquidations.certificat'] as $route) {
            $response = $this->actingAs($this->admin)->get(route($route, $liq));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_etats_pdf_xlsx_csv(): void
    {
        $this->liquidation();

        $this->actingAs($this->admin)->get(route('liquidations.reports.show', ['rapport' => 'registre', 'format' => 'pdf']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->admin)->get(route('liquidations.reports.show', ['rapport' => 'par-type', 'format' => 'csv']))
            ->assertOk();

        $xlsx = $this->actingAs($this->admin)->get(route('liquidations.reports.show', ['rapport' => 'par-statut', 'format' => 'xlsx']));
        $xlsx->assertOk();
    }
}
