<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('id')->constrained('services')->nullOnDelete()->cascadeOnUpdate();
            $table->string('prenom', 255)->nullable()->after('name');
            $table->string('telephone', 50)->nullable()->after('email');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif')->after('password');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn([
                'service_id',
                'prenom',
                'telephone',
                'statut',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
