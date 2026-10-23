<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_receptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_recep_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('type_reception', 30);
            $table->date('date_reception');
            $table->string('numero_pv', 100)->nullable();
            $table->text('commission')->nullable();
            $table->foreignId('responsable_id')->nullable()
                ->constrained('users', 'id', 'liq_recep_resp_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->boolean('conforme')->nullable();
            $table->text('reserves')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('liquidation_id', 'liq_recep_liq_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_receptions');
    }
};
