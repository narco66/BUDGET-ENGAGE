<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table pivot polymorphe : rattache un document GED à n'importe quelle entité
 * métier (Engagement, Liquidation, Ordonnancement, Paiement, LigneBudgetaire,
 * ExerciceBudgetaire, RBM, structures...). N'altère aucune table existante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_document_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('role', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->unique(['ged_document_id', 'attachable_type', 'attachable_id'], 'ged_doc_attachable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_document_attachments');
    }
};
