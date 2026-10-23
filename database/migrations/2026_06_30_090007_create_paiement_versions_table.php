<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paiement_id')
                ->constrained('paiements', 'id', 'pay_ver_pay_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->json('snapshot');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')
                ->constrained('users', 'id', 'pay_ver_created_by_fk')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['paiement_id', 'numero_version'], 'pay_ver_pay_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_versions');
    }
};
