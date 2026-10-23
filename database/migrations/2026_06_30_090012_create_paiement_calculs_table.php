<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_calculs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_calc_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('paiement_ligne_id')->nullable()
                ->constrained('paiement_lignes', 'id', 'pay_calc_ligne_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('type_calcul', 60);
            $table->text('formule')->nullable();
            $table->json('entrees')->nullable();
            $table->decimal('resultat', 18, 2)->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'pay_calc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['paiement_id', 'type_calcul'], 'pay_calc_pay_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_calculs');
    }
};
