<?php

namespace Tests\Feature\Rbm;

use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RbmImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_download_rbm_import_template(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('administrateur_budget');

        $this->actingAs($user)
            ->get(route('rbm.import.template'))
            ->assertOk();
    }

    public function test_user_without_import_permission_cannot_access_import(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('agent_initiateur');

        $this->actingAs($user)
            ->get(route('rbm.import.index'))
            ->assertForbidden();
    }

    public function test_user_can_import_valid_rbm_matrix_csv(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('administrateur_budget');

        TypeIndicateur::query()->create(['code' => 'QUANT', 'libelle' => 'Quantitatif', 'statut' => 'actif']);
        UniteMesure::query()->create(['code' => 'PCT', 'libelle' => 'Pourcentage', 'statut' => 'actif']);

        $csv = implode(',', [
            'Code pilier', 'Libelle pilier', 'Code axe', 'Libelle axe',
            'Code produit', 'Libelle produit', 'Code sous-produit', 'Libelle sous-produit',
            'Code activite', 'Libelle activite', 'Code tache', 'Libelle tache',
            'Code indicateur', 'Libelle indicateur', 'Code type indicateur', 'Code unite mesure',
            'Ordre', 'Statut',
        ])."\n";

        $csv .= implode(',', [
            'P-IMP', 'Pilier import', 'A-IMP', 'Axe import',
            'PR-IMP', 'Produit import', 'SP-IMP', 'Sous-produit import',
            'AC-IMP', 'Activite import', 'T-IMP', 'Tache import',
            'IND-IMP', 'Indicateur import', 'QUANT', 'PCT',
            '1', 'actif',
        ])."\n";

        $file = UploadedFile::fake()->createWithContent('import-rbm.csv', $csv);

        $this->actingAs($user)
            ->post(route('rbm.import.store'), ['fichier' => $file])
            ->assertOk()
            ->assertSee(__('Import réussi'));

        $this->assertDatabaseHas('piliers', ['code' => 'P-IMP']);
        $this->assertDatabaseHas('taches', ['code' => 'T-IMP']);
        $this->assertDatabaseHas('indicateurs', ['code' => 'IND-IMP']);
    }

    public function test_import_rejects_rows_with_unknown_type_indicateur(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('administrateur_budget');

        UniteMesure::query()->create(['code' => 'PCT', 'libelle' => 'Pourcentage', 'statut' => 'actif']);

        $csv = "Code pilier,Libelle pilier,Code axe,Libelle axe,Code produit,Libelle produit,Code sous-produit,Libelle sous-produit,Code activite,Libelle activite,Code tache,Libelle tache,Code indicateur,Libelle indicateur,Code type indicateur,Code unite mesure,Ordre,Statut\n";
        $csv .= "P1,Pilier,A1,Axe,PR1,Produit,SP1,Sous-produit,AC1,Activite,T1,Tache,IND1,Indicateur,INCONNU,PCT,1,actif\n";

        $file = UploadedFile::fake()->createWithContent('import-rbm.csv', $csv);

        $this->actingAs($user)
            ->post(route('rbm.import.store'), ['fichier' => $file])
            ->assertOk()
            ->assertSee(__('Import rejeté'));

        $this->assertDatabaseMissing('piliers', ['code' => 'P1']);
    }
}
