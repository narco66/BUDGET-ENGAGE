<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_historiques', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_hist_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('action', 80);
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->foreignId('user_id')->nullable()
                ->constrained('users', 'id', 'ord_hist_user_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['ordonnancement_id', 'created_at'], 'ord_hist_ord_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_historiques');
    }
};
