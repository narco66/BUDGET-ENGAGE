<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liquidation_id')->constrained('liquidations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('montant_ordonnance', 18, 2);
            $table->date('date_ordonnancement');
            $table->string('reference', 100)->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancements');
    }
};
