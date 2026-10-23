<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rapprochement bancaire : lignes de relevé importées (Excel/CSV/MT940/CAMT.053)
 * rapprochées automatiquement ou manuellement aux paiements, avec écarts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_rapprochements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')->nullable()
                ->constrained('paiements', 'id', 'pay_rappr_pay_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('paiement_banque_id')->nullable()
                ->constrained('paiement_banques', 'id', 'pay_rappr_banque_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('reference_releve', 150)->nullable();
            $table->date('date_operation');
            $table->decimal('montant_releve', 18, 2);
            $table->decimal('montant_paiement', 18, 2)->nullable();
            $table->decimal('ecart', 18, 2)->nullable();
            $table->string('statut', 30)->default('non_rapproche'); // rapproche | ecart | non_rapproche
            $table->string('mode', 20)->default('manuel'); // auto | manuel
            $table->string('source_import', 30)->nullable(); // excel | csv | mt940 | camt053

            $table->boolean('valide')->default(false);
            $table->foreignId('valide_par')->nullable()
                ->constrained('users', 'id', 'pay_rappr_valide_par_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('valide_at')->nullable();
            $table->text('observation')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_rappr_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['paiement_banque_id', 'date_operation'], 'pay_rappr_banque_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_rapprochements');
    }
};
