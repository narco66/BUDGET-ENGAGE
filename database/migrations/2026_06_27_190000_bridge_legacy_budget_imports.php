<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_imports', function (Blueprint $table): void {
            $table->foreignId('legacy_import_budget_id')
                ->nullable()
                ->after('restored_from_id')
                ->constrained('imports_budget')
                ->nullOnDelete();
        });

        Schema::table('imports_budget', function (Blueprint $table): void {
            $table->foreignId('migrated_budget_import_id')
                ->nullable()
                ->after('validated_at')
                ->constrained('budget_imports')
                ->nullOnDelete();
        });

        $this->migrateLegacyRows();
    }

    public function down(): void
    {
        Schema::table('imports_budget', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('migrated_budget_import_id');
        });

        Schema::table('budget_imports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('legacy_import_budget_id');
        });
    }

    private function migrateLegacyRows(): void
    {
        if (! Schema::hasTable('imports_budget') || ! Schema::hasTable('budget_imports')) {
            return;
        }

        $legacyRows = DB::table('imports_budget')
            ->whereNull('migrated_budget_import_id')
            ->get();

        foreach ($legacyRows as $legacy) {
            $statut = match ($legacy->statut) {
                'valide' => 'valide',
                'rejete' => 'echoue',
                'annule' => 'annule',
                default => 'simule',
            };

            $modernId = DB::table('budget_imports')->insertGetId([
                'exercice_budgetaire_id' => $legacy->exercice_budgetaire_id,
                'version' => 'legacy-csv-1.0',
                'mode' => 'legacy_csv',
                'statut' => $statut,
                'fichier_original' => $legacy->fichier_original,
                'chemin_fichier' => $legacy->chemin_fichier,
                'source_label' => 'Import CSV legacy (imports_budget)',
                'nb_lignes' => $legacy->total_lignes,
                'nb_erreurs' => $legacy->lignes_rejetees,
                'rapport' => $legacy->rapport_erreurs,
                'resume' => json_encode([
                    'lignes_valides' => $legacy->lignes_valides,
                    'lignes_rejetees' => $legacy->lignes_rejetees,
                ]),
                'legacy_import_budget_id' => $legacy->id,
                'user_id' => $legacy->imported_by,
                'started_at' => $legacy->imported_at,
                'finished_at' => $legacy->validated_at ?? $legacy->imported_at,
                'created_at' => $legacy->created_at ?? now(),
                'updated_at' => $legacy->updated_at ?? now(),
            ]);

            DB::table('imports_budget')
                ->where('id', $legacy->id)
                ->update(['migrated_budget_import_id' => $modernId]);
        }
    }
};
