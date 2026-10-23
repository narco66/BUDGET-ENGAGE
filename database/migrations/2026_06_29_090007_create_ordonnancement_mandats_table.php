<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_mandats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_mandat_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('numero', 60)->unique();
            $table->decimal('montant', 18, 2);
            $table->date('date_mandat');
            $table->string('statut', 40)->default('emis');
            $table->string('beneficiaire', 255)->nullable();
            $table->string('banque', 150)->nullable();
            $table->string('compte_bancaire', 60)->nullable();
            $table->string('hash_integrite', 128)->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'ord_mandat_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ordonnancement_id', 'ord_mandat_ord_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_mandats');
    }
};
