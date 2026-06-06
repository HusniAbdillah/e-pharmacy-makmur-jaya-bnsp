<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Kolom path file resep
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('resep_path')->nullable()->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('resep_path');
        });
    }
};
