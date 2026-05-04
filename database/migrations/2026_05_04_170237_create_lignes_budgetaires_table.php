<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('nature_depense_id')->constrained('natures_depenses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_financement_id')->nullable()->constrained('sources_financement')->nullOnDelete()->cascadeOnUpdate();
            $table->string('code', 100);
            $table->string('libelle', 255);
            $table->decimal('montant_vote', 18, 2)->default(0);
            $table->decimal('montant_revise', 18, 2)->default(0);
            $table->decimal('montant_engage', 18, 2)->default(0);
            $table->decimal('montant_liquide', 18, 2)->default(0);
            $table->decimal('montant_ordonnance', 18, 2)->default(0);
            $table->decimal('montant_paye', 18, 2)->default(0);
            $table->enum('statut', ['active', 'suspendue', 'cloturee'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['exercice_budgetaire_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_budgetaires');
    }
};
