<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des comptes bancaires de décaissement (moteur bancaire) :
 * banque, agence, IBAN/RIB, BIC/SWIFT, devise, signataires, solde et plafond.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_banques', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('libelle');
            $table->string('banque', 150)->nullable();
            $table->string('agence', 150)->nullable();
            $table->string('titulaire', 200)->nullable();
            $table->string('iban', 60)->nullable();
            $table->string('rib', 60)->nullable();
            $table->string('bic_swift', 20)->nullable();
            $table->string('devise', 10)->default('XAF');

            $table->decimal('solde_initial', 18, 2)->nullable();
            $table->decimal('plafond_decaissement', 18, 2)->nullable();
            $table->json('signataires')->nullable();

            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_banque_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'id', 'pay_banque_updated_by_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_banques');
    }
};
