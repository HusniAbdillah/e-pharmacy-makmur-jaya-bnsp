<?php

namespace Database\Seeders;

use App\Models\KategoriObat;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk mengisi 4 kategori obat wajib sesuai studi kasus.
 * Menggunakan firstOrCreate agar aman dijalankan berulang kali (idempoten).
 */
class KategoriObatSeeder extends Seeder
{
    /**
     * Daftar kategori obat sesuai standar kasus uji BNSP.
     *
     * @var array<int, string>
     */
    private array $kategoris = [
        'Obat Resep',      // Obat keras yang memerlukan resep dokter
        'Obat Bebas',      // Obat yang dapat dibeli tanpa resep (logo hijau/biru)
        'Suplemen',        // Vitamin, mineral, dan suplemen kesehatan
        'Alat Kesehatan',  // Alkes non-obat: termometer, glukometer, kasa, dll.
    ];

    /**
     * Jalankan seeder kategori obat.
     */
    public function run(): void
    {
        $this->command->info('  → Menyemai kategori obat...');

        foreach ($this->kategoris as $namaKategori) {
            KategoriObat::firstOrCreate(['name' => $namaKategori]);
        }

        $this->command->info('  ✓ ' . count($this->kategoris) . ' kategori obat berhasil disemai.');
    }
}
