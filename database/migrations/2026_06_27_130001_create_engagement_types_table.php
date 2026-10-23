<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel administrable des types d'engagement (architecture polymorphe).
 * Le type pilote les champs spécifiques (EAV), les documents obligatoires et,
 * à terme, un circuit de validation dédié — sans modifier le schéma de base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->string('famille', 60)->nullable();
            $table->string('nature', 60)->nullable();
            $table->json('champs_specifiques')->nullable();
            $table->json('documents_obligatoires')->nullable();
            $table->string('workflow_code', 100)->nullable();
            $table->boolean('permet_generation')->default(true);
            $table->boolean('permet_creation_manuelle')->default(true);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('est_actif');
            $table->index('famille');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_types');
    }
};
