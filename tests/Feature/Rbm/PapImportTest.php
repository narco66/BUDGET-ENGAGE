<?php

namespace Tests\Feature\Rbm;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\PapImport;
use App\Models\PapLigneBudgetaire;
use App\Models\Pilier;
use App\Models\User;
use App\Services\Rbm\PapImportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PapImportTest extends TestCase
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
    private function row(string $code, string $libelle, string $niveau, ?string $parent, float $total, float $ceeac, float $ptf, int $ordre): array
    {
        return [
            'code' => $code,
            'libelle' => $libelle,
            'niveau' => $niveau,
            'parent_code' => $parent ?? '',
            'montant_total' => $total,
            'montant_ceeac' => $ceeac,
            'montant_ptf' => $ptf,
            'ordre' => $ordre,
            'source' => 'TEST',
            '_line' => $ordre + 2,
        ];
    }

    public function test_real_dataset_import_reconciles_with_official_grand_total(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();

        $import = $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $this->admin());

        $this->assertSame('valide', $import->statut);
        $this->assertSame(6, $import->nb_piliers);
        $this->assertEqualsWithDelta(22_365_281_000, (float) $import->montant_total, 0.5);

        // CEEAC + PTF des piliers se reconcilient avec l'entete officielle.
        $ceeac = PapLigneBudgetaire::query()->where('niveau', 'pilier')->sum('montant_ceeac');
        $ptf = PapLigneBudgetaire::query()->where('niveau', 'pilier')->sum('montant_ptf');
        $this->assertEqualsWithDelta(8_335_000_000, (float) $ceeac, 0.5);
        $this->assertEqualsWithDelta(14_030_281_000, (float) $ptf, 0.5);

        // Les noeuds RBM ont ete crees.
        $this->assertSame(6, Pilier::query()->count());
        $this->assertGreaterThan(0, PapLigneBudgetaire::query()->where('niveau', 'produit')->count());
    }

    public function test_simulation_does_not_write_pap_data(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();

        $import = $service->run($service->rowsFromDataset(), $exercice, 'simulation', $this->admin());

        $this->assertSame('simule', $import->statut);
        $this->assertSame(0, PapLigneBudgetaire::query()->count());
        $this->assertSame(0, Pilier::query()->count());
        $this->assertGreaterThan(0, $import->nb_creations);
    }

    public function test_replacement_historizes_then_restores(): void
    {
        $service = app(PapImportService::class);
        $admin = $this->admin();
        $exercice = $this->exercice();

        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);
        $initialCount = PapLigneBudgetaire::query()->count();
        $this->assertGreaterThan(0, $initialCount);

        // Nouveau remplacement avec un jeu reduit -> snapshot de l'ancien.
        $rows = [
            $this->row('201', 'Pilier unique', 'pilier', null, 1000, 1000, 0, 0),
        ];
        $replace = $service->run($rows, $exercice, 'remplacement', $admin);
        $this->assertSame('valide', $replace->statut);
        $this->assertSame(1, PapLigneBudgetaire::query()->count());
        $this->assertSame($initialCount, $replace->nb_suppressions);
        $this->assertNotEmpty($replace->snapshot);

        // Restauration.
        $restore = $service->restore($replace, $admin);
        $this->assertSame('valide', $restore->statut);
        $this->assertSame($initialCount, PapLigneBudgetaire::query()->count());
    }

    public function test_missing_parent_blocks_import(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();

        $rows = [
            $this->row('20111', 'Produit orphelin', 'produit', '2011', 100, 100, 0, 0),
        ];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('echoue', $import->statut);
        $this->assertGreaterThan(0, $import->nb_erreurs);
        $this->assertSame(0, PapLigneBudgetaire::query()->count());
    }

    public function test_negative_amount_blocks_import(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();

        $rows = [$this->row('201', 'Pilier negatif', 'pilier', null, -100, 0, 0, 0)];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('echoue', $import->statut);
        $this->assertSame(0, PapLigneBudgetaire::query()->count());
    }

    public function test_subtotal_incoherence_is_a_warning_not_blocking(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();

        $rows = [
            $this->row('201', 'Pilier', 'pilier', null, 1000, 1000, 0, 0),
            $this->row('2011', 'Axe', 'axe', '201', 700, 700, 0, 1), // 700 != 1000 -> avertissement
        ];
        $import = $service->run($rows, $exercice, 'creation', $this->admin());

        $this->assertSame('valide', $import->statut);
        $this->assertGreaterThan(0, $import->nb_avertissements);
        $this->assertSame(2, PapLigneBudgetaire::query()->count());
    }

    public function test_other_exercise_data_is_not_deleted_on_replacement(): void
    {
        $service = app(PapImportService::class);
        $admin = $this->admin();
        $exercice2025 = $this->exercice(2025);
        $exercice2026 = $this->exercice(2026);

        PapLigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice2025->id,
            'code' => '999', 'libelle' => 'Ligne 2025', 'niveau' => 'pilier',
            'montant_total' => 500, 'montant_ceeac' => 500, 'montant_ptf' => 0, 'ordre' => 0, 'source' => 'TEST',
        ]);

        $service->run($service->rowsFromDataset(), $exercice2026, 'remplacement', $admin);

        $this->assertDatabaseHas('pap_lignes_budgetaires', [
            'exercice_budgetaire_id' => $exercice2025->id,
            'code' => '999',
        ]);
    }

    public function test_index_requires_permission_and_admin_can_view(): void
    {
        $this->exercice();

        $simple = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $simple->assignRole('agent_initiateur');
        $this->actingAs($simple)->get(route('rbm.pap-import.index'))->assertForbidden();

        $this->actingAs($this->admin())->get(route('rbm.pap-import.index'))->assertOk();
    }

    public function test_replacement_requires_confirmation_via_http(): void
    {
        $exercice = $this->exercice();
        $admin = $this->admin();

        // Sans confirmation -> erreur de validation.
        $this->actingAs($admin)
            ->from(route('rbm.pap-import.index'))
            ->post(route('rbm.pap-import.store'), [
                'exercice_budgetaire_id' => $exercice->id,
                'version' => '1.0',
                'mode' => 'remplacement',
                'source' => 'document',
            ])
            ->assertSessionHasErrors('confirmation');

        $this->assertSame(0, PapImport::query()->where('statut', 'valide')->count());
    }

    public function test_template_download_is_ok(): void
    {
        $this->actingAs($this->admin())
            ->get(route('rbm.pap-import.template.csv'))
            ->assertOk();
    }

    public function test_programmation_page_renders(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation'))
            ->assertOk()
            ->assertSee('22 365 281 000');
    }

    public function test_dashboard_renders_with_pap_section(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Plan Annuel de Performance');
    }

    public function test_programmation_exports_excel_and_pdf(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation.export.excel', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdf = $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation.export.pdf', ['exercice_budgetaire_id' => $exercice->id]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', strtolower((string) $pdf->headers->get('content-type')));

        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation.export.source', ['exercice_budgetaire_id' => $exercice->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_programmation_niveau_filter_shows_flat_levels(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        // Filtre "axe" : 21 axes attendus, et un libellé de pilier ne doit pas apparaître en ligne de tableau.
        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation', ['exercice_budgetaire_id' => $exercice->id, 'niveau' => 'axe']))
            ->assertOk()
            ->assertSee('Ventilation filtrée')
            ->assertSee('2011');

        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation', ['exercice_budgetaire_id' => $exercice->id, 'niveau' => 'produit']))
            ->assertOk()
            ->assertSee('20111');
    }

    public function test_programmation_source_filter_renders(): void
    {
        $service = app(PapImportService::class);
        $exercice = $this->exercice();
        $admin = $this->admin();
        $service->run($service->rowsFromDataset(), $exercice, 'remplacement', $admin);

        // Source = PTF uniquement : seul l'axe 2031 (290k CEEAC + 6.6Md PTF) est mixte ; un produit purement PTF existe (20231).
        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation', ['exercice_budgetaire_id' => $exercice->id, 'niveau' => 'produit', 'source' => 'ptf']))
            ->assertOk()
            ->assertSee('Ventilation filtrée')
            ->assertSee('20231');

        // Source = mixte : combine niveau axe + source.
        $this->actingAs($admin)
            ->get(route('rbm.pap-import.programmation', ['exercice_budgetaire_id' => $exercice->id, 'niveau' => 'tous', 'source' => 'mixte']))
            ->assertOk()
            ->assertSee('Ventilation filtrée');
    }
}
