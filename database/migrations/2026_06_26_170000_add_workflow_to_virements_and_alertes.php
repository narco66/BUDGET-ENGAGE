<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virements_credits', function (Blueprint $table) {
            $table->foreignId('approuve_par')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('decision_at')->nullable()->after('approuve_par');
            $table->text('motif_decision')->nullable()->after('decision_at');
        });

        Schema::table('alertes_budgetaires', function (Blueprint $table) {
            $table->text('commentaire_traitement')->nullable()->after('traitee_le');
        });
    }

    public function down(): void
    {
        Schema::table('virements_credits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approuve_par');
            $table->dropColumn(['decision_at', 'motif_decision']);
        });

        Schema::table('alertes_budgetaires', function (Blueprint $table) {
            $table->dropColumn('commentaire_traitement');
        });
    }
};
