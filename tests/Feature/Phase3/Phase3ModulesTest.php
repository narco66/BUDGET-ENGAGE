<?php

namespace Tests\Feature\Phase3;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\Activite;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\Indicateur;
use App\Models\User;
use App\Services\Recettes\RecetteService;
use App\Services\SuiviEvaluation\SuiviEvaluationService;
use App\Services\SuiviPhysique\SuiviPhysiqueService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3ModulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $this->admin->assignRole('administrateur_general');
    }

    public function test_recette_perception_updates_budget_line_on_validation(): void
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        $ligne = BudgetLigne::query()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'section' => 'recette',
            'niveau' => 'ligne',
            'categorie' => 'recette_interne',
            'code' => '72101',
            'libelle' => 'Test recette',
            'montant_total' => 1_000_000,
            'montant_ceeac' => 1_000_000,
            'montant_ptf' => 0,
        ]);

        $perception = app(RecetteService::class)->enregistrer([
            'exercice_budgetaire_id' => $exercice->id,
            'budget_ligne_id' => $ligne->id,
            'date_perception' => now()->toDateString(),
            'montant' => 250_000,
            'mode_reglement' => 'virement',
        ], $this->admin, true);

        $ligne->refresh();
        $this->assertEqualsWithDelta(250_000, (float) $ligne->montant_realise, 0.5);
        $this->assertSame('valide', $perception->statut->value);

        $synth = app(RecetteService::class)->syntheseExercice($exercice);
        $this->assertEqualsWithDelta(250_000, $synth['montant_percu'], 0.5);
    }

    public function test_recettes_dashboard_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('recettes.dashboard'))
            ->assertOk()
            ->assertSee(__('Gestion des recettes'));
    }

    public function test_suivi_physique_synthesis_after_validation(): void
    {
        $exercice = ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => StatutExerciceBudgetaire::Ouvert, 'est_actif' => true,
        ]);
        $activite = Activite::factory()->create();

        $realisation = app(SuiviPhysiqueService::class)->enregistrer([
            'activite_id' => $activite->id,
            'exercice_budgetaire_id' => $exercice->id,
            'trimestre' => 1,
            'annee' => 2026,
            'periode_debut' => '2026-01-01',
            'periode_fin' => '2026-03-31',
            'taux_avancement' => 45,
        ], $this->admin);

        app(SuiviPhysiqueService::class)->valider($realisation, $this->admin);

        $synth = app(SuiviPhysiqueService::class)->syntheseExercice($exercice);
        $this->assertSame(1, $synth['nb_realisations']);
        $this->assertEqualsWithDelta(45, $synth['taux_avancement_moyen'], 0.1);

        $this->actingAs($this->admin)->get(route('suivi-physique.index'))->assertOk();
    }

    public function test_suivi_evaluation_trimestrial_report(): void
    {
        $exercice = ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => StatutExerciceBudgetaire::Ouvert, 'est_actif' => true,
        ]);
        $indicateur = Indicateur::factory()->create();

        $realisation = app(SuiviEvaluationService::class)->enregistrer([
            'indicateur_id' => $indicateur->id,
            'exercice_budgetaire_id' => $exercice->id,
            'trimestre' => 2,
            'annee' => 2026,
            'periode_debut' => '2026-04-01',
            'periode_fin' => '2026-06-30',
            'valeur_cible' => 100,
            'valeur_realisee' => 80,
        ], $this->admin);

        app(SuiviEvaluationService::class)->valider($realisation, $this->admin);

        $rapport = app(SuiviEvaluationService::class)->rapportTrimestriel($exercice, 2);
        $row = $rapport->firstWhere('indicateur_id', $indicateur->id);
        $this->assertTrue($row['saisi']);
        $this->assertEqualsWithDelta(80, $row['taux_realisation'], 0.1);

        $this->actingAs($this->admin)
            ->get(route('suivi-evaluation.index', ['exercice_budgetaire_id' => $exercice->id, 'trimestre' => 2]))
            ->assertOk()
            ->assertSee($indicateur->code);
    }
}
