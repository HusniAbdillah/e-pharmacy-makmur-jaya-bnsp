# DOKUMEN PERENCANAAN PROYEK (PROJECT MANAGEMENT)
## Sistem E-Commerce Penjualan Obat Klinik Makmur Jaya

Dokumen ini disusun untuk memenuhi Unit Kompetensi Manajemen Dasar Proyek:
1. M.702090.001.01 Mengelola Proyek Secara Terintegrasi (Integration Management)
2. M.702090.002.01 Mengelola Ruang Lingkup Proyek (Scope Management)
3. M.702090.005.01 Mengelola Kualitas Proyek (Quality Management)

---

### 1. Project Charter (Piagam Proyek)

#### 1.1 Latar Belakang Proyek
Klinik Makmur Jaya melayani sekitar 150 hingga 200 pasien per hari dengan lebih dari 2.000 jenis obat terdaftar. Pencatatan manual menimbulkan inefisiensi inventarisasi, risiko obat kedaluwarsa, serta keterlambatan pelayanan. Untuk menyelesaikan isu tersebut, proyek pembangunan sistem e-commerce ini dilaksanakan dengan tujuan memusatkan database, memfasilitasi transaksi online, serta mengotomatiskan pengelolaan stok fisik.

#### 1.2 Tujuan Proyek
*   Membangun aplikasi e-commerce penjualan obat klinik berbasis web dengan autentikasi multi-level (Admin, Apoteker, Kasir, Pasien).
*   Mengimplementasikan algoritma pengelolaan stok otomatis berbasis First In First Out (FIFO) untuk mengurangi risiko obat kedaluwarsa yang tidak terdeteksi.
*   Menyediakan saluran pengunggahan dan verifikasi resep dokter secara digital bagi Apoteker.
*   Mengintegrasikan transaksi langsung (offline) di klinik dengan Point of Sale (POS) Kasir dan transaksi online pasien.

#### 1.3 Struktur Organisasi Proyek
*   **Project Sponsor:** Manajemen Klinik Makmur Jaya
*   **Project Manager & Systems Analyst:** Asesi (Web Developer)
*   **Quality Assurance (QA) & Auditor:** Asesor LSP

---

### 2. Project Integration Management (Pengelolaan Proyek Terintegrasi)

Integration Management dirancang untuk menyelaraskan setiap tahap siklus hidup proyek (SDLC) mulai dari inisiasi, perencanaan, eksekusi, pemantauan, hingga penutupan proyek (Go-Live).

#### 2.1 Alur Koordinasi Pekerjaan
Seluruh artefak kode disimpan dalam repositori berbasis Git. Integrasi fungsionalitas antar modul dikawal ketat dengan pengujian otomatis sebelum penggabungan kode (code merging). Sinkronisasi database MySQL, antrean latar belakang (Job Queue), dan antarmuka dinamis (Livewire) diintegrasikan langsung pada file konfigurasi inti Laravel (.env) untuk memastikan keselarasan operasional.

---

### 3. Project Scope Management (Pengelolaan Ruang Lingkup Proyek)

Ruang lingkup proyek didefinisikan secara tegas untuk mencegah terjadinya penambahan lingkup yang tidak terkendali (scope creep).

#### 3.1 Work Breakdown Structure (WBS)

```
1. Inisiasi dan Analisis Kebutuhan
   1.1. Pemetaan alur bisnis transaksi obat klinik.
   1.2. Pembuatan daftar kebutuhan fungsional & non-fungsional.
2. Perancangan Sistem
   2.1. Perancangan database relasional MySQL.
   2.2. Pembuatan diagram arsitektur perangkat keras.
3. Pengembangan Perangkat Lunak (TALL Stack)
   3.1. Pembuatan skema autentikasi & middleware RBAC.
   3.2. Pengembangan modul transaksi Pasien (Katalog, Keranjang, Checkout, Resep).
   3.3. Pengembangan modul Point of Sale (POS) Kasir.
   3.4. Pembuatan antrean asinkron FIFO untuk pemotongan stok.
   3.5. Modul pelaporan DOMPDF untuk Apoteker.
4. Jaminan Kualitas & Pengujian (QA)
   4.1. Pembuatan unit test Laravel.
   4.2. Pengujian kegagalan sistem & debugging.
   4.3. Pelaksanaan UAT (User Acceptance Testing).
5. Peluncuran (Cutover)
   5.1. Migrasi database master obat via paralel CSV import.
   5.2. Go-Live & serah terima panduan teknis.
```

#### 3.2 Batasan Proyek
*   Sistem tidak terhubung dengan payment gateway pihak ketiga (pembayaran diverifikasi secara manual oleh Kasir/Apoteker).
*   Sistem tidak mengelola logistik pengiriman kurir eksternal; pengambilan obat dilakukan di tempat atau diatur manual oleh pihak klinik.

---

### 4. Project Quality Management (Pengelolaan Kualitas Proyek)

Pengelolaan kualitas memastikan sistem bekerja sesuai standar akurasi medis dan keandalan e-commerce.

#### 4.1 Key Performance Indicators (KPIs) Kualitas

| Indikator Kinerja | Standar Target | Metode Pengukuran |
| :--- | :--- | :--- |
| **Akurasi Stok FIFO** | 0% Kesalahan (Zero Defect) | Unit testing & peninjauan log audit transaksi stok fisik. |
| **Responsivitas UI** | Waktu muat halaman < 1.5 detik | DevTools Network analyzer pada query teroptimasi. |
| **Keandalan Paralel Import** | 100% data ter-import dengan benar | Validasi checksum total baris CSV versus jumlah baris database. |
| **Cakupan Pengujian** | Seluruh jalur autentikasi teruji | Eksekusi `php artisan test` dengan tingkat kelulusan 100%. |

#### 4.2 Quality Checklist

*   [x] Seluruh input formulir dilengkapi validasi tipe data dan batas maksimum karakter.
*   [x] Seluruh kueri SQL krusial diuji menggunakan indeks untuk menghindari bottleneck.
*   [x] File resep dokter yang diunggah divalidasi format ekstensi (.jpg, .png, .pdf) dan batas ukuran maksimal 2MB.
*   [x] Sandi pengguna dienkripsi menggunakan Bcrypt dengan cost factor minimal 12.
