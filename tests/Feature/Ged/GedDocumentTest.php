<?php

namespace Tests\Feature\Ged;

use App\Enums\StatutDocumentGed;
use App\Models\GedDocument;
use App\Models\GedTypeDocument;
use App\Models\GedVersion;
use App\Models\User;
use App\Services\Ged\GedArchiveService;
use App\Services\Ged\GedDocumentService;
use App\Services\Ged\GedSignatureService;
use App\Services\Ged\GedWorkflowService;
use Database\Seeders\GedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GedDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(GedSeeder::class);
        Storage::fake('local');
    }

    private function gestionnaire(): User
    {
        $user = User::factory()->create();
        $user->assignRole('gestionnaire_ged');

        return $user;
    }

    private function type(): GedTypeDocument
    {
        return GedTypeDocument::query()->create([
            'code' => 'CTR', 'libelle' => 'Contrat', 'prefixe_reference' => 'CTR',
            'statut' => 'actif', 'niveau_confidentialite_defaut' => 'interne',
        ]);
    }

    private function deposer(User $user, ?GedTypeDocument $type = null): GedDocument
    {
        $type ??= $this->type();

        return app(GedDocumentService::class)->store(
            ['titre' => 'Document test', 'type_document_id' => $type->id, 'module_source' => 'engagement'],
            UploadedFile::fake()->create('doc.pdf', 64, 'application/pdf'),
            $user
        );
    }

    public function test_depot_cree_document_version_reference_et_hash(): void
    {
        $user = $this->gestionnaire();
        $type = $this->type();

        $response = $this->actingAs($user)->post(route('ged.documents.store'), [
            'titre' => 'Contrat de prestation',
            'type_document_id' => $type->id,
            'module_source' => 'engagement',
            'fichier' => UploadedFile::fake()->create('contrat.pdf', 128, 'application/pdf'),
        ]);

        $document = GedDocument::query()->first();
        $this->assertNotNull($document);
        $response->assertRedirect(route('ged.documents.show', $document, absolute: false));
        $this->assertStringStartsWith('CEEAC/GED/', (string) $document->reference);
        $this->assertNotNull($document->hash_sha256);
        $this->assertSame(1, $document->version_courante);
        $this->assertSame(StatutDocumentGed::Depose, $document->statut);
        $this->assertSame(1, $document->versions()->count());
        $this->assertTrue(Storage::disk('local')->exists($document->chemin_fichier));
    }

    public function test_nouvelle_version_conserve_historique(): void
    {
        $user = $this->gestionnaire();
        $document = $this->deposer($user);

        $this->actingAs($user)->post(route('ged.documents.versions.store', $document), [
            'fichier' => UploadedFile::fake()->create('doc-v2.pdf', 80, 'application/pdf'),
            'commentaire' => 'Mise à jour',
        ])->assertRedirect();

        $document->refresh();
        $this->assertSame(2, $document->version_courante);
        $this->assertSame(2, GedVersion::query()->where('ged_document_id', $document->id)->count());
    }

    public function test_workflow_soumission_puis_validation(): void
    {
        $user = $this->gestionnaire();
        $document = $this->deposer($user);
        $workflow = app(GedWorkflowService::class);

        $workflow->start($document, $user);
        $this->assertSame(StatutDocumentGed::EnValidation, $document->fresh()->statut);

        $workflow->decide($document->fresh(), $user, 'valide', 'Conforme');
        $this->assertSame(StatutDocumentGed::Valide, $document->fresh()->statut);
    }

    public function test_signature_apposee_et_integrite_detectee(): void
    {
        $user = $this->gestionnaire();
        $document = $this->deposer($user);
        $signatures = app(GedSignatureService::class);

        $signature = $signatures->request($document, $user, $user);
        $signatures->sign($signature->fresh(), $user);

        $document->refresh();
        $this->assertTrue($document->est_signe);
        $this->assertTrue($document->est_verrouille);

        $ok = $signatures->verify($signature->fresh());
        $this->assertTrue($ok['integrite_fichier']);
        $this->assertTrue($ok['scelle_valide']);

        // Altération du fichier → intégrité non conforme.
        Storage::disk('local')->put($document->chemin_fichier, 'falsifie');
        $altered = $signatures->verify($signature->fresh());
        $this->assertFalse($altered['integrite_fichier']);
    }

    public function test_archivage_et_restauration(): void
    {
        $user = $this->gestionnaire();
        $document = $this->deposer($user);
        $archives = app(GedArchiveService::class);

        $archives->archive($document, $user, 'Clôture');
        $document->refresh();
        $this->assertTrue($document->est_archive);
        $this->assertSame(StatutDocumentGed::Archive, $document->statut);

        $archives->restore($document, $user);
        $document->refresh();
        $this->assertFalse($document->est_archive);
        $this->assertSame(StatutDocumentGed::Valide, $document->statut);
    }

    public function test_acces_refuse_sans_permission(): void
    {
        $user = User::factory()->create(); // aucun rôle GED

        $this->actingAs($user)->get(route('ged.documents.index'))->assertForbidden();
        $this->actingAs($user)->get(route('ged.documents.create'))->assertForbidden();
    }

    public function test_rattachement_polymorphe_visible_depuis_le_document(): void
    {
        $user = $this->gestionnaire();
        $document = $this->deposer($user);

        $this->actingAs($user)->post(route('ged.documents.attachments.store', $document), [
            'attachable_type' => 'exercice_budgetaire',
            'attachable_id' => \App\Models\ExerciceBudgetaire::query()->create([
                'annee' => 2026, 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut' => 'ouvert', 'est_actif' => true,
            ])->id,
        ])->assertRedirect();

        $this->assertSame(1, $document->attachments()->count());
    }

    public function test_cloisonnement_masque_les_documents_confidentiels_dans_les_listes(): void
    {
        $owner = User::factory()->create();
        $reference = 'SECRET-REF-XYZ';
        GedDocument::query()->create([
            'reference' => $reference, 'titre' => 'Dossier sensible',
            'niveau_confidentialite' => 'tres_confidentiel', 'statut' => 'valide',
            'date_expiration' => now()->subDay()->toDateString(), 'created_by' => $owner->id,
            'version_courante' => 1,
        ]);

        // Lecteur non administrateur, non propriétaire : ne doit pas voir le document.
        $viewer = User::factory()->create();
        $viewer->assignRole('auditeur'); // ged.view sans ged.admin

        $this->actingAs($viewer)->get(route('ged.expires'))->assertOk()->assertDontSee($reference);
        $this->actingAs($viewer)->get(route('ged.conservation.index'))->assertOk()->assertDontSee($reference);

        // L'administrateur GED le voit.
        $this->actingAs($this->gestionnaire())->get(route('ged.expires'))->assertOk()->assertSee($reference);
    }
}
