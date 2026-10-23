<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité NON destructive de la transmission d'un engagement au module
 * Liquidation (acte d'handover, empêche toute double transmission).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->timestamp('transmis_liquidation_at')->nullable()->after('approved_at');
            $table->foreignId('transmis_par')->nullable()->after('transmis_liquidation_at')
                ->constrained('users', 'id', 'engagements_transmis_par_fk')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transmis_par');
            $table->dropColumn('transmis_liquidation_at');
        });
    }
};
