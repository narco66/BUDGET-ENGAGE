<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\BudgetImport;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\User;
use App\Services\Budget\BudgetImportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetImportTest extends TestCase
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

    private function exercice(int $annee = 2026): ExerciceBudgetaire
    {
        return ExerciceBudgetaire::query()->create([
            'annee' => $annee,
            'date_debut' => "{$annee}-01-01",
            'date_fin' => "{$annee}-12-31",
            'statut' => StatutExerciceBudgetaire::Ouvert,
            'est_actif' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $section, string $niveau, string $code, ?string $parent, float $total, float $ceeac, float $ptf, int $ordre): array
    {
        return [
            'section' => $section, 'niveau' => $niveau, 'categorie' => null,
            'code' => $code, 'parent_code' => $parent ?? '', 'libelle' => 'L '.$code,
            'montant_total' => $total, 'montant_ceeac' => $ceeac, 'montant_ptf' => $ptf,
            'montant_anterieur' => null, 'montant_realise' => null, 'montant_ptf_anterieur' => null,
            'montant_ptf_realise' => null, 'taux_realisation' => null, 'variation' => null,
            'ordre' => $ordre, 'source' => 'TEST', '_line' => $ordre + 2,
        ];
    }

    public function test_real_dataset_import_balances_recettes_depenses(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        $import = $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $this->admin());

        $this->assertSame('valide', $import->statut);
        $this->assertEqualsWithDelta(40_305_795_803, (float) $import->montant_recettes, 0.5);
        $this->assertEqualsWithDelta(40_305_795_803, (float) $import->montant_depenses, 0.5);
        $this->assertEqualsWithDelta(26_275_514_803, (float) $import->montant_ceeac, 0.5);
        $this->assertEqualsWithDelta(14_030_281_000, (float) $import->montant_ptf, 0.5);
        $this->assertSame(258, BudgetLigne::query()->count());
        $this->assertSame(5, $import->nb_titres);
    }

    public function test_pap_reconciliation_warns_when_pap_absent_and_ok_when_present(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        // Sans PAP importe : une information de rapprochement est presente, import valide.
        $import = $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $this->admin());
        $this->assertSame('valide', $import->statut);
        $rapport = collect($import->rapport);
        $this->assertTrue($rapport->where('colonne', 'pap')->isNotEmpty());
    }

    public function test_simulation_does_not_write(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        $import = $service->run($service->rowsFromDataset(), $exercice, 'simulation', $this->admin());

        $this->assertSame('simule', $import->statut);
        $this->assertSame(0, BudgetLigne::query()->count());
        $this->assertGreaterThan(0, $import->nb_creations);
    }

    public function test_replacement_historizes_and_restores(): void
    {
        $service = app(BudgetImportService::class);
        $admin = $this->admin();
        $exercice = $this->exercice();

        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);
        $initial = BudgetLigne::query()->count();

        $rows = [$this->row('depense', 'titre', 'D-FONC', null, 1000, 1000, 0, 0)];
        $replace = $service->run($rows, $exercice, 'remplacement', $admin);
        $this->assertSame(1, BudgetLigne::query()->count());
        $this->assertSame($initial, $replace->nb_suppressions);

        $restore = $service->restore($replace, $admin);
        $this->assertSame('valide', $restore->statut);
        $this->assertSame($initial, BudgetLigne::query()->count());
    }

    public function test_missing_parent_blocks(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        $rows = [$this->row('depense', 'chapitre', '66', 'D-FONC', 100, 100, 0, 0)];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('echoue', $import->statut);
        $this->assertSame(0, BudgetLigne::query()->count());
    }

    public function test_negative_amount_blocks(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        $rows = [$this->row('depense', 'titre', 'D-FONC', null, -100, 0, 0, 0)];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('echoue', $import->statut);
    }

    public function test_subtotal_incoherence_is_warning(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();

        $rows = [
            $this->row('depense', 'titre', 'D-FONC', null, 1000, 1000, 0, 0),
            $this->row('depense', 'chapitre', '66', 'D-FONC', 700, 700, 0, 1),
        ];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('valide', $import->statut);
        $this->assertGreaterThan(0, $import->nb_avertissements);
    }

    public function test_other_exercise_not_deleted_on_replacement(): void
    {
        $service = app(BudgetImportService::class);
        $exercice2025 = $this->exercice(2025);
        $exercice2026 = $this->exercice(2026);

        BudgetLigne::query()->create([
            'exercice_budgetaire_id' => $exercice2025->id, 'section' => 'depense', 'niveau' => 'titre',
            'code' => 'X-2025', 'libelle' => 'Garde', 'montant_total' => 5, 'montant_ceeac' => 5, 'montant_ptf' => 0, 'ordre' => 0, 'source' => 'TEST',
        ]);

        $service->run($service->rowsFromDataset(), $exercice2026, 'remplacement', $this->admin());

        $this->assertDatabaseHas('budget_lignes', ['exercice_budgetaire_id' => $exercice2025->id, 'code' => 'X-2025']);
    }

    public function test_index_permission_and_replacement_confirmation(): void
    {
        $exercice = $this->exercice();

        $simple = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $simple->assignRole('agent_initiateur');
        $this->actingAs($simple)->get(route('budget.nomenclature.index'))->assertForbidden();

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('budget.nomenclature.index'))->assertOk();

        $this->actingAs($admin)
            ->from(route('budget.nomenclature.index'))
            ->post(route('budget.nomenclature.store'), [
                'exercice_budgetaire_id' => $exercice->id, 'version' => '1.0', 'mode' => 'remplacement', 'source' => 'document',
            ])
            ->assertSessionHasErrors('confirmation');
        $this->assertSame(0, BudgetImport::query()->where('statut', 'valide')->count());
    }

    public function test_synthese_renders(): void
    {
        $service = app(BudgetImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('budget.nomenclature.synthese', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertSee('40 305 795 803');
    }

    public function test_template_csv_download(): void
    {
        $this->actingAs($this->admin())
            ->get(route('budget.nomenclature.template.csv'))
            ->assertOk();
    }
}
