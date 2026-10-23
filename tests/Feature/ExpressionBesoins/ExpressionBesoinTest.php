<?php

namespace Tests\Feature\ExpressionBesoins;

use App\Enums\StatutBesoin;
use App\Enums\StatutEngagement;
use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\ExerciceBudgetaire;
use App\Models\ExpressionBesoin;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SourceFinancement;
use App\Models\SousProduit;
use App\Models\User;
use Database\Seeders\ExpressionBesoinSeeder;
use Database\Seeders\GedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpressionBesoinTest extends TestCase
{
    use RefreshDatabase;

    private ExerciceBudgetaire $exercice;

    private LigneBudgetaire $ligne;

    private SourceFinancement $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(GedSeeder::class);
        $this->seed(ExpressionBesoinSeeder::class);
        Storage::fake('local');

        $this->exercice = ExerciceBudgetaire::query()->create([
            'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
            'statut' => 'ouvert', 'est_actif' => true,
        ]);
        $nature = NatureDepense::query()->create(['code' => 'NAT', 'libelle' => 'Nature', 'statut' => 'actif']);
        $this->source = SourceFinancement::query()->create(['code' => 'SRC', 'libelle' => 'Source', 'statut' => 'actif']);
        $this->ligne = LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $this->exercice->id, 'nature_depense_id' => $nature->id,
            'code' => 'LB-001', 'libelle' => 'Ligne', 'montant_vote' => 1000000, 'montant_revise' => 1000000,
            'statut' => 'active',
        ]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        return $user;
    }

    private function ligne(array $override = []): array
    {
        return array_merge([
            'designation' => 'Ordinateur', 'quantite' => 2, 'unite' => 'u', 'prix_unitaire' => 300000,
            'ligne_budgetaire_id' => $this->ligne->id, 'source_financement_id' => $this->source->id,
        ], $override);
    }

    public function test_creation_besoin_hors_pap(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap',
            'categorie_hors_pap' => 'informatique',
            'objet' => 'Achat matériel',
            'justification' => 'Renouvellement',
            'lignes' => [$this->ligne()],
        ]);

        $besoin = ExpressionBesoin::query()->first();
        $this->assertNotNull($besoin);
        $response->assertRedirect(route('expression-besoins.besoins.show', $besoin, absolute: false));
        $this->assertStringStartsWith('EB-2026-', $besoin->reference);
        $this->assertSame(StatutBesoin::Brouillon, $besoin->statut);
        $this->assertSame('600000.00', $besoin->montant_total);
        $this->assertSame(1, $besoin->lignes()->count());
    }

    public function test_creation_besoin_pap(): void
    {
        $user = $this->admin();
        $pilier = Pilier::query()->create(['code' => 'P1', 'libelle' => 'Pilier', 'statut' => 'actif']);
        $axe = AxeStrategique::query()->create(['pilier_id' => $pilier->id, 'code' => 'A1', 'libelle' => 'Axe', 'statut' => 'actif']);
        $produit = Produit::query()->create(['axe_strategique_id' => $axe->id, 'code' => 'PR1', 'libelle' => 'Produit', 'statut' => 'actif']);
        $sp = SousProduit::query()->create(['produit_id' => $produit->id, 'code' => 'SP1', 'libelle' => 'SP', 'statut' => 'actif']);
        $activite = Activite::query()->create(['sous_produit_id' => $sp->id, 'code' => 'AC1', 'libelle' => 'Activité', 'statut' => 'actif']);

        $this->actingAs($user)->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'pap',
            'pilier_id' => $pilier->id, 'axe_strategique_id' => $axe->id, 'produit_id' => $produit->id,
            'sous_produit_id' => $sp->id, 'activite_id' => $activite->id,
            'objet' => 'Besoin PAP',
            'lignes' => [$this->ligne()],
        ])->assertRedirect();

        $this->assertSame($activite->id, ExpressionBesoin::query()->first()->activite_id);
    }

    public function test_pap_sans_activite_est_rejete(): void
    {
        $this->actingAs($this->admin())->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'pap',
            'objet' => 'Sans activité',
            'lignes' => [$this->ligne()],
        ])->assertSessionHasErrors('activite_id');
    }

    public function test_hors_pap_sans_categorie_est_rejete(): void
    {
        $this->actingAs($this->admin())->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap',
            'objet' => 'Sans catégorie',
            'justification' => 'x',
            'lignes' => [$this->ligne()],
        ])->assertSessionHasErrors('categorie_hors_pap');
    }

    public function test_besoin_sans_ligne_est_rejete(): void
    {
        $this->actingAs($this->admin())->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap',
            'categorie_hors_pap' => 'informatique',
            'objet' => 'Vide',
            'justification' => 'x',
            'lignes' => [],
        ])->assertSessionHasErrors('lignes');
    }

    public function test_ligne_sans_imputation_est_rejetee(): void
    {
        $this->actingAs($this->admin())->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap',
            'categorie_hors_pap' => 'informatique',
            'objet' => 'Ligne incomplète',
            'justification' => 'x',
            'lignes' => [['designation' => 'X', 'quantite' => 1, 'prix_unitaire' => 100]],
        ])->assertSessionHasErrors(['lignes.0.ligne_budgetaire_id', 'lignes.0.source_financement_id']);
    }

    public function test_piece_deposee_dans_ged(): void
    {
        $user = $this->admin();
        $besoin = ExpressionBesoin::query()->create([
            'reference' => 'EB-2026-0001', 'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap', 'categorie_hors_pap' => 'informatique', 'objet' => 'Test',
            'statut' => StatutBesoin::Brouillon->value, 'created_by' => $user->id, 'montant_total' => 0,
        ]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.pieces.store', $besoin), [
            'fichier' => UploadedFile::fake()->create('devis.pdf', 32, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame(1, $besoin->gedAttachments()->count());
        $this->assertSame(1, $besoin->gedDocuments()->count());
        $doc = $besoin->gedDocuments()->first();
        $this->assertSame('expression_besoin', $doc->module_source);
    }

    public function test_acces_refuse_sans_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('expression-besoins.besoins.index'))->assertForbidden();
    }

    private function creerBesoin(User $user): ExpressionBesoin
    {
        $besoin = ExpressionBesoin::query()->create([
            'reference' => 'EB-2026-'.substr(uniqid(), -4),
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap', 'categorie_hors_pap' => 'informatique',
            'objet' => 'Test', 'justification' => 'Justifié',
            'statut' => StatutBesoin::Brouillon->value, 'montant_total' => 600000, 'created_by' => $user->id,
        ]);
        $besoin->lignes()->create([
            'designation' => 'X', 'quantite' => 2, 'prix_unitaire' => 300000, 'montant' => 600000,
            'ligne_budgetaire_id' => $this->ligne->id, 'source_financement_id' => $this->source->id,
        ]);

        return $besoin;
    }

    public function test_soumission_demarre_le_workflow(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);

        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin))->assertRedirect();

        $besoin->refresh();
        $this->assertSame(StatutBesoin::Soumis, $besoin->statut);
        $this->assertSame(5, $besoin->validations()->count());
    }

    public function test_cycle_complet_de_validation(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin));

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.decide', $besoin), [
                'decision' => 'valide',
            ])->assertRedirect();
        }

        $this->assertSame(StatutBesoin::Valide, $besoin->refresh()->statut);
    }

    public function test_rejet_met_le_besoin_en_rejete(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin));

        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.decide', $besoin), [
            'decision' => 'rejete', 'commentaire' => 'Non conforme',
        ])->assertRedirect();

        $this->assertSame(StatutBesoin::Rejete, $besoin->refresh()->statut);
    }

    public function test_soumission_sans_justification_bloquee(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['justification' => null]);

        $this->actingAs($user)
            ->post(route('expression-besoins.besoins.workflow.submit', $besoin))
            ->assertSessionHasErrors('workflow');
    }

    public function test_conversion_cree_un_engagement(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin))->assertRedirect();

        $besoin->refresh();
        $this->assertSame(StatutBesoin::Converti, $besoin->statut);
        $this->assertNotNull($besoin->engagement_id);

        $engagement = $besoin->engagement;
        $this->assertSame(StatutEngagement::Brouillon, $engagement->statut);
        $this->assertSame('600000.00', $engagement->montant);
        $this->assertSame(1, $engagement->imputations()->count());
        $this->assertTrue($engagement->expressionBesoin->is($besoin));
    }

    public function test_double_conversion_interdite(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin))->assertRedirect();

        // Le besoin est désormais Converti avec un engagement → seconde conversion refusée.
        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin->refresh()))->assertForbidden();
    }

    public function test_conversion_refusee_si_non_valide(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user); // Brouillon

        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin))->assertForbidden();
    }

    public function test_modification_recalcule_le_total(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);

        $this->actingAs($user)->put(route('expression-besoins.besoins.update', $besoin), [
            'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap', 'categorie_hors_pap' => 'logistique',
            'objet' => 'Objet modifié', 'justification' => 'maj',
            'lignes' => [$this->ligne(['quantite' => 5, 'prix_unitaire' => 100000])],
        ])->assertRedirect();

        $besoin->refresh();
        $this->assertSame('Objet modifié', $besoin->objet);
        $this->assertSame('500000.00', $besoin->montant_total);
    }

    public function test_audit_timeline_enregistree(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        app(\App\Services\ExpressionBesoin\ExpressionBesoinService::class); // service résolu

        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin));

        $timeline = app(\App\Services\ExpressionBesoin\ExpressionBesoinAuditService::class)->timeline($besoin->refresh());
        $this->assertTrue($timeline->isNotEmpty());
        $this->assertTrue($timeline->contains(fn ($e) => ($e->properties['action'] ?? null) === 'soumission'));
    }

    public function test_export_fiche_pdf(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);

        $this->actingAs($user)->get(route('expression-besoins.besoins.export.pdf', $besoin))->assertOk();
    }

    public function test_export_registre_excel(): void
    {
        $user = $this->admin();
        $this->creerBesoin($user);

        $this->actingAs($user)->get(route('expression-besoins.besoins.export.registre.excel'))->assertOk();
    }

    public function test_conversion_multi_lignes_cree_plusieurs_imputations(): void
    {
        $user = $this->admin();
        $ligne2 = LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $this->exercice->id, 'nature_depense_id' => $this->ligne->nature_depense_id,
            'code' => 'LB-002', 'libelle' => 'Ligne 2', 'montant_vote' => 1000000, 'montant_revise' => 1000000,
            'statut' => 'active',
        ]);

        $besoin = ExpressionBesoin::query()->create([
            'reference' => 'EB-2026-9000', 'exercice_budgetaire_id' => $this->exercice->id,
            'type_besoin' => 'hors_pap', 'categorie_hors_pap' => 'logistique', 'objet' => 'Multi',
            'justification' => 'x', 'statut' => StatutBesoin::Valide->value, 'montant_total' => 500000, 'created_by' => $user->id,
        ]);
        $besoin->lignes()->createMany([
            ['designation' => 'A', 'quantite' => 1, 'prix_unitaire' => 200000, 'montant' => 200000, 'ligne_budgetaire_id' => $this->ligne->id, 'source_financement_id' => $this->source->id],
            ['designation' => 'B', 'quantite' => 1, 'prix_unitaire' => 300000, 'montant' => 300000, 'ligne_budgetaire_id' => $ligne2->id, 'source_financement_id' => $this->source->id],
        ]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin))->assertRedirect();

        $this->assertSame(2, $besoin->refresh()->engagement->imputations()->count());
    }

    public function test_conversion_reprend_les_pieces_ged(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        app(\App\Services\ExpressionBesoin\ExpressionBesoinGedService::class)
            ->deposer($besoin, UploadedFile::fake()->create('devis.pdf', 16, 'application/pdf'), $user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin))->assertRedirect();

        $this->assertSame(1, $besoin->refresh()->engagement->gedAttachments()->count());
    }

    public function test_revision_controlee_remet_en_brouillon(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value, 'submitted_at' => now(), 'validated_at' => now()]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.revise', $besoin), [
            'motif_revision' => 'Erreur de montant',
        ])->assertRedirect();

        $besoin->refresh();
        $this->assertSame(StatutBesoin::Brouillon, $besoin->statut);
        $this->assertSame(2, $besoin->version_revision);
        $this->assertNull($besoin->validated_at);
        $this->assertSame('Erreur de montant', $besoin->derniere_revision_motif);
    }

    public function test_revision_refusee_si_converti(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);
        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin));

        $this->actingAs($user)
            ->post(route('expression-besoins.besoins.revise', $besoin->refresh()), ['motif_revision' => 'x'])
            ->assertForbidden();
    }

    public function test_etats_pap_et_hors_pap_pdf(): void
    {
        $user = $this->admin();
        $this->creerBesoin($user);

        $this->actingAs($user)->get(route('expression-besoins.besoins.export.etat-pap.pdf'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.etat-hors-pap.pdf'))->assertOk();
    }

    public function test_consolidation_et_rapprochement_accessibles(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);
        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin));

        $this->actingAs($user)->get(route('expression-besoins.consolidation'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.rapprochement'))->assertOk()->assertSee($besoin->reference);
    }

    public function test_ligne_budgetaire_doit_appartenir_a_l_exercice(): void
    {
        $user = $this->admin();
        $exercice2 = ExerciceBudgetaire::query()->create([
            'annee' => 2025, 'date_debut' => '2025-01-01', 'date_fin' => '2025-12-31',
            'statut' => 'ouvert', 'est_actif' => false,
        ]);
        $ligneAutreExercice = LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice2->id, 'nature_depense_id' => $this->ligne->nature_depense_id,
            'code' => 'LB-2025', 'libelle' => 'Ligne 2025', 'montant_vote' => 1000000, 'statut' => 'active',
        ]);

        $this->actingAs($user)->post(route('expression-besoins.besoins.store'), [
            'exercice_budgetaire_id' => $this->exercice->id, // 2026
            'type_besoin' => 'hors_pap', 'categorie_hors_pap' => 'informatique',
            'objet' => 'Mauvais exercice', 'justification' => 'x',
            'lignes' => [$this->ligne(['ligne_budgetaire_id' => $ligneAutreExercice->id])],
        ])->assertSessionHasErrors('lignes.0.ligne_budgetaire_id');
    }

    public function test_export_historique_pdf(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);
        $this->actingAs($user)->post(route('expression-besoins.besoins.revise', $besoin), ['motif_revision' => 'correction']);

        $this->actingAs($user)->get(route('expression-besoins.besoins.export.historique.pdf', $besoin))->assertOk();
    }

    public function test_exports_excel_consolidation_et_rapprochement(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);
        $this->actingAs($user)->post(route('expression-besoins.besoins.convert', $besoin));

        $this->actingAs($user)->get(route('expression-besoins.consolidation.excel'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.rapprochement.excel'))->assertOk();
    }

    public function test_annulation_besoin_en_circuit(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin));

        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.cancel', $besoin), [
            'motif' => 'Plus nécessaire',
        ])->assertRedirect();

        $besoin->refresh();
        $this->assertSame(StatutBesoin::Annule, $besoin->statut);
        $this->assertSame(0, $besoin->validations()->count());
    }

    public function test_controle_budgetaire_bloque_si_credit_insuffisant(): void
    {
        $user = $this->admin();
        $this->ligne->update(['montant_revise' => 400_000]);
        $besoin = $this->creerBesoin($user);
        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.submit', $besoin));

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.decide', $besoin), [
                'decision' => 'valide',
            ])->assertRedirect();
        }

        $this->actingAs($user)->post(route('expression-besoins.besoins.workflow.decide', $besoin), [
            'decision' => 'valide',
        ])->assertSessionHasErrors('workflow');

        $this->assertSame(StatutBesoin::EnValidation, $besoin->refresh()->statut);
    }

    // --- Rendu des vues écran -------------------------------------------------

    public function test_pages_principales_se_rendent(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);

        $this->actingAs($user)->get(route('expression-besoins.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.index'))->assertOk()->assertSee($besoin->reference);
        $this->actingAs($user)->get(route('expression-besoins.besoins.create'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.edit', $besoin))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.show', $besoin))->assertOk();
    }

    public function test_filtre_par_nature_pap_horspap_sur_index(): void
    {
        $user = $this->admin();
        $horsPap = $this->creerBesoin($user); // hors_pap

        // L'index filtré sur PAP ne doit pas montrer un besoin Hors PAP.
        $this->actingAs($user)
            ->get(route('expression-besoins.besoins.index', ['type_besoin' => 'pap']))
            ->assertOk()
            ->assertDontSee($horsPap->reference);

        $this->actingAs($user)
            ->get(route('expression-besoins.besoins.index', ['type_besoin' => 'hors_pap']))
            ->assertOk()
            ->assertSee($horsPap->reference);
    }

    public function test_archives_liste_les_besoins_supprimes(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $reference = $besoin->reference;
        $besoin->delete();

        $this->actingAs($user)
            ->get(route('expression-besoins.besoins.archives'))
            ->assertOk()
            ->assertSee($reference);

        // Un besoin actif ne doit pas apparaître dans les archives.
        $actif = $this->creerBesoin($user);
        $this->actingAs($user)
            ->get(route('expression-besoins.besoins.archives'))
            ->assertOk()
            ->assertDontSee($actif->reference);
    }

    // --- Nouveaux états PDF ---------------------------------------------------

    public function test_nouveaux_etats_pdf_se_generent(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);
        $besoin->update(['statut' => StatutBesoin::Valide->value]);

        $this->actingAs($user)->get(route('expression-besoins.consolidation.pdf'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.etat-structure.pdf'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.etat-categorie.pdf'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.etat-pieces.pdf'))->assertOk();
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.bordereau.pdf'))->assertOk();
    }

    // --- Versionnement GED des PDF générés ------------------------------------

    public function test_fiche_pdf_archivee_et_versionnee_en_ged(): void
    {
        $user = $this->admin();
        $besoin = $this->creerBesoin($user);

        // Première génération : dépôt initial (version 1).
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.pdf', $besoin))->assertOk();

        $fiche = $besoin->gedAttachments()->where('role', 'fiche_generee')->first();
        $this->assertNotNull($fiche, 'La fiche générée doit être rattachée au besoin.');
        $document = \App\Models\GedDocument::query()->find($fiche->ged_document_id);
        $this->assertSame(1, (int) $document->version_courante);
        $this->assertSame('expression_besoin', $document->module_source);

        // Seconde génération : nouvelle version du même document (pas de doublon).
        $this->actingAs($user)->get(route('expression-besoins.besoins.export.pdf', $besoin))->assertOk();

        $this->assertSame(1, $besoin->gedAttachments()->where('role', 'fiche_generee')->count());
        $this->assertSame(2, (int) $document->refresh()->version_courante);
        $this->assertSame(2, $document->versions()->count());
    }
}
