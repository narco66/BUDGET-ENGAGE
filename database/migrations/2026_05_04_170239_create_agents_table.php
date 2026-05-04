<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->string('matricule', 100)->nullable()->unique();
            $table->string('nom', 255);
            $table->string('prenom', 255)->nullable();
            $table->string('fonction', 255)->nullable();
            $table->string('grade', 255)->nullable();
            $table->string('categorie', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('banque', 255)->nullable();
            $table->string('compte_bancaire', 100)->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
