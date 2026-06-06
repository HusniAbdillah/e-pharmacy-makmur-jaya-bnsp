# DOKUMEN MIGRASI, CUTOVER, DAN PERUBAHAN
## Sistem E-Commerce Penjualan Obat Klinik Makmur Jaya

Dokumen ini disusun untuk memenuhi Unit Kompetensi Pengelolaan Perubahan dan Migrasi:
1. J.620100.024.02 Melakukan Migrasi Ke Teknologi Baru
2. J.620100.041.01 Melaksanakan Cutover Aplikasi
3. J.620100.047.01 Melakukan Pembaharuan Perangkat Lunak
4. J.620100.043.01 Menganalisis Dampak Perubahan Terhadap Aplikasi

---

### 1. Strategi Migrasi Data (Data Migration Strategy)

Migrasi data dilakukan untuk memindahkan data master obat dan persediaan dari sistem pencatatan spreadsheet manual (Microsoft Excel / CSV) ke dalam database relasional MySQL Klinik Makmur Jaya.

#### 1.1 Pemetaan Kolom (Field Mapping)

| Kolom Asal (CSV) | Kolom Tujuan (MySQL) | Tipe Data Tujuan | Aturan Validasi |
| :--- | :--- | :--- | :--- |
| **KODE_OBAT** | `id` / `code` | BIGINT (Primary Key) | Harus unik, numerik. |
| **NAMA_OBAT** | `name` | VARCHAR(255) | Wajib diisi, minimal 3 karakter. |
| **KATEGORI** | `kategori_obat_id` | BIGINT (Foreign Key) | Harus terdaftar pada tabel `kategori_obats`. |
| **KOMPOSISI** | `composition` | TEXT | Opsional. |
| **DOSIS** | `dose` | VARCHAR(100) | Wajib diisi. |
| **EFEK_SAMPING** | `side_effects` | TEXT | Opsional. |
| **HARGA** | `price` | DECIMAL(12,2) | Angka desimal, tidak boleh negatif. |
| **WAJIB_RESEP**| `requires_prescription`| BOOLEAN | Bernilai 0 (tidak) atau 1 (ya). |

#### 1.2 Validasi Pasca-Migrasi (Post-Migration Validation)
Setelah proses impor paralel selesai, sistem otomatis menjalankan skrip verifikasi data untuk membandingkan total baris masukan dengan total baris tersimpan:
```php
$csvCount = count(file($csvFilePath)) - 1; // Kurangi baris header
$dbCount = Obat::count();

if ($csvCount !== $dbCount) {
    Log::error("Peringatan: Jumlah data hasil migrasi tidak cocok! CSV: {$csvCount}, DB: {$dbCount}");
}
```

---

### 2. Rencana Cutover (Cutover Plan)

Proses pemindahan operasional dari sistem lama ke sistem baru dijadwalkan secara ketat selama periode sepi transaksi (malam hari).

#### 2.1 Jadwal Timeline Aktivitas

*   **Tahap Pra-Cutover (H-2):**
    *   [x] Sosialisasi penutupan sementara sistem pemesanan manual kepada pasien.
    *   [x] Backup database staging dan kode aplikasi stabil.
*   **Tahap Cutover (H-1, Jam 22:00 - 02:00):**
    *   [x] Matikan akses tulis ke pencatatan manual.
    *   [x] Eksekusi migrasi database ke PostgreSQL/MySQL produksi:
        ```bash
        php artisan migrate:fresh --seed
        ```
    *   [x] Jalankan kueri parallel CSV import untuk data obat terkini.
    *   [x] Jalankan optimasi sistem Laravel:
        ```bash
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        ```
    *   [x] Aktifkan daemon process manager (Supervisor) untuk menjalankan worker queue:
        ```bash
        php artisan queue:work
        ```
*   **Tahap Pasca-Cutover (Go-Live):**
    *   [x] Verifikasi koneksi internet internal klinik menuju server produksi.
    *   [x] Uji transaksi simulasi di kasir POS dan akun pasien (1 transaksi tes).
    *   [x] Nyalakan kembali DNS domain klinik penuh menuju server baru.

---

### 3. Rencana Pemulihan (Rollback Plan)

Apabila setelah Go-Live terdeteksi kegagalan sistem kritis (seperti data transaksi korup atau kegagalan koneksi database yang tidak dapat dipulihkan dalam 30 menit):
1.  **Pengalihan DNS:** Arahkan kembali DNS domain utama klinik ke server cadangan statis yang berisi pesan pemberitahuan pemeliharaan (*maintenance page*).
2.  **Pemulihan Backup:** Hapus instansi database yang rusak dan restore database dari file sql backup terenkripsi yang diambil sebelum proses cutover dimulai.
3.  **Investigasi:** Aktifkan tim pengembang untuk membaca log tingkat keparahan error (*severity logs*) di `storage/logs/laravel.log`.

---

### 4. Strategi Pembaharuan Perangkat Lunak (Software Update Strategy)

Setiap perubahan kode perangkat lunak harus melalui alur **Git Version Control** untuk memastikan tidak ada fitur lama yang terganggu (*broken features*).

#### 4.1 Git Branching Strategy
*   **main/master branch:** Hanya berisi kode produksi yang 100% stabil.
*   **staging/development branch:** Digunakan untuk mengintegrasikan fitur baru sebelum diuji secara menyeluruh.
*   **feature/branch-name:** Cabang khusus pengembang untuk menulis kode fitur baru secara terisolasi (misal `feature/pos-counter`).

#### 4.2 Analisis Dampak Perubahan (Impact Analysis)

Apabila dilakukan perubahan skema pada tabel `transaksis` (misalnya penambahan kolom atau modifikasi tipe data kolom `status` seperti yang dilakukan pada MySQL baru-baru ini):
*   **Modul Terdampak:** Modul Checkout Pasien, Dashboard Ringkasan Admin, POS Kasir, dan Riwayat Transaksi Pasien.
*   **Pencegahan Dampak Buruk:**
    *   Gunakan Laravel database transaction (`DB::transaction`) untuk memastikan kueri multi-tabel atomik (jika satu gagal, seluruhnya dibatalkan).
    *   Lakukan pengujian integrasi otomatis sebelum men-deploy perubahan tersebut untuk mendeteksi kegagalan referensi objek atau kueri database yang pincang.
