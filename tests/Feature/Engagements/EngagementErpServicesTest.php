<?php

namespace Tests\Feature\Engagements;

use App\Models\Engagement;
use App\Models\EngagementReservationBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\SourceFinancement;
use App\Models\User;
use App\Services\Engagement\EngagementReservationService;
use Database\Seeders\EngagementModuleSeeder;
use Database\Seeders\GedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EngagementErpServicesTest extends TestCase
{
    use RefreshDatabase;

    private ExerciceBudgetaire $exercice;

    private LigneBudgetaire $ligne;

    private Fournisseur $fournisseur;

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
        SourceFinancement::query()->create(['code' => 'SRC', 'libelle' => 'Source', 'statut' => 'actif']);
        $this->ligne = LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $this->exercice->id, 'nature_depense_id' => $nature->id,
            'code' => 'LB-001', 'libelle' => 'Ligne', 'montant_vote' => 5000000, 'montant_revise' => 5000000,
            'statut' => 'active',
        ]);
        $this->fournisseur = Fournisseur::query()->create(['raison_sociale' => 'ACME', 'statut' => 'actif']);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        return $user;
    }

    private function creerEngagement(string $statut = 'brouillon'): Engagement
    {
        $engagement = Engagement::query()->create([
            'numero' => 'ENG-2026-'.substr(uniqid(), -6), 'exercice_budgetaire_id' => $this->exercice->id,
            'ligne_budgetaire_id' => $this->ligne->id, 'type_engagement' => 'achat',
            'beneficiaire_type' => 'autre', 'beneficiaire_autre' => 'Test', 'objet' => 'Test',
            'montant' => 1000000, 'statut' => $statut, 'created_by' => User::factory()->create()->id,
        ]);
        $engagement->imputations()->create(['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 1000000]);

        return $engagement;
    }

    public function test_ledger_reserve_puis_libere(): void
    {
        $engagement = $this->creerEngagement('approuve');
        $service = app(EngagementReservationService::class);

        $service->recordForEngagement($engagement);
        $this->assertSame(1, EngagementReservationBudgetaire::query()
            ->where('engagement_id', $engagement->id)->where('sens', 'reservation')->where('statut', 'active')->count());

        // Idempotent : un second appel ne crée pas de doublon.
        $service->recordForEngagement($engagement);
        $this->assertSame(1, EngagementReservationBudgetaire::query()
            ->where('engagement_id', $engagement->id)->where('sens', 'reservation')->count());

        $service->releaseForEngagement($engagement->fresh());
        $this->assertSame(0, EngagementReservationBudgetaire::query()
            ->where('engagement_id', $engagement->id)->where('sens', 'reservation')->where('statut', 'active')->count());
        $this->assertSame(1, EngagementReservationBudgetaire::query()
            ->where('engagement_id', $engagement->id)->where('sens', 'liberation')->count());
    }

    public function test_modification_cree_une_version(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('engagements.store'), [
            'exercice_budgetaire_id' => $this->exercice->id, 'type_engagement' => 'achat',
            'beneficiaire_type' => 'fournisseur', 'beneficiaire_id' => $this->fournisseur->id, 'objet' => 'Initial',
            'imputations' => [['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 800000]],
        ])->assertRedirect();

        $engagement = Engagement::query()->latest('id')->first();

        $this->actingAs($user)->patch(route('engagements.update', $engagement), [
            'exercice_budgetaire_id' => $this->exercice->id, 'type_engagement' => 'achat',
            'beneficiaire_type' => 'fournisseur', 'beneficiaire_id' => $this->fournisseur->id, 'objet' => 'Modifié',
            'imputations' => [['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 900000]],
        ])->assertRedirect();

        $this->assertSame(1, $engagement->versions()->count());
        $this->assertSame('Modifié', $engagement->refresh()->objet);
    }

    public function test_depot_piece_dans_la_ged(): void
    {
        $user = $this->admin();
        $engagement = $this->creerEngagement('brouillon');

        $this->actingAs($user)->post(route('engagements.ged.store', $engagement), [
            'fichier' => UploadedFile::fake()->create('contrat.pdf', 32, 'application/pdf'),
            'titre' => 'Contrat',
        ])->assertRedirect();

        $this->assertSame(1, $engagement->gedAttachments()->count());
        $doc = $engagement->gedDocuments()->first();
        $this->assertSame('engagement', $doc->module_source);
    }
}
