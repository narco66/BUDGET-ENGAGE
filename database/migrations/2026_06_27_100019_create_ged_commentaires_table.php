<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_commentaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_document_id')->constrained('ged_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ged_commentaires')->nullOnDelete()->cascadeOnUpdate();
            $table->text('contenu');
            $table->timestamps();
            $table->softDeletes();

            $table->index('ged_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_commentaires');
    }
};
