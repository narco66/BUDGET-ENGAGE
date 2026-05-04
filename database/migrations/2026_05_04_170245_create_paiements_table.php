<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordonnancement_id')->constrained('ordonnancements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('montant_paye', 18, 2);
            $table->date('date_paiement');
            $table->string('reference_paiement', 100)->nullable();
            $table->string('mode_paiement', 100)->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
