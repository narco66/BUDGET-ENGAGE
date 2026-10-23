<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_signatures', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ged_version_id')->nullable()->constrained('ged_versions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('signataire_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('demande_par')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('statut', 20)->default('en_attente');
            $table->string('algorithme', 50)->default('HMAC-SHA256');
            $table->string('hash_signe', 64)->nullable();
            $table->text('signature_value')->nullable();
            $table->text('certificat')->nullable();
            $table->timestamp('horodatage')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->string('adresse_ip', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('ged_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_signatures');
    }
};
