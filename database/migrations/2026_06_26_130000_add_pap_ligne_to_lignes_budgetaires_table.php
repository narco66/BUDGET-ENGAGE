<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_budgetaires', function (Blueprint $table) {
            $table->foreignId('pap_ligne_budgetaire_id')
                ->nullable()
                ->after('source_financement_id')
                ->constrained('pap_lignes_budgetaires')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->index('pap_ligne_budgetaire_id');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_budgetaires', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pap_ligne_budgetaire_id');
        });
    }
};
