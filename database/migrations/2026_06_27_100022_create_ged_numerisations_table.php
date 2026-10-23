<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_numerisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->nullable()->constrained('ged_documents')->nullOnDelete()->cascadeOnUpdate();
            $table->string('lot_reference', 150)->nullable();
            $table->string('source', 50)->default('upload');
            $table->unsignedInteger('nb_pages')->nullable();
            $table->unsignedInteger('resolution_dpi')->nullable();
            $table->string('format', 30)->nullable();
            $table->string('ocr_status', 30)->default('en_attente');
            $table->longText('ocr_contenu')->nullable();
            $table->string('chemin_fichier', 500)->nullable();
            $table->foreignId('numerise_par')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('date_numerisation')->nullable();
            $table->timestamps();

            $table->index('ged_document_id');
            $table->index('ocr_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_numerisations');
    }
};
