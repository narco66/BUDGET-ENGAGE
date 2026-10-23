<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_dossiers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 150)->nullable()->unique();
            $table->string('code', 100)->nullable();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('ged_dossiers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('categorie_id')->nullable()->constrained('ged_categories')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('exercice_budgetaire_id')->nullable()->constrained('exercices_budgetaires')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->string('module_source', 100)->nullable();
            $table->string('statut', 20)->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('parent_id');
            $table->index('exercice_budgetaire_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_dossiers');
    }
};
