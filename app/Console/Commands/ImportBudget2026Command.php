<?php

namespace App\Console\Commands;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\User;
use App\Services\Budget\BudgetImportService;
use Illuminate\Console\Command;

class ImportBudget2026Command extends Command
{
    protected $signature = 'budget:import-2026
        {--mode=remplacement : creation|mise_a_jour|fusion|remplacement|simulation}
        {--year=2026 : Annee de l\'exercice budgetaire}
        {--budget-version=1.0 : Version du budget}
        {--simulate : Force le mode simulation}
        {--user= : Email de l\'utilisateur executant (defaut : 1er administrateur)}';

    protected $description = 'Importe le Budget officiel de la CEEAC dans la base BUDGET-ENGAGE.';

    public function handle(BudgetImportService $service): int
    {
        $mode = $this->option('simulate') ? 'simulation' : (string) $this->option('mode');
        if (! in_array($mode, BudgetImportService::MODES, true)) {
            $this->error("Mode invalide : {$mode}. Valeurs : ".implode(', ', BudgetImportService::MODES));

            return self::FAILURE;
        }

        $year = (int) $this->option('year');
        $exercice = ExerciceBudgetaire::query()->firstOrCreate(['annee' => $year], [
            'date_debut' => "{$year}-01-01",
            'date_fin' => "{$year}-12-31",
            'statut' => StatutExerciceBudgetaire::Ouvert,
            'est_actif' => true,
        ]);

        $user = $this->resolveUser();
        if ($user === null) {
            $this->error('Aucun utilisateur disponible. Lancez d\'abord le seeder (php artisan db:seed).');

            return self::FAILURE;
        }

        $rows = $service->rowsFromDataset();
        $this->info("Lignes du dataset Budget {$year} : ".count($rows));
        $this->info("Mode : {$mode} | Exercice : {$exercice->annee} | Utilisateur : {$user->email}");

        $import = $service->run($rows, $exercice, $mode, $user, [
            'version' => (string) $this->option('budget-version'),
            'source_label' => 'Document officiel Budget '.$year.' (commande CLI)',
        ]);

        $this->newLine();
        $this->table(['Indicateur', 'Valeur'], [
            ['Statut', $import->statut],
            ['Titres', $import->nb_titres],
            ['Chapitres', $import->nb_chapitres],
            ['Articles', $import->nb_articles],
            ['Paragraphes', $import->nb_paragraphes],
            ['Lignes', $import->nb_lignes],
            ['Recettes', $import->nb_recettes],
            ['Depenses', $import->nb_depenses],
            ['Creations', $import->nb_creations],
            ['Mises a jour', $import->nb_mises_a_jour],
            ['Suppressions', $import->nb_suppressions],
            ['Avertissements', $import->nb_avertissements],
            ['Erreurs (bloquantes)', $import->nb_erreurs],
            ['Total recettes (FCFA)', number_format((float) $import->montant_recettes, 0, '.', ' ')],
            ['Total depenses (FCFA)', number_format((float) $import->montant_depenses, 0, '.', ' ')],
            ['CEEAC (FCFA)', number_format((float) $import->montant_ceeac, 0, '.', ' ')],
            ['PTF (FCFA)', number_format((float) $import->montant_ptf, 0, '.', ' ')],
        ]);

        $attendu = 40_305_795_803;
        if ($import->statut !== 'echoue') {
            $ecartR = (int) round((float) $import->montant_recettes) - $attendu;
            $ecartD = (int) round((float) $import->montant_depenses) - $attendu;
            if ($ecartR === 0 && $ecartD === 0) {
                $this->info("Reconciliation OK : recettes = depenses = {$attendu} FCFA (conforme a l'entete du Budget).");
            } else {
                $this->warn("Reconciliation : ecart recettes {$ecartR}, ecart depenses {$ecartD} vs {$attendu}.");
            }
        }

        if ($import->statut === 'echoue') {
            $this->error("Import echoue : {$import->nb_erreurs} anomalie(s) bloquante(s). Voir l'import #{$import->id}.");

            return self::FAILURE;
        }

        $this->info("Import #{$import->id} termine ({$import->statut}).");

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $email = $this->option('user');
        if ($email !== null) {
            return User::query()->where('email', $email)->first();
        }

        return User::query()->whereHas('roles', fn ($q) => $q->where('name', 'administrateur_general'))->first()
            ?? User::query()->orderBy('id')->first();
    }
}
