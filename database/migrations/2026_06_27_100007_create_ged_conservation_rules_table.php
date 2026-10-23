<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_conservation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->foreignId('type_document_id')->nullable()->constrained('ged_types_documents')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('duree_conservation_mois')->default(0);
            $table->string('sort_final', 40)->default('archivage_definitif');
            $table->string('declenchement', 40)->default('date_document');
            $table->unsignedInteger('alerte_avant_jours')->nullable();
            $table->boolean('conservation_obligatoire')->default(false);
            $table->boolean('est_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('est_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_conservation_rules');
    }
};
