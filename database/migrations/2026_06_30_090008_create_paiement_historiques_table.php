<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_historiques', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_hist_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('action', 80);
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->foreignId('user_id')->nullable()
                ->constrained('users', 'id', 'pay_hist_user_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['paiement_id', 'created_at'], 'pay_hist_pay_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_historiques');
    }
};
