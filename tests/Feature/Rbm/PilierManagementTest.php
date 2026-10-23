<?php

namespace Tests\Feature\Rbm;

use App\Models\Pilier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PilierManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_with_rbm_permission_can_create_pilier(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('administrateur_budget');

        $response = $this->actingAs($user)->post(route('rbm.piliers.store'), [
            'code' => 'PIL001',
            'libelle' => 'Pilier test',
            'description' => 'Description test',
            'ordre' => 1,
            'statut' => 'actif',
        ]);

        $response->assertRedirect(route('rbm.piliers.index'));
        $this->assertDatabaseHas('piliers', ['code' => 'PIL001']);
    }

    public function test_user_without_rbm_permission_cannot_access_piliers_index(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('agent_initiateur');

        $this->actingAs($user)
            ->get(route('rbm.piliers.index'))
            ->assertForbidden();
    }

    public function test_pilier_code_must_be_unique(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('administrateur_general');

        Pilier::factory()->create(['code' => 'DUP001']);

        $this->actingAs($user)->post(route('rbm.piliers.store'), [
            'code' => 'DUP001',
            'libelle' => 'Autre pilier',
            'ordre' => 1,
            'statut' => 'actif',
        ])->assertSessionHasErrors('code');
    }
}
