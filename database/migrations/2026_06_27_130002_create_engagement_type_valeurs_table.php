<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Valeurs des champs spécifiques d'un engagement (EAV), pilotées par le type.
 * Évite toute modification du schéma de base lors de l'ajout de nouveaux types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_type_valeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('cle', 150);
            $table->text('valeur')->nullable();
            $table->string('type_valeur', 30)->default('string');
            $table->timestamps();

            $table->unique(['engagement_id', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_type_valeurs');
    }
};
