<?php

namespace Tests\Feature\Budget;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Services\Budget\CreditAvailabilityService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreditAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'statut' => 'actif']);
        $user->assignRole('administrateur_general');

        return $user;
    }

    private function ligne(ExerciceBudgetaire $ex, float $revise): LigneBudgetaire
    {
        return LigneBudgetaire::factory()->for($ex, 'exerciceBudgetaire')->create([
            'montant_vote' => $revise, 'montant_revise' => $revise, 'montant_engage' => 0,
        ]);
    }

    private function engagement(ExerciceBudgetaire $ex, LigneBudgetaire $ligne, float $montant, StatutEngagement $statut, User $user): Engagement
    {
        $eng = Engagement::factory()->create([
            'exercice_budgetaire_id' => $ex->id,
            'ligne_budgetaire_id' => $ligne->id,
            'montant' => $montant,
            'statut' => $statut,
            'created_by' => $user->id,
        ]);
        $eng->imputations()->create(['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => $montant]);

        return $eng;
    }

    public function test_disponible_previsionnel_subtracts_pending_reservations(): void
    {
        $svc = app(CreditAvailabilityService::class);
        $ex = ExerciceBudgetaire::factory()->create();
        $ligne = $this->ligne($ex, 1_000_000);
        $admin = $this->admin();

        // Un engagement brouillon reserve 700 000.
        $a = $this->engagement($ex, $ligne, 700_000, StatutEngagement::Brouillon, $admin);

        $this->assertSame('300000.00', $svc->disponible($ligne));
        // En excluant A : tout le plafond redevient disponible.
        $this->assertSame('1000000.00', $svc->disponible($ligne, $a->id));
        // Disponible comptable (sans reservations) = plafond - engage.
        $this->assertSame('1000000.00', $svc->disponibleComptable($ligne));
    }

    public function test_assert_imputations_blocks_over_reservation(): void
    {
        $svc = app(CreditAvailabilityService::class);
        $ex = ExerciceBudgetaire::factory()->create();
        $ligne = $this->ligne($ex, 1_000_000);
        $admin = $this->admin();
        $this->engagement($ex, $ligne, 700_000, StatutEngagement::EnValidation, $admin);

        // 400 000 > 300 000 disponibles -> exception.
        $this->expectException(ValidationException::class);
        $svc->assertImputations([['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => 400_000]]);
    }

    public function test_assert_imputations_allows_within_reservation(): void
    {
        $svc = app(CreditAvailabilityService::class);
        $ex = ExerciceBudgetaire::factory()->create();
        $ligne = $this->ligne($ex, 1_000_000);
        $admin = $this->admin();
        $this->engagement($ex, $ligne, 700_000, StatutEngagement::EnValidation, $admin);

        $svc->assertImputations([['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => 300_000]]);
        $this->assertTrue(true); // pas d'exception
    }

    public function test_store_engagement_blocked_when_exceeding_credit(): void
    {
        $ex = ExerciceBudgetaire::factory()->create();
        $ligne = $this->ligne($ex, 500_000);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('engagements.create'))
            ->post(route('engagements.store'), [
                'exercice_budgetaire_id' => $ex->id,
                'type_engagement' => 'achat',
                'beneficiaire_type' => 'autre',
                'beneficiaire_autre' => 'X',
                'objet' => 'Test',
                'date_engagement' => now()->toDateString(),
                'imputations' => [
                    ['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => '600000.00', 'observation' => null],
                ],
            ])
            ->assertSessionHasErrors('imputations.0.montant_impute');

        $this->assertSame(0, Engagement::query()->count());
    }

    public function test_store_engagement_allowed_within_credit(): void
    {
        $ex = ExerciceBudgetaire::factory()->create();
        $ligne = $this->ligne($ex, 500_000);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('engagements.store'), [
                'exercice_budgetaire_id' => $ex->id,
                'type_engagement' => 'achat',
                'beneficiaire_type' => 'autre',
                'beneficiaire_autre' => 'X',
                'objet' => 'Test',
                'date_engagement' => now()->toDateString(),
                'imputations' => [
                    ['ligne_budgetaire_id' => $ligne->id, 'montant_impute' => '450000.00', 'observation' => null],
                ],
            ])
            ->assertRedirect(route('engagements.index'));

        $this->assertSame(1, Engagement::query()->count());
    }
}
