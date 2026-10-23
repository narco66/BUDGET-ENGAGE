<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_doc_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ged_document_id')->nullable()
                ->constrained('ged_documents', 'id', 'pay_doc_ged_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('categorie', 30)->default('specifique'); // herite | specifique
            $table->string('type_piece', 100)->nullable();
            $table->string('source', 60)->nullable();
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_doc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paiement_id', 'categorie'], 'pay_doc_pay_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_documents');
    }
};
