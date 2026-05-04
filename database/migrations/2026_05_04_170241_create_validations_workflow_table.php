<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validations_workflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('etape', 100);
            $table->unsignedInteger('ordre');
            $table->foreignId('validateur_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('decision', ['en_attente', 'valide', 'rejete', 'retourne'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_decision')->nullable();
            $table->boolean('est_validation_deleguee')->default(false);
            $table->timestamps();

            $table->index(['engagement_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validations_workflow');
    }
};
