<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Écritures comptables générées par le paiement (partie double SYSCOHADA) :
 * une ligne par compte mouvementé (débit / crédit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_ecritures_comptables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_ecr_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('numero_ecriture', 60);
            $table->date('date_ecriture');
            $table->string('journal', 20)->default('BQ');
            $table->string('compte', 20);
            $table->string('libelle');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('devise', 10)->default('XAF');
            $table->string('lettrage', 30)->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_ecr_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['paiement_id', 'numero_ecriture'], 'pay_ecr_pay_num_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_ecritures_comptables');
    }
};
