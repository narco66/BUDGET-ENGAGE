<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_doc_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ged_document_id')->nullable()
                ->constrained('ged_documents', 'id', 'ord_doc_ged_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('categorie', 30)->default('specifique'); // herite | specifique
            $table->string('type_piece', 100)->nullable();
            $table->string('source', 60)->nullable(); // besoin | engagement | liquidation | ordonnancement
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'ord_doc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ordonnancement_id', 'categorie'], 'ord_doc_ord_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_documents');
    }
};
