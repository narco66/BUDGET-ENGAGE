<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal des calculs financiers : chaque opération de calcul (montant net,
 * retenue, pénalité, révision…) est historisée pour audit et reconstitution.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_calculs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_calc_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('liquidation_ligne_id')->nullable()
                ->constrained('liquidation_lignes', 'id', 'liq_calc_ligne_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('type_calcul', 60);
            $table->text('formule')->nullable();
            $table->json('entrees')->nullable();
            $table->decimal('resultat', 18, 2)->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'liq_calc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['liquidation_id', 'type_calcul'], 'liq_calc_liq_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_calculs');
    }
};
