<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Buat tabel batch_stoks — inti dari logika inventori FIFO.
     *
     * Setiap baris mewakili satu batch pengiriman obat dari supplier.
     * Saat terjadi penjualan, sistem akan mengambil stok dari batch
     * dengan tanggal kadaluarsa PALING AWAL terlebih dahulu (FIFO).
     *
     * Kolom current_stock akan berkurang setiap ada penjualan.
     * Batch dianggap habis jika current_stock = 0.
     */
    public function up(): void
    {
        Schema::create("batch_stoks", function (Blueprint $table) {
            $table->id();

            // Relasi ke obat — tidak boleh hapus obat jika masih ada batch aktif
            $table
                ->foreignId("obat_id")
                ->constrained("obats")
                ->onDelete("restrict");

            // Kode batch dari supplier, harus unik secara global (mis: BT-AMX-2026-001)
            $table->string("batch_number")->unique();

            $table->unsignedInteger("initial_stock"); // Jumlah stok saat batch pertama kali masuk
            $table->unsignedInteger("current_stock"); // Stok saat ini (berkurang setiap penjualan FIFO)
            $table->date("expiration_date"); // Tanggal kadaluarsa — kunci urutan FIFO

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel batch_stoks.
     */
    public function down(): void
    {
        Schema::dropIfExists("batch_stoks");
    }
};
