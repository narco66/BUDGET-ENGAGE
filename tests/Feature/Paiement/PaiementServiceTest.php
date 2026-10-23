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
use App\Services\Paiement\PaiementCalculationService;
use App\Services\Paiement\PaiementService;
use App\Services\Paiement\PaiementWorkflowService;
use Database\Seeders\LiquidationTypeSeeder;
use Database\Seeders\OrdonnancementTypeSeeder;
use Database\Seeders\PaiementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaiementServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ExerciceBudgetaire $exercice;

    private PaiementBanque $banque;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(LiquidationTypeSeeder::class);
        $this->seed(OrdonnancementTypeSeeder::class);
        $this->seed(PaiementTypeSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('administrateur_general');

        $this->exercice = ExerciceBudgetaire::factory()->create();
        $this->banque = PaiementBanque::query()->create([
            'code' => 'TEST-XAF', 'libelle' => 'Compte test', 'banque' => 'BEAC',
            'iban' => 'CM2110002000000123456789012', 'bic_swift' => 'BEACCMCX', 'devise' => 'XAF',
            'plafond_decaissement' => 1_000_000_000, 'est_actif' => true,
        ]);
    }

    /**
     * Construit un ordonnancement MANDATÉ prêt à être payé.
     *
     * @param  array<int, array{montant: int}>  $imputations
     */
    private function ordonnancementMandate(array $imputations): Ordonnancement
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
        $sfS = app(LiquidationServiceFaitService::class);
        $sf = $sfS->enregistrer($liquidation, [
            'date_constatation' => now()->toDateString(),
            'conformite_quantitative' => true, 'conformite_qualitative' => true,
            'conformite_technique' => true, 'conformite_contractuelle' => true,
        ], $this->user);
        $sfS->valider($sf, $this->user);
        $lwf = app(LiquidationWorkflowService::class);
        $lwf->soumettre($liquidation->fresh(), $this->user);
        $this->boucler($liquidation, fn () => $liquidation->fresh()->statut === StatutLiquidation::Valide, fn () => $lwf->processDecision($liquidation->fresh(), $this->user, DecisionWorkflow::Valide, 'ok'));
        app(LiquidationService::class)->certifier($liquidation->fresh(), $this->user);

        $os = app(OrdonnancementService::class);
        $ord = $os->genererDepuisLiquidation($liquidation->fresh(), $this->user);
        $owf = app(OrdonnancementWorkflowService::class);
        $owf->soumettre($ord->fresh(), $this->user);
        $this->boucler($ord, fn () => $ord->fresh()->statut === StatutOrdonnancement::Valide, fn () => $owf->processDecision($ord->fresh(), $this->user, DecisionWorkflow::Valide, 'ok'));
        $os->genererMandat($ord->fresh(), $this->user);

        return $ord->fresh(['lignes']);
    }

    private function boucler(object $model, callable $done, callable $step): void
    {
        for ($i = 0; $i < 8; $i++) {
            if ($done()) {
                return;
            }
            $step();
        }
    }

    private function service(): PaiementService
    {
        return app(PaiementService::class);
    }

    private function parcourirCircuit(Paiement $paiement): void
    {
        $wf = app(PaiementWorkflowService::class);
        $wf->programmer($paiement->fresh(), $this->user);
        $this->boucler($paiement, fn () => $paiement->fresh()->statut === StatutPaiement::Valide, fn () => $wf->processDecision($paiement->fresh(), $this->user, DecisionWorkflow::Valide, 'ok'));
    }

    private function jusquaTransmis(Ordonnancement $ord, array $data = []): Paiement
    {
        $paiement = $this->service()->genererDepuisOrdonnancement($ord, $this->user, array_merge(['paiement_banque_id' => $this->banque->id], $data));
        $this->parcourirCircuit($paiement);
        $this->service()->transmettreBanque($paiement->fresh(), $this->user);

        return $paiement->fresh();
    }

    public function test_generation_automatique_depuis_ordonnancement(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);

        $paiement = $this->service()->genererDepuisOrdonnancement($ord, $this->user, ['paiement_banque_id' => $this->banque->id]);

        $this->assertSame(StatutPaiement::Brouillon, $paiement->statut);
        $this->assertSame(1, $paiement->lignes()->count());
        $this->assertSame('500000.00', number_format((float) $paiement->montant_ordonnance_ref, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $paiement->montant_paye, 2, '.', ''));
        $this->assertStringStartsWith('PAY-', $paiement->numero);
    }

    public function test_creation_manuelle_exige_justification(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);

        $this->expectException(ValidationException::class);
        $this->service()->creer([
            'ordonnancement_id' => $ord->id,
            'mode_creation' => 'manuel',
            'lignes' => [['designation' => 'X', 'ligne_budgetaire_id' => $ord->lignes->first()->ligne_budgetaire_id, 'montant_paye' => 1000]],
        ], $this->user);
    }

    public function test_moteur_calcul_reste_et_taux(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $lb = $ord->lignes->first()->ligne_budgetaire_id;

        $paiement = $this->service()->creer([
            'ordonnancement_id' => $ord->id,
            'mode_creation' => 'automatique',
            'lignes' => [['designation' => 'P', 'ligne_budgetaire_id' => $lb, 'montant_ordonnance' => 500_000, 'montant_paye' => 300_000]],
        ], $this->user);

        $this->assertSame('500000.00', number_format((float) $paiement->montant_ordonnance_ref, 2, '.', ''));
        $this->assertSame('200000.00', number_format((float) $paiement->reste_a_payer, 2, '.', ''));
        $this->assertSame('300000.00', number_format((float) app(PaiementCalculationService::class)->totalAPayer($paiement), 2, '.', ''));
        $this->assertSame(1, $paiement->calculs()->count());
    }

    public function test_controle_tresorerie_bloque_si_plafond_depasse(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $petiteBanque = PaiementBanque::query()->create([
            'code' => 'MINI', 'libelle' => 'Plafond mini', 'iban' => 'CM2110002000000999999999999',
            'devise' => 'XAF', 'plafond_decaissement' => 100_000, 'est_actif' => true,
        ]);

        $paiement = $this->service()->genererDepuisOrdonnancement($ord, $this->user, ['paiement_banque_id' => $petiteBanque->id]);
        $this->parcourirCircuit($paiement);

        // Plafond 100k < 500k → bloqué.
        try {
            $this->service()->transmettreBanque($paiement->fresh(), $this->user);
            $this->fail('Le contrôle de trésorerie aurait dû bloquer.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tresorerie', $e->errors());
        }

        // Avec dérogation → passe.
        $this->service()->transmettreBanque($paiement->fresh(), $this->user, true);
        $this->assertSame(StatutPaiement::TransmisBanque, $paiement->fresh()->statut);
    }

    public function test_multi_devises_calcule_ecart_change(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $lb = $ord->lignes->first()->ligne_budgetaire_id;

        $paiement = $this->service()->creer([
            'ordonnancement_id' => $ord->id,
            'mode_creation' => 'automatique',
            'devise' => 'XAF', 'devise_paiement' => 'EUR', 'taux_change' => 0.0015,
            'lignes' => [['designation' => 'Intl', 'ligne_budgetaire_id' => $lb, 'montant_ordonnance' => 500_000, 'montant_paye' => 500_000]],
        ], $this->user);

        // ecart = 500000 × 0.0015 − 500000 ≠ 0
        $this->assertNotSame('0.00', number_format((float) $paiement->fresh()->ecart_change, 2, '.', ''));
    }

    public function test_cycle_complet_execution_impacte_ledger_et_genere_ecritures(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $ligne = $ord->lignes->first()->ligneBudgetaire;
        $engagement = $ord->liquidation->engagement;

        $paiement = $this->jusquaTransmis($ord);
        $this->service()->executer($paiement->fresh(), $this->user);

        $fresh = $paiement->fresh();
        $this->assertSame(StatutPaiement::Execute, $fresh->statut);
        $this->assertSame('500000.00', number_format((float) $fresh->montant_paye, 2, '.', ''));
        $this->assertNotNull($fresh->preuve_numero);
        $this->assertNotNull($fresh->preuve_hash);
        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_paye, 2, '.', ''));
        $this->assertSame(StatutEngagement::Paye, $engagement->fresh()->statut);

        // Écritures comptables partie double équilibrées.
        $this->assertSame(2, $fresh->ecritures()->count());
        $debit = (string) $fresh->ecritures()->sum('debit');
        $credit = (string) $fresh->ecritures()->sum('credit');
        $this->assertSame($debit, $credit);
        $this->assertSame('500000.00', number_format((float) $debit, 2, '.', ''));
    }

    public function test_paiement_partiel_ne_solde_pas_engagement(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $lb = $ord->lignes->first()->ligne_budgetaire_id;
        $engagement = $ord->liquidation->engagement;

        $paiement = $this->service()->creer([
            'ordonnancement_id' => $ord->id,
            'mode_creation' => 'automatique',
            'paiement_banque_id' => $this->banque->id,
            'lignes' => [['designation' => 'Partiel', 'ligne_budgetaire_id' => $lb, 'montant_ordonnance' => 500_000, 'montant_paye' => 200_000]],
        ], $this->user);
        $this->parcourirCircuit($paiement);
        $this->service()->transmettreBanque($paiement->fresh(), $this->user);
        $this->service()->executer($paiement->fresh(), $this->user);

        $this->assertSame(StatutEngagement::Ordonnance, $engagement->fresh()->statut);
    }

    public function test_multi_lignes_impacte_chaque_ligne(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000], ['montant' => 300_000]]);
        $lignes = $ord->lignes;
        $engagement = $ord->liquidation->engagement;

        $paiement = $this->jusquaTransmis($ord);
        $this->assertSame(2, $paiement->lignes()->count());
        $this->service()->executer($paiement->fresh(), $this->user);

        $this->assertSame('500000.00', number_format((float) $lignes[0]->ligneBudgetaire->fresh()->montant_paye, 2, '.', ''));
        $this->assertSame('300000.00', number_format((float) $lignes[1]->ligneBudgetaire->fresh()->montant_paye, 2, '.', ''));
        $this->assertSame(StatutEngagement::Paye, $engagement->fresh()->statut);
    }

    public function test_rejet_bancaire(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $paiement = $this->jusquaTransmis($ord);

        $rejet = $this->service()->signalerRejet($paiement->fresh(), [
            'motif' => 'insuffisance_provision',
            'description' => 'Provision insuffisante',
        ], $this->user);

        $this->assertSame(StatutPaiement::Echoue, $paiement->fresh()->statut);
        $this->assertSame(1, $paiement->rejets()->count());
        $this->assertSame('insuffisance_provision', $rejet->motif);
    }

    public function test_rapprochement_et_cloture(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $paiement = $this->jusquaTransmis($ord);
        $this->service()->executer($paiement->fresh(), $this->user);

        $this->service()->rapprocher($paiement->fresh(), $this->user);
        $this->assertSame(StatutPaiement::Rapproche, $paiement->fresh()->statut);

        $this->service()->cloturer($paiement->fresh(), $this->user);
        $this->assertSame(StatutPaiement::Cloture, $paiement->fresh()->statut);
    }

    public function test_annulation_reprend_le_ledger(): void
    {
        $ord = $this->ordonnancementMandate([['montant' => 500_000]]);
        $ligne = $ord->lignes->first()->ligneBudgetaire;
        $engagement = $ord->liquidation->engagement;

        $paiement = $this->jusquaTransmis($ord);
        $this->service()->executer($paiement->fresh(), $this->user);
        $this->assertSame('500000.00', number_format((float) $ligne->fresh()->montant_paye, 2, '.', ''));

        $this->service()->annuler($paiement->fresh(), $this->user, 'Erreur');

        $this->assertSame('0.00', number_format((float) $ligne->fresh()->montant_paye, 2, '.', ''));
        $this->assertSoftDeleted('paiements', ['id' => $paiement->id]);
        $this->assertSame(StatutEngagement::Ordonnance, $engagement->fresh()->statut);
    }
}
