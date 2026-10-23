<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versions d'un engagement : à chaque modification autorisée, un instantané
 * complet (engagement + imputations + champs spécifiques) est conservé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->json('snapshot');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['engagement_id', 'numero_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_versions');
    }
};
