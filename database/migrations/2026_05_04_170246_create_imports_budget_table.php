<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports_budget', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('fichier_original', 255);
            $table->string('chemin_fichier', 500);
            $table->enum('statut', ['importe', 'en_validation', 'valide', 'rejete', 'annule'])->default('importe');
            $table->unsignedInteger('total_lignes')->default(0);
            $table->unsignedInteger('lignes_valides')->default(0);
            $table->unsignedInteger('lignes_rejetees')->default(0);
            $table->json('rapport_erreurs')->nullable();
            $table->foreignId('imported_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports_budget');
    }
};
