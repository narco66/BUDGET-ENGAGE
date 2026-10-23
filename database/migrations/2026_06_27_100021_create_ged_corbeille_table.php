<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_corbeille', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('supprime_par')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('motif')->nullable();
            $table->timestamp('date_suppression')->nullable();
            $table->date('date_purge_prevue')->nullable();
            $table->foreignId('restaure_par')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('date_restauration')->nullable();
            $table->timestamps();

            $table->index('ged_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_corbeille');
    }
};
