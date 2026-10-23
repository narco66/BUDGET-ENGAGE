<?php

namespace Database\Seeders;

use App\Enums\BeneficiaireType;
use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Enums\StatutExerciceBudgetaire;
use App\Models\Agent;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Models\User;
use App\Models\ValidationWorkflow;
use App\Services\EngagementExecutionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientDemoPackSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ParametresControleSeeder::class,
        ]);

        $year = (int) date('Y');
        $exercice = ExerciceBudgetaire::query()->updateOrCreate(
            ['annee' => $year],
            [
                'date_debut' => now()->startOfYear()->toDateString(),
                'date_fin' => now()->endOfYear()->toDateString(),
                'statut' => StatutExerciceBudgetaire::Ouvert,
                'est_actif' => true,
            ]
        );

        $dept = Departement::query()->updateOrCreate(['code' => 'DEM'], ['libelle' => 'Département Démonstration', 'description' => null, 'statut' => 'actif']);
        $dir = Direction::query()->updateOrCreate(['departement_id' => $dept->id, 'code' => 'DCL'], ['libelle' => 'Direction Client', 'description' => null, 'statut' => 'actif']);
        $svc = Service::query()->updateOrCreate(['direction_id' => $dir->id, 'code' => 'OPS'], ['libelle' => 'Service Opérations', 'description' => null, 'statut' => 'actif']);

        $natureAchat = NatureDepense::query()->firstOrCreate(['code' => '606'], ['libelle' => 'Achats de biens et services', 'description' => null, 'statut' => 'actif']);
        $natureMission = NatureDepense::query()->firstOrCreate(['code' => '625'], ['libelle' => 'Missions et déplacements', 'description' => null, 'statut' => 'actif']);
        $source = SourceFinancement::query()->firstOrCreate(['code' => 'BF'], ['libelle' => 'Budget de fonctionnement', 'description' => null, 'statut' => 'actif']);

        $ligneA = LigneBudgetaire::query()->updateOrCreate(
            ['exercice_budgetaire_id' => $exercice->id, 'code' => 'LIG-CLIENT-001'],
            [
                'departement_id' => $dept->id,
                'direction_id' => $dir->id,
                'service_id' => $svc->id,
                'nature_depense_id' => $natureAchat->id,
                'source_financement_id' => $source->id,
                'libelle' => 'Acquisitions et prestations',
                'montant_vote' => 350_000_000,
                'montant_revise' => 350_000_000,
                'statut' => 'active',
            ]
        );
        $ligneB = LigneBudgetaire::query()->updateOrCreate(
            ['exercice_budgetaire_id' => $exercice->id, 'code' => 'LIG-CLIENT-002'],
            [
                'departement_id' => $dept->id,
                'direction_id' => $dir->id,
                'service_id' => $svc->id,
                'nature_depense_id' => $natureMission->id,
                'source_financement_id' => $source->id,
                'libelle' => 'Missions et ateliers',
                'montant_vote' => 180_000_000,
                'montant_revise' => 180_000_000,
                'statut' => 'active',
            ]
        );

        $users = [
            'admin.ceeac@example.com' => ['name' => 'Admin', 'prenom' => 'Demo', 'role' => 'administrateur_general'],
            'initiateur.client@example.com' => ['name' => 'Aline', 'prenom' => 'Initiateur', 'role' => 'agent_initiateur'],
            'responsable.service@example.com' => ['name' => 'Samuel', 'prenom' => 'Service', 'role' => 'responsable_service'],
            'controleur.budget@example.com' => ['name' => 'Brice', 'prenom' => 'Budget', 'role' => 'controleur_budgetaire'],
            'controleur.financier@example.com' => ['name' => 'Nora', 'prenom' => 'Finance', 'role' => 'controleur_financier'],
            'ordonnateur@example.com' => ['name' => 'Joel', 'prenom' => 'Ordonnateur', 'role' => 'ordonnateur'],
            'comptable@example.com' => ['name' => 'Iris', 'prenom' => 'Comptable', 'role' => 'comptable'],
            'auditeur@example.com' => ['name' => 'Aude', 'prenom' => 'Audit', 'role' => 'auditeur'],
        ];
        $u = [];
        foreach ($users as $email => $meta) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'service_id' => $svc->id,
                    'name' => $meta['name'],
                    'prenom' => $meta['prenom'],
                    'telephone' => '+237690100100',
                    'password' => Hash::make('password'),
                    'statut' => 'actif',
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$meta['role']]);
            $u[$email] = $user;
        }

        $fournisseur = Fournisseur::query()->updateOrCreate(
            ['numero_fiscal' => 'CLI-DEMO-001'],
            [
                'raison_sociale' => 'Prestations CEEAC Démo',
                'sigle' => 'PCD',
                'adresse' => 'Avenue Institutionnelle',
                'telephone' => '+237671000000',
                'email' => 'contact@pcd.example',
                'banque' => 'BGFI',
                'compte_bancaire' => 'CM11 10001 00000 000001',
                'type_fournisseur' => 'prestataire',
                'statut' => 'actif',
            ]
        );
        $agent = Agent::query()->updateOrCreate(
            ['matricule' => 'AGT-CLIENT-01'],
            [
                'service_id' => $svc->id,
                'nom' => 'Meka',
                'prenom' => 'Ruth',
                'fonction' => 'Gestionnaire',
                'grade' => 'A1',
                'categorie' => 'Cadre',
                'email' => 'ruth.meka@example.com',
                'telephone' => '+237672000000',
                'banque' => 'UBA',
                'compte_bancaire' => 'CM22 10001 00000 000010',
                'statut' => 'actif',
            ]
        );

        $create = function (string $numero, array $base, array $imputations) use ($exercice, $u): Engagement {
            $eng = Engagement::query()->updateOrCreate(
                ['numero' => $numero],
                array_merge([
                    'exercice_budgetaire_id' => $exercice->id,
                    'ligne_budgetaire_id' => $imputations[0]['ligne_budgetaire_id'],
                    'departement_id' => null,
                    'direction_id' => null,
                    'service_id' => null,
                    'type_engagement' => 'achat',
                    'beneficiaire_type' => BeneficiaireType::Autre,
                    'beneficiaire_id' => null,
                    'beneficiaire_autre' => 'Bénéficiaire démonstration',
                    'objet' => 'Objet démonstration',
                    'montant' => collect($imputations)->sum('montant_impute'),
                    'date_engagement' => now()->toDateString(),
                    'statut' => StatutEngagement::Brouillon,
                    'created_by' => $u['initiateur.client@example.com']->id,
                    'is_urgence' => false,
                ], $base)
            );

            $eng->imputations()->delete();
            $eng->imputations()->createMany($imputations);

            return $eng->fresh();
        };

        // Scénario 1: Brouillon
        $create("ENG-{$year}-0001", [
            'objet' => 'Achat de fournitures de bureau - en préparation',
            'statut' => StatutEngagement::Brouillon,
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseur->id,
        ], [
            ['ligne_budgetaire_id' => $ligneA->id, 'montant_impute' => 2_500_000, 'observation' => 'Dossier non soumis'],
        ]);

        // Scénario 2: En validation (multi-imputations)
        $engValidation = $create("ENG-{$year}-0002", [
            'objet' => 'Organisation atelier régional (salle + logistique)',
            'statut' => StatutEngagement::EnValidation,
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseur->id,
            'submitted_at' => now()->subDays(2),
        ], [
            ['ligne_budgetaire_id' => $ligneA->id, 'montant_impute' => 5_000_000, 'observation' => 'Location salle'],
            ['ligne_budgetaire_id' => $ligneB->id, 'montant_impute' => 3_000_000, 'observation' => 'Logistique participants'],
        ]);

        // Scénario 3: Approuvé puis partiellement exécuté
        $engPartiel = $create("ENG-{$year}-0003", [
            'objet' => 'Mission technique de supervision',
            'statut' => StatutEngagement::Approuve,
            'beneficiaire_type' => BeneficiaireType::Agent,
            'beneficiaire_id' => $agent->id,
            'submitted_at' => now()->subDays(10),
            'approved_at' => now()->subDays(8),
        ], [
            ['ligne_budgetaire_id' => $ligneB->id, 'montant_impute' => 7_500_000, 'observation' => 'Mission principale'],
        ]);

        // Scénario 4: Complètement payé
        $engPaye = $create("ENG-{$year}-0004", [
            'objet' => 'Maintenance annuelle infrastructure réseau',
            'statut' => StatutEngagement::Approuve,
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseur->id,
            'submitted_at' => now()->subDays(20),
            'approved_at' => now()->subDays(18),
        ], [
            ['ligne_budgetaire_id' => $ligneA->id, 'montant_impute' => 15_000_000, 'observation' => 'Forfait annuel'],
        ]);

        // Historique workflow lisible pour narration
        foreach ([$engValidation, $engPartiel, $engPaye] as $eng) {
            $eng->validationsWorkflow()->delete();
            $steps = config('engagement_workflow.steps', []);
            $ordre = 1;
            foreach ($steps as $step) {
                $isPending = $eng->statut === StatutEngagement::EnValidation && $ordre >= 3;
                ValidationWorkflow::query()->create([
                    'engagement_id' => $eng->id,
                    'etape' => $step['key'],
                    'ordre' => $ordre,
                    'validateur_id' => $u['admin.ceeac@example.com']->id,
                    'decision' => $isPending ? DecisionWorkflow::EnAttente : DecisionWorkflow::Valide,
                    'commentaire' => $isPending ? null : 'Validation démonstration',
                    'date_decision' => $isPending ? null : now()->subDays(5),
                    'est_validation_deleguee' => false,
                ]);
                $ordre++;
            }
        }

        // Exécution simple et ultra lisible
        $execution = app(EngagementExecutionService::class);
        $comptable = $u['comptable@example.com'];

        if (! $engPartiel->liquidations()->exists()) {
            $imp = $engPartiel->imputations()->first();
            $liq = $execution->recordLiquidation($engPartiel, [
                'engagement_imputation_id' => $imp?->id,
                'montant_liquide' => '4500000.00',
                'date_liquidation' => now()->subDays(6)->toDateString(),
                'reference' => 'LIQ-PACK-0003',
                'commentaire' => 'Liquidation partielle',
            ], $comptable);
            $execution->recordOrdonnancement($liq, [
                'montant_ordonnance' => '2500000.00',
                'date_ordonnancement' => now()->subDays(5)->toDateString(),
                'reference' => 'ORD-PACK-0003',
                'commentaire' => 'Ordonnancement partiel',
            ], $comptable);
        }

        if (! $engPaye->liquidations()->exists()) {
            $imp = $engPaye->imputations()->first();
            $liq = $execution->recordLiquidation($engPaye, [
                'engagement_imputation_id' => $imp?->id,
                'montant_liquide' => '15000000.00',
                'date_liquidation' => now()->subDays(16)->toDateString(),
                'reference' => 'LIQ-PACK-0004',
                'commentaire' => 'Liquidation complète',
            ], $comptable);
            $ord = $execution->recordOrdonnancement($liq, [
                'montant_ordonnance' => '15000000.00',
                'date_ordonnancement' => now()->subDays(15)->toDateString(),
                'reference' => 'ORD-PACK-0004',
                'commentaire' => 'Ordonnancement complet',
            ], $comptable);
            $execution->recordPaiement($ord, [
                'montant_paye' => '15000000.00',
                'date_paiement' => now()->subDays(14)->toDateString(),
                'reference_paiement' => 'PAY-PACK-0004',
                'mode_paiement' => 'Virement',
                'commentaire' => 'Paiement complet',
            ], $comptable);
        }
    }
}
