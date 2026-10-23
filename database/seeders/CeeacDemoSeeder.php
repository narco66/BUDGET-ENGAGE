<?php

namespace Database\Seeders;

use App\Enums\BeneficiaireType;
use App\Enums\DecisionWorkflow;
use App\Enums\NiveauRisqueAlerte;
use App\Enums\StatutEngagement;
use App\Enums\StatutExerciceBudgetaire;
use App\Enums\StatutImportBudget;
use App\Enums\StatutTraitementAlerte;
use App\Models\Agent;
use App\Models\AlerteAudit;
use App\Models\ConnexionLog;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\ImportBudget;
use App\Models\JournalAudit;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\PieceJustificative;
use App\Models\SalaireChargePersonnel;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Models\User;
use App\Models\ValidationWorkflow;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CeeacDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ParametresControleSeeder::class,
        ]);

        $deptBudget = Departement::query()->firstOrCreate(['code' => 'DB'], ['libelle' => 'Direction du Budget', 'description' => null, 'statut' => 'actif']);
        $deptFin = Departement::query()->firstOrCreate(['code' => 'DF'], ['libelle' => 'Direction Financière', 'description' => null, 'statut' => 'actif']);

        $dirBudget = Direction::query()->firstOrCreate(['departement_id' => $deptBudget->id, 'code' => 'CB'], ['libelle' => 'Cellule budgétaire', 'description' => null, 'statut' => 'actif']);
        $dirControle = Direction::query()->firstOrCreate(['departement_id' => $deptFin->id, 'code' => 'CF'], ['libelle' => 'Contrôle financier', 'description' => null, 'statut' => 'actif']);

        $svcSuivi = Service::query()->firstOrCreate(['direction_id' => $dirBudget->id, 'code' => 'SUIVI'], ['libelle' => 'Suivi des engagements', 'description' => null, 'statut' => 'actif']);
        $svcSolde = Service::query()->firstOrCreate(['direction_id' => $dirControle->id, 'code' => 'SOLDE'], ['libelle' => 'Solde et exécution', 'description' => null, 'statut' => 'actif']);

        $natureAchat = NatureDepense::query()->firstOrCreate(['code' => '606'], ['libelle' => 'Achats de biens et services', 'description' => null, 'statut' => 'actif']);
        $natureMission = NatureDepense::query()->firstOrCreate(['code' => '625'], ['libelle' => 'Missions et déplacements', 'description' => null, 'statut' => 'actif']);
        $natureSalaire = NatureDepense::query()->firstOrCreate(['code' => '641'], ['libelle' => 'Rémunération du personnel', 'description' => null, 'statut' => 'actif']);

        $sourceFonct = SourceFinancement::query()->firstOrCreate(['code' => 'BF'], ['libelle' => 'Budget de fonctionnement', 'description' => null, 'statut' => 'actif']);
        $sourceProjet = SourceFinancement::query()->firstOrCreate(['code' => 'PRJ'], ['libelle' => 'Financement projet', 'description' => null, 'statut' => 'actif']);

        $year = (int) date('Y');
        $exerciceN = ExerciceBudgetaire::query()->firstOrCreate(['annee' => $year], [
            'date_debut' => now()->startOfYear()->toDateString(),
            'date_fin' => now()->endOfYear()->toDateString(),
            'statut' => StatutExerciceBudgetaire::Ouvert,
            'est_actif' => true,
        ]);
        $exerciceN1 = ExerciceBudgetaire::query()->firstOrCreate(['annee' => $year - 1], [
            'date_debut' => now()->subYear()->startOfYear()->toDateString(),
            'date_fin' => now()->subYear()->endOfYear()->toDateString(),
            'statut' => StatutExerciceBudgetaire::Cloture,
            'est_actif' => false,
        ]);

        $lignes = [
            'LIG-DEMO-001' => LigneBudgetaire::query()->updateOrCreate(
                ['exercice_budgetaire_id' => $exerciceN->id, 'code' => 'LIG-DEMO-001'],
                ['departement_id' => $deptBudget->id, 'direction_id' => $dirBudget->id, 'service_id' => $svcSuivi->id, 'nature_depense_id' => $natureAchat->id, 'source_financement_id' => $sourceFonct->id, 'libelle' => 'Achats institutionnels', 'montant_vote' => 900_000_000, 'montant_revise' => 900_000_000, 'statut' => 'active']
            ),
            'LIG-DEMO-002' => LigneBudgetaire::query()->updateOrCreate(
                ['exercice_budgetaire_id' => $exerciceN->id, 'code' => 'LIG-DEMO-002'],
                ['departement_id' => $deptBudget->id, 'direction_id' => $dirBudget->id, 'service_id' => $svcSuivi->id, 'nature_depense_id' => $natureMission->id, 'source_financement_id' => $sourceFonct->id, 'libelle' => 'Missions et ateliers', 'montant_vote' => 450_000_000, 'montant_revise' => 480_000_000, 'statut' => 'active']
            ),
            'LIG-DEMO-003' => LigneBudgetaire::query()->updateOrCreate(
                ['exercice_budgetaire_id' => $exerciceN->id, 'code' => 'LIG-DEMO-003'],
                ['departement_id' => $deptFin->id, 'direction_id' => $dirControle->id, 'service_id' => $svcSolde->id, 'nature_depense_id' => $natureSalaire->id, 'source_financement_id' => $sourceFonct->id, 'libelle' => 'Salaires personnel permanent', 'montant_vote' => 1_200_000_000, 'montant_revise' => 1_250_000_000, 'statut' => 'active']
            ),
            'LIG-DEMO-004' => LigneBudgetaire::query()->updateOrCreate(
                ['exercice_budgetaire_id' => $exerciceN->id, 'code' => 'LIG-DEMO-004'],
                ['departement_id' => $deptFin->id, 'direction_id' => $dirControle->id, 'service_id' => $svcSolde->id, 'nature_depense_id' => $natureAchat->id, 'source_financement_id' => $sourceProjet->id, 'libelle' => 'Équipements projet transformation digitale', 'montant_vote' => 700_000_000, 'montant_revise' => 700_000_000, 'statut' => 'active']
            ),
        ];

        $users = [
            'admin.ceeac@example.com' => ['name' => 'Administrateur', 'prenom' => 'CEEAC', 'role' => 'administrateur_general', 'service_id' => $svcSuivi->id],
            'initiateur.budget@example.com' => ['name' => 'Nadia', 'prenom' => 'Moukouri', 'role' => 'agent_initiateur', 'service_id' => $svcSuivi->id],
            'responsable.service@example.com' => ['name' => 'Responsable', 'prenom' => 'Service', 'role' => 'responsable_service', 'service_id' => $svcSuivi->id],
            'responsable.direction@example.com' => ['name' => 'Responsable', 'prenom' => 'Direction', 'role' => 'responsable_direction', 'service_id' => $svcSuivi->id],
            'responsable.departement@example.com' => ['name' => 'Responsable', 'prenom' => 'Departement', 'role' => 'responsable_departement', 'service_id' => $svcSolde->id],
            'controleur.budget@example.com' => ['name' => 'Jean', 'prenom' => 'Budget', 'role' => 'controleur_budgetaire', 'service_id' => $svcSuivi->id],
            'controleur.financier@example.com' => ['name' => 'Paul', 'prenom' => 'Finance', 'role' => 'controleur_financier', 'service_id' => $svcSolde->id],
            'comptable@example.com' => ['name' => 'Elodie', 'prenom' => 'Comptable', 'role' => 'comptable', 'service_id' => $svcSolde->id],
            'ordonnateur@example.com' => ['name' => 'Marc', 'prenom' => 'Ordonnateur', 'role' => 'ordonnateur', 'service_id' => $svcSolde->id],
            'auditeur@example.com' => ['name' => 'Alice', 'prenom' => 'Audit', 'role' => 'auditeur', 'service_id' => $svcSolde->id],
        ];

        $createdUsers = [];
        foreach ($users as $email => $meta) {
            $user = User::query()->updateOrCreate(['email' => $email], [
                'service_id' => $meta['service_id'],
                'name' => $meta['name'],
                'prenom' => $meta['prenom'],
                'telephone' => '+237690000000',
                'password' => Hash::make('password'),
                'statut' => 'actif',
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$meta['role']]);
            $createdUsers[$email] = $user;
        }

        $fournisseurA = Fournisseur::query()->updateOrCreate(['numero_fiscal' => 'M123456789'], ['raison_sociale' => 'Global Tech SARL', 'sigle' => 'GTS', 'adresse' => 'Boulevard CEEAC, Yaounde', 'telephone' => '+237670100100', 'email' => 'contact@globaltech.example', 'banque' => 'BGFI', 'compte_bancaire' => 'CM21 10001 00000 000001', 'type_fournisseur' => 'prestataire', 'statut' => 'actif']);
        $fournisseurB = Fournisseur::query()->updateOrCreate(['numero_fiscal' => 'M987654321'], ['raison_sociale' => 'Afri Services & Consulting', 'sigle' => 'ASC', 'adresse' => 'Avenue de la Paix, Libreville', 'telephone' => '+24101122334', 'email' => 'admin@asc.example', 'banque' => 'UBA', 'compte_bancaire' => 'GA41 20002 00000 000321', 'type_fournisseur' => 'consultance', 'statut' => 'actif']);

        $agentA = Agent::query()->updateOrCreate(['matricule' => 'AGT-0001'], ['service_id' => $svcSolde->id, 'nom' => 'Biko', 'prenom' => 'Armel', 'fonction' => 'Analyste', 'grade' => 'A2', 'categorie' => 'Cadre', 'email' => 'armel.biko@example.com', 'telephone' => '+237677111111', 'banque' => 'SCB', 'compte_bancaire' => 'CM22 10003 00000 000001', 'statut' => 'actif']);
        $agentB = Agent::query()->updateOrCreate(['matricule' => 'AGT-0002'], ['service_id' => $svcSuivi->id, 'nom' => 'Njoya', 'prenom' => 'Celine', 'fonction' => 'Gestionnaire budget', 'grade' => 'A1', 'categorie' => 'Cadre', 'email' => 'celine.njoya@example.com', 'telephone' => '+237677222222', 'banque' => 'ECOBANK', 'compte_bancaire' => 'CM22 10004 00000 000002', 'statut' => 'actif']);

        foreach ([1, 2, 3, 4] as $mois) {
            SalaireChargePersonnel::query()->updateOrCreate(
                ['agent_id' => $agentA->id, 'exercice_budgetaire_id' => $exerciceN->id, 'mois' => $mois],
                ['montant_salaire' => 650_000, 'montant_charges' => 120_000, 'commentaire' => 'Paie mensuelle - démonstration']
            );
            SalaireChargePersonnel::query()->updateOrCreate(
                ['agent_id' => $agentB->id, 'exercice_budgetaire_id' => $exerciceN->id, 'mois' => $mois],
                ['montant_salaire' => 820_000, 'montant_charges' => 155_000, 'commentaire' => 'Paie mensuelle - démonstration']
            );
        }

        $createEngagement = function (string $numero, array $attrs, array $imputations) use ($exerciceN, $createdUsers): Engagement {
            $engagement = Engagement::query()->updateOrCreate(
                ['numero' => $numero],
                array_merge([
                    'exercice_budgetaire_id' => $exerciceN->id,
                    'ligne_budgetaire_id' => $imputations[0]['ligne_budgetaire_id'],
                    'departement_id' => null,
                    'direction_id' => null,
                    'service_id' => null,
                    'type_engagement' => 'achat',
                    'beneficiaire_type' => BeneficiaireType::Autre,
                    'beneficiaire_id' => null,
                    'beneficiaire_autre' => 'Bénéficiaire démonstration',
                    'objet' => 'Engagement de démonstration',
                    'montant' => collect($imputations)->sum('montant_impute'),
                    'date_engagement' => now()->toDateString(),
                    'statut' => StatutEngagement::Brouillon,
                    'created_by' => $createdUsers['initiateur.budget@example.com']->id,
                    'is_urgence' => false,
                    'justification_urgence' => null,
                ], $attrs)
            );

            if (! $engagement->imputations()->exists()) {
                $engagement->imputations()->createMany($imputations);
            }

            return $engagement->fresh();
        };

        $engPaye = $createEngagement('ENG-DEMO-2026-0001', [
            'type_engagement' => 'prestation',
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseurA->id,
            'objet' => 'Contrat de maintenance SI multi-sites',
            'statut' => StatutEngagement::Approuve,
            'submitted_at' => now()->subDays(50),
            'approved_at' => now()->subDays(45),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-001']->id, 'montant_impute' => 65_000_000, 'observation' => 'Lot 1'],
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-004']->id, 'montant_impute' => 85_000_000, 'observation' => 'Lot 2'],
        ]);

        $engOrd = $createEngagement('ENG-DEMO-2026-0002', [
            'type_engagement' => 'mission',
            'beneficiaire_type' => BeneficiaireType::Agent,
            'beneficiaire_id' => $agentB->id,
            'objet' => 'Mission régionale de suivi des projets',
            'statut' => StatutEngagement::Approuve,
            'submitted_at' => now()->subDays(30),
            'approved_at' => now()->subDays(26),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-002']->id, 'montant_impute' => 22_000_000, 'observation' => null],
        ]);

        $engLiq = $createEngagement('ENG-DEMO-2026-0003', [
            'type_engagement' => 'achat',
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseurB->id,
            'objet' => 'Acquisition de matériels bureautiques',
            'statut' => StatutEngagement::Approuve,
            'submitted_at' => now()->subDays(20),
            'approved_at' => now()->subDays(18),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-001']->id, 'montant_impute' => 18_500_000, 'observation' => null],
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-002']->id, 'montant_impute' => 9_500_000, 'observation' => null],
        ]);

        $engApp = $createEngagement('ENG-DEMO-2026-0004', [
            'type_engagement' => 'salaire',
            'beneficiaire_type' => BeneficiaireType::Agent,
            'beneficiaire_id' => $agentA->id,
            'objet' => 'Régularisation salariale trimestrielle',
            'statut' => StatutEngagement::Approuve,
            'submitted_at' => now()->subDays(15),
            'approved_at' => now()->subDays(12),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-003']->id, 'montant_impute' => 12_000_000, 'observation' => 'Trimestre 1'],
        ]);

        $createEngagement('ENG-DEMO-2026-0005', [
            'type_engagement' => 'prestation',
            'beneficiaire_type' => BeneficiaireType::Autre,
            'beneficiaire_autre' => 'ONG Partenaire',
            'objet' => 'Campagne de sensibilisation',
            'statut' => StatutEngagement::EnValidation,
            'submitted_at' => now()->subDays(3),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-002']->id, 'montant_impute' => 6_800_000, 'observation' => 'Vague 1'],
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-001']->id, 'montant_impute' => 4_200_000, 'observation' => 'Vague 2'],
        ]);

        $engRej = $createEngagement('ENG-DEMO-2026-0006', [
            'type_engagement' => 'achat',
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseurA->id,
            'objet' => 'Marché ponctuel rejeté pour dossier incomplet',
            'statut' => StatutEngagement::Rejete,
            'submitted_at' => now()->subDays(10),
            'rejected_at' => now()->subDays(9),
            'motif_rejet' => 'Pièces justificatives incomplètes',
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-001']->id, 'montant_impute' => 7_000_000, 'observation' => null],
        ]);

        $engRet = $createEngagement('ENG-DEMO-2026-0007', [
            'type_engagement' => 'mission',
            'beneficiaire_type' => BeneficiaireType::Agent,
            'beneficiaire_id' => $agentB->id,
            'objet' => 'Mission retournée pour correction budgétaire',
            'statut' => StatutEngagement::Retourne,
            'submitted_at' => now()->subDays(8),
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-002']->id, 'montant_impute' => 4_000_000, 'observation' => null],
        ]);

        $engBrouillon = $createEngagement('ENG-DEMO-2026-0008', [
            'type_engagement' => 'achat',
            'beneficiaire_type' => BeneficiaireType::Autre,
            'beneficiaire_autre' => 'Prestataire local',
            'objet' => 'Brouillon de dossier en préparation',
            'statut' => StatutEngagement::Brouillon,
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-004']->id, 'montant_impute' => 3_500_000, 'observation' => null],
        ]);

        $engAnnule = $createEngagement('ENG-DEMO-2026-0009', [
            'type_engagement' => 'prestation',
            'beneficiaire_type' => BeneficiaireType::Fournisseur,
            'beneficiaire_id' => $fournisseurB->id,
            'objet' => 'Engagement annulé par décision de gestion',
            'statut' => StatutEngagement::Annule,
            'submitted_at' => now()->subDays(40),
            'approved_at' => now()->subDays(35),
            'cancelled_at' => now()->subDays(33),
            'motif_annulation' => 'Changement de priorités institutionnelles',
        ], [
            ['ligne_budgetaire_id' => $lignes['LIG-DEMO-001']->id, 'montant_impute' => 11_500_000, 'observation' => null],
        ]);

        $execution = app(EngagementExecutionService::class);
        $comptable = $createdUsers['comptable@example.com'];

        if (! $engPaye->liquidations()->exists()) {
            foreach ($engPaye->imputations as $imp) {
                $liquidation = $execution->recordLiquidation($engPaye, [
                    'engagement_imputation_id' => $imp->id,
                    'montant_liquide' => (string) $imp->montant_impute,
                    'date_liquidation' => now()->subDays(40)->toDateString(),
                    'reference' => 'LIQ-'.$engPaye->id.'-'.$imp->id,
                    'commentaire' => 'Liquidation complète démonstration',
                ], $comptable);
                $ord = $execution->recordOrdonnancement($liquidation, [
                    'montant_ordonnance' => (string) $imp->montant_impute,
                    'date_ordonnancement' => now()->subDays(38)->toDateString(),
                    'reference' => 'ORD-'.$engPaye->id.'-'.$imp->id,
                    'commentaire' => 'Ordonnancement complet démonstration',
                ], $comptable);
                $execution->recordPaiement($ord, [
                    'montant_paye' => (string) $imp->montant_impute,
                    'date_paiement' => now()->subDays(35)->toDateString(),
                    'reference_paiement' => 'PAY-'.$engPaye->id.'-'.$imp->id,
                    'mode_paiement' => 'Virement',
                    'commentaire' => 'Paiement complet démonstration',
                ], $comptable);
            }
        }

        if (! $engOrd->liquidations()->exists()) {
            $imp = $engOrd->imputations()->first();
            $liq = $execution->recordLiquidation($engOrd, [
                'engagement_imputation_id' => $imp?->id,
                'montant_liquide' => '18000000.00',
                'date_liquidation' => now()->subDays(22)->toDateString(),
                'reference' => 'LIQ-'.$engOrd->id,
                'commentaire' => 'Liquidation partielle',
            ], $comptable);
            $execution->recordOrdonnancement($liq, [
                'montant_ordonnance' => '12000000.00',
                'date_ordonnancement' => now()->subDays(20)->toDateString(),
                'reference' => 'ORD-'.$engOrd->id,
                'commentaire' => 'Ordonnancement partiel',
            ], $comptable);
        }

        if (! $engLiq->liquidations()->exists()) {
            foreach ($engLiq->imputations as $imp) {
                $execution->recordLiquidation($engLiq, [
                    'engagement_imputation_id' => $imp->id,
                    'montant_liquide' => (string) bcdiv((string) $imp->montant_impute, '2', 2),
                    'date_liquidation' => now()->subDays(12)->toDateString(),
                    'reference' => 'LIQ-'.$engLiq->id.'-'.$imp->id,
                    'commentaire' => 'Liquidation en cours',
                ], $comptable);
            }
        }

        foreach ([$engApp, $engOrd, $engLiq, $engPaye] as $eng) {
            if (! $eng->validationsWorkflow()->exists()) {
                $ordre = 1;
                foreach ((array) config('engagement_workflow.steps') as $step) {
                    ValidationWorkflow::query()->create([
                        'engagement_id' => $eng->id,
                        'etape' => $step['key'],
                        'ordre' => $ordre++,
                        'validateur_id' => $createdUsers['admin.ceeac@example.com']->id,
                        'decision' => DecisionWorkflow::Valide,
                        'commentaire' => 'Validation de démonstration',
                        'date_decision' => now()->subDays(20),
                        'est_validation_deleguee' => false,
                    ]);
                }
            }
        }

        if (! $engRej->validationsWorkflow()->exists()) {
            ValidationWorkflow::query()->create([
                'engagement_id' => $engRej->id,
                'etape' => 'validation_service',
                'ordre' => 1,
                'validateur_id' => $createdUsers['responsable.service@example.com']->id,
                'decision' => DecisionWorkflow::Rejete,
                'commentaire' => 'Dossier rejeté: pièces incomplètes',
                'date_decision' => now()->subDays(9),
                'est_validation_deleguee' => false,
            ]);
        }

        if (! $engRet->validationsWorkflow()->exists()) {
            ValidationWorkflow::query()->create([
                'engagement_id' => $engRet->id,
                'etape' => 'validation_service',
                'ordre' => 1,
                'validateur_id' => $createdUsers['responsable.service@example.com']->id,
                'decision' => DecisionWorkflow::Retourne,
                'commentaire' => 'Retour pour ajustement des montants',
                'date_decision' => now()->subDays(7),
                'est_validation_deleguee' => false,
            ]);
        }

        foreach ([$engPaye, $engOrd, $engLiq, $engRej] as $eng) {
            if (! $eng->piecesJustificatives()->exists()) {
                PieceJustificative::query()->create([
                    'engagement_id' => $eng->id,
                    'type_piece' => 'Contrat',
                    'nom_original' => 'contrat_'.$eng->numero.'.pdf',
                    'nom_stocke' => 'demo_'.$eng->id.'_contrat.pdf',
                    'chemin' => 'engagements/'.$eng->id.'/demo_'.$eng->id.'_contrat.pdf',
                    'extension' => 'pdf',
                    'mime_type' => 'application/pdf',
                    'taille' => 154000,
                    'version' => 1,
                    'checksum' => hash('sha256', $eng->numero.'contrat'),
                    'uploaded_by' => $createdUsers['initiateur.budget@example.com']->id,
                ]);
            }
        }

        AlerteAudit::query()->updateOrCreate(
            ['engagement_id' => $engRej->id, 'code_alerte' => 'AUD-WF-REJ'],
            ['type_alerte' => 'Rejet workflow', 'niveau_risque' => NiveauRisqueAlerte::Eleve, 'score_risque' => 75, 'description' => 'Engagement rejeté au niveau service pour insuffisance documentaire.', 'statut_traitement' => StatutTraitementAlerte::Nouvelle, 'detectee_le' => now()->subDays(9), 'traitee_par' => null, 'commentaire_traitement' => null]
        );
        AlerteAudit::query()->updateOrCreate(
            ['engagement_id' => $engAnnule->id, 'code_alerte' => 'AUD-WF-ANN'],
            ['type_alerte' => 'Annulation engagement', 'niveau_risque' => NiveauRisqueAlerte::Critique, 'score_risque' => 90, 'description' => 'Engagement annulé après approbation.', 'statut_traitement' => StatutTraitementAlerte::EnAnalyse, 'detectee_le' => now()->subDays(33), 'traitee_par' => $createdUsers['auditeur@example.com']->id, 'commentaire_traitement' => 'Contrôle documentaire en cours']
        );

        ImportBudget::query()->updateOrCreate(
            ['exercice_budgetaire_id' => $exerciceN->id, 'fichier_original' => 'import_budget_initial_'.date('Y').'.xlsx'],
            ['chemin_fichier' => 'imports/'.date('Y').'/import_budget_initial.xlsx', 'statut' => StatutImportBudget::Valide, 'total_lignes' => 120, 'lignes_valides' => 118, 'lignes_rejetees' => 2, 'rapport_erreurs' => [['ligne' => 44, 'erreur' => 'Code nature inconnu'], ['ligne' => 97, 'erreur' => 'Montant négatif']], 'imported_by' => $createdUsers['admin.ceeac@example.com']->id, 'validated_by' => $createdUsers['controleur.budget@example.com']->id, 'imported_at' => now()->subDays(60), 'validated_at' => now()->subDays(59)]
        );
        ImportBudget::query()->updateOrCreate(
            ['exercice_budgetaire_id' => $exerciceN1->id, 'fichier_original' => 'import_budget_cloture_'.($year - 1).'.csv'],
            ['chemin_fichier' => 'imports/'.($year - 1).'/import_cloture.csv', 'statut' => StatutImportBudget::Valide, 'total_lignes' => 84, 'lignes_valides' => 84, 'lignes_rejetees' => 0, 'rapport_erreurs' => [], 'imported_by' => $createdUsers['admin.ceeac@example.com']->id, 'validated_by' => $createdUsers['controleur.budget@example.com']->id, 'imported_at' => now()->subDays(200), 'validated_at' => now()->subDays(199)]
        );

        foreach ($createdUsers as $email => $user) {
            ConnexionLog::query()->updateOrCreate(
                ['email' => $email, 'connecte_le' => now()->subMinutes(rand(10, 500))->format('Y-m-d H:i:s')],
                ['user_id' => $user->id, 'succes' => true, 'adresse_ip' => '10.0.0.'.rand(10, 99), 'user_agent' => 'Mozilla/5.0 (Demo Seeder)']
            );
        }

        JournalAudit::query()->updateOrCreate(
            ['action' => 'creation', 'entite' => 'engagement', 'entite_id' => $engPaye->id],
            ['user_id' => $createdUsers['initiateur.budget@example.com']->id, 'anciennes_valeurs' => null, 'nouvelles_valeurs' => ['numero' => $engPaye->numero, 'montant' => $engPaye->montant, 'statut' => $engPaye->statut->value], 'adresse_ip' => '10.0.0.24', 'user_agent' => 'Seeder Demo', 'created_at' => now()->subDays(50)]
        );
        JournalAudit::query()->updateOrCreate(
            ['action' => 'validation', 'entite' => 'engagement', 'entite_id' => $engPaye->id + 100000],
            ['user_id' => $createdUsers['ordonnateur@example.com']->id, 'anciennes_valeurs' => ['statut' => 'en_validation'], 'nouvelles_valeurs' => ['statut' => 'approuve'], 'adresse_ip' => '10.0.0.35', 'user_agent' => 'Seeder Demo', 'created_at' => now()->subDays(45)]
        );

        BudgetJournal::record($createdUsers['admin.ceeac@example.com'], 'Initialisation dataset de démonstration grandeur nature.', null, [
            'seed' => 'CeeacDemoSeeder',
            'engagements_demo' => 9,
            'lignes_budgetaires_demo' => 4,
        ]);
    }
}
