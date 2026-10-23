<?php

namespace Tests\Feature\Engagements;

use App\Models\Engagement;
use App\Models\EngagementType;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\SourceFinancement;
use App\Models\User;
use Database\Seeders\EngagementModuleSeeder;
use Database\Seeders\EngagementTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementTypePolymorphismTest extends TestCase
{
    use RefreshDatabase;

    private ExerciceBudgetaire $exercice;

    private LigneBudgetaire $ligne;

    private SourceFinancement $source;

    private Fournisseur $fournisseur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EngagementModuleSeeder::class);
        $this->seed(EngagementTypeSeeder::class);

        $this->exercice = ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => 'ouvert', 'est_actif' => true,
        ]);
        $nature = NatureDepense::query()->create(['code' => 'NAT', 'libelle' => 'Nature', 'statut' => 'actif']);
        $this->source = SourceFinancement::query()->create(['code' => 'SRC', 'libelle' => 'Source', 'statut' => 'actif']);
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

    private function payload(array $override = [], array $imputationOverride = []): array
    {
        return array_merge([
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_engagement' => 'achat',
            'beneficiaire_type' => 'fournisseur',
            'beneficiaire_id' => $this->fournisseur->id,
            'objet' => 'Engagement typé test',
            'imputations' => [array_merge([
                'ligne_budgetaire_id' => $this->ligne->id,
                'montant_impute' => 1000000,
                'source_financement_id' => $this->source->id,
                'centre_cout' => 'CC-1',
                'centre_responsabilite' => 'CR-1',
            ], $imputationOverride)],
        ], $override);
    }

    public function test_creation_engagement_type_persiste_eav_et_dimensions(): void
    {
        $user = $this->admin();
        $typeJur = EngagementType::query()->where('code', 'JUR')->firstOrFail();

        $this->actingAs($user)->post(route('engagements.store'), $this->payload([
            'engagement_type_id' => $typeJur->id,
            'type_valeurs' => ['contrat' => 'CTR-2026-007', 'duree' => '12'],
        ]))->assertRedirect(route('engagements.index', absolute: false));

        $engagement = Engagement::query()->latest('id')->first();
        $this->assertNotNull($engagement);
        $this->assertSame($typeJur->id, $engagement->engagement_type_id);
        $this->assertSame('manuel', $engagement->mode_creation);
        $this->assertSame('CTR-2026-007', $engagement->typeValeurs()->where('cle', 'contrat')->value('valeur'));

        $imputation = $engagement->imputations()->first();
        $this->assertSame($this->source->id, $imputation->source_financement_id);
        $this->assertSame('CC-1', $imputation->centre_cout);
    }

    public function test_champ_type_obligatoire_manquant_est_rejete(): void
    {
        $user = $this->admin();
        $typeJur = EngagementType::query()->where('code', 'JUR')->firstOrFail();

        // Le type JUR exige le champ « contrat » (requis) : absent → rejet.
        $this->actingAs($user)->post(route('engagements.store'), $this->payload([
            'engagement_type_id' => $typeJur->id,
            'type_valeurs' => ['duree' => '6'],
        ]))->assertSessionHasErrors('type_valeurs');
    }

    public function test_engagement_sans_type_reste_possible(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('engagements.store'), $this->payload())
            ->assertRedirect(route('engagements.index', absolute: false));

        $engagement = Engagement::query()->latest('id')->first();
        $this->assertNull($engagement->engagement_type_id);
        $this->assertSame(0, $engagement->typeValeurs()->count());
    }
}
