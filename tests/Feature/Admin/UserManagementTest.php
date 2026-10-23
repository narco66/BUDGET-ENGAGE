<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('administrateur_general');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'telephone' => null,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'service_id' => null,
            'role' => 'agent_initiateur',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'jean.dupont@example.com']);
        $this->assertTrue(User::query()->where('email', 'jean.dupont@example.com')->first()?->hasRole('agent_initiateur'));
    }

    public function test_admin_can_open_users_index_and_update_user(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('administrateur_general');

        $target = User::factory()->create([
            'email' => 'target.user@example.com',
            'email_verified_at' => now(),
            'statut' => 'actif',
        ]);
        $target->assignRole('agent_initiateur');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $target), [
            'name' => 'Target',
            'prenom' => 'Updated',
            'email' => 'target.user.updated@example.com',
            'telephone' => '+237699000000',
            'service_id' => null,
            'role' => 'responsable_service',
            'statut' => 'inactif',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('admin.users.index', absolute: false));
        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'email' => 'target.user.updated@example.com',
            'statut' => 'inactif',
        ]);
        $this->assertTrue($target->fresh()?->hasRole('responsable_service'));
    }

    public function test_admin_can_open_user_detail_with_history_timeline(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('administrateur_general');

        $target = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $target->assignRole('agent_initiateur');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSee('Détail utilisateur');
    }

    public function test_admin_can_export_user_timeline_csv(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('administrateur_general');

        $target = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.timeline.export.csv', ['user' => $target, 'period' => 30]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_non_privileged_user_cannot_open_user_creation_form(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('agent_initiateur');

        $this->actingAs($user)
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }
}
