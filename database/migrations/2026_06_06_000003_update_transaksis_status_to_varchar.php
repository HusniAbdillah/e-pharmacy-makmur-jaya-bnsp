<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    // Perluas kolom status
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL: ubah ke varchar
            DB::statement('ALTER TABLE transaksis DROP CONSTRAINT IF EXISTS transaksis_status_check');
            DB::statement('ALTER TABLE transaksis ALTER COLUMN status TYPE VARCHAR(50) USING status::VARCHAR');
            DB::statement("ALTER TABLE transaksis ALTER COLUMN status SET DEFAULT 'menunggu_pembayaran'");
        } else {
            // Ubah tipe kolom status
            Schema::table('transaksis', function (Blueprint $table) {
                $table->string('status', 50)->default('menunggu_pembayaran')->change();
            });
        }
        // Migrasikan data lama
        DB::statement("UPDATE transaksis SET status = 'menunggu_pembayaran' WHERE status = 'menunggu'");
    }

    public function down(): void
    {
        // Migrasikan data kembali
        DB::statement("UPDATE transaksis SET status = 'menunggu' WHERE status IN ('menunggu_verifikasi','menunggu_pembayaran')");
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transaksis ALTER COLUMN status SET DEFAULT 'menunggu'");
            DB::statement("ALTER TABLE transaksis ADD CONSTRAINT transaksis_status_check CHECK (status IN ('menunggu','diproses','selesai','dibatalkan'))");
        } else {
            // Ubah tipe kolom kembali
            Schema::table('transaksis', function (Blueprint $table) {
                $table->string('status', 20)->default('menunggu')->change();
            });
        }
    }
};
