<?php

namespace Tests\Feature\Liquidation;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Enums\StatutLiquidation;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\User;
use App\Services\Liquidation\LiquidationService;
use App\Services\Liquidation\LiquidationServiceFaitService;
use App\Services\Liquidation\LiquidationToOrdonnancementService;
use App\Services\Liquidation\LiquidationWorkflowService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LiquidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ExerciceBudgetaire $exercice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('administrateur_general');

        $this->exercice = ExerciceBudgetaire::factory()->create();
    }

    /**
     * @param  array<int, array{montant: int}>  $imputations
     */
    private function makeEngagement(array $imputations): Engagement
    {
        $lignes = [];
        $total = 0;
        foreach ($imputations as $imp) {
            $lignes[] = LigneBudgetaire::factory()->for($this->exercice, 'exerciceBudgetaire')->create([
                'montant_vote' => 100_000_000,
            ]);
            $total += $imp['montant'];
        }

        $engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $this->exercice->id,
            'ligne_budgetaire_id' => $lignes[0]->id,
            'montant' => $total,
            'statut' => StatutEngagement::Approuve,
        ]);

        foreach ($imputations as $i => $imp) {
            $engagement->imputations()->create([
                'ligne_budgetaire_id' => $lignes[$i]->id,
                'montant_impute' => $imp['montant'],
            ]);
        }

        return $engagement;
    }

    private function service(): LiquidationService
    {
        return app(LiquidationService::class);
    }

    private function validerServiceFait(Liquidation $liquidation): void
    {
        $sfService = app(LiquidationServiceFaitService::class);
        $sf = $sfService->enregistrer($liquidation, [
            'date_constatation' => now()->toDateString(),
            'conformite_quantitative' => true,
            'conformite_qualitative' => true,
            'conformite_technique' => true,
            'conformite_contractuelle' => true,
        ], $this->user);
        $sfService->valider($sf, $this->user);
    }

    private function parcourirCircuit(Liquidation $liquidation): void
    {
        $wf = app(LiquidationWorkflowService::class);
        $wf->soumettre($liquidation->fresh(), $this->user);

        for ($i = 0; $i < 6; $i++) {
            $fresh = $liquidation->fresh();
            if ($fresh->statut === StatutLiquidation::Valide) {
                break;
            }
            $wf->processDecision($fresh, $this->user, DecisionWorkflow::Valide, 'OK');
        }
    }

    public function test_generation_automatique_depuis_engagement(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);

        $this->assertSame(StatutLiquidation::Brouillon, $liquidation->statut);
        $this->assertSame(1, $liquidation->lignes()->count());
        $this->assertSame('500000.00', number_format((float) $liquidation->montant_net, 2, '.', ''));
        $this->assertStringStartsWith('LIQ-', $liquidation->numero);
    }

    public function test_creation_manuelle_exige_justification(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        $this->expectException(ValidationException::class);
        $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'manuel',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id, 'montant_brut' => 1000]],
        ], $this->user);
    }

    public function test_moteur_de_calcul_montant_net(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        $liquidation = $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'automatique',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [[
                'designation' => 'Prestation',
                'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id,
                'montant_brut' => 1000,
                'montant_retenues' => 100,
                'montant_penalites' => 50,
                'montant_taxes' => 30,
                'montant_revision_prix' => 20,
            ]],
        ], $this->user);

        // 1000 - 100 - 50 + 20 + 30 = 900
        $this->assertSame('900.00', number_format((float) $liquidation->montant_net, 2, '.', ''));
        $this->assertSame(1, $liquidation->calculs()->count());
    }

    public function test_calcul_brut_depuis_quantite_et_prix(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        $liquidation = $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'automatique',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [[
                'designation' => 'Fournitures',
                'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id,
                'quantite_realisee' => 10,
                'prix_unitaire' => 2500,
            ]],
        ], $this->user);

        $this->assertSame('25000.00', number_format((float) $liquidation->montant_net, 2, '.', ''));
    }

    public function test_soumission_impossible_sans_service_fait(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);
        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);

        $this->expectException(ValidationException::class);
        app(LiquidationWorkflowService::class)->soumettre($liquidation, $this->user);
    }

    public function test_cycle_complet_certification_impacte_le_ledger(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);
        $ligne = $engagement->ligneBudgetaire;

        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);
        $this->validerServiceFait($liquidation);
        $this->parcourirCircuit($liquidation);

        $this->assertSame(StatutLiquidation::Valide, $liquidation->fresh()->statut);

        $this->service()->certifier($liquidation->fresh(), $this->user);

        $fresh = $liquidation->fresh();
        $this->assertSame(StatutLiquidation::Certifie, $fresh->statut);
        $this->assertNotNull($fresh->certificat_numero);
        $this->assertNotNull($fresh->certificat_hash);
        $this->assertSame('500000.00', number_format((float) $fresh->montant_liquide, 2, '.', ''));
        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_liquide, 2, '.', ''));
        $this->assertSame(StatutEngagement::Liquide, $engagement->fresh()->statut);
    }

    public function test_liquidation_partielle_ne_passe_pas_engagement_en_liquide(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        $liquidation = $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'automatique',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [['designation' => 'Partiel', 'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id, 'montant_brut' => 200_000]],
        ], $this->user);

        $this->validerServiceFait($liquidation);
        $this->parcourirCircuit($liquidation);
        $this->service()->certifier($liquidation->fresh(), $this->user);

        $this->assertSame(StatutEngagement::Approuve, $engagement->fresh()->statut);
    }

    public function test_liquidations_successives_respectent_le_reste(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);

        // 1ère liquidation 300k certifiée
        $liq1 = $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'automatique',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [['designation' => 'P1', 'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id, 'montant_brut' => 300_000]],
        ], $this->user);
        $this->validerServiceFait($liq1);
        $this->parcourirCircuit($liq1);
        $this->service()->certifier($liq1->fresh(), $this->user);

        // 2ème liquidation 300k → dépasse le reste (200k) à la certification
        $liq2 = $this->service()->creer([
            'engagement_id' => $engagement->id,
            'mode_creation' => 'complementaire',
            'date_liquidation' => now()->toDateString(),
            'lignes' => [['designation' => 'P2', 'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id, 'montant_brut' => 300_000]],
        ], $this->user);
        $this->validerServiceFait($liq2);

        $this->expectException(ValidationException::class);
        app(\App\Services\Liquidation\LiquidationValidationService::class)->assertMontantDansEngagement($liq2->fresh());
    }

    public function test_liquidation_multi_lignes_impacte_chaque_ligne_budgetaire(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000], ['montant' => 300_000]]);
        [$imp1, $imp2] = $engagement->imputations->all();

        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);
        $this->assertSame(2, $liquidation->lignes()->count());

        $this->validerServiceFait($liquidation);
        $this->parcourirCircuit($liquidation);
        $this->service()->certifier($liquidation->fresh(), $this->user);

        $this->assertSame('500000.00', number_format((float) $imp1->ligneBudgetaire->fresh()->montant_liquide, 2, '.', ''));
        $this->assertSame('300000.00', number_format((float) $imp2->ligneBudgetaire->fresh()->montant_liquide, 2, '.', ''));
        $this->assertSame(StatutEngagement::Liquide, $engagement->fresh()->statut);
    }

    public function test_transmission_ordonnancement_et_anti_double(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);
        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);
        $this->validerServiceFait($liquidation);
        $this->parcourirCircuit($liquidation);
        $this->service()->certifier($liquidation->fresh(), $this->user);

        $transmission = app(LiquidationToOrdonnancementService::class);
        $ordonnancement = $transmission->transmettre($liquidation->fresh(), $this->user);

        $this->assertNotNull($ordonnancement->id);
        $this->assertSame(StatutLiquidation::TransmisOrdonnancement, $liquidation->fresh()->statut);
        $this->assertSame(StatutEngagement::Ordonnance, $engagement->fresh()->statut);

        $this->expectException(ValidationException::class);
        $transmission->transmettre($liquidation->fresh(), $this->user);
    }

    public function test_annulation_reprend_le_ledger(): void
    {
        $engagement = $this->makeEngagement([['montant' => 500_000]]);
        $ligne = $engagement->ligneBudgetaire;
        $liquidation = $this->service()->genererDepuisEngagement($engagement, $this->user);
        $this->validerServiceFait($liquidation);
        $this->parcourirCircuit($liquidation);
        $this->service()->certifier($liquidation->fresh(), $this->user);

        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_liquide, 2, '.', ''));

        $this->service()->annuler($liquidation->fresh(), $this->user, 'Erreur');

        $this->assertSame('0.00', number_format((float) $ligne->fresh()->montant_liquide, 2, '.', ''));
        $this->assertSoftDeleted('liquidations', ['id' => $liquidation->id]);
        $this->assertSame(StatutEngagement::Approuve, $engagement->fresh()->statut);
    }
}
