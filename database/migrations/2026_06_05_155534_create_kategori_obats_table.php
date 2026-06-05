<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Buat tabel kategori_obats.
     * Contoh kategori: Antibiotik, Vitamin, Analgesik, Antihipertensi, dll.
     */
    public function up(): void
    {
        Schema::create("kategori_obats", function (Blueprint $table) {
            $table->id();
            $table->string("name"); // Nama kategori obat
            $table->timestamps();
        });
    }

    /**
     * Hapus tabel kategori_obats.
     */
    public function down(): void
    {
        Schema::dropIfExists("kategori_obats");
    }
};
