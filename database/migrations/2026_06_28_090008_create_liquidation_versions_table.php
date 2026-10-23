<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_ver_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->json('snapshot');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')
                ->constrained('users', 'id', 'liq_ver_created_by_fk')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['liquidation_id', 'numero_version'], 'liq_ver_liq_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_versions');
    }
};
