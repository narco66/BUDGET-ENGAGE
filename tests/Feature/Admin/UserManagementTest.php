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
