<?php

namespace Tests\Feature\Engagements;

use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\User;
use Database\Seeders\EngagementModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private ExerciceBudgetaire $exercice;

    private LigneBudgetaire $ligne;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EngagementModuleSeeder::class);

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
            'numero' => 'ENG-2026-AB12CD34', 'exercice_budgetaire_id' => $this->exercice->id,
            'ligne_budgetaire_id' => $this->ligne->id, 'type_engagement' => 'achat',
            'beneficiaire_type' => 'autre', 'beneficiaire_autre' => 'Bénéficiaire test', 'objet' => 'Achat',
            'montant' => 1000000, 'statut' => 'approuve', 'created_by' => $user->id, 'approved_at' => now(),
        ]);
        $engagement->imputations()->create(['ligne_budgetaire_id' => $this->ligne->id, 'montant_impute' => 1000000]);

        return $engagement;
    }

    public function test_certificat_pdf(): void
    {
        $user = $this->admin();
        $engagement = $this->engagementApprouve($user);

        $this->actingAs($user)->get(route('engagements.documents.certificat', $engagement))->assertOk();
    }

    public function test_registre_et_bordereau_pdf_excel(): void
    {
        $user = $this->admin();
        $this->engagementApprouve($user);

        $this->actingAs($user)->get(route('engagements.documents.registre.pdf'))->assertOk();
        $this->actingAs($user)->get(route('engagements.documents.registre.excel'))->assertOk();
        $this->actingAs($user)->get(route('engagements.documents.bordereau.pdf'))->assertOk();
    }

    public function test_etat_credits_reserves_pdf_excel(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->get(route('engagements.documents.credits.pdf'))->assertOk();
        $this->actingAs($user)->get(route('engagements.documents.credits.excel'))->assertOk();
    }

    public function test_tableau_de_bord_accessible(): void
    {
        $user = $this->admin();
        $this->engagementApprouve($user);

        $this->actingAs($user)->get(route('engagements.dashboard'))->assertOk();
    }

    public function test_export_refuse_sans_permission(): void
    {
        $user = User::factory()->create(); // aucun rôle

        $this->actingAs($user)->get(route('engagements.documents.registre.pdf'))->assertForbidden();
    }
}
