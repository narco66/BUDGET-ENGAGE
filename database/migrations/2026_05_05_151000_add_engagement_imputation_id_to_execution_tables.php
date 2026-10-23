<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liquidations', function (Blueprint $table): void {
            $table->foreignId('engagement_imputation_id')
                ->nullable()
                ->after('engagement_id')
                ->constrained('engagement_imputations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('ordonnancements', function (Blueprint $table): void {
            $table->foreignId('engagement_imputation_id')
                ->nullable()
                ->after('liquidation_id')
                ->constrained('engagement_imputations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('paiements', function (Blueprint $table): void {
            $table->foreignId('engagement_imputation_id')
                ->nullable()
                ->after('ordonnancement_id')
                ->constrained('engagement_imputations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('engagement_imputation_id');
        });

        Schema::table('ordonnancements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('engagement_imputation_id');
        });

        Schema::table('liquidations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('engagement_imputation_id');
        });
    }
};
