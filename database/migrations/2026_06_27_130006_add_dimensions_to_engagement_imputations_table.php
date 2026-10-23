<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichissement NON destructif des imputations d'engagement avec des dimensions
 * analytiques optionnelles (source de financement, activité PAP, centres). Ces
 * colonnes sont nullable : le mécanisme de réservation existant (qui ne lit que
 * ligne_budgetaire_id + montant_impute) n'est pas affecté.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagement_imputations', function (Blueprint $table) {
            $table->foreignId('source_financement_id')->nullable()->after('ligne_budgetaire_id')
                ->constrained('sources_financement')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()->after('source_financement_id')
                ->constrained('activites')->nullOnDelete()->cascadeOnUpdate();
            $table->string('centre_cout', 255)->nullable()->after('activite_id');
            $table->string('centre_responsabilite', 255)->nullable()->after('centre_cout');
        });
    }

    public function down(): void
    {
        Schema::table('engagement_imputations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_financement_id');
            $table->dropConstrainedForeignId('activite_id');
            $table->dropColumn(['centre_cout', 'centre_responsabilite']);
        });
    }
};
