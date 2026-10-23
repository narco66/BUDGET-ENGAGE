<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_types_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->foreignId('categorie_id')->nullable()->constrained('ged_categories')->nullOnDelete()->cascadeOnUpdate();
            $table->string('prefixe_reference', 30)->nullable();
            $table->unsignedInteger('duree_conservation_mois')->nullable();
            $table->string('sort_final', 40)->nullable();
            $table->string('niveau_confidentialite_defaut', 30)->default('interne');
            $table->boolean('est_signable')->default(false);
            $table->json('extensions_autorisees')->nullable();
            $table->unsignedBigInteger('taille_max_ko')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->string('statut', 20)->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('categorie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_types_documents');
    }
};
