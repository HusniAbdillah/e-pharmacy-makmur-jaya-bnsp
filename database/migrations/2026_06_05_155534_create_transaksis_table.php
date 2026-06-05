<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Buat tabel transaksis (kepala/header faktur penjualan).
     * Setiap baris mewakili satu sesi pembelian/penjualan obat.
     */
    public function up(): void
    {
        Schema::create("transaksis", function (Blueprint $table) {
            $table->id();

            // Nomor invoice unik, di-generate oleh aplikasi (mis: INV-20260605-001)
            $table->string("invoice_number")->unique();

            // Kasir atau pasien yang melakukan transaksi
            // onDelete restrict: riwayat transaksi harus tetap ada meski akun dinonaktifkan
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("restrict");

            $table->decimal("total_price", 12, 2); // Total nilai transaksi (Rupiah)

            // Status alur transaksi
            $table
                ->enum("status", [
                    "menunggu",
                    "diproses",
                    "selesai",
                    "dibatalkan",
                ])
                ->default("menunggu");

            // true = transaksi online dari aplikasi pasien, false = kasir langsung
            $table->boolean("is_online")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel transaksis.
     */
    public function down(): void
    {
        Schema::dropIfExists("transaksis");
    }
};
