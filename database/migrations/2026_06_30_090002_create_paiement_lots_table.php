<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lots de paiement (paiements groupés) : regroupent plusieurs paiements pour un
 * décaissement bancaire commun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_lots', function (Blueprint $table): void {
            $table->id();
            $table->string('numero', 60)->unique();
            $table->string('libelle')->nullable();
            $table->date('date_lot');
            $table->string('statut', 40)->default('ouvert');
            $table->foreignId('paiement_banque_id')->nullable()
                ->constrained('paiement_banques', 'id', 'pay_lot_banque_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('montant_total', 18, 2)->default(0);
            $table->string('devise', 10)->default('XAF');
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_lot_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_lots');
    }
};
