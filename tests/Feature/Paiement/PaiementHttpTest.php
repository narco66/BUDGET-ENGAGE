<?php

namespace Tests\Feature\Paiement;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Enums\StatutLiquidation;
use App\Enums\StatutOrdonnancement;
use App\Enums\StatutPaiement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\PaiementBanque;
use App\Models\User;
use App\Services\Liquidation\LiquidationService;
use App\Services\Liquidation\LiquidationServiceFaitService;
use App\Services\Liquidation\LiquidationWorkflowService;
use App\Services\Ordonnancement\OrdonnancementService;
use App\Services\Ordonnancement\OrdonnancementWorkflowService;
use App\Services\Paiement\PaiementService;
use App\Services\Paiement\PaiementWorkflowService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\OrdonnancementTypeSeeder;
use Database\Seeders\PaiementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaiementHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Ordonnancement $ordonnancement;

    private PaiementBanque $banque;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);
        $this->seed(OrdonnancementTypeSeeder::class);
        $this->seed(PaiementTypeSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrateur_general');

        $this->banque = PaiementBanque::query()->create([
            'code' => 'TEST-XAF', 'libelle' => 'Compte test', 'iban' => 'CM2110002000000123456789012',
            'devise' => 'XAF', 'plafond_decaissement' => 1_000_000_000, 'est_actif' => true,
        ]);

        $this->ordonnancement = $this->ordonnancementMandate();
    }

    private function ordonnancementMandate(): Ordonnancement
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

        $liq = app(LiquidationService::class)->genererDepuisEngagement($engagement, $this->admin);
        $sfS = app(LiquidationServiceFaitService::class);
        $sf = $sfS->enregistrer($liq, [
            'date_constatation' => now()->toDateString(),
            'conformite_quantitative' => true, 'conformite_qualitative' => true,
            'conformite_technique' => true, 'conformite_contractuelle' => true,
        ], $this->admin);
        $sfS->valider($sf, $this->admin);
        $lwf = app(LiquidationWorkflowService::class);
        $lwf->soumettre($liq->fresh(), $this->admin);
        $this->loop(fn () => $liq->fresh()->statut === StatutLiquidation::Valide, fn () => $lwf->processDecision($liq->fresh(), $this->admin, DecisionWorkflow::Valide, 'ok'));
        app(LiquidationService::class)->certifier($liq->fresh(), $this->admin);

        $os = app(OrdonnancementService::class);
        $ord = $os->genererDepuisLiquidation($liq->fresh(), $this->admin);
        $owf = app(OrdonnancementWorkflowService::class);
        $owf->soumettre($ord->fresh(), $this->admin);
        $this->loop(fn () => $ord->fresh()->statut === StatutOrdonnancement::Valide, fn () => $owf->processDecision($ord->fresh(), $this->admin, DecisionWorkflow::Valide, 'ok'));
        $os->genererMandat($ord->fresh(), $this->admin);

        return $ord->fresh(['lignes']);
    }

    private function loop(callable $done, callable $step): void
    {
        for ($i = 0; $i < 8; $i++) {
            if ($done()) {
                return;
            }
            $step();
        }
    }

    private function paiement(): Paiement
    {
        return app(PaiementService::class)->genererDepuisOrdonnancement($this->ordonnancement, $this->admin, ['paiement_banque_id' => $this->banque->id]);
    }

    private function paiementExecute(): Paiement
    {
        $p = $this->paiement();
        $wf = app(PaiementWorkflowService::class);
        $wf->programmer($p->fresh(), $this->admin);
        $this->loop(fn () => $p->fresh()->statut === StatutPaiement::Valide, fn () => $wf->processDecision($p->fresh(), $this->admin, DecisionWorkflow::Valide, 'ok'));
        app(PaiementService::class)->transmettreBanque($p->fresh(), $this->admin);
        app(PaiementService::class)->executer($p->fresh(), $this->admin);

        return $p->fresh();
    }

    public function test_pages_principales_accessibles(): void
    {
        $this->actingAs($this->admin)->get(route('paiements.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.reports.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.banques.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.show', $this->paiement()))->assertOk();
    }

    public function test_generation_via_http(): void
    {
        $this->actingAs($this->admin)
            ->post(route('ordonnancements.generer-paiement', $this->ordonnancement))
            ->assertRedirect();

        $this->assertDatabaseCount('paiements', 1);
    }

    public function test_store_cree_un_paiement(): void
    {
        $lb = $this->ordonnancement->lignes->first()->ligne_budgetaire_id;

        $response = $this->actingAs($this->admin)->post(route('paiements.store'), [
            'ordonnancement_id' => $this->ordonnancement->id,
            'mode_creation' => 'automatique',
            'paiement_banque_id' => $this->banque->id,
            'lignes' => [['designation' => 'Test', 'ligne_budgetaire_id' => $lb, 'montant_ordonnance' => 500_000, 'montant_paye' => 150_000]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('paiements', ['ordonnancement_id' => $this->ordonnancement->id, 'reste_a_payer' => 350_000]);
    }

    public function test_permissions_refusees_sans_droit(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent_initiateur');

        $this->actingAs($agent)->get(route('paiements.create'))->assertForbidden();
        $this->actingAs($agent)->post(route('paiements.store'), [
            'ordonnancement_id' => $this->ordonnancement->id,
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $this->ordonnancement->lignes->first()->ligne_budgetaire_id, 'montant_paye' => 1000]],
        ])->assertForbidden();
    }

    public function test_documents_pdf_telechargeables(): void
    {
        $p = $this->paiement();

        foreach (['paiements.ordre-virement', 'paiements.bordereau', 'paiements.avis'] as $route) {
            $response = $this->actingAs($this->admin)->get(route($route, $p));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_recu_et_quittance_apres_execution(): void
    {
        $p = $this->paiementExecute();

        foreach (['paiements.recu', 'paiements.quittance'] as $route) {
            $this->actingAs($this->admin)->get(route($route, $p))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_etats_pdf_xlsx_csv(): void
    {
        $this->paiement();

        $this->actingAs($this->admin)->get(route('paiements.reports.show', ['rapport' => 'registre', 'format' => 'pdf']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($this->admin)->get(route('paiements.reports.show', ['rapport' => 'par-banque', 'format' => 'csv']))->assertOk();
        $this->actingAs($this->admin)->get(route('paiements.reports.show', ['rapport' => 'par-statut', 'format' => 'xlsx']))->assertOk();
    }
}
