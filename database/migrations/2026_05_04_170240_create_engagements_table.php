<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 100)->unique();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ligne_budgetaire_id')->constrained('lignes_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->string('type_engagement', 100);
            $table->enum('beneficiaire_type', ['fournisseur', 'agent', 'autre']);
            $table->unsignedBigInteger('beneficiaire_id')->nullable();
            $table->text('beneficiaire_autre')->nullable();
            $table->text('objet');
            $table->decimal('montant', 18, 2);
            $table->date('date_engagement')->nullable();
            $table->string('statut', 50)->default('brouillon');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->text('motif_annulation')->nullable();

            $table->boolean('is_urgence')->default(false);
            $table->text('justification_urgence')->nullable();
            $table->enum('statut_regularisation', [
                'en_attente_regularisation',
                'en_cours_regularisation',
                'regularise',
                'non_regularise',
            ])->nullable();
            $table->date('regularisation_deadline')->nullable();
            $table->foreignId('engagement_urgence_parent_id')->nullable()->constrained('engagements')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedTinyInteger('score_risque')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('type_engagement');
            $table->index('date_engagement');
            $table->index('ligne_budgetaire_id');
            $table->index('exercice_budgetaire_id');
            $table->index(['beneficiaire_type', 'beneficiaire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};
