<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expression_besoin_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expression_besoin_id')->constrained('expression_besoins')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('designation', 255);
            $table->decimal('quantite', 15, 3)->default(1);
            $table->string('unite', 50)->nullable();
            $table->decimal('prix_unitaire', 18, 2)->default(0);
            $table->decimal('montant', 18, 2)->default(0);
            $table->foreignId('ligne_budgetaire_id')->nullable()->constrained('lignes_budgetaires')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('source_financement_id')->nullable()->constrained('sources_financement')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete()->cascadeOnUpdate();
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('expression_besoin_id');
            $table->index('ligne_budgetaire_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expression_besoin_lignes');
    }
};
