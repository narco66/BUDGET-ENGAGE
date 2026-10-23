<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_metadonnees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('cle', 150);
            $table->text('valeur')->nullable();
            $table->string('type_valeur', 30)->default('string');
            $table->timestamps();

            $table->unique(['ged_document_id', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_metadonnees');
    }
};
