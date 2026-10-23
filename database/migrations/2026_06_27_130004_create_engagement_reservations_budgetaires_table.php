<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre explicite des réservations de crédit par engagement et ligne
 * budgétaire. La source de vérité du crédit engagé reste `lignes_budgetaires.
 * montant_engage` (inchangée) ; cette table fournit la traçabilité détaillée
 * (réservation à l'approbation, libération à l'annulation).
 *
 * Noms de contraintes FK explicites et courts : le nom de table dépasse la
 * limite d'identifiant MySQL (64 car.) avec les noms auto-générés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_reservations_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')
                ->constrained('engagements', 'id', 'eng_resa_engagement_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ligne_budgetaire_id')
                ->constrained('lignes_budgetaires', 'id', 'eng_resa_ligne_fk')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('exercice_budgetaire_id')->nullable()
                ->constrained('exercices_budgetaires', 'id', 'eng_resa_exercice_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('montant_reserve', 18, 2);
            $table->string('sens', 20)->default('reservation');
            $table->string('statut', 20)->default('active');
            $table->foreignId('reserve_par')->nullable()
                ->constrained('users', 'id', 'eng_resa_user_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('date_reservation')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->index('engagement_id', 'eng_resa_engagement_idx');
            $table->index('ligne_budgetaire_id', 'eng_resa_ligne_idx');
            $table->index('statut', 'eng_resa_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_reservations_budgetaires');
    }
};
