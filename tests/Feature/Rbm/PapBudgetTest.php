<?php

namespace Tests\Feature\Rbm;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\PapLigneBudgetaire;
use App\Services\Rbm\PapBudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PapBudgetTest extends TestCase
{
    use RefreshDatabase;

    private function exercice(): ExerciceBudgetaire
    {
        return ExerciceBudgetaire::query()->create([
            'annee' => 2026,
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-12-31',
            'statut' => StatutExerciceBudgetaire::Ouvert,
            'est_actif' => true,
        ]);
    }

    public function test_synthese_and_coverage_rollup_by_pilier(): void
    {
        $exercice = $this->exercice();

        $pilier = PapLigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice->id, 'code' => '201', 'libelle' => 'Pilier 1',
            'niveau' => 'pilier', 'montant_total' => 1_000_000, 'montant_ceeac' => 600_000, 'montant_ptf' => 400_000, 'ordre' => 0, 'source' => 'TEST',
        ]);
        $produit = PapLigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice->id, 'code' => '20111', 'libelle' => 'Produit', 'parent_code' => '2011',
            'niveau' => 'produit', 'montant_total' => 250_000, 'montant_ceeac' => 250_000, 'montant_ptf' => 0, 'ordre' => 1, 'source' => 'TEST',
        ]);

        $nature = NatureDepense::query()->create(['code' => '606', 'libelle' => 'Achats', 'statut' => 'actif']);

        // Ligne budgetaire liee au produit -> doit remonter au pilier 201.
        LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'nature_depense_id' => $nature->id,
            'pap_ligne_budgetaire_id' => $produit->id,
            'code' => 'LB-1', 'libelle' => 'Ligne liee',
            'montant_vote' => 200_000, 'montant_revise' => 200_000, 'statut' => 'active',
        ]);
        // Ligne non liee -> ne compte pas dans la couverture.
        LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice->id,
            'nature_depense_id' => $nature->id,
            'code' => 'LB-2', 'libelle' => 'Ligne non liee',
            'montant_vote' => 999, 'montant_revise' => 999, 'statut' => 'active',
        ]);

        $service = app(PapBudgetService::class);
        $synthese = $service->synthese($exercice);

        $this->assertEqualsWithDelta(1_000_000, $synthese['montant_total'], 0.5);
        $this->assertEqualsWithDelta(200_000, $synthese['montant_budgete'], 0.5);
        $this->assertSame(1, $synthese['nb_lignes_liees']);
        $this->assertEqualsWithDelta(20.0, $synthese['taux_couverture'], 0.1);

        $parPilier = $service->programmationParPilier($exercice);
        $this->assertCount(1, $parPilier);
        $this->assertEqualsWithDelta(200_000, $parPilier->first()['montant_budgete'], 0.5);
        $this->assertEqualsWithDelta(800_000, $parPilier->first()['ecart'], 0.5);
    }

    public function test_hierarchie_rolls_budget_up_through_axe_and_pilier(): void
    {
        $exercice = $this->exercice();

        PapLigneBudgetaire::query()->create(['exercice_budgetaire_id' => $exercice->id, 'code' => '201', 'libelle' => 'Pilier', 'niveau' => 'pilier', 'montant_total' => 1_000_000, 'montant_ceeac' => 1_000_000, 'montant_ptf' => 0, 'ordre' => 0, 'source' => 'T']);
        PapLigneBudgetaire::query()->create(['exercice_budgetaire_id' => $exercice->id, 'code' => '2011', 'libelle' => 'Axe', 'parent_code' => '201', 'niveau' => 'axe', 'montant_total' => 600_000, 'montant_ceeac' => 600_000, 'montant_ptf' => 0, 'ordre' => 1, 'source' => 'T']);
        $produit = PapLigneBudgetaire::query()->create(['exercice_budgetaire_id' => $exercice->id, 'code' => '20111', 'libelle' => 'Produit', 'parent_code' => '2011', 'niveau' => 'produit', 'montant_total' => 300_000, 'montant_ceeac' => 300_000, 'montant_ptf' => 0, 'ordre' => 2, 'source' => 'T']);
        // Sous-produit lie : doit remonter au produit, a l'axe et au pilier.
        $sousProduit = PapLigneBudgetaire::query()->create(['exercice_budgetaire_id' => $exercice->id, 'code' => '201111', 'libelle' => 'SP', 'parent_code' => '20111', 'niveau' => 'sous_produit', 'montant_total' => 300_000, 'montant_ceeac' => 300_000, 'montant_ptf' => 0, 'ordre' => 3, 'source' => 'T']);

        $nature = NatureDepense::query()->create(['code' => '606', 'libelle' => 'Achats', 'statut' => 'actif']);
        LigneBudgetaire::query()->create([
            'exercice_budgetaire_id' => $exercice->id, 'nature_depense_id' => $nature->id,
            'pap_ligne_budgetaire_id' => $sousProduit->id, 'code' => 'LB-1', 'libelle' => 'L',
            'montant_vote' => 150_000, 'montant_revise' => 150_000, 'statut' => 'active',
        ]);

        $service = app(PapBudgetService::class);
        $hier = $service->programmationHierarchie($exercice);

        $pilier = $hier->first();
        $this->assertEqualsWithDelta(150_000, $pilier['montant_budgete'], 0.5);
        $axe = $pilier['axes']->first();
        $this->assertEqualsWithDelta(150_000, $axe['montant_budgete'], 0.5);
        $produitRow = $axe['produits']->first();
        $this->assertEqualsWithDelta(150_000, $produitRow['montant_budgete'], 0.5);

        // Export a plat : 1 pilier + 1 axe + 1 produit = 3 lignes.
        $this->assertCount(3, $service->programmationFlat($exercice));
    }

    public function test_classify_source(): void
    {
        $this->assertSame('CEEAC', PapBudgetService::classifySource(100, 0));
        $this->assertSame('PTF', PapBudgetService::classifySource(0, 100));
        $this->assertSame('Mixte', PapBudgetService::classifySource(60, 40));
        $this->assertSame('Néant', PapBudgetService::classifySource(0, 0));
    }
}
