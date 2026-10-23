<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Services\Budget\BudgetImportService;
use App\Services\Budget\BudgetReportService;
use App\Services\Budget\BudgetSyncService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        return $user;
    }

    private function exercice(): ExerciceBudgetaire
    {
        return ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => StatutExerciceBudgetaire::Ouvert, 'est_actif' => true,
        ]);
    }

    private function importBudget(ExerciceBudgetaire $exercice, User $admin): void
    {
        app(BudgetImportService::class)->run(
            app(BudgetImportService::class)->rowsFromDataset(), $exercice, 'remplacement', $admin
        );
    }

    public function test_sync_generates_operational_lines_reconciling_to_total(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);

        $result = app(BudgetSyncService::class)->synchronize($exercice, $admin);

        $this->assertGreaterThan(0, $result['creations']);
        $this->assertEqualsWithDelta(40_305_795_803, $result['montant'], 0.5);

        $sumVote = (float) LigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->sum('montant_vote');
        $this->assertEqualsWithDelta(40_305_795_803, $sumVote, 0.5);

        // Les feuilles budget sont reliees a une ligne operationnelle.
        $this->assertGreaterThan(0, BudgetLigne::query()->whereNotNull('ligne_budgetaire_id')->count());
    }

    public function test_sync_is_idempotent_and_preserves_execution_amounts(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);
        $sync = app(BudgetSyncService::class);

        $first = $sync->synchronize($exercice, $admin);
        // Simule une consommation sur une ligne.
        $ligne = LigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->where('code', '66101')->first();
        $ligne->update(['montant_engage' => 1_000_000]);

        $second = $sync->synchronize($exercice, $admin);
        $this->assertSame(0, $second['creations']);
        $this->assertSame($first['creations'], $second['mises_a_jour']);

        // L'engagement n'est pas ecrase.
        $this->assertEqualsWithDelta(1_000_000, (float) $ligne->fresh()->montant_engage, 0.5);
    }

    public function test_report_execution_tracks_engagements(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);
        app(BudgetSyncService::class)->synchronize($exercice, $admin);

        LigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->where('code', '66101')
            ->update(['montant_engage' => 2_000_000, 'montant_paye' => 1_000_000]);

        $rep = app(BudgetReportService::class)->synthese($exercice);
        $this->assertEqualsWithDelta(40_305_795_803, $rep['credits_ouverts'], 0.5);
        $this->assertEqualsWithDelta(2_000_000, $rep['montant_engage'], 0.5);
        $this->assertEqualsWithDelta(1_000_000, $rep['montant_paye'], 0.5);
        // Disponible = crédits ouverts − engagé.
        $this->assertEqualsWithDelta(40_305_795_803 - 2_000_000, $rep['disponible'], 0.5);

        $cats = app(BudgetReportService::class)->executionParCategorie($exercice);
        $this->assertTrue($cats->firstWhere('categorie', 'personnel')['montant_engage'] >= 2_000_000);
    }

    public function test_sync_requires_permission_via_http(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);

        $simple = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $simple->assignRole('agent_initiateur');
        $this->actingAs($simple)
            ->post(route('budget.nomenclature.synchronize', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('budget.nomenclature.synchronize', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertRedirect();
        $this->assertGreaterThan(0, LigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->count());
    }

    public function test_dashboard_shows_budget_section(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);
        app(BudgetSyncService::class)->synchronize($exercice, $admin);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('40 305 795 803');
    }

    public function test_execution_exports_excel_and_pdf(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();
        $this->importBudget($exercice, $admin);
        app(BudgetSyncService::class)->synchronize($exercice, $admin);

        $this->actingAs($admin)
            ->get(route('budget.nomenclature.execution.export.excel', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdf = $this->actingAs($admin)
            ->get(route('budget.nomenclature.execution.export.pdf', ['exercice_budgetaire_id' => $exercice->id]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', strtolower((string) $pdf->headers->get('content-type')));
    }
}
