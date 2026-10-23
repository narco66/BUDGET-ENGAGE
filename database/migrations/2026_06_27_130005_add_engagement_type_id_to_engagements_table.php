<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajout NON destructif du type d'engagement référentiel. La colonne existante
 * `type_engagement` (string libre) est conservée pour rétrocompatibilité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->foreignId('engagement_type_id')->nullable()->after('type_engagement')
                ->constrained('engagement_types')->nullOnDelete()->cascadeOnUpdate();
            $table->string('mode_creation', 30)->default('manuel')->after('engagement_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('engagement_type_id');
            $table->dropColumn('mode_creation');
        });
    }
};
