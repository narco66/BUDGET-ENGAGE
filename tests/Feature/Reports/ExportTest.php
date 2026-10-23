<?php

namespace Tests\Feature\Reports;

use App\Models\Engagement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_download_engagement_pdf(): void
    {
        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('engagements.export.pdf', $engagement))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_with_reports_permission_can_export_excel(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        Engagement::factory()->count(2)->create([
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('engagements.export.excel'))
            ->assertOk();

        Excel::assertDownloaded('engagements-export.xlsx');
    }

    public function test_user_with_reports_permission_can_export_csv(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_budget');

        Engagement::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('engagements.export.csv'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
