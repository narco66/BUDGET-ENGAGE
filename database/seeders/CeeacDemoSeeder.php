<?php

namespace Database\Seeders;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CeeacDemoSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Departement::query()->firstOrCreate(
            ['code' => 'DB'],
            ['libelle' => 'Direction du Budget', 'description' => null, 'statut' => 'actif']
        );

        $dir = Direction::query()->firstOrCreate(
            ['departement_id' => $dept->id, 'code' => 'CB'],
            ['libelle' => 'Cellule budgétaire', 'description' => null, 'statut' => 'actif']
        );

        $svc = Service::query()->firstOrCreate(
            ['direction_id' => $dir->id, 'code' => 'SUIVI'],
            ['libelle' => 'Suivi des engagements', 'description' => null, 'statut' => 'actif']
        );

        $nature = NatureDepense::query()->firstOrCreate(
            ['code' => '606'],
            ['libelle' => 'Achats de biens et services', 'description' => null, 'statut' => 'actif']
        );

        $source = SourceFinancement::query()->firstOrCreate(
            ['code' => 'BF'],
            ['libelle' => 'Budget de fonctionnement', 'description' => null, 'statut' => 'actif']
        );

        $exercice = ExerciceBudgetaire::query()->firstOrCreate(
            ['annee' => (int) date('Y')],
            [
                'date_debut' => now()->startOfYear()->toDateString(),
                'date_fin' => now()->endOfYear()->toDateString(),
                'statut' => StatutExerciceBudgetaire::Ouvert,
                'est_actif' => true,
            ]
        );

        LigneBudgetaire::query()->firstOrCreate(
            [
                'exercice_budgetaire_id' => $exercice->id,
                'code' => 'LIG-DEMO-001',
            ],
            [
                'departement_id' => $dept->id,
                'direction_id' => $dir->id,
                'service_id' => $svc->id,
                'nature_depense_id' => $nature->id,
                'source_financement_id' => $source->id,
                'libelle' => 'Ligne de démonstration — exécution budgétaire',
                'montant_vote' => 1_000_000_000,
                'montant_revise' => 1_000_000_000,
                'montant_engage' => 0,
                'montant_liquide' => 0,
                'montant_ordonnance' => 0,
                'montant_paye' => 0,
                'statut' => 'active',
            ]
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin.ceeac@example.com'],
            [
                'service_id' => $svc->id,
                'name' => 'Administrateur',
                'prenom' => 'CEEAC',
                'telephone' => null,
                'password' => Hash::make('password'),
                'statut' => 'actif',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('administrateur_general');
    }
}
