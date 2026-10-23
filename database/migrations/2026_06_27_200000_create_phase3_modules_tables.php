<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recettes_perceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnDelete();
            $table->foreignId('budget_ligne_id')->nullable()->constrained('budget_lignes')->nullOnDelete();
            $table->string('numero', 40);
            $table->date('date_perception');
            $table->decimal('montant', 18, 2);
            $table->string('reference')->nullable();
            $table->string('source_emetteur')->nullable();
            $table->string('mode_reglement', 40)->default('virement');
            $table->string('statut', 20)->default('brouillon');
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motif_annulation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exercice_budgetaire_id', 'numero']);
            $table->index(['exercice_budgetaire_id', 'statut']);
        });

        Schema::create('suivi_physique_realisations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre');
            $table->unsignedSmallInteger('annee');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->decimal('taux_avancement', 5, 2)->default(0);
            $table->decimal('quantite_realisee', 18, 4)->nullable();
            $table->string('libelle_realisation')->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['activite_id', 'exercice_budgetaire_id', 'trimestre', 'annee'], 'suivi_physique_unique_periode');
        });

        Schema::create('indicateur_realisations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('indicateur_id')->constrained('indicateurs')->cascadeOnDelete();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre');
            $table->unsignedSmallInteger('annee');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->decimal('valeur_cible', 18, 4)->nullable();
            $table->decimal('valeur_realisee', 18, 4);
            $table->string('source_donnee')->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['indicateur_id', 'exercice_budgetaire_id', 'trimestre', 'annee'], 'indicateur_realisation_unique_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicateur_realisations');
        Schema::dropIfExists('suivi_physique_realisations');
        Schema::dropIfExists('recettes_perceptions');
    }
};
