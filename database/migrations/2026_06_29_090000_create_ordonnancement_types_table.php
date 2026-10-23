<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancement_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->string('famille', 60)->nullable();
            $table->string('nature', 60)->nullable();

            $table->json('champs_specifiques')->nullable();
            $table->json('documents_obligatoires')->nullable();
            $table->json('regles_calcul')->nullable();
            $table->string('workflow_code', 60)->nullable();

            $table->boolean('permet_generation')->default(true);
            $table->boolean('permet_creation_manuelle')->default(true);
            $table->boolean('exige_mandat')->default(true);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);

            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'id', 'ord_type_created_by_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'id', 'ord_type_updated_by_fk')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancement_types');
    }
};
