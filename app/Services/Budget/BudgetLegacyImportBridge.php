<?php

namespace App\Services\Budget;

use App\Enums\StatutImportBudget;
use App\Models\BudgetImport;
use App\Models\ImportBudget;

/**
 * Pont de migration non destructive entre imports_budget (legacy CSV)
 * et budget_imports (nomenclature 2026).
 */
class BudgetLegacyImportBridge
{
    public function findOrMigrate(ImportBudget $legacy): BudgetImport
    {
        if ($legacy->migrated_budget_import_id !== null) {
            return BudgetImport::query()->findOrFail($legacy->migrated_budget_import_id);
        }

        return $this->migrate($legacy);
    }

    public function migrate(ImportBudget $legacy): BudgetImport
    {
        if ($legacy->migrated_budget_import_id !== null) {
            return BudgetImport::query()->findOrFail($legacy->migrated_budget_import_id);
        }

        $modern = BudgetImport::query()->create([
            'exercice_budgetaire_id' => $legacy->exercice_budgetaire_id,
            'version' => 'legacy-csv-1.0',
            'mode' => 'legacy_csv',
            'statut' => $this->mapStatut($legacy->statut),
            'fichier_original' => $legacy->fichier_original,
            'chemin_fichier' => $legacy->chemin_fichier,
            'source_label' => 'Import CSV legacy (imports_budget)',
            'nb_lignes' => $legacy->total_lignes,
            'nb_erreurs' => $legacy->lignes_rejetees,
            'rapport' => $legacy->rapport_erreurs,
            'resume' => [
                'lignes_valides' => $legacy->lignes_valides,
                'lignes_rejetees' => $legacy->lignes_rejetees,
            ],
            'legacy_import_budget_id' => $legacy->id,
            'user_id' => $legacy->imported_by,
            'started_at' => $legacy->imported_at,
            'finished_at' => $legacy->validated_at ?? $legacy->imported_at,
        ]);

        $legacy->update(['migrated_budget_import_id' => $modern->id]);

        return $modern;
    }

    private function mapStatut(StatutImportBudget $statut): string
    {
        return match ($statut) {
            StatutImportBudget::Valide => 'valide',
            StatutImportBudget::Rejete => 'echoue',
            StatutImportBudget::Annule => 'annule',
            default => 'simule',
        };
    }
}
