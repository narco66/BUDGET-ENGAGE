<?php

namespace Tests\Feature\Engagements;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\PieceJustificative;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PieceJustificativeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_upload_piece_on_own_editable_engagement(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $response = $this->actingAs($user)->post(
            route('engagements.pieces-justificatives.store', $engagement),
            [
                'piece' => UploadedFile::fake()->create('devis.pdf', 256, 'application/pdf'),
                'type_piece' => 'devis',
            ]
        );

        $response->assertRedirect(route('engagements.show', $engagement, absolute: false));

        $piece = PieceJustificative::query()->first();
        $this->assertNotNull($piece);
        $this->assertSame($engagement->id, $piece->engagement_id);
        $this->assertSame($user->id, $piece->uploaded_by);
        $this->assertNotNull($piece->checksum);
        $this->assertTrue(Storage::disk('private')->exists($piece->chemin));
    }

    public function test_user_cannot_upload_piece_on_non_editable_engagement(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Soumis,
        ]);

        $this->actingAs($user)->post(
            route('engagements.pieces-justificatives.store', $engagement),
            ['piece' => UploadedFile::fake()->create('facture.pdf', 128, 'application/pdf')]
        )->assertForbidden();
    }

    public function test_user_can_download_piece_linked_to_engagement(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('engagements/1/pieces/test.pdf', 'dummy');

        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $piece = PieceJustificative::query()->create([
            'engagement_id' => $engagement->id,
            'type_piece' => 'facture',
            'nom_original' => 'test.pdf',
            'nom_stocke' => 'test.pdf',
            'chemin' => 'engagements/1/pieces/test.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'taille' => 5,
            'version' => 1,
            'checksum' => hash('sha256', 'dummy'),
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('engagements.pieces-justificatives.download', [$engagement, $piece]))
            ->assertOk();
    }

    public function test_download_returns_not_found_if_piece_not_linked_to_engagement(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('engagements/1/pieces/test.pdf', 'dummy');

        $user = User::factory()->create();
        $user->assignRole('agent_initiateur');

        $engagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $otherEngagement = Engagement::factory()->create([
            'created_by' => $user->id,
            'statut' => StatutEngagement::Brouillon,
        ]);

        $piece = PieceJustificative::query()->create([
            'engagement_id' => $otherEngagement->id,
            'type_piece' => 'facture',
            'nom_original' => 'test.pdf',
            'nom_stocke' => 'test.pdf',
            'chemin' => 'engagements/1/pieces/test.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'taille' => 5,
            'version' => 1,
            'checksum' => hash('sha256', 'dummy'),
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('engagements.pieces-justificatives.download', [$engagement, $piece]))
            ->assertNotFound();
    }
}
