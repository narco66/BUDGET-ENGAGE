<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extension ERP additive de la table paiements. Colonnes nullable afin de
 * préserver les paiements « simples » existants. montant_paye reste la valeur
 * canonique (= 0 en brouillon, renseignée à l'exécution bancaire) exploitée par
 * le ledger budgétaire et le recalcul de statut de l'engagement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table): void {
            if (! Schema::hasColumn('paiements', 'paiement_type_id')) {
                $table->foreignId('paiement_type_id')->nullable()->after('ordonnancement_id')
                    ->constrained('paiement_types', 'id', 'pay_type_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('paiements', 'parent_paiement_id')) {
                $table->foreignId('parent_paiement_id')->nullable()->after('paiement_type_id')
                    ->constrained('paiements', 'id', 'pay_parent_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('paiements', 'paiement_lot_id')) {
                $table->foreignId('paiement_lot_id')->nullable()->after('parent_paiement_id')
                    ->constrained('paiement_lots', 'id', 'pay_lot_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('paiements', 'paiement_banque_id')) {
                $table->foreignId('paiement_banque_id')->nullable()->after('paiement_lot_id')
                    ->constrained('paiement_banques', 'id', 'pay_banque_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('paiements', 'exercice_budgetaire_id')) {
                $table->foreignId('exercice_budgetaire_id')->nullable()->after('paiement_banque_id')
                    ->constrained('exercices_budgetaires', 'id', 'pay_exercice_fk')->nullOnDelete()->cascadeOnUpdate();
            }
            if (! Schema::hasColumn('paiements', 'fournisseur_id')) {
                $table->foreignId('fournisseur_id')->nullable()->after('exercice_budgetaire_id')
                    ->constrained('fournisseurs', 'id', 'pay_fournisseur_fk')->nullOnDelete()->cascadeOnUpdate();
            }

            if (! Schema::hasColumn('paiements', 'numero')) {
                $table->string('numero', 60)->nullable()->unique()->after('reference_paiement');
            }
            if (! Schema::hasColumn('paiements', 'mode_creation')) {
                $table->string('mode_creation', 30)->nullable()->after('numero');
            }
            if (! Schema::hasColumn('paiements', 'statut')) {
                $table->string('statut', 40)->nullable()->after('mode_creation');
            }
            if (! Schema::hasColumn('paiements', 'objet')) {
                $table->text('objet')->nullable()->after('statut');
            }
            if (! Schema::hasColumn('paiements', 'justification')) {
                $table->text('justification')->nullable()->after('objet');
            }
            if (! Schema::hasColumn('paiements', 'beneficiaire_type')) {
                $table->string('beneficiaire_type', 30)->nullable()->after('justification');
            }
            if (! Schema::hasColumn('paiements', 'beneficiaire_id')) {
                $table->unsignedBigInteger('beneficiaire_id')->nullable()->after('beneficiaire_type');
            }

            // Multi-devises.
            if (! Schema::hasColumn('paiements', 'devise')) {
                $table->string('devise', 10)->nullable()->default('XAF')->after('beneficiaire_id');
            }
            if (! Schema::hasColumn('paiements', 'devise_paiement')) {
                $table->string('devise_paiement', 10)->nullable()->after('devise');
            }
            foreach (['taux_change', 'ecart_change', 'frais_bancaires'] as $col) {
                if (! Schema::hasColumn('paiements', $col)) {
                    $table->decimal($col, 18, 6)->nullable()->after('devise_paiement');
                }
            }

            // Agrégats.
            foreach (['montant_ordonnance_ref', 'reste_a_payer', 'taux_paiement', 'montant_transmis_banque'] as $col) {
                if (! Schema::hasColumn('paiements', $col)) {
                    $table->decimal($col, 18, 2)->nullable()->after('frais_bancaires');
                }
            }

            // Trésorerie / banque.
            if (! Schema::hasColumn('paiements', 'priorite')) {
                $table->string('priorite', 20)->nullable()->after('montant_transmis_banque');
            }
            if (! Schema::hasColumn('paiements', 'date_prevue')) {
                $table->date('date_prevue')->nullable()->after('priorite');
            }
            if (! Schema::hasColumn('paiements', 'date_valeur')) {
                $table->date('date_valeur')->nullable()->after('date_prevue');
            }
            if (! Schema::hasColumn('paiements', 'reference_bancaire')) {
                $table->string('reference_bancaire', 100)->nullable()->after('date_valeur');
            }
            if (! Schema::hasColumn('paiements', 'numero_cheque')) {
                $table->string('numero_cheque', 60)->nullable()->after('reference_bancaire');
            }
            if (! Schema::hasColumn('paiements', 'numero_swift')) {
                $table->string('numero_swift', 60)->nullable()->after('numero_cheque');
            }

            // Jalons du circuit.
            foreach ([
                'programmed_at', 'controle_tresorerie_at', 'controle_comptable_at', 'validation_financiere_at',
                'validation_signataires_at', 'transmis_banque_at', 'execute_at', 'rapproche_at', 'cloture_at',
            ] as $col) {
                if (! Schema::hasColumn('paiements', $col)) {
                    $table->timestamp($col)->nullable()->after('numero_swift');
                }
            }

            if (! Schema::hasColumn('paiements', 'preuve_numero')) {
                $table->string('preuve_numero', 60)->nullable()->after('cloture_at');
            }
            if (! Schema::hasColumn('paiements', 'quittance_numero')) {
                $table->string('quittance_numero', 60)->nullable()->after('preuve_numero');
            }
            if (! Schema::hasColumn('paiements', 'preuve_hash')) {
                $table->string('preuve_hash', 128)->nullable()->after('quittance_numero');
            }

            foreach (['validated_by', 'executed_by', 'transmis_banque_par'] as $col) {
                if (! Schema::hasColumn('paiements', $col)) {
                    $table->foreignId($col)->nullable()->after('preuve_hash')
                        ->constrained('users', 'id', 'pay_'.substr($col, 0, 10).'_fk')->nullOnDelete()->cascadeOnUpdate();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table): void {
            foreach ([
                'pay_type_fk' => 'paiement_type_id',
                'pay_parent_fk' => 'parent_paiement_id',
                'pay_lot_fk' => 'paiement_lot_id',
                'pay_banque_fk' => 'paiement_banque_id',
                'pay_exercice_fk' => 'exercice_budgetaire_id',
                'pay_fournisseur_fk' => 'fournisseur_id',
                'pay_validated_'.'_fk' => 'validated_by',
                'pay_executed_b_fk' => 'executed_by',
                'pay_transmis_b_fk' => 'transmis_banque_par',
            ] as $fk => $col) {
                if (Schema::hasColumn('paiements', $col)) {
                    try {
                        $table->dropForeign($fk);
                    } catch (\Throwable $e) {
                        // FK name variant — ignoré au rollback.
                    }
                }
            }

            foreach ([
                'paiement_type_id', 'parent_paiement_id', 'paiement_lot_id', 'paiement_banque_id',
                'exercice_budgetaire_id', 'fournisseur_id', 'numero', 'mode_creation', 'statut', 'objet',
                'justification', 'beneficiaire_type', 'beneficiaire_id', 'devise', 'devise_paiement',
                'taux_change', 'ecart_change', 'frais_bancaires', 'montant_ordonnance_ref', 'reste_a_payer',
                'taux_paiement', 'montant_transmis_banque', 'priorite', 'date_prevue', 'date_valeur',
                'reference_bancaire', 'numero_cheque', 'numero_swift', 'programmed_at', 'controle_tresorerie_at',
                'controle_comptable_at', 'validation_financiere_at', 'validation_signataires_at', 'transmis_banque_at',
                'execute_at', 'rapproche_at', 'cloture_at', 'preuve_numero', 'quittance_numero', 'preuve_hash',
                'validated_by', 'executed_by', 'transmis_banque_par',
            ] as $col) {
                if (Schema::hasColumn('paiements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
