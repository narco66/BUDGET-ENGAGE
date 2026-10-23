<?php

namespace Tests\Feature\Engagements;

use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementMultiImputationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_store_creates_multiple_imputations_and_recalculates_total(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrateur_general');

        $exercice = ExerciceBudgetaire::factory()->create();
        $ligneA = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create();
        $ligneB = LigneBudgetaire::factory()->for($exercice, 'exerciceBudgetaire')->create();

        $response = $this->actingAs($user)->post(route('engagements.store'), [
            'exercice_budgetaire_id' => $exercice->id,
            'type_engagement' => 'achat',
            'beneficiaire_type' => 'autre',
            'beneficiaire_autre' => 'Prestataire X',
            'objet' => 'Achat equipements',
            'date_engagement' => now()->toDateString(),
            'imputations' => [
                [
                    'ligne_budgetaire_id' => $ligneA->id,
                    'montant_impute' => '125000.50',
                    'observation' => 'Tranche 1',
                ],
                [
                    'ligne_budgetaire_id' => $ligneB->id,
                    'montant_impute' => '240000.00',
                    'observation' => 'Tranche 2',
                ],
            ],
        ]);

        $response->assertRedirect(route('engagements.index'));

        $engagement = Engagement::query()->with('imputations')->latest('id')->firstOrFail();
        $this->assertSame('365000.50', number_format((float) $engagement->montant, 2, '.', ''));
        $this->assertCount(2, $engagement->imputations);
        $this->assertSame($ligneA->id, (int) $engagement->ligne_budgetaire_id);
    }
}
