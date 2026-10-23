<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ged_workflow_etape_id')->nullable()->constrained('ged_workflow_etapes')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('ordre')->nullable();
            $table->string('libelle_etape', 255)->nullable();
            $table->string('type_etape', 40)->nullable();
            $table->foreignId('validateur_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('statut', 20)->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamp('date_action')->nullable();
            $table->timestamps();

            $table->index(['ged_document_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_validations');
    }
};
