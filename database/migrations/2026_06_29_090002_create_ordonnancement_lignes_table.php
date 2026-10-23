<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_lignes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_ligne_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('liquidation_ligne_id')->nullable()
                ->constrained('liquidation_lignes', 'id', 'ord_ligne_liqligne_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('engagement_imputation_id')->nullable()
                ->constrained('engagement_imputations', 'id', 'ord_ligne_imput_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ligne_budgetaire_id')->nullable()
                ->constrained('lignes_budgetaires', 'id', 'ord_ligne_lb_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('source_financement_id')->nullable()
                ->constrained('sources_financement', 'id', 'ord_ligne_sf_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('activite_id')->nullable()
                ->constrained('activites', 'id', 'ord_ligne_act_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tache_id')->nullable()
                ->constrained('taches', 'id', 'ord_ligne_tache_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('pap_ligne_budgetaire_id')->nullable()
                ->constrained('pap_lignes_budgetaires', 'id', 'ord_ligne_pap_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('centre_cout', 100)->nullable();
            $table->string('centre_responsabilite', 100)->nullable();

            $table->string('designation');
            $table->decimal('montant_liquide', 18, 2)->default(0);
            $table->decimal('montant_ordonnance', 18, 2)->default(0);
            $table->decimal('reste_a_ordonnancer', 18, 2)->nullable();

            $table->string('mode_paiement', 60)->nullable();
            $table->date('echeance')->nullable();
            $table->string('banque', 150)->nullable();
            $table->string('compte_bancaire', 60)->nullable();
            $table->text('observation')->nullable();
            $table->unsignedInteger('ordre')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ordonnancement_id', 'ordre'], 'ord_ligne_ord_ordre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_lignes');
    }
};
