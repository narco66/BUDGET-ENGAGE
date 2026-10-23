<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extension ERP additive de la table ordonnancements. Colonnes nullable afin de
 * préserver les ordonnancements « simples » existants. montant_ordonnance reste
 * la valeur canonique (= 0 en brouillon, renseignée à la validation/mandat)
 * exploitée par le ledger budgétaire et le recalcul de statut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordonnancements', function (Blueprint $table): void {
            if (! Schema::hasColumn('ordonnancements', 'ordonnancement_type_id')) {
                $table->foreignId('ordonnancement_type_id')->nullable()->after('liquidation_id')
                    ->constrained('ordonnancement_types', 'id', 'ord_type_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('ordonnancements', 'parent_ordonnancement_id')) {
                $table->foreignId('parent_ordonnancement_id')->nullable()->after('ordonnancement_type_id')
                    ->constrained('ordonnancements', 'id', 'ord_parent_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('ordonnancements', 'exercice_budgetaire_id')) {
                $table->foreignId('exercice_budgetaire_id')->nullable()->after('parent_ordonnancement_id')
                    ->constrained('exercices_budgetaires', 'id', 'ord_exercice_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('ordonnancements', 'fournisseur_id')) {
                $table->foreignId('fournisseur_id')->nullable()->after('exercice_budgetaire_id')
                    ->constrained('fournisseurs', 'id', 'ord_fournisseur_fk')->nullOnDelete()->cascadeOnUpdate();
            }

            if (! Schema::hasColumn('ordonnancements', 'numero')) {
                $table->string('numero', 60)->nullable()->unique()->after('reference');
            }
            if (! Schema::hasColumn('ordonnancements', 'mode_creation')) {
                $table->string('mode_creation', 30)->nullable()->after('numero');
            }
            if (! Schema::hasColumn('ordonnancements', 'statut')) {
                $table->string('statut', 40)->nullable()->after('mode_creation');
            }
            if (! Schema::hasColumn('ordonnancements', 'objet')) {
                $table->text('objet')->nullable()->after('statut');
            }
            if (! Schema::hasColumn('ordonnancements', 'devise')) {
                $table->string('devise', 10)->nullable()->default('XAF')->after('objet');
            }
            if (! Schema::hasColumn('ordonnancements', 'justification')) {
                $table->text('justification')->nullable()->after('devise');
            }
            if (! Schema::hasColumn('ordonnancements', 'beneficiaire_type')) {
                $table->string('beneficiaire_type', 30)->nullable()->after('justification');
            }
            if (! Schema::hasColumn('ordonnancements', 'beneficiaire_id')) {
                $table->unsignedBigInteger('beneficiaire_id')->nullable()->after('beneficiaire_type');
            }

            foreach ([
                'montant_liquide_ref',
                'reste_a_ordonnancer',
                'taux_ordonnancement',
                'montant_transmis_paiement',
            ] as $col) {
                if (! Schema::hasColumn('ordonnancements', $col)) {
                    $table->decimal($col, 18, 2)->nullable()->after('beneficiaire_id');
                }
            }

            foreach ([
                'submitted_at',
                'controle_budgetaire_at',
                'controle_comptable_at',
                'controle_financier_at',
                'visa_ordonnateur_at',
                'valide_at',
                'mandate_at',
                'transmis_paiement_at',
            ] as $col) {
                if (! Schema::hasColumn('ordonnancements', $col)) {
                    $table->timestamp($col)->nullable()->after('montant_transmis_paiement');
                }
            }

            if (! Schema::hasColumn('ordonnancements', 'mandat_numero')) {
                $table->string('mandat_numero', 60)->nullable()->after('transmis_paiement_at');
            }
            if (! Schema::hasColumn('ordonnancements', 'visa_numero')) {
                $table->string('visa_numero', 60)->nullable()->after('mandat_numero');
            }
            if (! Schema::hasColumn('ordonnancements', 'visa_hash')) {
                $table->string('visa_hash', 128)->nullable()->after('visa_numero');
            }
            if (! Schema::hasColumn('ordonnancements', 'transmis_paiement_par')) {
                $table->foreignId('transmis_paiement_par')->nullable()->after('visa_hash')
                    ->constrained('users', 'id', 'ord_transmis_par_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('ordonnancements', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('transmis_paiement_par')
                    ->constrained('users', 'id', 'ord_validated_by_fk')->nullOnDelete()->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordonnancements', function (Blueprint $table): void {
            foreach ([
                'ord_type_fk' => 'ordonnancement_type_id',
                'ord_parent_fk' => 'parent_ordonnancement_id',
                'ord_exercice_fk' => 'exercice_budgetaire_id',
                'ord_fournisseur_fk' => 'fournisseur_id',
                'ord_transmis_par_fk' => 'transmis_paiement_par',
                'ord_validated_by_fk' => 'validated_by',
            ] as $fk => $col) {
                if (Schema::hasColumn('ordonnancements', $col)) {
                    $table->dropForeign($fk);
                }
            }

            $columns = [
                'ordonnancement_type_id', 'parent_ordonnancement_id', 'exercice_budgetaire_id', 'fournisseur_id',
                'numero', 'mode_creation', 'statut', 'objet', 'devise', 'justification',
                'beneficiaire_type', 'beneficiaire_id',
                'montant_liquide_ref', 'reste_a_ordonnancer', 'taux_ordonnancement', 'montant_transmis_paiement',
                'submitted_at', 'controle_budgetaire_at', 'controle_comptable_at', 'controle_financier_at',
                'visa_ordonnateur_at', 'valide_at', 'mandate_at', 'transmis_paiement_at',
                'mandat_numero', 'visa_numero', 'visa_hash', 'transmis_paiement_par', 'validated_by',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('ordonnancements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
