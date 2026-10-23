<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pap_lignes_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('cadre_logique_id')->nullable()->constrained('cadres_logiques')->nullOnDelete()->cascadeOnUpdate();

            // Rattachement aux noeuds RBM existants (un seul renseigne selon le niveau).
            $table->foreignId('pilier_id')->nullable()->constrained('piliers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('axe_strategique_id')->nullable()->constrained('axes_strategiques')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('sous_produit_id')->nullable()->constrained('sous_produits')->nullOnDelete()->cascadeOnUpdate();

            $table->string('code', 50);
            $table->string('libelle', 1000);
            $table->enum('niveau', ['pilier', 'axe', 'produit', 'sous_produit']);
            $table->string('parent_code', 50)->nullable();

            // Budget 2026 (en FCFA).
            $table->decimal('montant_total', 18, 2)->default(0);
            $table->decimal('montant_ceeac', 18, 2)->default(0);
            $table->decimal('montant_ptf', 18, 2)->default(0);

            // Comparatifs 2025 (en FCFA, optionnels).
            $table->decimal('montant_ceeac_2025', 18, 2)->nullable();
            $table->decimal('realisation_ceeac_2025', 18, 2)->nullable();
            $table->decimal('montant_ptf_2025', 18, 2)->nullable();
            $table->decimal('realisation_ptf_2025', 18, 2)->nullable();

            $table->unsignedInteger('ordre')->default(0);
            $table->string('source', 100)->default('PAP');
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exercice_budgetaire_id', 'code']);
            $table->index(['exercice_budgetaire_id', 'niveau']);
            $table->index(['exercice_budgetaire_id', 'parent_code']);
            $table->index('cadre_logique_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pap_lignes_budgetaires');
    }
};
