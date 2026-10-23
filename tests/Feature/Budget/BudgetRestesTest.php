<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\BudgetImport;
use App\Models\ExerciceBudgetaire;
use App\Models\ImportBudget;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Services\Budget\BudgetLegacyImportBridge;
use App\Services\Budget\BudgetRestesService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetRestesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_restes_pour_ligne_calcule_la_chaine_complete(): void
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        $ligne = LigneBudgetaire::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'montant_vote' => 1_000_000,
            'montant_revise' => 1_000_000,
            'montant_engage' => 600_000,
            'montant_liquide' => 400_000,
            'montant_ordonnance' => 300_000,
            'montant_paye' => 200_000,
        ]);

        $restes = app(BudgetRestesService::class)->restesPourLigne($ligne);

        $this->assertEquals('1000000.00', $restes['plafond']);
        $this->assertEquals('400000.00', $restes['reste_a_engager']);
        $this->assertEquals('200000.00', $restes['reste_a_liquider']);
        $this->assertEquals('100000.00', $restes['reste_a_ordonnancer']);
        $this->assertEquals('100000.00', $restes['reste_a_payer']);
    }

    public function test_synthese_exercice_agrege_les_lignes(): void
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        LigneBudgetaire::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'montant_revise' => 500_000,
            'montant_engage' => 100_000,
        ]);
        LigneBudgetaire::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'montant_revise' => 300_000,
            'montant_engage' => 50_000,
        ]);

        $synth = app(BudgetRestesService::class)->syntheseExercice($exercice);

        $this->assertSame(2, $synth['nb_lignes']);
        $this->assertEqualsWithDelta(800_000, $synth['plafond'], 0.5);
        $this->assertEqualsWithDelta(150_000, $synth['montant_engage'], 0.5);
        $this->assertEqualsWithDelta(650_000, $synth['reste_a_engager'], 0.5);
    }

    public function test_situation_page_is_accessible_with_budget_view(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_budget');
        $exercice = ExerciceBudgetaire::factory()->create(['est_actif' => true]);
        LigneBudgetaire::factory()->create(['exercice_budgetaire_id' => $exercice->id]);

        $this->actingAs($user)
            ->get(route('budget.situation.index', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertSee(__('Situation budgétaire'));
    }

    public function test_situation_export_csv(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_budget');
        $exercice = ExerciceBudgetaire::factory()->create();
        LigneBudgetaire::factory()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'code' => 'TEST-001',
        ]);

        $this->actingAs($user)
            ->get(route('budget.situation.export', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
