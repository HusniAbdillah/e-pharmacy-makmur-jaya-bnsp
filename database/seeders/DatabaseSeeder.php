<?php

namespace Database\Seeders;

use App\Enums\RolePengguna;
use App\Enums\StatusTransaksi;
use App\Models\BatchStok;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Isi database dengan data awal untuk pengembangan dan pengujian.
     *
     * Urutan eksekusi:
     *   1. Kategori obat (referensi dasar)
     *   2. Akun pengguna per peran (kredensial tetap untuk demo)
     *   3. Master data obat per kategori
     *   4. Batch stok per obat dengan variasi kadaluarsa (untuk uji FIFO)
     */
    public function run(): void
    {
        $this->command->info("");
        $this->command->info("╔══════════════════════════════════════╗");
        $this->command->info("║   Makmur Jaya E-Pharmacy — Seeder   ║");
        $this->command->info("╚══════════════════════════════════════╝");

        // ------------------------------------------------------------------
        // LANGKAH 1: Kategori Obat
        // ------------------------------------------------------------------
        $this->call(KategoriObatSeeder::class);

        // ------------------------------------------------------------------
        // LANGKAH 2: Pengguna Tetap Per Peran (kredensial demo)
        // ------------------------------------------------------------------
        $this->command->info("  → Menyemai akun pengguna per peran...");

        $penggunaTetap = [
            [
                "name" => "Administrator Utama",
                "email" => "admin@makmurjaya.id",
                "password" => Hash::make("Admin@123"),
                "role" => RolePengguna::Admin,
            ],
            [
                "name" => "Apt. Dewi Rahayu, S.Farm.",
                "email" => "apoteker@makmurjaya.id",
                "password" => Hash::make("Apoteker@123"),
                "role" => RolePengguna::Apoteker,
            ],
            [
                "name" => "Budi Santoso",
                "email" => "kasir@makmurjaya.id",
                "password" => Hash::make("Kasir@123"),
                "role" => RolePengguna::Kasir,
            ],
            [
                "name" => "Siti Nurhaliza",
                "email" => "pasien@makmurjaya.id",
                "password" => Hash::make("Pasien@123"),
                "role" => RolePengguna::Pasien,
            ],
        ];

        foreach ($penggunaTetap as $data) {
            User::firstOrCreate(
                ["email" => $data["email"]],
                [
                    "name" => $data["name"],
                    "password" => $data["password"],
                    "role" => $data["role"],
                    "email_verified_at" => now(),
                ],
            );
        }

        // Tambahkan 5 pasien acak untuk simulasi data nyata
        User::factory(5)->pasien()->create();

        $this->command->info(
            "  ✓ Akun pengguna berhasil disemai (4 tetap + 5 pasien acak).",
        );

        // ------------------------------------------------------------------
        // LANGKAH 2.5: Supplier
        // ------------------------------------------------------------------
        $this->command->info("  → Menyemai data supplier...");
        $suppliersData = [
            [
                'name' => 'PT. Kimia Farma Trading & Distribution',
                'phone' => '021-3847777',
                'email' => 'info@kimiafarma.co.id',
                'address' => 'Jl. Budi Utomo No. 1, Jakarta Pusat',
            ],
            [
                'name' => 'PT. Kalbe Farma Tbk',
                'phone' => '021-8983322',
                'email' => 'contact@kalbe.co.id',
                'address' => 'Kawasan Industri Delta Silicon, Cikarang, Bekasi',
            ],
            [
                'name' => 'PT. Bina San Prima',
                'phone' => '022-7310000',
                'email' => 'bandung@binasanprima.com',
                'address' => 'Jl. Soekarno-Hatta No. 162, Bandung',
            ],
            [
                'name' => 'PT. Mensa Bina Sukses',
                'phone' => '021-46822222',
                'email' => 'mbs@mensa.co.id',
                'address' => 'Jl. Pulo Kambing II No. 27, JIEP Pulogadung, Jakarta Timur',
            ]
        ];

        $suppliers = collect();
        foreach ($suppliersData as $s) {
            $suppliers->push(\App\Models\Supplier::firstOrCreate(['name' => $s['name']], $s));
        }
        $this->command->info("  ✓ Data supplier berhasil disemai.");

        // ------------------------------------------------------------------
        // LANGKAH 3: Master Data Obat Per Kategori
        // ------------------------------------------------------------------
        $this->command->info("  → Menyemai data obat per kategori...");

        $kategoriObatResep = KategoriObat::where("name", "Obat Resep")->first();
        $kategoriObatBebas = KategoriObat::where("name", "Obat Bebas")->first();
        $kategoriSuplemen = KategoriObat::where("name", "Suplemen")->first();
        $kategoriAlatKesehatan = KategoriObat::where(
            "name",
            "Alat Kesehatan",
        )->first();

        // --- Obat Resep (perlu resep dokter) ---
        $obatResep = collect([
            [
                "name" => "Amoxicillin 500mg",
                "description" =>
                    "Antibiotik golongan penisilin untuk infeksi bakteri saluran napas, telinga, dan kemih.",
                "composition" =>
                    "Amoxicillin trihydrate 574mg setara Amoxicillin 500mg",
                "dose" =>
                    "3 x 1 kapsul/hari (setiap 8 jam), habiskan antibiotik.",
                "side_effects" => "Mual, diare, ruam kulit, anafilaksis.",
                "price" => 4500,
                "minimum_stock" => 20,
            ],
            [
                "name" => "Metformin 500mg",
                "description" =>
                    "Antidiabetik oral biguanida untuk mengontrol gula darah pada diabetes tipe 2.",
                "composition" => "Metformin HCl 500mg",
                "dose" => "2-3 x 1 tablet/hari bersama makanan.",
                "side_effects" => "Mual, muntah, diare pada awal terapi.",
                "price" => 3800,
                "minimum_stock" => 30,
            ],
            [
                "name" => "Amlodipine 5mg",
                "description" =>
                    "Antagonis kalsium untuk hipertensi dan angina pektoris stabil.",
                "composition" => "Amlodipine besylate setara Amlodipine 5mg",
                "dose" => "1 x 1 tablet/hari pada waktu yang sama.",
                "side_effects" =>
                    "Pembengkakan pergelangan kaki, pusing, kemerahan wajah.",
                "price" => 5200,
                "minimum_stock" => 15,
            ],
            [
                "name" => "Simvastatin 20mg",
                "description" =>
                    "Statin untuk menurunkan kolesterol LDL dan trigliserida.",
                "composition" => "Simvastatin 20mg",
                "dose" => "1 x 1 tablet/hari pada malam hari.",
                "side_effects" =>
                    "Nyeri otot (mialgia), peningkatan enzim hati.",
                "price" => 7000,
                "minimum_stock" => 10,
            ],
            [
                "name" => "Dexamethasone 0,5mg",
                "description" =>
                    "Kortikosteroid untuk peradangan, alergi berat, dan kondisi autoimun.",
                "composition" => "Dexamethasone 0,5mg",
                "dose" => "Sesuai petunjuk dokter. Jangan berhenti tiba-tiba.",
                "side_effects" =>
                    "Peningkatan nafsu makan, retensi cairan, peningkatan gula darah.",
                "price" => 2800,
                "minimum_stock" => 10,
            ],
        ]);

        // --- Obat Bebas (tanpa resep) ---
        $obatBebas = collect([
            [
                "name" => "Paracetamol 500mg",
                "description" =>
                    "Analgesik dan antipiretik untuk nyeri ringan-sedang dan demam.",
                "composition" => "Paracetamol (Acetaminophen) 500mg",
                "dose" => "3-4 x 1 tablet/hari bila perlu, maks. 4000mg/hari.",
                "side_effects" =>
                    "Aman pada dosis terapeutik. Overdosis berisiko kerusakan hati.",
                "price" => 1200,
                "minimum_stock" => 50,
            ],
            [
                "name" => "Ibuprofen 400mg",
                "description" =>
                    "NSAID untuk nyeri, demam, dan peradangan ringan.",
                "composition" => "Ibuprofen 400mg",
                "dose" => "3 x 1 tablet/hari sesudah makan.",
                "side_effects" => "Nyeri lambung, mual, pusing.",
                "price" => 3000,
                "minimum_stock" => 25,
            ],
            [
                "name" => "Cetirizine 10mg",
                "description" =>
                    "Antihistamin generasi kedua untuk rhinitis alergi dan urtikaria.",
                "composition" => "Cetirizine dihydrochloride 10mg",
                "dose" => "1 x 1 tablet/hari (dianjurkan malam hari).",
                "side_effects" =>
                    "Mengantuk ringan, mulut kering, sakit kepala.",
                "price" => 4000,
                "minimum_stock" => 20,
            ],
            [
                "name" => "Omeprazole 20mg",
                "description" =>
                    "Penghambat pompa proton untuk tukak lambung dan GERD.",
                "composition" => "Omeprazole 20mg",
                "dose" => "1 x 1 kapsul/hari sebelum makan pagi.",
                "side_effects" => "Sakit kepala, mual, diare.",
                "price" => 6500,
                "minimum_stock" => 15,
            ],
            [
                "name" => "Antasida Doen Tablet",
                "description" =>
                    "Antasida kombinasi untuk menetralkan asam lambung dan meredakan nyeri ulu hati.",
                "composition" =>
                    "Aluminium Hydroxide 200mg, Magnesium Hydroxide 200mg",
                "dose" => "3-4 x 1-2 tablet/hari, kunyah 1 jam setelah makan.",
                "side_effects" => "Konstipasi atau diare.",
                "price" => 1500,
                "minimum_stock" => 30,
            ],
        ]);

        // --- Suplemen ---
        $suplemen = collect([
            [
                "name" => "Vitamin C 1000mg",
                "description" =>
                    "Suplemen antioksidan untuk daya tahan tubuh dan penyembuhan luka.",
                "composition" => "Ascorbic Acid (Vitamin C) 1000mg",
                "dose" =>
                    "1 x 1 tablet effervescent/hari dilarutkan dalam air.",
                "side_effects" =>
                    "Mual, diare, batu ginjal pada dosis tinggi berkepanjangan.",
                "price" => 8000,
                "minimum_stock" => 20,
            ],
            [
                "name" => "Vitamin D3 1000 IU",
                "description" =>
                    "Suplemen Vitamin D untuk kesehatan tulang dan imunitas.",
                "composition" => "Cholecalciferol (Vitamin D3) 1000 IU",
                "dose" => "1 x 1 kapsul lunak/hari bersama makanan berlemak.",
                "side_effects" => "Hiperkalsemia pada overdosis.",
                "price" => 12000,
                "minimum_stock" => 15,
            ],
            [
                "name" => "Zinc 10mg",
                "description" =>
                    "Suplemen mineral esensial untuk imunitas dan penyembuhan luka.",
                "composition" => "Zinc Sulfate setara Zinc elemental 10mg",
                "dose" => "1 x 1 tablet/hari bersama makanan.",
                "side_effects" => "Mual jika perut kosong.",
                "price" => 5500,
                "minimum_stock" => 15,
            ],
            [
                "name" => "Vitamin B Complex",
                "description" =>
                    "Suplemen kombinasi vitamin B1, B2, B3, B5, B6, B7, B9, B12 untuk energi dan fungsi saraf.",
                "composition" =>
                    "B1 100mg, B2 10mg, B3 50mg, B5 20mg, B6 10mg, B12 50mcg",
                "dose" => "1 x 1 tablet/hari bersama makanan.",
                "side_effects" =>
                    "Urine berwarna kuning cerah (normal), mual pada perut kosong.",
                "price" => 9500,
                "minimum_stock" => 20,
            ],
            [
                "name" => "Omega-3 Fish Oil 1000mg",
                "description" =>
                    "Suplemen asam lemak omega-3 untuk kesehatan jantung dan fungsi kognitif.",
                "composition" => "Fish oil 1000mg (EPA 180mg, DHA 120mg)",
                "dose" => "1-2 x 1 kapsul lunak/hari bersama makanan.",
                "side_effects" => "Napas dan sendawa berbau ikan, mual ringan.",
                "price" => 18000,
                "minimum_stock" => 10,
            ],
        ]);

        // --- Alat Kesehatan ---
        $alatKesehatan = collect([
            [
                "name" => "Kasa Steril 10x10cm",
                "description" =>
                    "Kasa pembalut luka steril untuk perawatan luka dan P3K.",
                "composition" =>
                    "Kapas murni steril 10x10cm, dikemas individual.",
                "dose" => "Gunakan sesuai kebutuhan. Ganti minimal 1x sehari.",
                "side_effects" =>
                    "Tidak ada. Bersihkan luka sebelum penggantian.",
                "price" => 2500,
                "minimum_stock" => 30,
            ],
            [
                "name" => "Plester Elastis 5cm x 4.5m",
                "description" =>
                    "Perban elastis untuk balutan kompres dan imobilisasi sendi.",
                "composition" =>
                    "Kain rajut elastis dengan pengikat kait pengait.",
                "dose" =>
                    "Lilitkan merata pada area yang membutuhkan penyangga.",
                "side_effects" =>
                    "Pembengkakan atau mati rasa jika terlalu ketat.",
                "price" => 15000,
                "minimum_stock" => 10,
            ],
            [
                "name" => "Masker Medis 3-Ply (50 pcs)",
                "description" =>
                    "Masker bedah tiga lapis untuk perlindungan dasar dari droplet dan debu.",
                "composition" =>
                    "Lapisan PP non-woven, lapisan filter meltblown, lapisan dalam.",
                "dose" =>
                    "Gunakan satu kali pakai, ganti jika basah atau kotor.",
                "side_effects" =>
                    "Tidak ada. Pastikan ukuran sesuai untuk perlindungan optimal.",
                "price" => 35000,
                "minimum_stock" => 10,
            ],
            [
                "name" => "Termometer Digital Aksila",
                "description" =>
                    "Termometer digital untuk pengukuran suhu tubuh melalui ketiak dengan cepat dan akurat.",
                "composition" => "Sensor NTC, layar LCD, baterai LR41.",
                "dose" =>
                    "Jepit di ketiak selama 10 detik hingga bunyi bip. Bersihkan setelah pakai.",
                "side_effects" => "Tidak ada.",
                "price" => 45000,
                "minimum_stock" => 5,
            ],
            [
                "name" => "Syringe 3ml (1 pcs)",
                "description" =>
                    "Jarum suntik sekali pakai steril kapasitas 3ml untuk injeksi dan pengambilan sampel.",
                "composition" =>
                    "Barrel polipropilen, plunger karet, jarum baja 23G.",
                "dose" =>
                    "Gunakan sekali pakai. Buang di tempat benda tajam (sharps container).",
                "side_effects" => "Tidak ada jika digunakan dengan benar.",
                "price" => 3500,
                "minimum_stock" => 20,
            ],
        ]);

        // Fungsi helper untuk membuat obat dari koleksi
        $buatObat = function (
            \Illuminate\Support\Collection $daftarObat,
            KategoriObat $kategori,
            bool $perluResep,
        ) use ($suppliers): \Illuminate\Support\Collection {
            return $daftarObat->map(function (array $data) use (
                $kategori,
                $perluResep,
                $suppliers,
            ): Obat {
                // Pilih supplier acak
                $supplierId = $suppliers->isNotEmpty() ? $suppliers->random()->id : null;
                return Obat::firstOrCreate(
                    ["name" => $data["name"]],
                    array_merge($data, [
                        "kategori_obat_id" => $kategori->id,
                        "supplier_id" => $supplierId,
                        "requires_prescription" => $perluResep,
                    ]),
                );
            });
        };

        $semuaObat = collect()
            ->merge($buatObat($obatResep, $kategoriObatResep, true))
            ->merge($buatObat($obatBebas, $kategoriObatBebas, false))
            ->merge($buatObat($suplemen, $kategoriSuplemen, false))
            ->merge($buatObat($alatKesehatan, $kategoriAlatKesehatan, false));

        $this->command->info(
            "  ✓ {$semuaObat->count()} produk obat berhasil disemai.",
        );

        // ------------------------------------------------------------------
        // LANGKAH 4: Batch Stok Per Obat (Variasi Kadaluarsa untuk Uji FIFO)
        // ------------------------------------------------------------------
        $this->command->info(
            "  → Menyemai batch stok dengan variasi kadaluarsa...",
        );

        $totalBatch = 0;

        foreach ($semuaObat as $obat) {
            // Setiap obat mendapatkan 5 batch dengan profil kadaluarsa berbeda:
            $batchConfig = [
                // [state, label]
                ["kadaluarsa", "sudah kadaluarsa (untuk alert dasbor)"],
                [
                    "hampirKadaluarsa30",
                    "kadaluarsa dalam 30 hari (prioritas jual FIFO)",
                ],
                ["hampirKadaluarsa60", "kadaluarsa dalam 60 hari"],
                ["hampirKadaluarsa90", "kadaluarsa dalam 90 hari"],
                ["stokPenuh", "stok penuh (kadaluarsa normal, > 6 bulan)"],
            ];

            foreach ($batchConfig as [$state, $label]) {
                BatchStok::factory()->for($obat)->$state()->create();
                $totalBatch++;
            }
        }

        $this->command->info(
            "  ✓ {$totalBatch} batch stok berhasil disemai ({$semuaObat->count()} obat × 5 batch).",
        );

        // ------------------------------------------------------------------
        // LANGKAH 5: Transaksi & Detail Transaksi (Untuk grafik dasbor & data)
        // ------------------------------------------------------------------
        $this->command->info("  → Menyemai data transaksi & detail...");

        $pasienUsers = User::where('role', RolePengguna::Pasien)->get();
        if ($pasienUsers->isEmpty()) {
            $pasienUsers = collect([User::where('email', 'pasien@makmurjaya.id')->first()]);
        }

        $jumlahTransaksiPerMinggu = [
            3 => 8,  // 3 minggu lalu: 8 transaksi
            2 => 14, // 2 minggu lalu: 14 transaksi
            1 => 22, // 1 minggu lalu: 22 transaksi
            0 => 30, // Minggu ini: 30 transaksi
        ];

        $totalTransaksiDibuat = 0;
        $totalDetailDibuat = 0;

        foreach ($jumlahTransaksiPerMinggu as $mingguLalu => $jumlah) {
            for ($j = 0; $j < $jumlah; $j++) {
                $isOnline = (bool)rand(0, 1);
                $status = StatusTransaksi::Selesai;

                // Variasikan status transaksi untuk minggu ini
                if ($mingguLalu === 0 && $j < 8) {
                    $statusOptions = [
                        StatusTransaksi::MenungguVerifikasi,
                        StatusTransaksi::MenungguPembayaran,
                        StatusTransaksi::Diproses,
                        StatusTransaksi::Dibatalkan
                    ];
                    $status = $statusOptions[array_rand($statusOptions)];
                }

                // Tentukan tanggal transaksi acak pada minggu tersebut
                $tanggalTransaksi = now()->subWeeks($mingguLalu)->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                // Ambil pasien acak
                $pasien = $pasienUsers->random();

                // Invoice format: INV-YYYYMMDD-XXXX
                $invoiceNumber = 'INV-' . $tanggalTransaksi->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Buat transaksi
                $transaksi = Transaksi::create([
                    "invoice_number" => $invoiceNumber,
                    "user_id" => $pasien->id,
                    "total_price" => 0, // Akan dihitung setelah detail dibuat
                    "status" => $status,
                    "is_online" => $isOnline,
                    "resep_path" => ($isOnline && rand(0, 1)) ? "resep/dummy_resep.pdf" : null,
                    "created_at" => $tanggalTransaksi,
                    "updated_at" => $tanggalTransaksi,
                ]);

                // Buat 1 sampai 4 item detail transaksi
                $totalPrice = 0;
                $itemCount = rand(1, 4);
                $selectedObats = $semuaObat->random($itemCount);

                foreach ($selectedObats as $obat) {
                    // Cari batch stok yang valid untuk obat ini (paling tidak yang status stokPenuh atau hampirKadaluarsa)
                    $batch = $obat->batchStoks()->where('current_stock', '>', 0)->first();
                    if (!$batch) {
                        $batch = $obat->batchStoks()->first();
                    }

                    $qty = rand(1, 5);
                    $unitPrice = $obat->price;
                    $subtotal = $qty * $unitPrice;
                    $totalPrice += $subtotal;

                    DetailTransaksi::create([
                        "transaksi_id" => $transaksi->id,
                        "obat_id" => $obat->id,
                        "batch_stok_id" => $batch ? $batch->id : null,
                        "quantity" => $qty,
                        "unit_price" => $unitPrice,
                        "created_at" => $tanggalTransaksi,
                        "updated_at" => $tanggalTransaksi,
                    ]);

                    // Jika transaksi selesai, potong stok batch
                    if ($status === StatusTransaksi::Selesai && $batch && $batch->current_stock >= $qty) {
                        $batch->decrement('current_stock', $qty);
                    }

                    $totalDetailDibuat++;
                }

                // Update total_price di transaksi
                $transaksi->update(['total_price' => $totalPrice]);
                $totalTransaksiDibuat++;
            }
        }

        $this->command->info(
            "  ✓ {$totalTransaksiDibuat} transaksi & {$totalDetailDibuat} detail transaksi berhasil disemai.",
        );

        // ------------------------------------------------------------------
        // LANGKAH 6: Audit Log (Log Aktivitas)
        // ------------------------------------------------------------------
        $this->command->info("  → Menyemai data log aktivitas...");

        $adminUser = User::where('role', RolePengguna::Admin)->first();
        $apotekerUser = User::where('role', RolePengguna::Apoteker)->first();
        $kasirUser = User::where('role', RolePengguna::Kasir)->first();

        $auditLogs = [
            [
                'user_id' => $adminUser->id,
                'action' => 'create',
                'model' => 'Supplier',
                'changes' => ['name' => 'PT. Kimia Farma Trading & Distribution'],
                'created_at' => now()->subDays(10),
            ],
            [
                'user_id' => $adminUser->id,
                'action' => 'create',
                'model' => 'Obat',
                'changes' => ['name' => 'Amoxicillin 500mg', 'price' => 4500],
                'created_at' => now()->subDays(9),
            ],
            [
                'user_id' => $apotekerUser->id,
                'action' => 'create',
                'model' => 'BatchStok',
                'changes' => ['batch_number' => 'BT-KF-102026-001', 'initial_stock' => 150],
                'created_at' => now()->subDays(8),
            ],
            [
                'user_id' => $kasirUser->id,
                'action' => 'create',
                'model' => 'Transaksi',
                'changes' => ['invoice_number' => 'INV-20260601-0001', 'total_price' => 35000],
                'created_at' => now()->subDays(5),
            ],
            [
                'user_id' => $apotekerUser->id,
                'action' => 'verify',
                'model' => 'Transaksi',
                'changes' => ['invoice_number' => 'INV-20260602-0042', 'status' => 'menunggu_pembayaran'],
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $adminUser->id,
                'action' => 'update',
                'model' => 'Obat',
                'changes' => ['name' => 'Paracetamol 500mg', 'price' => ['old' => 1000, 'new' => 1200]],
                'created_at' => now()->subHours(12),
            ],
        ];

        foreach ($auditLogs as $log) {
            AuditLog::create(array_merge($log, [
                'updated_at' => $log['created_at'],
            ]));
        }

        $this->command->info("  ✓ Data log aktivitas berhasil disemai.");

        // ------------------------------------------------------------------
        // RINGKASAN AKHIR & KREDENSIAL DEMO
        // ------------------------------------------------------------------
        $this->command->info("");
        $this->command->info(
            "╔══════════════════════════════════════════════════════╗",
        );
        $this->command->info(
            "║            KREDENSIAL AKUN DEMO                     ║",
        );
        $this->command->info(
            "╠══════════════════════════════════════════════════════╣",
        );
        $this->command->info(
            "║  Admin    : admin@makmurjaya.id    / Admin@123       ║",
        );
        $this->command->info(
            "║  Apoteker : apoteker@makmurjaya.id / Apoteker@123   ║",
        );
        $this->command->info(
            "║  Kasir    : kasir@makmurjaya.id    / Kasir@123       ║",
        );
        $this->command->info(
            "║  Pasien   : pasien@makmurjaya.id   / Pasien@123      ║",
        );
        $this->command->info(
            "╚══════════════════════════════════════════════════════╝",
        );
        $this->command->info("");
    }
}

