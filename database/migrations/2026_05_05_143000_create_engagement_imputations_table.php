<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_imputations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ligne_budgetaire_id')->constrained('lignes_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('montant_impute', 18, 2);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['engagement_id', 'ligne_budgetaire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_imputations');
    }
};
