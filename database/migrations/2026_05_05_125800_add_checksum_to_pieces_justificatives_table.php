<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pieces_justificatives', function (Blueprint $table) {
            $table->string('checksum', 64)->nullable()->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('pieces_justificatives', function (Blueprint $table) {
            $table->dropColumn('checksum');
        });
    }
};
