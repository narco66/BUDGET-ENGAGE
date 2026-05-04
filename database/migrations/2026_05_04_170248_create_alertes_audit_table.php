<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->nullable()->constrained('engagements')->nullOnDelete()->cascadeOnUpdate();
            $table->string('code_alerte', 50)->index();
            $table->string('type_alerte', 100);
            $table->enum('niveau_risque', ['faible', 'moyen', 'eleve', 'critique']);
            $table->unsignedTinyInteger('score_risque')->default(0);
            $table->text('description');
            $table->enum('statut_traitement', [
                'nouvelle',
                'en_analyse',
                'confirmee',
                'non_confirmee',
                'corrigee',
                'cloturee',
            ])->default('nouvelle');
            $table->timestamp('detectee_le')->nullable();
            $table->foreignId('traitee_par')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->text('commentaire_traitement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes_audit');
    }
};
