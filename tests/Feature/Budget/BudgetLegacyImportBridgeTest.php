<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutImportBudget;
use App\Models\BudgetImport;
use App\Models\ExerciceBudgetaire;
use App\Models\ImportBudget;
use App\Models\User;
use App\Services\Budget\BudgetLegacyImportBridge;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetLegacyImportBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_migrate_creates_budget_import_and_links_both_ways(): void
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        $legacy = ImportBudget::query()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'fichier_original' => 'legacy.csv',
            'chemin_fichier' => 'imports-budget/legacy.csv',
            'statut' => StatutImportBudget::EnValidation,
            'total_lignes' => 10,
            'lignes_valides' => 9,
            'lignes_rejetees' => 1,
            'imported_by' => User::factory()->create()->id,
            'imported_at' => now(),
        ]);

        $bridge = app(BudgetLegacyImportBridge::class);
        $modern = $bridge->migrate($legacy);

        $this->assertInstanceOf(BudgetImport::class, $modern);
        $this->assertSame('legacy_csv', $modern->mode);
        $this->assertSame($legacy->id, $modern->legacy_import_budget_id);
        $this->assertSame($modern->id, $legacy->fresh()->migrated_budget_import_id);
    }

    public function test_find_or_migrate_is_idempotent(): void
    {
        $exercice = ExerciceBudgetaire::factory()->create();
        $legacy = ImportBudget::query()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'fichier_original' => 'legacy.csv',
            'chemin_fichier' => 'imports-budget/legacy.csv',
            'statut' => StatutImportBudget::Valide,
            'total_lignes' => 5,
            'lignes_valides' => 5,
            'lignes_rejetees' => 0,
            'imported_by' => User::factory()->create()->id,
            'imported_at' => now(),
        ]);

        $bridge = app(BudgetLegacyImportBridge::class);
        $first = $bridge->findOrMigrate($legacy);
        $second = $bridge->findOrMigrate($legacy->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, BudgetImport::query()->where('legacy_import_budget_id', $legacy->id)->count());
    }
}
