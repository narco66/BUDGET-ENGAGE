<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virements_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ligne_source_id')->constrained('lignes_budgetaires')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('ligne_destination_id')->constrained('lignes_budgetaires')->restrictOnDelete()->cascadeOnUpdate();

            $table->decimal('montant', 18, 2);
            $table->text('motif');

            $table->decimal('source_revise_avant', 18, 2);
            $table->decimal('source_revise_apres', 18, 2);
            $table->decimal('destination_revise_avant', 18, 2);
            $table->decimal('destination_revise_apres', 18, 2);

            $table->string('statut', 20)->default('valide'); // valide | annule
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adresse_ip', 64)->nullable();
            $table->timestamps();

            $table->index(['exercice_budgetaire_id', 'created_at']);
            $table->index('ligne_source_id');
            $table->index('ligne_destination_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virements_credits');
    }
};
