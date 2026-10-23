<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_validations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_val_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('etape', 100);
            $table->unsignedInteger('ordre');
            $table->foreignId('validateur_id')->nullable()
                ->constrained('users', 'id', 'pay_val_validateur_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->string('decision', 30)->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_decision')->nullable();
            $table->boolean('est_validation_deleguee')->default(false);
            $table->timestamps();

            $table->index(['paiement_id', 'ordre'], 'pay_val_pay_ordre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_validations');
    }
};
