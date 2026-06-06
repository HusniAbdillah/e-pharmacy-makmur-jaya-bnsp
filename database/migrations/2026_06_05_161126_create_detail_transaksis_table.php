<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Buat tabel detail_transaksis (baris/item dalam faktur penjualan).
     *
     * Setiap baris mencatat SATU jenis obat dari SATU batch tertentu.
     * Jika satu obat diambil dari 2 batch berbeda, akan ada 2 baris terpisah.
     * Ini memungkinkan pelacakan FIFO yang akurat per transaksi.
     *
     * CATATAN PENTING: unit_price adalah snapshot harga saat transaksi terjadi,
     * bukan referensi ke harga obat saat ini. Ini memastikan integritas data historis.
     */
    public function up(): void
    {
        Schema::create("detail_transaksis", function (Blueprint $table) {
            $table->id();

            // Detail ikut terhapus jika transaksi induknya dihapus (cascade)
            $table
                ->foreignId("transaksi_id")
                ->constrained("transaksis")
                ->onDelete("cascade");

            // Referensi ke obat — dilindungi untuk menjaga integritas riwayat penjualan
            $table
                ->foreignId("obat_id")
                ->constrained("obats")
                ->onDelete("restrict");

            // Referensi ke batch spesifik — nullable agar mendukung pemrosesan FIFO asinkron (via Job).
            // Nilai ini diisi oleh ProcessBatchStockUpdate setelah kalkulasi FIFO selesai.
            // TIDAK BOLEH null pada saat transaksi berstatus 'selesai'.
            $table
                ->foreignId("batch_stok_id")
                ->nullable()
                ->constrained("batch_stoks")
                ->onDelete("restrict");

            $table->unsignedInteger("quantity"); // Jumlah unit yang dibeli dalam item ini
            $table->decimal("unit_price", 12, 2); // Snapshot harga satuan saat transaksi

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel detail_transaksis.
     */
    public function down(): void
    {
        Schema::dropIfExists("detail_transaksis");
    }
};
