<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pap_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_budgetaire_id')->constrained('exercices_budgetaires')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('cadre_logique_id')->nullable()->constrained('cadres_logiques')->nullOnDelete()->cascadeOnUpdate();

            $table->string('version', 30)->default('1.0');
            $table->string('mode', 30); // simulation | creation | mise_a_jour | remplacement
            $table->string('statut', 30)->default('en_cours'); // en_cours | simule | valide | echoue | annule

            $table->string('fichier_original')->nullable();
            $table->string('chemin_fichier')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('source_label')->nullable();

            $table->unsignedInteger('nb_piliers')->default(0);
            $table->unsignedInteger('nb_axes')->default(0);
            $table->unsignedInteger('nb_produits')->default(0);
            $table->unsignedInteger('nb_sous_produits')->default(0);
            $table->unsignedInteger('nb_lignes')->default(0);
            $table->unsignedInteger('nb_creations')->default(0);
            $table->unsignedInteger('nb_mises_a_jour')->default(0);
            $table->unsignedInteger('nb_suppressions')->default(0);
            $table->unsignedInteger('nb_doublons')->default(0);
            $table->unsignedInteger('nb_avertissements')->default(0);
            $table->unsignedInteger('nb_erreurs')->default(0);
            $table->decimal('montant_total', 18, 2)->default(0);

            $table->json('rapport')->nullable();   // anomalies classees par severite
            $table->json('resume')->nullable();     // synthese chiffree
            $table->longText('snapshot')->nullable(); // sauvegarde JSON des donnees remplacees

            $table->foreignId('restored_from_id')->nullable()->constrained('pap_imports')->nullOnDelete();
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
        Schema::dropIfExists('pap_imports');
    }
};
