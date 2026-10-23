<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ligne_budgetaire_id')->constrained('lignes_budgetaires')->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('code', 100);
            $table->string('type', 40)->default('seuil_engagement');
            $table->string('niveau', 20); // avertissement | critique | depassement
            $table->unsignedSmallInteger('seuil');         // % declencheur
            $table->decimal('taux', 7, 2)->default(0);     // taux d'engagement constate
            $table->decimal('montant_engage', 18, 2)->default(0);
            $table->decimal('plafond', 18, 2)->default(0);
            $table->string('message', 500);
            $table->string('statut', 20)->default('nouvelle'); // nouvelle | traitee
            $table->timestamp('detectee_le')->nullable();
            $table->foreignId('traitee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traitee_le')->nullable();
            $table->timestamps();

            $table->unique('ligne_budgetaire_id');
            $table->index(['exercice_budgetaire_id', 'statut']);
            $table->index('niveau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes_budgetaires');
    }
};
