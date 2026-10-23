<?php

namespace Tests\Feature\Ordonnancement;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Enums\StatutLiquidation;
use App\Enums\StatutOrdonnancement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\User;
use App\Services\Liquidation\LiquidationService;
use App\Services\Liquidation\LiquidationServiceFaitService;
use App\Services\Liquidation\LiquidationWorkflowService;
use App\Services\Ordonnancement\OrdonnancementCalculationService;
use App\Services\Ordonnancement\OrdonnancementService;
use App\Services\Ordonnancement\OrdonnancementToPaiementService;
use App\Services\Ordonnancement\OrdonnancementValidationService;
use App\Services\Ordonnancement\OrdonnancementWorkflowService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\OrdonnancementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrdonnancementServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ExerciceBudgetaire $exercice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);
        $this->seed(OrdonnancementTypeSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('administrateur_general');

        $this->exercice = ExerciceBudgetaire::factory()->create();
    }

    /**
     * Construit une liquidation CERTIFIÉE (engagement approuvé → liquidation →
     * service fait → circuit → certification) prête à être ordonnancée.
     *
     * @param  array<int, array{montant: int}>  $imputations
     */
    private function liquidationCertifiee(array $imputations): Liquidation
    {
        $lignes = [];
        $total = 0;
        foreach ($imputations as $imp) {
            $lignes[] = LigneBudgetaire::factory()->for($this->exercice, 'exerciceBudgetaire')->create(['montant_vote' => 100_000_000]);
            $total += $imp['montant'];
        }

        $engagement = Engagement::factory()->create([
            'exercice_budgetaire_id' => $this->exercice->id,
            'ligne_budgetaire_id' => $lignes[0]->id,
            'montant' => $total,
            'statut' => StatutEngagement::Approuve,
        ]);
        foreach ($imputations as $i => $imp) {
            $engagement->imputations()->create(['ligne_budgetaire_id' => $lignes[$i]->id, 'montant_impute' => $imp['montant']]);
        }

        $liquidation = app(LiquidationService::class)->genererDepuisEngagement($engagement, $this->user);

        $sfService = app(LiquidationServiceFaitService::class);
        $sf = $sfService->enregistrer($liquidation, [
            'date_constatation' => now()->toDateString(),
            'conformite_quantitative' => true, 'conformite_qualitative' => true,
            'conformite_technique' => true, 'conformite_contractuelle' => true,
        ], $this->user);
        $sfService->valider($sf, $this->user);

        $lwf = app(LiquidationWorkflowService::class);
        $lwf->soumettre($liquidation->fresh(), $this->user);
        for ($i = 0; $i < 6; $i++) {
            $l = $liquidation->fresh();
            if ($l->statut === StatutLiquidation::Valide) {
                break;
            }
            $lwf->processDecision($l, $this->user, DecisionWorkflow::Valide, 'ok');
        }
        app(LiquidationService::class)->certifier($liquidation->fresh(), $this->user);

        return $liquidation->fresh(['lignes', 'engagement']);
    }

    private function service(): OrdonnancementService
    {
        return app(OrdonnancementService::class);
    }

    private function parcourirCircuit(Ordonnancement $ordonnancement): void
    {
        $wf = app(OrdonnancementWorkflowService::class);
        $wf->soumettre($ordonnancement->fresh(), $this->user);
        for ($i = 0; $i < 6; $i++) {
            $o = $ordonnancement->fresh();
            if ($o->statut === StatutOrdonnancement::Valide) {
                break;
            }
            $wf->processDecision($o, $this->user, DecisionWorkflow::Valide, 'ok');
        }
    }

    public function test_generation_automatique_depuis_liquidation(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);

        $ordonnancement = $this->service()->genererDepuisLiquidation($liquidation, $this->user);

        $this->assertSame(StatutOrdonnancement::Brouillon, $ordonnancement->statut);
        $this->assertSame(1, $ordonnancement->lignes()->count());
        $this->assertSame('500000.00', number_format((float) $ordonnancement->montant_liquide_ref, 2, '.', ''));
        $this->assertStringStartsWith('ORD-', $ordonnancement->numero);
        // En-tête montant_ordonnance reste 0 tant que non mandaté.
        $this->assertSame('0.00', number_format((float) $ordonnancement->montant_ordonnance, 2, '.', ''));
    }

    public function test_creation_manuelle_exige_justification(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);

        $this->expectException(ValidationException::class);
        $this->service()->creer([
            'liquidation_id' => $liquidation->id,
            'mode_creation' => 'manuel',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $liquidation->lignes->first()->ligne_budgetaire_id, 'montant_ordonnance' => 1000]],
        ], $this->user);
    }

    public function test_moteur_calcul_reste_et_taux(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $lb = $liquidation->lignes->first()->ligne_budgetaire_id;

        $ordonnancement = $this->service()->creer([
            'liquidation_id' => $liquidation->id,
            'mode_creation' => 'automatique',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'P', 'ligne_budgetaire_id' => $lb, 'montant_liquide' => 500_000, 'montant_ordonnance' => 300_000]],
        ], $this->user);

        $this->assertSame('500000.00', number_format((float) $ordonnancement->montant_liquide_ref, 2, '.', ''));
        $this->assertSame('200000.00', number_format((float) $ordonnancement->reste_a_ordonnancer, 2, '.', ''));
        $this->assertSame('300000.00', number_format((float) app(OrdonnancementCalculationService::class)->totalAOrdonnancer($ordonnancement), 2, '.', ''));
        $this->assertSame(1, $ordonnancement->calculs()->count());
    }

    public function test_controle_financier_detecte_indisponibilite(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        // Ligne budgétaire neuve, sans crédit liquidé (montant_liquide = 0).
        $autreLigne = LigneBudgetaire::factory()->for($this->exercice, 'exerciceBudgetaire')->create(['montant_vote' => 100_000_000]);

        $ordonnancement = $this->service()->creer([
            'liquidation_id' => $liquidation->id,
            'mode_creation' => 'automatique',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'P', 'ligne_budgetaire_id' => $autreLigne->id, 'montant_liquide' => 0, 'montant_ordonnance' => 1000]],
        ], $this->user);

        $controles = app(OrdonnancementValidationService::class)->controleFinancier($ordonnancement);
        $this->assertFalse($controles['disponibilite']);
    }

    public function test_soumission_bloquee_si_depasse_reste_liquidation(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $lb = $liquidation->lignes->first()->ligne_budgetaire_id;

        $ordonnancement = $this->service()->creer([
            'liquidation_id' => $liquidation->id,
            'mode_creation' => 'automatique',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'Trop', 'ligne_budgetaire_id' => $lb, 'montant_liquide' => 500_000, 'montant_ordonnance' => 700_000]],
        ], $this->user);

        $this->expectException(ValidationException::class);
        app(OrdonnancementWorkflowService::class)->soumettre($ordonnancement->fresh(), $this->user);
    }

    public function test_cycle_complet_mandat_impacte_le_ledger(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $ligne = $liquidation->lignes->first()->ligneBudgetaire;

        $ordonnancement = $this->service()->genererDepuisLiquidation($liquidation, $this->user);
        $this->parcourirCircuit($ordonnancement);
        $this->assertSame(StatutOrdonnancement::Valide, $ordonnancement->fresh()->statut);

        $this->service()->genererMandat($ordonnancement->fresh(), $this->user);

        $fresh = $ordonnancement->fresh();
        $this->assertSame(StatutOrdonnancement::Mandate, $fresh->statut);
        $this->assertSame('500000.00', number_format((float) $fresh->montant_ordonnance, 2, '.', ''));
        $this->assertNotNull($fresh->mandat_numero);
        $this->assertNotNull($fresh->visa_hash);
        $this->assertSame(1, $fresh->mandats()->count());
        $this->assertSame(1, $fresh->ordresPaiement()->count());
        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_ordonnance, 2, '.', ''));
        $this->assertSame(StatutEngagement::Ordonnance, $liquidation->engagement->fresh()->statut);
    }

    public function test_ordonnancement_partiel_ne_passe_pas_engagement_en_ordonnance(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $lb = $liquidation->lignes->first()->ligne_budgetaire_id;

        $ordonnancement = $this->service()->creer([
            'liquidation_id' => $liquidation->id,
            'mode_creation' => 'automatique',
            'date_ordonnancement' => now()->toDateString(),
            'lignes' => [['designation' => 'Partiel', 'ligne_budgetaire_id' => $lb, 'montant_liquide' => 500_000, 'montant_ordonnance' => 200_000]],
        ], $this->user);

        $this->parcourirCircuit($ordonnancement);
        $this->service()->genererMandat($ordonnancement->fresh(), $this->user);

        $this->assertSame(StatutEngagement::Liquide, $liquidation->engagement->fresh()->statut);
    }

    public function test_ordonnancement_multi_lignes_impacte_chaque_ligne(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000], ['montant' => 300_000]]);
        $lignes = $liquidation->lignes;

        $ordonnancement = $this->service()->genererDepuisLiquidation($liquidation, $this->user);
        $this->assertSame(2, $ordonnancement->lignes()->count());

        $this->parcourirCircuit($ordonnancement);
        $this->service()->genererMandat($ordonnancement->fresh(), $this->user);

        $this->assertSame('500000.00', number_format((float) $lignes[0]->ligneBudgetaire->fresh()->montant_ordonnance, 2, '.', ''));
        $this->assertSame('300000.00', number_format((float) $lignes[1]->ligneBudgetaire->fresh()->montant_ordonnance, 2, '.', ''));
        $this->assertSame(StatutEngagement::Ordonnance, $liquidation->engagement->fresh()->statut);
    }

    public function test_transmission_paiement_et_anti_double(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $ordonnancement = $this->service()->genererDepuisLiquidation($liquidation, $this->user);
        $this->parcourirCircuit($ordonnancement);
        $this->service()->genererMandat($ordonnancement->fresh(), $this->user);

        $transmission = app(OrdonnancementToPaiementService::class);
        $paiement = $transmission->transmettre($ordonnancement->fresh(), $this->user);

        $this->assertNotNull($paiement->id);
        $this->assertSame(StatutOrdonnancement::TransmisPaiement, $ordonnancement->fresh()->statut);
        $this->assertSame(StatutEngagement::Paye, $liquidation->engagement->fresh()->statut);

        $this->expectException(ValidationException::class);
        $transmission->transmettre($ordonnancement->fresh(), $this->user);
    }

    public function test_annulation_reprend_le_ledger(): void
    {
        $liquidation = $this->liquidationCertifiee([['montant' => 500_000]]);
        $ligne = $liquidation->lignes->first()->ligneBudgetaire;

        $ordonnancement = $this->service()->genererDepuisLiquidation($liquidation, $this->user);
        $this->parcourirCircuit($ordonnancement);
        $this->service()->genererMandat($ordonnancement->fresh(), $this->user);

        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_ordonnance, 2, '.', ''));

        $this->service()->annuler($ordonnancement->fresh(), $this->user, 'Erreur');

        $this->assertSame('0.00', number_format((float) $ligne->fresh()->montant_ordonnance, 2, '.', ''));
        $this->assertSoftDeleted('ordonnancements', ['id' => $ordonnancement->id]);
        $this->assertSame(StatutEngagement::Liquide, $liquidation->engagement->fresh()->statut);
    }
}
