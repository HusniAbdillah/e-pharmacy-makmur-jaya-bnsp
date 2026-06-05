<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Buat tabel obats (master data produk obat).
     * Tabel ini hanya menyimpan informasi produk, BUKAN stok.
     * Stok dikelola secara terpisah di tabel batch_stoks (logika FIFO).
     */
    public function up(): void
    {
        Schema::create("obats", function (Blueprint $table) {
            $table->id();

            // Relasi ke kategori — tidak boleh hapus kategori jika masih ada obat
            $table
                ->foreignId("kategori_obat_id")
                ->constrained("kategori_obats")
                ->onDelete("restrict");

            $table->string("name"); // Nama generik/merk obat
            $table->text("description")->nullable(); // Deskripsi manfaat dan kegunaan
            $table->text("composition")->nullable(); // Komposisi / zat aktif
            $table->string("dose"); // Aturan dosis (mis: 3x1 tablet/hari)
            $table->text("side_effects")->nullable(); // Efek samping yang perlu diketahui pasien
            $table->decimal("price", 12, 2); // Harga jual per unit (dalam Rupiah)
            $table->unsignedInteger("minimum_stock")->default(10); // Batas stok minimum untuk peringatan kritis
            $table->boolean("requires_prescription")->default(false); // true = wajib resep dokter (obat keras)

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel obats.
     */
    public function down(): void
    {
        Schema::dropIfExists("obats");
    }
};
