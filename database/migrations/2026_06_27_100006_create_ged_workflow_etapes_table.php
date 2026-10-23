<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_workflow_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ged_workflow_id')->constrained('ged_workflows')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(1);
            $table->string('libelle', 255);
            $table->string('type_etape', 40)->default('validation');
            $table->string('permission_requise', 100)->nullable();
            $table->string('role_requis', 100)->nullable();
            $table->unsignedInteger('delai_jours')->nullable();
            $table->boolean('est_obligatoire')->default(true);
            $table->boolean('notifie')->default(true);
            $table->timestamps();

            $table->unique(['ged_workflow_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_workflow_etapes');
    }
};
