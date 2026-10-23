<?php

namespace Tests\Feature\DesignSystem;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_landing_page_uses_design_system_classes(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('BUDGET-ENGAGE')
            ->assertSee('btn btn-primary', false);
    }

    public function test_login_page_uses_guest_layout_and_buttons(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('btn btn-primary', false);
    }

    public function test_budget_lignes_index_uses_ds_components(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)
            ->get(route('budget.lignes.index'))
            ->assertOk()
            ->assertSee('data-table', false)
            ->assertSee('page-shell', false);
    }

    public function test_money_formatter_formats_fcfa(): void
    {
        $this->assertSame('1 000 000 FCFA', \App\Support\MoneyFormatter::format(1_000_000));
    }
}
