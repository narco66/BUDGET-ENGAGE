<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('indicateur_tache')) {
            Schema::create('indicateur_tache', function (Blueprint $table) {
                $table->id();
                $table->foreignId('indicateur_id')->constrained('indicateurs')->cascadeOnDelete();
                $table->foreignId('tache_id')->constrained('taches')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['indicateur_id', 'tache_id']);
            });
        }

        if (! Schema::hasTable('indicateur_source_verification')) {
            Schema::create('indicateur_source_verification', function (Blueprint $table) {
                $table->id();
                $table->foreignId('indicateur_id')->constrained('indicateurs')->cascadeOnDelete();
                $table->foreignId('source_verification_id')->constrained('sources_verification')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['indicateur_id', 'source_verification_id'], 'indicateur_source_verif_unique');
            });
        }

        if (! Schema::hasTable('cadres_logiques')) {
            Schema::create('cadres_logiques', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('annee')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['annee', 'statut']);
            });
        }

        if (! Schema::hasTable('cadre_logique_elements')) {
            Schema::create('cadre_logique_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadre_logique_id')->constrained('cadres_logiques')->cascadeOnDelete();
            $table->string('niveau', 50);
            $table->string('element_type');
            $table->unsignedBigInteger('element_id');
            $table->unsignedInteger('ordre')->default(0);
            $table->text('hypotheses')->nullable();
            $table->text('resultat_attendu')->nullable();
            $table->timestamps();

            $table->index(['element_type', 'element_id'], 'cadre_elem_morph_idx');
            $table->unique(
                ['cadre_logique_id', 'niveau', 'element_type', 'element_id'],
                'cadre_logique_element_unique'
            );
            });
        }

        if (! Schema::hasTable('chaines_resultats')) {
            Schema::create('chaines_resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadre_logique_id')->nullable()->constrained('cadres_logiques')->nullOnDelete();
            $table->string('code', 50);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->string('niveau', 50);
            $table->string('element_type')->nullable();
            $table->unsignedBigInteger('element_id')->nullable();
            $table->foreignId('indicateur_id')->nullable()->constrained('indicateurs')->nullOnDelete();
            $table->text('resultat_attendu')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['element_type', 'element_id'], 'chaine_elem_morph_idx');
            $table->unique(['cadre_logique_id', 'code']);
            $table->index(['niveau', 'statut', 'ordre']);
            });
        }

        if (! Schema::hasTable('rbm_modification_historiques')) {
            Schema::create('rbm_modification_historiques', function (Blueprint $table) {
            $table->id();
            $table->string('historisable_type');
            $table->unsignedBigInteger('historisable_id');
            $table->string('action', 50);
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['historisable_type', 'historisable_id'], 'rbm_hist_morph_idx');
            $table->index(['historisable_type', 'historisable_id', 'created_at'], 'rbm_hist_subject_idx');
            });
        }

        if (! Schema::hasTable('rbm_observations')) {
            Schema::create('rbm_observations', function (Blueprint $table) {
            $table->id();
            $table->string('observable_type');
            $table->unsignedBigInteger('observable_id');
            $table->text('contenu');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['observable_type', 'observable_id'], 'rbm_obs_morph_idx');
            $table->index(['observable_type', 'observable_id', 'created_at'], 'rbm_obs_subject_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rbm_observations');
        Schema::dropIfExists('rbm_modification_historiques');
        Schema::dropIfExists('chaines_resultats');
        Schema::dropIfExists('cadre_logique_elements');
        Schema::dropIfExists('cadres_logiques');
        Schema::dropIfExists('indicateur_source_verification');
        Schema::dropIfExists('indicateur_tache');
    }
};
