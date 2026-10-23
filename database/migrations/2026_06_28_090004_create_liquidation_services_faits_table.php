<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidation_services_faits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liquidation_id')
                ->constrained('liquidations', 'id', 'liq_sf_liq_fk')->cascadeOnUpdate()->cascadeOnDelete();

            $table->date('date_constatation');
            $table->foreignId('responsable_id')->nullable()
                ->constrained('users', 'id', 'liq_sf_resp_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->string('commission')->nullable();

            $table->boolean('conformite_quantitative')->nullable();
            $table->boolean('conformite_qualitative')->nullable();
            $table->boolean('conformite_technique')->nullable();
            $table->boolean('conformite_contractuelle')->nullable();

            $table->text('reserves')->nullable();
            $table->boolean('reserves_levees')->default(false);
            $table->timestamp('reserves_levees_at')->nullable();
            $table->text('observations')->nullable();

            $table->boolean('valide')->default(false);
            $table->foreignId('valide_par')->nullable()
                ->constrained('users', 'id', 'liq_sf_valide_par_fk')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('valide_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('liquidation_id', 'liq_sf_liq_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_services_faits');
    }
};
