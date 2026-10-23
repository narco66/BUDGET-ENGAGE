<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('reference_archive', 150)->nullable()->unique();
            $table->year('annee')->nullable();
            $table->string('module', 100)->nullable();
            $table->string('type_document', 100)->nullable();
            $table->string('structure', 150)->nullable();
            $table->text('motif')->nullable();
            $table->string('chemin_archive', 500)->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->boolean('est_verrouille')->default(true);
            $table->foreignId('archive_par')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('date_archivage')->nullable();
            $table->foreignId('restaure_par')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('date_restauration')->nullable();
            $table->timestamps();

            $table->index('annee');
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_archives');
    }
};
