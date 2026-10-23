<?php

namespace Tests\Feature\ChaineDepense;

use App\Models\Engagement;
use App\Models\User;
use App\Support\ChaineDepense\DossierWorkflowPresenter;
use Database\Seeders\ExpressionBesoinSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChaineDepenseViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_engagements_index_uses_chaine_components(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)
            ->get(route('engagements.index'))
            ->assertOk()
            ->assertSee('data-table', false)
            ->assertSee('page-shell', false);
    }

    public function test_execution_dashboard_uses_workflow_cockpit_layout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)
            ->get(route('execution.dashboard'))
            ->assertOk()
            ->assertSee('kpi-card', false)
            ->assertSee('data-table', false);
    }

    public function test_liquidations_index_uses_chaine_components(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)
            ->get(route('liquidations.index'))
            ->assertOk()
            ->assertSee('page-shell', false)
            ->assertSee('data-table', false);
    }

    public function test_ordonnancements_and_paiements_index_render(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)->get(route('ordonnancements.index'))->assertOk();
        $this->actingAs($user)->get(route('paiements.index'))->assertOk();
    }

    public function test_module_dashboards_render(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)->get(route('liquidations.dashboard'))->assertOk()->assertSee('kpi-card', false);
        $this->actingAs($user)->get(route('ordonnancements.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('paiements.dashboard'))->assertOk();
    }

    public function test_expression_besoins_views_use_chaine_components(): void
    {
        $this->seed(ExpressionBesoinSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)->get(route('expression-besoins.dashboard'))->assertOk()->assertSee('kpi-card', false);
        $this->actingAs($user)->get(route('expression-besoins.besoins.index'))->assertOk()->assertSee('page-shell', false);
        $this->actingAs($user)->get(route('expression-besoins.besoins.create'))->assertOk()->assertSee('page-shell', false);
        $this->actingAs($user)->get(route('expression-besoins.consolidation'))->assertOk()->assertSee('page-shell', false);
        $this->actingAs($user)->get(route('expression-besoins.rapprochement'))->assertOk()->assertSee('page-shell', false);
    }

    public function test_lot5_views_use_chaine_components(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        $this->actingAs($user)->get(route('engagements.dashboard'))->assertOk()->assertSee('kpi-card', false);
        $this->actingAs($user)->get(route('engagements.create'))->assertOk()->assertSee('page-shell', false);
        $this->actingAs($user)->get(route('liquidations.index'))->assertOk();
    }

    public function test_dossier_workflow_presenter_builds_engagement_step(): void
    {
        $engagement = Engagement::factory()->create(['numero' => 'ENG-2026-0001']);

        $steps = DossierWorkflowPresenter::fromEngagement($engagement);

        $this->assertTrue(collect($steps)->contains(fn ($s) => $s['key'] === 'engagement'));
    }
}
