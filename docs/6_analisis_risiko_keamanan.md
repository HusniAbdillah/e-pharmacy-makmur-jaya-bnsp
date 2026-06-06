# ANALISIS RISIKO KEAMANAN INFORMASI DAN MONITORING SUMBER DAYA
## Sistem E-Commerce Penjualan Obat Klinik Makmur Jaya

Dokumen ini disusun untuk memenuhi Unit Kompetensi Keamanan dan Pemantauan Aplikasi:
1. J.62090.018.01 Mengelola Resiko Keamanan Informasi
2. J.620100.044.01 Menerapkan Alert Notification Jika Aplikasi Bermasalah
3. J.620100.045.01 Melakukan Pemantauan Resource Yang Digunakan Aplikasi

---

### 1. Matriks Risiko Keamanan (Security Risk Matrix)

Kami membagi penilaian risiko berdasarkan dampak dan probabilitas kejadian sebelum dan sesudah mitigasi diaktifkan:

| No | Jenis Ancaman | Dampak | Probabilitas | Tingkat Risiko | Solusi Mitigasi |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **SQL Injection (SQLi)** | Tinggi | Rendah | Sedang | Parameter binding menggunakan PDO pada query builder Eloquent. |
| 2 | **Cross-Site Scripting (XSS)** | Sedang | Sedang | Sedang | Automatic escaping di blade compiler dan pembersihan input. |
| 3 | **CSRF (Cross-Site Request Forgery)** | Sedang | Rendah | Rendah | Token csrf terenkripsi pada setiap transaksi posting data. |
| 4 | **Password Cracking / Bruteforce** | Tinggi | Sedang | Tinggi | Penerapan Bcrypt hashing serta perlambatan rate-limiting login. |
| 5 | **Akses Ilegal Halaman Kontrol (Privilege Escalation)** | Tinggi | Rendah | Sedang | Middleware RBAC ketat dan pemeriksaan hak akses di gate rute. |

---

### 2. Analisis Mitigasi Keamanan Tingkat Lanjut

#### 2.1 SQL Injection Prevention (Pencegahan SQL Injection)
Aplikasi tidak pernah menyusun kueri SQL mentah menggunakan penggabungan string langsung dari masukan pengguna. Eloquent ORM secara default menerjemahkan kueri menjadi *Prepared Statements* menggunakan driver PDO PHP:
```php
// SQL Aman: Parameter dipisahkan dari instruksi kueri
$obat = Obat::where('id', '=', $id)->first();
```

#### 2.2 Cross-Site Scripting (XSS) Prevention (Pencegahan XSS)
Seluruh keluaran dinamis yang diproses melalui template blade menggunakan sintaks `{{ $data }}`. Sintaks ini secara otomatis menjalankan fungsi `htmlspecialchars()` di PHP untuk menonaktifkan kode HTML atau tag JavaScript jahat (misal: `<script>alert('hack')</script>`) yang berpotensi menyusup ke database atau kolom input.

#### 2.3 Cross-Site Request Forgery (CSRF) Mitigation (Mitigasi CSRF)
Setiap sesi pengguna yang aktif diberikan token CSRF unik yang dihasilkan secara acak oleh Laravel. Token ini diperiksa pada setiap permintaan POST/PUT/DELETE:
```html
<form action="/checkout" method="POST">
    @csrf
    <!-- Input token terenkripsi dikirim otomatis -->
</form>
```
Jika token tidak ada atau tidak cocok dengan sesi aktif, web server akan mengembalikan respons error HTTP 419 (Page Expired) dan membatalkan transaksi.

#### 2.4 Hashing Kata Sandi & Proteksi Akun
Sistem melarang penyimpanan kata sandi teks polos (*plain text*). Semua kata sandi dienkripsi satu arah menggunakan algoritma **Bcrypt** dengan *work factor (rounds)* minimal 12, yang terbukti tangguh terhadap serangan brute-force dan rainbow-table:
```php
$user->password = Hash::make($request->password);
```

#### 2.5 Role-Based Access Control (RBAC) & Route Guards
Hak akses dibatasi secara kaku di level middleware. Pengguna yang mencoba mengakses rute di luar hak aksesnya akan diblokir sebelum instruksi logika controller dijalankan:
```php
// Mencegah pasien atau tamu membuka halaman pos kasir
Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/pos', PosKonter::class);
});
```

---

### 3. Penerapan Notifikasi Alert Gangguan Aplikasi

Untuk memastikan kegagalan fatal (misalnya kegagalan koneksi database MySQL atau crash server) segera diketahui oleh tim pengembang, sistem mengonfigurasi Laravel Error Handler untuk menangkap kesalahan tak terduga (*uncaught exceptions*):

*   **Penyaringan Severity:** Log kesalahan disaring berdasarkan urgensi (`CRITICAL` atau `ERROR`).
*   **Pengiriman Alert:** Jika terjadi pengecualian tingkat `CRITICAL`, handler memicu pengiriman notifikasi kesalahan ke file audit log internal dan mengirimkan alert via Webhook Slack atau modul alert bawaan agar tim administrator segera melakukan investigasi sebelum menurunkan performa bisnis.

---

### 4. Pemantauan Sumber Daya Aplikasi (Resource Monitoring)

Pemantauan sumber daya server dilakukan untuk mencegah terjadinya degradasi performa akibat penggunaan CPU, RAM, atau I/O disk yang berlebihan.

*   **Pemantauan Database Pool:** Membatasi koneksi aktif maksimum ke MySQL untuk mencegah server database kelebihan beban kueri (*database bottleneck*).
*   **Queue Monitoring:** Memantau jumlah antrean tersisa menggunakan perintah CLI secara berkala:
    ```bash
    php artisan queue:monitor database:default
    ```
    Jika jumlah antrean tertunda melebihi 100 job, sistem administrator akan menerima peringatan untuk menambah instance queue worker.
*   **Disk & RAM Alert:** Memantau penggunaan memori PHP menggunakan fungsi `memory_get_usage()` pada pemrosesan impor berkas CSV yang besar guna memastikan batas memori PHP (*memory limit*) tidak terlampaui.
