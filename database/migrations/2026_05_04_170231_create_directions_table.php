<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['departement_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};
