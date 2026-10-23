<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_calculs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_calc_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ordonnancement_ligne_id')->nullable()
                ->constrained('ordonnancement_lignes', 'id', 'ord_calc_ligne_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->string('type_calcul', 60);
            $table->text('formule')->nullable();
            $table->json('entrees')->nullable();
            $table->decimal('resultat', 18, 2)->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'ord_calc_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['ordonnancement_id', 'type_calcul'], 'ord_calc_ord_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_calculs');
    }
};
