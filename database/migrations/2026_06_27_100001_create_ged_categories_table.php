<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('ged_categories')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('ordre')->default(0);
            $table->string('couleur', 20)->nullable();
            $table->string('icone', 100)->nullable();
            $table->string('statut', 20)->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_categories');
    }
};
