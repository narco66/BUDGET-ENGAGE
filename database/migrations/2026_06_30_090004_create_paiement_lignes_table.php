<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_lignes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_ligne_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('ordonnancement_ligne_id')->nullable()
                ->constrained('ordonnancement_lignes', 'id', 'pay_ligne_ordligne_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('liquidation_ligne_id')->nullable()
                ->constrained('liquidation_lignes', 'id', 'pay_ligne_liqligne_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('engagement_imputation_id')->nullable()
                ->constrained('engagement_imputations', 'id', 'pay_ligne_imput_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ligne_budgetaire_id')->nullable()
                ->constrained('lignes_budgetaires', 'id', 'pay_ligne_lb_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('source_financement_id')->nullable()
                ->constrained('sources_financement', 'id', 'pay_ligne_sf_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()
                ->constrained('activites', 'id', 'pay_ligne_act_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tache_id')->nullable()
                ->constrained('taches', 'id', 'pay_ligne_tache_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('pap_ligne_budgetaire_id')->nullable()
                ->constrained('pap_lignes_budgetaires', 'id', 'pay_ligne_pap_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('centre_cout', 100)->nullable();
            $table->string('centre_responsabilite', 100)->nullable();

            $table->string('designation');
            $table->decimal('montant_ordonnance', 18, 2)->default(0);
            $table->decimal('montant_paye', 18, 2)->default(0);
            $table->decimal('reste_a_payer', 18, 2)->nullable();

            $table->string('devise', 10)->default('XAF');
            $table->decimal('taux_change', 18, 6)->nullable();
            $table->decimal('frais_bancaires', 18, 2)->default(0);
            $table->string('reference_bancaire', 100)->nullable();

            $table->text('observation')->nullable();
            $table->unsignedInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paiement_id', 'ordre'], 'pay_ligne_pay_ordre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_lignes');
    }
};
