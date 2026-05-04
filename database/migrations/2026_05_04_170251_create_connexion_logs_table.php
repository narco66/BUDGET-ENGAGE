<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connexion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('email', 255)->nullable()->index();
            $table->boolean('succes')->default(false);
            $table->string('adresse_ip', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('connecte_le')->useCurrent();

            $table->index(['user_id', 'connecte_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connexion_logs');
    }
};
