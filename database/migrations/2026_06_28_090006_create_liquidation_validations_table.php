<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_validations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_val_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('etape', 100);
            $table->unsignedInteger('ordre');
            $table->foreignId('validateur_id')->nullable()
                ->constrained('users', 'id', 'liq_val_validateur_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->string('decision', 30)->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_decision')->nullable();
            $table->boolean('est_validation_deleguee')->default(false);

            $table->timestamps();

            $table->index(['liquidation_id', 'ordre'], 'liq_val_liq_ordre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_validations');
    }
};
