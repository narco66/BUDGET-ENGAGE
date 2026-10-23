<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extension ERP additive de la table liquidations.
 *
 * Toutes les colonnes sont nullable afin de préserver intégralement les
 * liquidations « simples » déjà enregistrées par EngagementExecutionService.
 * Le champ montant_liquide existant reste la valeur canonique (= montant_net)
 * exploitée par le ledger budgétaire et le recalcul de statut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liquidations', function (Blueprint $table): void {
            if (! Schema::hasColumn('liquidations', 'liquidation_type_id')) {
                $table->foreignId('liquidation_type_id')->nullable()->after('engagement_id')
                    ->constrained('liquidation_types', 'id', 'liq_type_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('liquidations', 'parent_liquidation_id')) {
                $table->foreignId('parent_liquidation_id')->nullable()->after('liquidation_type_id')
                    ->constrained('liquidations', 'id', 'liq_parent_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('liquidations', 'exercice_budgetaire_id')) {
                $table->foreignId('exercice_budgetaire_id')->nullable()->after('parent_liquidation_id')
                    ->constrained('exercices_budgetaires', 'id', 'liq_exercice_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('liquidations', 'fournisseur_id')) {
                $table->foreignId('fournisseur_id')->nullable()->after('exercice_budgetaire_id')
                    ->constrained('fournisseurs', 'id', 'liq_fournisseur_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }

            if (! Schema::hasColumn('liquidations', 'numero')) {
                $table->string('numero', 60)->nullable()->unique()->after('reference');
            }
            if (! Schema::hasColumn('liquidations', 'mode_creation')) {
                $table->string('mode_creation', 30)->nullable()->after('numero');
            }
            if (! Schema::hasColumn('liquidations', 'statut')) {
                $table->string('statut', 40)->nullable()->after('mode_creation');
            }
            if (! Schema::hasColumn('liquidations', 'objet')) {
                $table->text('objet')->nullable()->after('statut');
            }
            if (! Schema::hasColumn('liquidations', 'devise')) {
                $table->string('devise', 10)->nullable()->default('XAF')->after('objet');
            }
            if (! Schema::hasColumn('liquidations', 'justification')) {
                $table->text('justification')->nullable()->after('devise');
            }
            if (! Schema::hasColumn('liquidations', 'beneficiaire_type')) {
                $table->string('beneficiaire_type', 30)->nullable()->after('justification');
            }
            if (! Schema::hasColumn('liquidations', 'beneficiaire_id')) {
                $table->unsignedBigInteger('beneficiaire_id')->nullable()->after('beneficiaire_type');
            }

            // Agrégats financiers (moteur de calcul). Nullable, renseignés par le service.
            foreach ([
                'montant_brut',
                'total_avances',
                'total_acomptes',
                'total_retenues',
                'total_garanties',
                'total_penalites',
                'total_taxes',
                'montant_revision_prix',
                'montant_net',
                'taux_liquidation',
            ] as $col) {
                if (! Schema::hasColumn('liquidations', $col)) {
                    $table->decimal($col, 18, 2)->nullable()->after('beneficiaire_id');
                }
            }

            if (! Schema::hasColumn('liquidations', 'service_fait_valide')) {
                $table->boolean('service_fait_valide')->default(false)->after('taux_liquidation');
            }

            // Jalons du circuit de validation.
            foreach ([
                'submitted_at',
                'controle_technique_at',
                'controle_administratif_at',
                'controle_budgetaire_at',
                'controle_financier_at',
                'valide_at',
                'certifie_at',
                'transmis_ordonnancement_at',
            ] as $col) {
                if (! Schema::hasColumn('liquidations', $col)) {
                    $table->timestamp($col)->nullable()->after('service_fait_valide');
                }
            }

            if (! Schema::hasColumn('liquidations', 'certificat_numero')) {
                $table->string('certificat_numero', 60)->nullable()->after('transmis_ordonnancement_at');
            }
            if (! Schema::hasColumn('liquidations', 'certificat_hash')) {
                $table->string('certificat_hash', 128)->nullable()->after('certificat_numero');
            }
            if (! Schema::hasColumn('liquidations', 'transmis_ordonnancement_par')) {
                $table->foreignId('transmis_ordonnancement_par')->nullable()->after('certificat_hash')
                    ->constrained('users', 'id', 'liq_transmis_par_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('liquidations', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('transmis_ordonnancement_par')
                    ->constrained('users', 'id', 'liq_validated_by_fk')
                    ->nullOnDelete()->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::table('liquidations', function (Blueprint $table): void {
            foreach ([
                'liq_type_fk' => 'liquidation_type_id',
                'liq_parent_fk' => 'parent_liquidation_id',
                'liq_exercice_fk' => 'exercice_budgetaire_id',
                'liq_fournisseur_fk' => 'fournisseur_id',
                'liq_transmis_par_fk' => 'transmis_ordonnancement_par',
                'liq_validated_by_fk' => 'validated_by',
            ] as $fk => $col) {
                if (Schema::hasColumn('liquidations', $col)) {
                    $table->dropForeign($fk);
                }
            }

            $columns = [
                'liquidation_type_id', 'parent_liquidation_id', 'exercice_budgetaire_id', 'fournisseur_id',
                'numero', 'mode_creation', 'statut', 'objet', 'devise', 'justification',
                'beneficiaire_type', 'beneficiaire_id',
                'montant_brut', 'total_avances', 'total_acomptes', 'total_retenues', 'total_garanties',
                'total_penalites', 'total_taxes', 'montant_revision_prix', 'montant_net', 'taux_liquidation',
                'service_fait_valide',
                'submitted_at', 'controle_technique_at', 'controle_administratif_at', 'controle_budgetaire_at',
                'controle_financier_at', 'valide_at', 'certifie_at', 'transmis_ordonnancement_at',
                'certificat_numero', 'certificat_hash', 'transmis_ordonnancement_par', 'validated_by',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('liquidations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
