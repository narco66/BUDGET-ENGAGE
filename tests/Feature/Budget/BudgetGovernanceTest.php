<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\AlerteBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\PapLigneBudgetaire;
use App\Models\User;
use App\Models\VirementCredit;
use App\Services\Budget\BudgetAlertService;
use App\Services\Budget\BudgetImportService;
use App\Services\Budget\VirementCreditService;
use App\Services\Rbm\PapBudgetService;
use App\Services\Rbm\PapImportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BudgetGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();
    }

    private function admin(): User
    {
        $u = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $u->assignRole('administrateur_general');

        return $u;
    }

    private function exercice(int $annee = 2026): ExerciceBudgetaire
    {
        return ExerciceBudgetaire::query()->create([
            'annee' => $annee, 'date_debut' => "{$annee}-01-01", 'date_fin' => "{$annee}-12-31",
            'statut' => StatutExerciceBudgetaire::Ouvert, 'est_actif' => true,
        ]);
    }

    private function ligne(ExerciceBudgetaire $ex, float $revise, float $engage = 0): LigneBudgetaire
    {
        return LigneBudgetaire::factory()->for($ex, 'exerciceBudgetaire')->create([
            'montant_vote' => $revise, 'montant_revise' => $revise, 'montant_engage' => $engage,
        ]);
    }

    // ---------- Alertes de seuil ----------

    public function test_alert_levels_and_resolution(): void
    {
        $svc = app(BudgetAlertService::class);
        $ex = $this->exercice();
        $ligne = $this->ligne($ex, 1_000_000);

        $ligne->montant_engage = 850_000; // 85% -> avertissement
        $a = $svc->evaluate($ligne, false);
        $this->assertSame('avertissement', $a->niveau);

        $ligne->montant_engage = 970_000; // 97% -> critique
        $a = $svc->evaluate($ligne, false);
        $this->assertSame('critique', $a->niveau);

        $ligne->montant_engage = 1_100_000; // depassement
        $a = $svc->evaluate($ligne, false);
        $this->assertSame('depassement', $a->niveau);

        $ligne->montant_engage = 400_000; // 40% -> resolu (conserve en historique)
        $this->assertNull($svc->evaluate($ligne, false));
        $this->assertSame(1, AlerteBudgetaire::query()->count());
        $this->assertSame('resolue', AlerteBudgetaire::query()->first()->statut);
    }

    public function test_observer_creates_alert_on_engagement_change(): void
    {
        $ex = $this->exercice();
        $ligne = $this->ligne($ex, 1_000_000);

        $ligne->update(['montant_engage' => 900_000]); // observer -> evaluate

        $this->assertDatabaseHas('alertes_budgetaires', [
            'ligne_budgetaire_id' => $ligne->id, 'niveau' => 'avertissement',
        ]);
    }

    // ---------- Virements de credits ----------

    public function test_virement_workflow_propose_then_approve_moves_credit(): void
    {
        $ex = $this->exercice();
        $source = $this->ligne($ex, 300_000_000);
        $dest = $this->ligne($ex, 25_000_000);
        $admin = $this->admin();
        $svc = app(VirementCreditService::class);

        // Proposition : aucun mouvement.
        $v = $svc->propose($source, $dest, '20000000', 'Ajustement', $admin, '127.0.0.1');
        $this->assertSame('propose', $v->statut);
        $this->assertEqualsWithDelta(300_000_000, (float) $source->fresh()->montant_revise, 0.5);

        // Approbation : mouvement applique + tracabilite.
        $v = $svc->approve($v, $admin, 'Validé');
        $this->assertSame('approuve', $v->statut);
        $this->assertEqualsWithDelta(280_000_000, (float) $source->fresh()->montant_revise, 0.5);
        $this->assertEqualsWithDelta(45_000_000, (float) $dest->fresh()->montant_revise, 0.5);
        $this->assertDatabaseHas('journaux_audit', ['action' => 'virement_credit_approuve', 'entite_id' => $v->id]);
    }

    public function test_virement_reject_does_not_move_credit(): void
    {
        $ex = $this->exercice();
        $source = $this->ligne($ex, 300_000_000);
        $dest = $this->ligne($ex, 25_000_000);
        $admin = $this->admin();
        $svc = app(VirementCreditService::class);

        $v = $svc->propose($source, $dest, '20000000', 'Ajustement', $admin);
        $v = $svc->reject($v, $admin, 'Non justifié');

        $this->assertSame('rejete', $v->statut);
        $this->assertEqualsWithDelta(300_000_000, (float) $source->fresh()->montant_revise, 0.5);
    }

    public function test_virement_propose_blocked_when_exceeding_available(): void
    {
        $ex = $this->exercice();
        $source = $this->ligne($ex, 100_000_000, 90_000_000); // dispo = 10M
        $dest = $this->ligne($ex, 5_000_000);

        $this->expectException(ValidationException::class);
        app(VirementCreditService::class)->propose($source, $dest, '20000000', 'Trop', $this->admin());
    }

    public function test_virement_http_requires_permission(): void
    {
        $ex = $this->exercice();
        $source = $this->ligne($ex, 100_000_000);
        $dest = $this->ligne($ex, 5_000_000);

        $simple = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $simple->assignRole('agent_initiateur');
        $this->actingAs($simple)
            ->post(route('budget.virements.store'), [
                'ligne_source_id' => $source->id, 'ligne_destination_id' => $dest->id, 'montant' => '1000', 'motif' => 'x',
            ])->assertForbidden();

        $this->actingAs($this->admin())
            ->post(route('budget.virements.store'), [
                'ligne_source_id' => $source->id, 'ligne_destination_id' => $dest->id, 'montant' => '1000000', 'motif' => 'Réallocation',
            ])->assertRedirect();
        $this->assertSame(1, VirementCredit::query()->count());
    }

    // ---------- Etat consolide ----------

    public function test_consolide_links_pap_budget_execution(): void
    {
        $ex = $this->exercice();
        $admin = $this->admin();
        app(PapImportService::class)->run(app(PapImportService::class)->rowsFromDataset(), $ex, 'remplacement', $admin);

        // Lier une ligne operationnelle a un produit PAP du pilier 201.
        $produit = PapLigneBudgetaire::query()->where('exercice_budgetaire_id', $ex->id)->where('code', '20111')->first();
        $ligne = $this->ligne($ex, 150_000_000, 30_000_000);
        $ligne->update(['pap_ligne_budgetaire_id' => $produit->id]);

        $consolide = app(PapBudgetService::class)->consolideParPilier($ex);
        $p201 = $consolide->firstWhere('code', '201');

        $this->assertEqualsWithDelta(1_750_000_000, $p201['pap_total'], 0.5);
        $this->assertEqualsWithDelta(150_000_000, $p201['budgete'], 0.5);
        $this->assertEqualsWithDelta(30_000_000, $p201['engage'], 0.5);
    }

    public function test_consolide_page_renders(): void
    {
        $ex = $this->exercice();
        $admin = $this->admin();
        app(PapImportService::class)->run(app(PapImportService::class)->rowsFromDataset(), $ex, 'remplacement', $admin);
        app(BudgetImportService::class)->run(app(BudgetImportService::class)->rowsFromDataset(), $ex, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('budget.nomenclature.consolide', ['exercice_budgetaire_id' => $ex->id]))
            ->assertOk()
            ->assertSee('22 365 281 000');
    }

    public function test_consolide_exports_excel_and_pdf(): void
    {
        $ex = $this->exercice();
        $admin = $this->admin();
        app(PapImportService::class)->run(app(PapImportService::class)->rowsFromDataset(), $ex, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('budget.nomenclature.consolide.export.excel', ['exercice_budgetaire_id' => $ex->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdf = $this->actingAs($admin)
            ->get(route('budget.nomenclature.consolide.export.pdf', ['exercice_budgetaire_id' => $ex->id]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', strtolower((string) $pdf->headers->get('content-type')));
    }

    // ---------- Traitement / historique des alertes ----------

    public function test_alert_treat_restore_and_resolution_keeps_history(): void
    {
        $svc = app(BudgetAlertService::class);
        $admin = $this->admin();
        $ex = $this->exercice();
        $ligne = $this->ligne($ex, 1_000_000);

        $ligne->update(['montant_engage' => 900_000]); // -> alerte nouvelle (observer)
        $alerte = AlerteBudgetaire::query()->where('ligne_budgetaire_id', $ligne->id)->firstOrFail();
        $this->assertSame('nouvelle', $alerte->statut);

        // Traiter.
        $svc->traiter($alerte, $admin, 'Accusé réception');
        $this->assertSame('traitee', $alerte->fresh()->statut);

        // Restaurer (rouvrir).
        $svc->restaurer($alerte->fresh(), $admin);
        $this->assertSame('nouvelle', $alerte->fresh()->statut);

        // Resolution : la ligne repasse sous le seuil -> statut 'resolue', la ligne reste en historique.
        $ligne->update(['montant_engage' => 100_000]);
        $this->assertSame('resolue', $alerte->fresh()->statut);
        $this->assertSame(1, AlerteBudgetaire::query()->count());
    }

    public function test_alert_treat_via_http_requires_permission(): void
    {
        $admin = $this->admin();
        $ex = $this->exercice();
        $ligne = $this->ligne($ex, 1_000_000);
        $ligne->update(['montant_engage' => 900_000]);
        $alerte = AlerteBudgetaire::query()->where('ligne_budgetaire_id', $ligne->id)->firstOrFail();

        $simple = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $simple->assignRole('agent_initiateur');
        $this->actingAs($simple)->post(route('budget.alertes.traiter', $alerte))->assertForbidden();

        $this->actingAs($admin)->post(route('budget.alertes.traiter', $alerte), ['commentaire' => 'OK'])->assertRedirect();
        $this->assertSame('traitee', $alerte->fresh()->statut);

        $this->actingAs($admin)->get(route('budget.alertes.historique', ['exercice_budgetaire_id' => $ex->id]))->assertOk();
    }
}
