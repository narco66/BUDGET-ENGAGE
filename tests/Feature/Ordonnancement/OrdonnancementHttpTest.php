<?php

namespace Tests\Feature\Ordonnancement;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Enums\StatutLiquidation;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\User;
use App\Services\Liquidation\LiquidationService;
use App\Services\Liquidation\LiquidationServiceFaitService;
use App\Services\Liquidation\LiquidationWorkflowService;
use App\Enums\StatutOrdonnancement;
use App\Services\Ordonnancement\OrdonnancementService;
use App\Services\Ordonnancement\OrdonnancementWorkflowService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\OrdonnancementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdonnancementHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Liquidation $liquidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);
        $this->seed(OrdonnancementTypeSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrateur_general');

        $this->liquidation = $this->liquidationCertifiee();
    }

    private function liquidationCertifiee(): Liquidation
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        $ligne = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create(['montant_vote' => 10_000_000]);
        $engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'ligne_budgetaire_id' => $ligne->id,
            'montant' => 500_000,
            'statut' => StatutEngagement::Approuve,
        ]);
        $engagement->imputations()->create(['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => 500_000]);

        $liquidation = app(LiquidationService::class)->genererDepuisEngagement($engagement, $this->admin);
        $sfService = app(LiquidationServiceFaitService::class);
        $sf = $sfService->enregistrer($liquidation, [
            'date_constatation' => now()->toDateString(),
            'conformite_quantitative' => true, 'conformite_qualitative' => true,
            'conformite_technique' => true, 'conformite_contractuelle' => true,
        ], $this->admin);
        $sfService->valider($sf, $this->admin);
        $lwf = app(LiquidationWorkflowService::class);
        $lwf->soumettre($liquidation->fresh(), $this->admin);
        for ($i = 0; $i < 6; $i++) {
            $l = $liquidation->fresh();
            if ($l->statut === StatutLiquidation::Valide) {
                break;
            }
            $lwf->processDecision($l, $this->admin, DecisionWorkflow::Valide, 'ok');
        }
        app(LiquidationService::class)->certifier($liquidation->fresh(), $this->admin);

        return $liquidation->fresh(['lignes']);
    }

    private function ordonnancement(): Ordonnancement
    {
        return app(OrdonnancementService::class)->genererDepuisLiquidation($this->liquidation, $this->admin);
    }

    public function test_pages_principales_accessibles(): void
    {
        $this->actingAs($this->admin)->get(route('ordonnancements.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('ordonnancements.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('ordonnancements.dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('ordonnancements.reports.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('ordonnancements.show', $this->ordonnancement()))->assertOk();
    }

    public function test_generation_via_http(): void
    {
        $this->actingAs($this->admin)
            ->post(route('liquidations.generer-ordonnancement', $this->liquidation))
            ->assertRedirect();

        $this->assertDatabaseCount('ordonnancements', 1);
    }

    public function test_store_cree_un_ordonnancement(): void
    {
        $lb = $this->liquidation->lignes->first()->ligne_budgetaire_id;

        $response = $this->actingAs($this->admin)->post(route('ordonnancements.store'), [
            'liquidation_id' => $this->liquidation->id,
            'mode_creation' => 'automatique',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'Test', 'ligne_budgetaire_id' => $lb, 'montant_liquide' => 500_000, 'montant_ordonnance' => 150_000]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ordonnancements', ['liquidation_id' => $this->liquidation->id, 'reste_a_ordonnancer' => 350_000]);
    }

    public function test_permissions_refusees_sans_droit(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent_initiateur');

        $this->actingAs($agent)->get(route('ordonnancements.create'))->assertForbidden();
        $this->actingAs($agent)->post(route('ordonnancements.store'), [
            'liquidation_id' => $this->liquidation->id,
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $this->liquidation->lignes->first()->ligne_budgetaire_id, 'montant_ordonnance' => 1000]],
        ])->assertForbidden();
    }

    public function test_documents_pdf_telechargeables(): void
    {
        $ord = $this->ordonnancement();

        foreach (['ordonnancements.fiche', 'ordonnancements.bordereau'] as $route) {
            $response = $this->actingAs($this->admin)->get(route($route, $ord));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_mandat_et_ordre_paiement_pdf_avec_qr(): void
    {
        $ord = $this->ordonnancement();

        // Parcours du circuit puis génération du mandat.
        $wf = app(OrdonnancementWorkflowService::class);
        $wf->soumettre($ord->fresh(), $this->admin);
        for ($i = 0; $i < 6; $i++) {
            $o = $ord->fresh();
            if ($o->statut === StatutOrdonnancement::Valide) {
                break;
            }
            $wf->processDecision($o, $this->admin, DecisionWorkflow::Valide, 'ok');
        }
        app(OrdonnancementService::class)->genererMandat($ord->fresh(), $this->admin);

        foreach (['ordonnancements.mandat', 'ordonnancements.ordre-paiement'] as $route) {
            $response = $this->actingAs($this->admin)->get(route($route, $ord));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_etats_pdf_xlsx_csv(): void
    {
        $this->ordonnancement();

        $this->actingAs($this->admin)->get(route('ordonnancements.reports.show', ['rapport' => 'registre', 'format' => 'pdf']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($this->admin)->get(route('ordonnancements.reports.show', ['rapport' => 'par-statut', 'format' => 'csv']))->assertOk();
        $this->actingAs($this->admin)->get(route('ordonnancements.reports.show', ['rapport' => 'par-type', 'format' => 'xlsx']))->assertOk();
    }
}
