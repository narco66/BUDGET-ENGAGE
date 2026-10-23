<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_partages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('partage_par')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('partage_avec_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('partage_avec_role', 100)->nullable();
            $table->string('niveau_acces', 30)->default('lecture');
            $table->string('token', 80)->nullable()->unique();
            $table->timestamp('date_expiration')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->index('ged_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_partages');
    }
};
