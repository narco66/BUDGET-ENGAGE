<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaires_charges_personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('mois');
            $table->decimal('montant_salaire', 18, 2)->default(0);
            $table->decimal('montant_charges', 18, 2)->default(0);
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agent_id', 'exercice_budgetaire_id', 'mois'], 'scp_agent_exercice_mois_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaires_charges_personnels');
    }
};
