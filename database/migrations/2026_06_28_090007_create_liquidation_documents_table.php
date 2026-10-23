<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre documentaire de la liquidation. Le lien physique reste porté par la
 * GED (ged_document_attachments) ; cette table classe les pièces en
 * « héritées » (Engagement / Besoin) vs « spécifiques » à la liquidation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_doc_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ged_document_id')->nullable()
                ->constrained('ged_documents', 'id', 'liq_doc_ged_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('categorie', 30)->default('specifique'); // herite | specifique
            $table->string('type_piece', 100)->nullable();
            $table->string('source', 60)->nullable(); // engagement | besoin | liquidation
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'liq_doc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['liquidation_id', 'categorie'], 'liq_doc_liq_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_documents');
    }
};
