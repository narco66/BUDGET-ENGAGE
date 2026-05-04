<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercices_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->year('annee')->unique();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['preparatoire', 'ouvert', 'suspendu', 'cloture', 'archive'])->default('preparatoire');
            $table->boolean('est_actif')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercices_budgetaires');
    }
};
