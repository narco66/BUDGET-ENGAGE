<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rejets bancaires : motif (compte invalide, insuffisance de provision, erreur
 * de devise, doublon…), traitement et workflow de reprise.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_rejets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_rejet_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('motif', 60); // rejet_bancaire | compte_invalide | insuffisance_provision | erreur_devise | doublon | annulation
            $table->text('description')->nullable();
            $table->date('date_rejet');
            $table->string('reference_rejet', 100)->nullable();
            $table->boolean('traite')->default(false);
            $table->foreignId('traite_par')->nullable()
                ->constrained('users', 'id', 'pay_rejet_traite_par_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('traite_at')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_rejet_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('paiement_id', 'pay_rejet_pay_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_rejets');
    }
};
