<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('version', 30)->default('1.0');
            $table->string('mode', 30);   // simulation | creation | mise_a_jour | fusion | remplacement
            $table->string('statut', 30)->default('en_cours'); // en_cours | simule | valide | echoue | annule

            $table->string('fichier_original')->nullable();
            $table->string('chemin_fichier')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('source_label')->nullable();

            $table->unsignedInteger('nb_titres')->default(0);
            $table->unsignedInteger('nb_chapitres')->default(0);
            $table->unsignedInteger('nb_articles')->default(0);
            $table->unsignedInteger('nb_paragraphes')->default(0);
            $table->unsignedInteger('nb_lignes')->default(0);
            $table->unsignedInteger('nb_recettes')->default(0);
            $table->unsignedInteger('nb_depenses')->default(0);
            $table->unsignedInteger('nb_creations')->default(0);
            $table->unsignedInteger('nb_mises_a_jour')->default(0);
            $table->unsignedInteger('nb_suppressions')->default(0);
            $table->unsignedInteger('nb_doublons')->default(0);
            $table->unsignedInteger('nb_avertissements')->default(0);
            $table->unsignedInteger('nb_erreurs')->default(0);

            $table->decimal('montant_recettes', 18, 2)->default(0);
            $table->decimal('montant_depenses', 18, 2)->default(0);
            $table->decimal('montant_ceeac', 18, 2)->default(0);
            $table->decimal('montant_ptf', 18, 2)->default(0);

            $table->json('rapport')->nullable();
            $table->json('resume')->nullable();
            $table->longText('snapshot')->nullable();

            $table->foreignId('restored_from_id')->nullable()->constrained('budget_imports')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adresse_ip', 64)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duree_ms')->nullable();

            $table->timestamps();

            $table->index(['exercice_budgetaire_id', 'statut']);
            $table->index(['mode', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_imports');
    }
};
