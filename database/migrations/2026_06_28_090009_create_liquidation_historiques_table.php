<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_historiques', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_hist_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('action', 80);
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->foreignId('user_id')->nullable()
                ->constrained('users', 'id', 'liq_hist_user_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['liquidation_id', 'created_at'], 'liq_hist_liq_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_historiques');
    }
};
