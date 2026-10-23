<?php

namespace Tests\Feature\Engagements;

use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\User;
use App\Services\Engagement\EngagementGedService;
use Database\Seeders\EngagementModuleSeeder;
use Database\Seeders\GedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EngagementTransmissionTest extends TestCase
{
    use RefreshDatabase;

    private ExerciceBudgetaire $exercice;

    private LigneBudgetaire $ligne;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EngagementModuleSeeder::class);
        $this->seed(GedSeeder::class);
        Storage::fake('local');

        $this->exercice = ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => 'ouvert', 'est_actif' => true,
        ]);
        $nature = NatureDepense::query()->create(['code' => 'NAT', 'libelle' => 'Nature', 'statut' => 'actif']);
        $this->ligne = LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $this->exercice->id, 'nature_depense_id' => $nature->id,
            'code' => 'LB-001', 'libelle' => 'Ligne', 'montant_vote' => 5000000, 'montant_revise' => 5000000,
            'montant_engage' => 1000000, 'statut' => 'active',
        ]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        return $user;
    }

    private function engagementApprouve(User $user): Engagement
    {
        $engagement = Engagement::query()->create([
            'numero' => 'ENG-2026-'.substr(uniqid(), -6), 'exercice_budgetaire_id' => $this->exercice->id,
            'ligne_budgetaire_id' => $this->ligne->id, 'type_engagement' => 'achat',
            'beneficiaire_type' => 'autre', 'beneficiaire_autre' => 'Test', 'objet' => 'Achat',
            'montant' => 1000000, 'statut' => 'approuve', 'created_by' => $user->id, 'approved_at' => now(),
        ]);
        $engagement->imputations()->createMany([
            ['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 500000],
            ['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 500000],
        ]);

        return $engagement;
    }

    public function test_transmission_genere_les_liquidations_et_marque_l_engagement(): void
    {
        $user = $this->admin();
        $engagement = $this->engagementApprouve($user);

        $this->actingAs($user)->post(route('engagements.transmettre-liquidation', $engagement))->assertRedirect();

        $engagement->refresh();
        $this->assertNotNull($engagement->transmis_liquidation_at);
        $this->assertSame($user->id, $engagement->transmis_par);
        $this->assertSame(2, $engagement->liquidations()->count());
    }

    public function test_double_transmission_interdite(): void
    {
        $user = $this->admin();
        $engagement = $this->engagementApprouve($user);

        $this->actingAs($user)->post(route('engagements.transmettre-liquidation', $engagement))->assertRedirect();

        $this->actingAs($user)
            ->post(route('engagements.transmettre-liquidation', $engagement->fresh()))
            ->assertSessionHasErrors('transmission');

        $this->assertSame(2, $engagement->liquidations()->count());
    }

    public function test_transmission_reprend_les_pieces_ged(): void
    {
        $user = $this->admin();
        $engagement = $this->engagementApprouve($user);
        app(EngagementGedService::class)->deposer($engagement, UploadedFile::fake()->create('contrat.pdf', 16, 'application/pdf'), $user);

        $this->actingAs($user)->post(route('engagements.transmettre-liquidation', $engagement))->assertRedirect();

        $liquidation = $engagement->liquidations()->first();
        $this->assertSame(1, $liquidation->gedAttachments()->count());
    }

    public function test_transmission_refusee_sans_permission(): void
    {
        $owner = $this->admin();
        $engagement = $this->engagementApprouve($owner);

        $user = User::factory()->create(); // aucun rôle

        $this->actingAs($user)->post(route('engagements.transmettre-liquidation', $engagement))->assertForbidden();
    }
}
