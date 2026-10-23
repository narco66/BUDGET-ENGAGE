<?php

namespace App\Console\Commands;

use App\Enums\StatutExerciceBudgetaire;
use App\Models\ExerciceBudgetaire;
use App\Models\User;
use App\Services\Rbm\PapImportService;
use Illuminate\Console\Command;

class ImportPap2026Command extends Command
{
    protected $signature = 'pap:import-2026
        {--mode=remplacement : creation|mise_a_jour|remplacement|simulation}
        {--year=2026 : Annee de l\'exercice budgetaire}
        {--pap-version=1.0 : Version du PAP}
        {--simulate : Force le mode simulation}
        {--user= : Email de l\'utilisateur executant (defaut : 1er administrateur)}';

    protected $description = 'Importe le Plan Annuel de Performance (PAP) officiel dans la base BUDGET-ENGAGE.';

    public function handle(PapImportService $service): int
    {
        $mode = $this->option('simulate') ? 'simulation' : (string) $this->option('mode');
        if (! in_array($mode, PapImportService::MODES, true)) {
            $this->error("Mode invalide : {$mode}. Valeurs : ".implode(', ', PapImportService::MODES));

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
        $this->info("Lignes du dataset PAP {$year} : ".count($rows));
        $this->info("Mode : {$mode} | Exercice : {$exercice->annee} | Utilisateur : {$user->email}");

        $import = $service->run($rows, $exercice, $mode, $user, [
            'version' => (string) $this->option('pap-version'),
            'source_label' => 'Document officiel PAP '.$year.' (commande CLI)',
        ]);

        $this->newLine();
        $this->table(
            ['Indicateur', 'Valeur'],
            [
                ['Statut', $import->statut],
                ['Piliers', $import->nb_piliers],
                ['Axes', $import->nb_axes],
                ['Produits', $import->nb_produits],
                ['Sous-produits', $import->nb_sous_produits],
                ['Lignes', $import->nb_lignes],
                ['Creations', $import->nb_creations],
                ['Mises a jour', $import->nb_mises_a_jour],
                ['Suppressions', $import->nb_suppressions],
                ['Doublons', $import->nb_doublons],
                ['Avertissements', $import->nb_avertissements],
                ['Erreurs (bloquantes)', $import->nb_erreurs],
                ['Montant total (FCFA)', number_format((float) $import->montant_total, 0, '.', ' ')],
            ]
        );

        // Reconciliation avec l'entete officielle du document.
        $attendu = 22_365_281_000;
        $ecart = (int) round((float) $import->montant_total) - $attendu;
        if ($import->statut !== 'echoue') {
            if ($ecart === 0) {
                $this->info("Reconciliation OK : montant total = {$attendu} FCFA (conforme a l'entete du PAP).");
            } else {
                $this->warn("Reconciliation : ecart de {$ecart} FCFA par rapport a l'attendu ({$attendu}).");
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

        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'administrateur_general'))
            ->first()
            ?? User::query()->orderBy('id')->first();
    }
}
