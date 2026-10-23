<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expression_besoins', function (Blueprint $table) {
            $table->unsignedInteger('version_revision')->default(1)->after('statut');
            $table->timestamp('derniere_revision_at')->nullable()->after('version_revision');
            $table->text('derniere_revision_motif')->nullable()->after('derniere_revision_at');
        });
    }

    public function down(): void
    {
        Schema::table('expression_besoins', function (Blueprint $table) {
            $table->dropColumn(['version_revision', 'derniere_revision_at', 'derniere_revision_motif']);
        });
    }
};
