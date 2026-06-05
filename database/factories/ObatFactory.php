<?php

namespace Database\Factories;

use App\Models\KategoriObat;
use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat data dummy produk obat yang realistis.
 *
 * @extends Factory<Obat>
 */
class ObatFactory extends Factory
{
    /**
     * Kumpulan data obat realistis dengan informasi farmakologi dalam Bahasa Indonesia.
     * Digunakan sebagai sumber data acak agar data dummy tetap bermakna.
     *
     * @var array<int, array<string, mixed>>
     */
    private static array $katalogObat = [
        [
            'name'         => 'Amoxicillin 500mg',
            'description'  => 'Antibiotik golongan penisilin untuk mengobati infeksi bakteri pada saluran napas, telinga, dan saluran kemih.',
            'composition'  => 'Amoxicillin trihydrate setara Amoxicillin 500mg',
            'dose'         => '3 x 1 kapsul per hari (setiap 8 jam), habiskan seluruh seri antibiotik.',
            'side_effects' => 'Mual, diare, ruam kulit, dan kemungkinan reaksi alergi parah (anafilaksis).',
            'price'        => 4500,
            'resep'        => true,
        ],
        [
            'name'         => 'Paracetamol 500mg',
            'description'  => 'Analgesik dan antipiretik untuk meredakan nyeri ringan hingga sedang serta menurunkan demam.',
            'composition'  => 'Paracetamol (Acetaminophen) 500mg',
            'dose'         => '3-4 x 1 tablet per hari bila perlu, jangan melebihi 4000mg/hari.',
            'side_effects' => 'Umumnya aman pada dosis terapeutik. Dosis berlebih berisiko kerusakan hati.',
            'price'        => 1200,
            'resep'        => false,
        ],
        [
            'name'         => 'Ibuprofen 400mg',
            'description'  => 'Antiinflamasi nonsteroid (NSAID) untuk nyeri, demam, dan peradangan ringan.',
            'composition'  => 'Ibuprofen 400mg',
            'dose'         => '3 x 1 tablet per hari sesudah makan. Jangan dikonsumsi saat perut kosong.',
            'side_effects' => 'Nyeri lambung, mual, pusing. Hindari pada pasien maag atau gangguan ginjal.',
            'price'        => 3000,
            'resep'        => false,
        ],
        [
            'name'         => 'Metformin 500mg',
            'description'  => 'Antidiabetik oral golongan biguanida untuk mengontrol kadar gula darah pada diabetes tipe 2.',
            'composition'  => 'Metformin HCl 500mg',
            'dose'         => '2-3 x 1 tablet per hari bersama makanan. Dosis dapat ditingkatkan bertahap.',
            'side_effects' => 'Mual, muntah, diare (terutama pada awal terapi), asidosis laktat (jarang).',
            'price'        => 3800,
            'resep'        => true,
        ],
        [
            'name'         => 'Amlodipine 5mg',
            'description'  => 'Antagonis kalsium untuk mengobati hipertensi dan angina pektoris stabil.',
            'composition'  => 'Amlodipine besylate setara Amlodipine 5mg',
            'dose'         => '1 x 1 tablet per hari, konsumsi pada waktu yang sama setiap hari.',
            'side_effects' => 'Pembengkakan pergelangan kaki, pusing, kemerahan pada wajah, jantung berdebar.',
            'price'        => 5200,
            'resep'        => true,
        ],
        [
            'name'         => 'Cetirizine 10mg',
            'description'  => 'Antihistamin generasi kedua untuk rhinitis alergi dan urtikaria (biduran).',
            'composition'  => 'Cetirizine dihydrochloride 10mg',
            'dose'         => '1 x 1 tablet per hari (malam hari dianjurkan untuk mengurangi efek kantuk).',
            'side_effects' => 'Mengantuk ringan, mulut kering, sakit kepala.',
            'price'        => 4000,
            'resep'        => false,
        ],
        [
            'name'         => 'Omeprazole 20mg',
            'description'  => 'Penghambat pompa proton untuk tukak lambung, GERD, dan sindrom Zollinger-Ellison.',
            'composition'  => 'Omeprazole 20mg',
            'dose'         => '1 x 1 kapsul per hari sebelum makan pagi. Telan utuh, jangan dikunyah.',
            'side_effects' => 'Sakit kepala, mual, diare, nyeri perut. Penggunaan jangka panjang dapat memengaruhi kadar magnesium.',
            'price'        => 6500,
            'resep'        => false,
        ],
        [
            'name'         => 'Simvastatin 20mg',
            'description'  => 'Statin untuk menurunkan kadar kolesterol LDL dan trigliserida dalam darah.',
            'composition'  => 'Simvastatin 20mg',
            'dose'         => '1 x 1 tablet per hari pada malam hari.',
            'side_effects' => 'Nyeri otot (mialgia), peningkatan enzim hati. Segera laporkan nyeri otot hebat.',
            'price'        => 7000,
            'resep'        => true,
        ],
        [
            'name'         => 'Loratadine 10mg',
            'description'  => 'Antihistamin non-sedatif untuk mengatasi gejala alergi musiman dan pilek alergi.',
            'composition'  => 'Loratadine 10mg',
            'dose'         => '1 x 1 tablet per hari.',
            'side_effects' => 'Sakit kepala, mulut kering. Jarang menyebabkan kantuk.',
            'price'        => 3500,
            'resep'        => false,
        ],
        [
            'name'         => 'Dexamethasone 0,5mg',
            'description'  => 'Kortikosteroid untuk peradangan, reaksi alergi berat, dan kondisi autoimun.',
            'composition'  => 'Dexamethasone 0,5mg',
            'dose'         => 'Sesuai petunjuk dokter. Jangan menghentikan terapi secara tiba-tiba.',
            'side_effects' => 'Peningkatan nafsu makan, retensi cairan, peningkatan gula darah, supresi imun.',
            'price'        => 2800,
            'resep'        => true,
        ],
        [
            'name'         => 'Vitamin C 1000mg',
            'description'  => 'Suplemen antioksidan untuk meningkatkan daya tahan tubuh dan membantu penyembuhan luka.',
            'composition'  => 'Ascorbic Acid (Vitamin C) 1000mg',
            'dose'         => '1 x 1 tablet effervescent per hari dilarutkan dalam air.',
            'side_effects' => 'Mual, diare, dan batu ginjal pada konsumsi dosis tinggi berkepanjangan.',
            'price'        => 8000,
            'resep'        => false,
        ],
        [
            'name'         => 'Vitamin D3 1000 IU',
            'description'  => 'Suplemen untuk mencegah dan mengatasi defisiensi Vitamin D, mendukung kesehatan tulang dan imunitas.',
            'composition'  => 'Cholecalciferol (Vitamin D3) 1000 IU',
            'dose'         => '1 x 1 kapsul lunak per hari bersama makanan berlemak untuk penyerapan optimal.',
            'side_effects' => 'Dosis berlebihan dapat menyebabkan hiperkalsemia (kadar kalsium darah tinggi).',
            'price'        => 12000,
            'resep'        => false,
        ],
        [
            'name'         => 'Zinc 10mg',
            'description'  => 'Suplemen mineral esensial untuk imunitas, penyembuhan luka, dan pertumbuhan.',
            'composition'  => 'Zinc Sulfate setara Zinc elemental 10mg',
            'dose'         => '1 x 1 tablet per hari bersama makanan.',
            'side_effects' => 'Mual jika dikonsumsi saat perut kosong. Dosis tinggi dapat mengganggu penyerapan tembaga.',
            'price'        => 5500,
            'resep'        => false,
        ],
        [
            'name'         => 'Antasida Doen Tablet',
            'description'  => 'Antasida kombinasi untuk menetralkan asam lambung dan meredakan nyeri ulu hati.',
            'composition'  => 'Aluminium Hydroxide 200mg, Magnesium Hydroxide 200mg',
            'dose'         => '3-4 x 1-2 tablet per hari, kunyah sebelum ditelan, 1 jam setelah makan.',
            'side_effects' => 'Konstipasi (dari Al-hydroxide) atau diare (dari Mg-hydroxide).',
            'price'        => 1500,
            'resep'        => false,
        ],
        [
            'name'         => 'Kasa Steril 10x10cm',
            'description'  => 'Kasa pembalut luka steril untuk perawatan luka dan pertolongan pertama.',
            'composition'  => 'Kapas murni steril berukuran 10x10cm, dikemas secara individual.',
            'dose'         => 'Gunakan sesuai kebutuhan perawatan luka. Ganti minimal 1x sehari.',
            'side_effects' => 'Tidak ada efek samping. Pastikan luka dibersihkan sebelum penggantian.',
            'price'        => 2500,
            'resep'        => false,
        ],
    ];

    /**
     * Definisi state default: obat bebas dengan harga dan deskripsi acak.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil satu data obat secara acak dari katalog
        $data = fake()->randomElement(self::$katalogObat);

        return [
            'kategori_obat_id'      => KategoriObat::inRandomOrder()->first()?->id
                                        ?? KategoriObat::factory(),
            'name'                  => $data['name'],
            'description'           => $data['description'],
            'composition'           => $data['composition'],
            'dose'                  => $data['dose'],
            'side_effects'          => $data['side_effects'],
            'price'                 => $data['price'],
            'minimum_stock'         => fake()->numberBetween(5, 20),
            'requires_prescription' => $data['resep'],
        ];
    }

    /**
     * State: obat yang membutuhkan resep dokter.
     */
    public function butuhResep(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_prescription' => true,
        ]);
    }

    /**
     * State: obat bebas tanpa perlu resep dokter.
     */
    public function bebasResep(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_prescription' => false,
        ]);
    }

    /**
     * State: obat dengan harga premium (suplemen mahal, alat kesehatan).
     */
    public function hargaPremium(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->numberBetween(50000, 500000),
        ]);
    }
}
