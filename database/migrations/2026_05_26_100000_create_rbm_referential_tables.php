<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'ordre']);
        });

        Schema::create('axes_strategiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilier_id')->constrained('piliers')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pilier_id', 'code']);
            $table->unique(['pilier_id', 'libelle']);
            $table->index(['pilier_id', 'statut', 'ordre']);
        });

        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('axe_strategique_id')->constrained('axes_strategiques')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['axe_strategique_id', 'code']);
            $table->unique(['axe_strategique_id', 'libelle']);
            $table->index(['axe_strategique_id', 'statut', 'ordre']);
        });

        Schema::create('sous_produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['produit_id', 'code']);
            $table->unique(['produit_id', 'libelle']);
            $table->index(['produit_id', 'statut', 'ordre']);
        });

        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sous_produit_id')->constrained('sous_produits')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sous_produit_id', 'code']);
            $table->unique(['sous_produit_id', 'libelle']);
            $table->index(['sous_produit_id', 'statut', 'ordre']);
        });

        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['activite_id', 'code']);
            $table->unique(['activite_id', 'libelle']);
            $table->index(['activite_id', 'statut', 'ordre']);
        });

        Schema::create('types_indicateurs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'ordre']);
        });

        Schema::create('unites_mesure', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->string('symbole', 20)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'ordre']);
        });

        Schema::create('sources_verification', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'ordre']);
        });

        Schema::create('indicateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_indicateur_id')->constrained('types_indicateurs')->restrictOnDelete();
            $table->foreignId('unite_mesure_id')->constrained('unites_mesure')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type_indicateur_id', 'statut']);
            $table->index(['unite_mesure_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicateurs');
        Schema::dropIfExists('sources_verification');
        Schema::dropIfExists('unites_mesure');
        Schema::dropIfExists('types_indicateurs');
        Schema::dropIfExists('taches');
        Schema::dropIfExists('activites');
        Schema::dropIfExists('sous_produits');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('axes_strategiques');
        Schema::dropIfExists('piliers');
    }
};
