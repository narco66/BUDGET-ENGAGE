<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->string('chemin_fichier', 500);
            $table->string('nom_original', 255)->nullable();
            $table->string('nom_stocke', 255)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->text('commentaire')->nullable();
            $table->string('statut', 30)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['ged_document_id', 'numero_version']);
            $table->index('hash_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_versions');
    }
};
