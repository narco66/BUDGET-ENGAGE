<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->foreignId('type_document_id')->nullable()->constrained('ged_types_documents')->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('est_actif')->default(true);
            $table->boolean('est_par_defaut')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('est_actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_workflows');
    }
};
