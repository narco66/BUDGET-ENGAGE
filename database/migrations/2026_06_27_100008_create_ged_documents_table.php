<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ged_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 150)->nullable()->unique();
            $table->string('titre', 255);
            $table->text('description')->nullable();

            // Classement
            $table->foreignId('categorie_id')->nullable()->constrained('ged_categories')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('type_document_id')->nullable()->constrained('ged_types_documents')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('dossier_id')->nullable()->constrained('ged_dossiers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('conservation_rule_id')->nullable()->constrained('ged_conservation_rules')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('workflow_id')->nullable()->constrained('ged_workflows')->nullOnDelete()->cascadeOnUpdate();

            // Rattachement métier simple (le rattachement polymorphe multiple passe par ged_document_attachments)
            $table->string('module_source', 100)->nullable();
            $table->unsignedBigInteger('module_source_id')->nullable();
            $table->foreignId('exercice_budgetaire_id')->nullable()->constrained('exercices_budgetaires')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('departement_id')->nullable()->constrained('departements')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete()->cascadeOnUpdate();

            // Fichier
            $table->string('chemin_fichier', 500)->nullable();
            $table->string('nom_original', 255)->nullable();
            $table->string('nom_stocke', 255)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->unsignedInteger('version_courante')->default(1);

            // Cycle de vie
            $table->string('statut', 30)->default('brouillon');
            $table->string('niveau_confidentialite', 30)->default('interne');
            $table->date('date_document')->nullable();
            $table->timestamp('date_depot')->nullable();
            $table->date('date_expiration')->nullable();
            $table->timestamp('date_archivage')->nullable();
            $table->boolean('est_archive')->default(false);
            $table->boolean('est_verrouille')->default(false);
            $table->boolean('est_signe')->default(false);
            $table->string('signature_status', 20)->default('non_signe');
            $table->text('motif_archivage')->nullable();
            $table->longText('ocr_contenu')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('niveau_confidentialite');
            $table->index('type_document_id');
            $table->index('categorie_id');
            $table->index('dossier_id');
            $table->index('est_archive');
            $table->index('date_expiration');
            $table->index('hash_sha256');
            $table->index('exercice_budgetaire_id');
            $table->index(['module_source', 'module_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ged_documents');
    }
};
