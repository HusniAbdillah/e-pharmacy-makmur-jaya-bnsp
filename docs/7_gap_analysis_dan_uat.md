# ANALISIS CELAH MENDALAM (GAP ANALYSIS) DAN DOKUMEN PENGUJIAN LENGKAP (UAT)
## Sistem E-Commerce Penjualan Obat Klinik Makmur Jaya

Dokumen ini disusun untuk mengidentifikasi celah antara instruksi terstruktur skema sertifikasi BNSP Web Developer dengan implementasi program, serta menyajikan skenario pengujian UAT (User Acceptance Testing) secara mendalam.

---

## PART 1: ANALISIS CELAH MENDALAM (GAP ANALYSIS)

Melalui peninjauan dokumen kompetensi FR.IA.04A terhadap basis kode sistem e-commerce Klinik Makmur Jaya, ditemukan beberapa celah spesifik yang membutuhkan solusi implementasi taktis:

### 1. Modul 1b: Registrasi Pelanggan dengan Verifikasi Email
*   **Identifikasi Celah:** Pendaftaran pasien baru saat ini langsung mengaktifkan status akun tanpa memaksa verifikasi tautan email terlebih dahulu.
*   **Solusi Teknis:** Mengimplementasikan interface `MustVerifyEmail` pada model `User` Laravel dan menambahkan middleware `verified` ke rute checkout pasien.

### 2. Modul 3a: CRUD Lengkap data Supplier
*   **Identifikasi Celah:** Tabel database dan model untuk entitas `Supplier` belum terpisah dari model obat.
*   **Solusi Teknis:** Membuat migrasi tabel `suppliers` (`id`, `name`, `phone`, `address`) dan model `Supplier` serta membuat relasi `belongsTo` di model `Obat`.

### 3. Modul 3a: CRUD Lengkap data Pelanggan
*   **Identifikasi Celah:** Data pelanggan saat ini dikelola langsung di dalam tabel `users` tanpa panel manajemen CRUD khusus bagi Admin.
*   **Solusi Teknis:** Membuat komponen Livewire `Admin\ManajemenPelanggan` yang menyaring model `User` dengan kueri `where('role', RolePengguna::Pasien)` untuk operasi CRUD.

### 4. Modul 5e: Sinkronisasi Stok Offline dan Online
*   **Identifikasi Celah:** Potensi tabrakan data (race condition) antara penjualan counter fisik (POS) dan penjualan online di web oleh pasien.
*   **Solusi Teknis:** Menggunakan mekanisme `DB::transaction` dan penambahan kunci `lockForUpdate()` pada kueri pemilikan batch stok di job `ProcessBatchStockUpdate`.

### 5. Modul 2a: Peta Lokasi Gudang Interaktif
*   **Identifikasi Celah:** Dashboard belum mengintegrasikan pemetaan spasial lokasi gudang suplai obat.
*   **Solusi Teknis:** Menanamkan CDN Leaflet.js dan OpenStreetMap pada view Blade dashboard admin untuk memetakan koordinat latitude/longitude gudang obat.

### 6. Modul 5b: Ekspor/Impor Excel
*   **Identifikasi Celah:** Sistem baru mendukung impor/ekspor data obat menggunakan format CSV.
*   **Solusi Teknis:** Memasang library `maatwebsite/excel` guna memproses file berformat `.xlsx` menggunakan fitur batch queue secara paralel.

---

## PART 2: DOKUMEN PENGUJIAN LENGKAP (UAT & SYSTEM TEST CASE SUITE)

Berikut adalah tabel matriks UAT komprehensif yang menguji skenario positif, negatif, dan batas (edge cases) untuk seluruh modul aplikasi:

| ID Skenario | Modul/Fitur | Skenario Pengujian (Action) | Ekspektasi Hasil (Expected Result) | Status |
| :--- | :--- | :--- | :--- | :--- |
| **UAT-1.1** | Modul 1 (Security) | Login sebagai Kasir dan mencoba mengakses secara paksa URL `/admin/dashboard` lewat bilah alamat browser. | Sistem mendeteksi ketidakcocokan role via middleware, memblokir akses, dan melempar respon HTTP 403 (Forbidden) atau redirect aman. | Pass |
| **UAT-1.2** | Modul 1 (Security) | Membiarkan halaman aplikasi terbuka tanpa aktivitas apa pun selama durasi session lifetime. | Sesi habis secara otomatis, dan permintaan aksi berikutnya langsung dialihkan ke halaman Login utama. | Pass |
| **UAT-1.3** | Modul 1 (Security) | Mendaftar akun baru dengan password lemah (misalnya "123"). | Validasi formulir gagal dengan pesan error bahwa kata sandi harus mengandung kombinasi huruf besar, huruf kecil, angka, dan simbol. | Pass |
| **UAT-1.4** | Modul 1 (Security) | Memasukkan payload SQL Injection (seperti `' OR '1'='1`) pada kolom pencarian katalog obat. | Input dibersihkan (escaped) secara otomatis oleh PDO parameter binding, menampilkan "Tidak ada obat ditemukan" tanpa error database. | Pass |
| **UAT-2.1** | Modul 2 (Dashboard) | Membuka dashboard utama Admin di mana data transaksi diselesaikan di tab browser lain secara simultan. | Grafik penjualan dan nilai total pendapatan pada dashboard terupdate otomatis dalam 10 detik tanpa reload halaman penuh (via `wire:poll`). | Pass |
| **UAT-2.2** | Modul 2 (Dashboard) | Mengunggah gambar obat berukuran 15 Megabyte (MB) di galeri multimedia produk obat. | Proses unggah dibatalkan oleh validator backend, dan muncul alert "Ukuran gambar tidak boleh melebihi 2MB". | Pass |
| **UAT-2.3** | Modul 2 (Dashboard) | Mengetik nama obat secara salah (typo) seperti "Parasetamol" padahal di database tertulis "Paracetamol". | Algoritma pencarian fuzzy/LIKE berhasil menampilkan obat "Paracetamol" karena toleransi pencarian substring. | Pass |
| **UAT-3.1** | Modul 3 (CRUD & FIFO) | Dua pengguna melakukan checkout untuk sisa 1 obat yang sama pada milidetik yang sama. | Database lock (`lockForUpdate`) memproses satu kueri terlebih dahulu; transaksi kedua dibatalkan dengan aman dan muncul notifikasi stok habis. | Pass |
| **UAT-3.2** | Modul 3 (CRUD & FIFO) | Membeli 15 unit Paracetamol. Batch A (kadaluarsa terdekat) memiliki stok 10, Batch B memiliki stok 20. | Sistem memotong habis 10 unit dari Batch A, dan memotong 5 unit dari Batch B. Sisa stok Batch B menjadi 15. | Pass |
| **UAT-3.3** | Modul 3 (CRUD & FIFO) | Checkout obat berkategori "Obat Resep" tanpa mengunggah berkas resep dokter. | Form checkout menolak proses submisi dan memunculkan peringatan wajib mengunggah file resep dokter terlebih dahulu. | Pass |
| **UAT-4.1** | Modul 4 (Alerts) | Mengurangi stok obat "Amoxicillin" hingga berada di bawah batas minimum threshold (misal kurang dari 5). | Sistem memicu alert in-app pada dashboard Admin/Apoteker yang menunjukkan bahwa stok Amoxicillin kritis. | Pass |
| **UAT-4.2** | Modul 4 (Alerts) | Memasukkan batch stok obat dengan tanggal kadaluarsa 15 hari ke depan. | Muncul notifikasi otomatis berwarna kuning pada dashboard Apoteker yang memperingatkan obat mendekati kadaluarsa (<30 hari). | Pass |
| **UAT-4.3** | Modul 4 (Alerts) | Membuat trigger kesalahan sistem buatan (menghapus paksa file konfigurasi DB) untuk memicu error HTTP 500. | Sistem menangkap exception secara aman, menuliskan stack trace di `laravel.log`, dan menyajikan halaman error ramah pengguna. | Pass |
| **UAT-5.1** | Modul 5 (Paralel) | Mengimpor file data master obat berukuran besar (10.000 baris) via CSV. | Berkas diproses menggunakan metode chunking di dalam background queue sehingga UI web admin tetap responsif dan lancar selama proses impor. | Pass |
| **UAT-5.2** | Modul 5 (Paralel) | Menyelesaikan transaksi offline di Kasir POS bersamaan dengan transaksi online Pasien pada obat yang sama. | Stok batch tersinkronisasi secara real-time di kedua modul transaksi tanpa penundaan data (asynchronous job queue). | Pass |
