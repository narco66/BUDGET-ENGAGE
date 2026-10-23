<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expression_besoins', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 100)->unique();

            // Onglet 1 — informations générales
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('type_besoin', 20);
            $table->string('structure_demandeuse', 255)->nullable();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('demandeur_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->date('date_besoin')->nullable();
            $table->text('objet');
            $table->text('description')->nullable();
            $table->string('priorite', 20)->default('normale');
            $table->string('niveau_urgence', 20)->default('normal');

            // Onglet 2 — justification
            $table->text('justification')->nullable();
            $table->text('objectif_recherche')->nullable();
            $table->text('consequences_non_realisation')->nullable();
            $table->text('observations')->nullable();

            // Onglet 3 — rattachement stratégique (PAP)
            $table->foreignId('pilier_id')->nullable()->constrained('piliers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('axe_strategique_id')->nullable()->constrained('axes_strategiques')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('sous_produit_id')->nullable()->constrained('sous_produits')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tache_id')->nullable()->constrained('taches')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('indicateur_id')->nullable()->constrained('indicateurs')->nullOnDelete()->cascadeOnUpdate();
            $table->string('responsable_activite', 255)->nullable();

            // Onglet 3 — rattachement (Hors PAP)
            $table->string('categorie_hors_pap', 60)->nullable();
            $table->string('sous_categorie', 255)->nullable();
            $table->string('nature_besoin', 255)->nullable();
            $table->string('centre_cout', 255)->nullable();
            $table->string('centre_responsabilite', 255)->nullable();

            // Synthèse budgétaire
            $table->decimal('montant_total', 18, 2)->default(0);

            // Cycle de vie
            $table->string('statut', 30)->default('brouillon');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('motif_rejet')->nullable();

            // Conversion en engagement
            $table->foreignId('engagement_id')->nullable()->constrained('engagements')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('type_besoin');
            $table->index('exercice_budgetaire_id');
            $table->index('departement_id');
            $table->index('activite_id');
            $table->index('engagement_id');
            $table->index('date_besoin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expression_besoins');
    }
};
