<?php

namespace Tests\Feature\Engagements;

use App\Models\EngagementType;
use App\Models\User;
use Database\Seeders\EngagementModuleSeeder;
use Database\Seeders\EngagementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EngagementModuleSeeder::class);
    }

    public function test_admin_peut_creer_un_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        $this->actingAs($user)->post(route('engagements.types.store'), [
            'code' => 'TST', 'libelle' => 'Type test',
            'champs_specifiques' => '[{"cle":"contrat","libelle":"Contrat","type":"text","requis":true}]',
            'documents_obligatoires' => 'contrat, marche',
            'est_actif' => '1',
        ])->assertRedirect(route('engagements.types.index', absolute: false));

        $type = EngagementType::query()->where('code', 'TST')->first();
        $this->assertNotNull($type);
        $this->assertIsArray($type->champs_specifiques);
        $this->assertCount(1, $type->champs_specifiques);
        $this->assertSame(['contrat', 'marche'], $type->documents_obligatoires);
    }

    public function test_non_admin_ne_peut_pas_creer_de_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('agent_initiateur'); // engagement.view/create/update mais pas engagement.admin

        $this->actingAs($user)->post(route('engagements.types.store'), [
            'code' => 'XXX', 'libelle' => 'Refusé',
        ])->assertForbidden();
    }

    public function test_le_seeder_cree_les_12_types(): void
    {
        $this->seed(EngagementTypeSeeder::class);

        $this->assertSame(12, EngagementType::query()->count());
    }
}
