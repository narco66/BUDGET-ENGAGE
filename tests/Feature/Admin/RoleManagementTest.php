<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('administrateur_general');

        $role = Role::query()->where('name', 'agent_initiateur')->firstOrFail();

        $response = $this->actingAs($admin)->patch(route('admin.roles.update', $role), [
            'permissions' => ['engagements.create', 'engagements.submit'],
        ]);

        $response->assertRedirect(route('admin.roles.index', absolute: false));
        $this->assertTrue($role->fresh()->hasPermissionTo('engagements.create'));
        $this->assertTrue($role->fresh()->hasPermissionTo('engagements.submit'));
    }

    public function test_user_without_roles_manage_permission_cannot_access_roles_screen(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('agent_initiateur');

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }
}
