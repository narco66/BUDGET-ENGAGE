<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ordonnancement_id')
                ->constrained('ordonnancements', 'id', 'ord_ver_ord_fk')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->json('snapshot');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')
                ->constrained('users', 'id', 'ord_ver_created_by_fk')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['ordonnancement_id', 'numero_version'], 'ord_ver_ord_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_versions');
    }
};
