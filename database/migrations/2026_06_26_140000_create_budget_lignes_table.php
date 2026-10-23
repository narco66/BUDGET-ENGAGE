<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('version', 30)->default('1.0');

            $table->enum('section', ['recette', 'depense']);
            $table->enum('niveau', ['titre', 'chapitre', 'article', 'paragraphe', 'ligne']);
            $table->string('categorie', 60)->nullable(); // fonctionnement, investissement, equipement, personnel, recette_interne...

            $table->string('code', 50);
            $table->string('libelle', 1000);
            $table->string('parent_code', 50)->nullable();

            // Budget 2026 (FCFA).
            $table->decimal('montant_total', 18, 2)->default(0);
            $table->decimal('montant_ceeac', 18, 2)->default(0); // CEEAC / Etats membres
            $table->decimal('montant_ptf', 18, 2)->default(0);

            // Comparatifs 2025 (FCFA, optionnels).
            $table->decimal('montant_anterieur', 18, 2)->nullable();      // 2025 vote/attendu (CEEAC-EM)
            $table->decimal('montant_realise', 18, 2)->nullable();        // 2025 realise (CEEAC)
            $table->decimal('montant_ptf_anterieur', 18, 2)->nullable();  // 2025 PTF
            $table->decimal('montant_ptf_realise', 18, 2)->nullable();    // 2025 PTF realise
            $table->decimal('taux_realisation', 7, 2)->nullable();        // %
            $table->decimal('variation', 7, 2)->nullable();               // %

            // Rapprochements.
            $table->foreignId('pap_ligne_budgetaire_id')->nullable()->constrained('pap_lignes_budgetaires')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ligne_budgetaire_id')->nullable()->constrained('lignes_budgetaires')->nullOnDelete()->cascadeOnUpdate();

            $table->unsignedInteger('ordre')->default(0);
            $table->string('source', 100)->default('Budget');
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exercice_budgetaire_id', 'code']);
            $table->index(['exercice_budgetaire_id', 'section']);
            $table->index(['exercice_budgetaire_id', 'niveau']);
            $table->index(['exercice_budgetaire_id', 'parent_code']);
            $table->index(['exercice_budgetaire_id', 'categorie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lignes');
    }
};
