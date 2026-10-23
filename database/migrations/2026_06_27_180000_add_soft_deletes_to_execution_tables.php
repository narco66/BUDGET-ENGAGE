<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['liquidations', 'ordonnancements', 'paiements'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->softDeletes();
                $table->timestamp('cancelled_at')->nullable()->after('deleted_at');
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
                $table->text('motif_annulation')->nullable()->after('cancelled_by');
            });
        }
    }

    public function down(): void
    {
        foreach (['liquidations', 'ordonnancements', 'paiements'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('cancelled_by');
                $table->dropColumn(['motif_annulation', 'cancelled_at', 'deleted_at']);
            });
        }
    }
};
