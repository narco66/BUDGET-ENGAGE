<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_ordres_paiement', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_op_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ordonnancement_mandat_id')->nullable()
                ->constrained('ordonnancement_mandats', 'id', 'ord_op_mandat_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->string('numero', 60)->unique();
            $table->decimal('montant', 18, 2);
            $table->date('date_ordre');
            $table->string('mode_paiement', 60)->nullable();
            $table->string('beneficiaire', 255)->nullable();
            $table->string('banque', 150)->nullable();
            $table->string('compte_bancaire', 60)->nullable();
            $table->string('statut', 40)->default('emis');
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'ord_op_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ordonnancement_id', 'ord_op_ord_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_ordres_paiement');
    }
};
