# DOKUMEN PENGUJIAN (DEBUGGING & UAT)
## Sistem E-Commerce Penjualan Obat Klinik Makmur Jaya

Dokumen ini disusun untuk memenuhi Unit Kompetensi Pengujian Perangkat Lunak:
1. J.620100.025.02 Melakukan Debugging
2. J.620100.038.01 Melaksanakan Pengujian Oleh Pengguna (UAT)

---

### 1. Proses Debugging dan Penanganan Log Kesalahan

Untuk menjaga kualitas perangkat lunak tetap prima, Klinik Makmur Jaya mengimplementasikan dashboard pencatatan log terpusat yang membaca direktori `storage/logs/laravel.log` dan memisahkannya berdasarkan tingkat keparahan (*severity levels*):

```
+-------------------------------------------------------------------------+
|                              LOG VIEWER                                 |
+-------------------------------------------------------------------------+
| [CRITICAL] 2026-06-06 00:08:17 : pdo_pgsql driver not found.            |
| [ERROR]    2026-06-06 00:23:02 : Column status truncated on MySQL.       |
| [WARNING]  2026-06-06 02:15:44 : Stock for Paracetamol below 10 items.  |
| [INFO]     2026-06-06 07:18:55 : Seeder executed successfully.          |
+-------------------------------------------------------------------------+
```

Setiap kali terjadi kegagalan operasional (misalnya koneksi database terputus atau *error* data tipe pada kueri SQL), aplikasi akan menangkap pengecualian (*exceptions*), menghentikan transaksi database yang sedang berlangsung (*rollback*), mencatat detail *stack trace* ke berkas log, dan menyajikan halaman error yang informatif kepada pengguna tanpa membuka informasi sensitif server.

---

### 2. Skenario Pengujian UAT (User Acceptance Testing)

Pengujian UAT dirancang untuk memverifikasi kesesuaian sistem terhadap kebutuhan operasional nyata Klinik Makmur Jaya.

#### 2.1 Skenario 1: Autentikasi dan Proteksi Akses Rute (RBAC)

*   **Aktor:** Pengguna (Pasien dan Tamu/Guest)
*   **Kondisi Awal:** Pengguna belum masuk ke sistem (Guest).
*   **Prosedur UAT:**

| No | Langkah Pengujian | Input/Aksi | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Buka halaman katalog publik. | Akses URL `/` | Halaman katalog obat terbuka, menampilkan daftar obat bebas. | Lulus |
| 2 | Coba buka keranjang belanja. | Akses URL `/keranjang` | Halaman keranjang belanja kosong terbuka dengan benar. | Lulus |
| 3 | Coba akses halaman kasir POS tanpa login. | Akses URL `/kasir/pos` | Sistem memblokir akses dan mengalihkan pengguna ke halaman `/login`. | Lulus |
| 4 | Login sebagai Pasien, lalu coba akses dashboard admin. | Login `pasien@makmurjaya.id` -> Akses `/admin/dashboard` | Akses ditolak (Error 403 atau dialihkan ke halaman katalog). | Lulus |

---

#### 2.2 Skenario 2: Simulasi Pemotongan Stok FIFO Otomatis

*   **Aktor:** Pasien & Sistem Antrean
*   **Kondisi Awal:** Obat "Paracetamol" terdaftar dengan dua batch stok:
    *   Batch A: Sisa stok = 5, Kadaluarsa: 10 Juli 2026.
    *   Batch B: Sisa stok = 10, Kadaluarsa: 30 Desember 2026.
*   **Prosedur UAT:**

| No | Langkah Pengujian | Input/Aksi | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Pasien memesan Paracetamol sebanyak 7 unit. | Klik checkout -> bayar | Transaksi dibuat dengan status menunggu verifikasi / pembayaran. | Lulus |
| 2 | Sistem memproses antrean `ProcessBatchStockUpdate`. | Queue Worker berjalan | Sistem mengambil 5 unit dari Batch A (habis) dan 2 unit dari Batch B. | Lulus |
| 3 | Periksa sisa stok di database. | Cek tabel `batch_stoks` | Batch A memiliki `current_stock` = 0. Batch B memiliki `current_stock` = 8. | Lulus |

---

#### 2.3 Skenario 3: Alur Verifikasi Resep Dokter oleh Apoteker

*   **Aktor:** Pasien dan Apoteker
*   **Kondisi Awal:** Pasien memesan obat keras yang memerlukan resep dokter.
*   **Prosedur UAT:**

| No | Langkah Pengujian | Input/Aksi | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Pasien mengunggah file resep dan menyelesaikan checkout. | Unggah berkas resep (.jpg) -> kirim | Transaksi tersimpan dengan status `menunggu_pembayaran`. | Lulus |
| 2 | Apoteker login dan masuk ke panel transaksi admin/apoteker. | Buka panel manajemen transaksi | Apoteker dapat melihat berkas resep yang diunggah dan menekan tombol verifikasi. | Lulus |
| 3 | Apoteker menyetujui validitas resep. | Klik "Ubah Status" -> "Diproses" | Status transaksi diperbarui di dashboard pasien secara real-time. | Lulus |

---

### 3. Hasil Pengujian Otomatis (Automated Tests Summary)

Laravel secara native mendukung automated testing berbasis PHPUnit. Suite pengujian kami mencakup 26 test case fungsional:
*   **Uji Autentikasi:** Menguji alur masuk, keluar, verifikasi password, dan registrasi pasien baru.
*   **Uji Rute Dinamis:** Memastikan pengalihan halaman `/dashboard` sesuai dengan peran masing-masing pengguna.
*   **Uji Keranjang:** Memastikan penambahan kuantitas barang di keranjang dihitung secara akurat.

Seluruh **26 pengujian dinyatakan lulus (Passed)** dengan performa eksekusi di bawah 30 detik pada database uji SQLite / MySQL.
