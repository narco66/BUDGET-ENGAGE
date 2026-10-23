<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Liste de contrôle d'accès (ACL) fine par document : par utilisateur ou par rôle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_acces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('role_name', 100)->nullable();
            $table->boolean('peut_voir')->default(true);
            $table->boolean('peut_telecharger')->default(false);
            $table->boolean('peut_modifier')->default(false);
            $table->boolean('peut_signer')->default(false);
            $table->boolean('peut_partager')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index('ged_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_acces');
    }
};
