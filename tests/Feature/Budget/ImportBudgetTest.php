<?php

namespace Tests\Feature\Budget;

use App\Models\BudgetImport;
use App\Models\ExerciceBudgetaire;
use App\Models\ImportBudget;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_legacy_template_redirects_to_nomenclature(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        $this->actingAs($user)
            ->get(route('budget.imports.template'))
            ->assertRedirect(route('budget.nomenclature.template'));
    }

    public function test_user_can_upload_budget_import_file_and_bridge_to_nomenclature(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        $exercice = ExerciceBudgetaire::factory()->create();
        $csv = "Exercice,Code departement,Departement,Code direction,Direction,Code service,Service,Code ligne budgetaire,Libelle ligne budgetaire,Nature de depense,Source de financement,Montant vote,Montant revise\n";
        $csv .= "{$exercice->annee},DB,Direction Budget,DIR,Direction Budget,SER,Service Budget,LIG-001,Ligne test,Fonctionnement,Fonds propre,1000000,1000000\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $response = $this->actingAs($user)->post(route('budget.imports.store'), [
            'exercice_budgetaire_id' => $exercice->id,
            'fichier' => $file,
        ]);

        $import = ImportBudget::query()->first();
        $this->assertNotNull($import);
        $this->assertNotNull($import->migrated_budget_import_id);
        $this->assertNotNull(BudgetImport::query()->find($import->migrated_budget_import_id));
        $response->assertRedirect(route('budget.nomenclature.index', absolute: false));
    }
}
