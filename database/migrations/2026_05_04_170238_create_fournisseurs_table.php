<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('raison_sociale', 255);
            $table->string('sigle', 100)->nullable();
            $table->string('numero_fiscal', 100)->nullable()->index();
            $table->text('adresse')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('banque', 255)->nullable();
            $table->string('compte_bancaire', 100)->nullable();
            $table->string('type_fournisseur', 100)->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
