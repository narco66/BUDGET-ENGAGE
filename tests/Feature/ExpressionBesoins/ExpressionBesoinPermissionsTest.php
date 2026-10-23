<?php

namespace Tests\Feature\ExpressionBesoins;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpressionBesoinPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_grants_expression_besoins_access_to_initiator(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $this->actingAs($user)
            ->get(route('expression-besoins.besoins.index'))
            ->assertOk();
    }
}
