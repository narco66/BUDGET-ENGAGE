<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colonnes additives (nullable) alignant les lignes de besoin sur la
 * « Fiche d'état de besoins » institutionnelle CEEAC : code article, catégorie
 * (Bien / Service / Travaux / Prestation intellectuelle), spécifications,
 * priorité et échéance par ligne. Non destructif : les lignes existantes
 * conservent NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expression_besoin_lignes', function (Blueprint $table): void {
            if (! Schema::hasColumn('expression_besoin_lignes', 'code_article')) {
                $table->string('code_article', 60)->nullable()->after('designation');
            }
            if (! Schema::hasColumn('expression_besoin_lignes', 'categorie')) {
                $table->string('categorie', 40)->nullable()->after('code_article');
            }
            if (! Schema::hasColumn('expression_besoin_lignes', 'specifications')) {
                $table->text('specifications')->nullable()->after('categorie');
            }
            if (! Schema::hasColumn('expression_besoin_lignes', 'priorite')) {
                $table->string('priorite', 20)->nullable()->after('montant');
            }
            if (! Schema::hasColumn('expression_besoin_lignes', 'echeance')) {
                $table->date('echeance')->nullable()->after('priorite');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expression_besoin_lignes', function (Blueprint $table): void {
            foreach (['code_article', 'categorie', 'specifications', 'priorite', 'echeance'] as $col) {
                if (Schema::hasColumn('expression_besoin_lignes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
