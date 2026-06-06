<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Kolom path gambar obat
    public function up(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            $table->string('gambar_obat')->nullable()->after('requires_prescription');
        });
    }

    public function down(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            $table->dropColumn('gambar_obat');
        });
    }
};
