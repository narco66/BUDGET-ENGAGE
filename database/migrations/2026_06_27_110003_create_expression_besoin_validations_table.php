<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expression_besoin_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expression_besoin_id')->constrained('expression_besoins')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(1);
            $table->string('libelle_etape', 255);
            $table->string('type_etape', 60)->nullable();
            $table->string('permission_requise', 100)->nullable();
            $table->string('role_requis', 100)->nullable();
            $table->foreignId('validateur_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('statut', 20)->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamp('date_action')->nullable();
            $table->timestamps();

            $table->index(['expression_besoin_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expression_besoin_validations');
    }
};
