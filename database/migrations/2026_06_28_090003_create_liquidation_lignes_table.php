<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_lignes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_ligne_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();

            // Dimensions budgétaires / PAP (toutes optionnelles : liquidation simple → complexe).
            $table->foreignId('engagement_imputation_id')->nullable()
                ->constrained('engagement_imputations', 'id', 'liq_ligne_imput_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ligne_budgetaire_id')->nullable()
                ->constrained('lignes_budgetaires', 'id', 'liq_ligne_lb_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('source_financement_id')->nullable()
                ->constrained('sources_financement', 'id', 'liq_ligne_sf_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()
                ->constrained('activites', 'id', 'liq_ligne_act_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tache_id')->nullable()
                ->constrained('taches', 'id', 'liq_ligne_tache_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('pap_ligne_budgetaire_id')->nullable()
                ->constrained('pap_lignes_budgetaires', 'id', 'liq_ligne_pap_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('centre_cout', 100)->nullable();
            $table->string('centre_responsabilite', 100)->nullable();

            $table->string('designation');
            $table->decimal('quantite_prevue', 18, 4)->nullable();
            $table->decimal('quantite_realisee', 18, 4)->nullable();
            $table->decimal('prix_unitaire', 18, 2)->nullable();

            $table->decimal('montant_brut', 18, 2)->default(0);
            $table->decimal('montant_avances', 18, 2)->default(0);
            $table->decimal('montant_acomptes', 18, 2)->default(0);
            $table->decimal('montant_retenues', 18, 2)->default(0);
            $table->decimal('montant_garanties', 18, 2)->default(0);
            $table->decimal('montant_penalites', 18, 2)->default(0);
            $table->decimal('montant_taxes', 18, 2)->default(0);
            $table->decimal('montant_revision_prix', 18, 2)->default(0);
            $table->decimal('montant_net', 18, 2)->default(0);
            $table->decimal('reste_a_liquider', 18, 2)->nullable();

            $table->text('observation')->nullable();
            $table->unsignedInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['liquidation_id', 'ordre'], 'liq_ligne_liq_ordre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_lignes');
    }
};
