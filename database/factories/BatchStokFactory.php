<?php

namespace Database\Factories;

use App\Models\BatchStok;
use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat data dummy batch stok obat.
 * Menyediakan berbagai state untuk menguji semua skenario FIFO:
 *   - Stok normal (tersedia)
 *   - Mendekati kadaluarsa (30, 60, 90 hari)
 *   - Sudah kadaluarsa
 *   - Stok habis
 *
 * @extends Factory<BatchStok>
 */
class BatchStokFactory extends Factory
{
    /**
     * Definisi state default: batch aktif dengan stok normal dan kadaluarsa jauh.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stokAwal = fake()->numberBetween(50, 200);

        return [
            'obat_id'         => Obat::inRandomOrder()->first()?->id ?? Obat::factory(),
            // Format kode batch: BT-{2 huruf supplier}-{Tahun}{Bulan}-{3 digit urutan}
            'batch_number'    => strtoupper(fake()->unique()->bothify('BT-??-######-###')),
            'initial_stock'   => $stokAwal,
            'current_stock'   => fake()->numberBetween((int) ($stokAwal * 0.5), $stokAwal),
            'expiration_date' => fake()->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
        ];
    }

    // =========================================================================
    // STATES — untuk menguji semua skenario FIFO dan monitoring dasbor
    // =========================================================================

    /**
     * State: batch dengan stok penuh dan kadaluarsa normal (6 bulan - 2 tahun).
     * Skenario: batch baru dari supplier, belum banyak terjual.
     */
    public function stokPenuh(): static
    {
        return $this->state(function (array $attributes) {
            $stokAwal = fake()->numberBetween(100, 300);
            return [
                'initial_stock'   => $stokAwal,
                'current_stock'   => $stokAwal, // Belum ada yang terjual
                'expiration_date' => fake()->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: batch yang akan kadaluarsa dalam 30 hari ke depan.
     * Skenario: WASPADA — harus dijual sebelum kadaluarsa.
     * Dalam FIFO, batch ini diprioritaskan penjualannya.
     */
    public function hampirKadaluarsa30(): static
    {
        return $this->state(function (array $attributes) {
            $stokAwal = fake()->numberBetween(30, 100);
            return [
                'initial_stock'   => $stokAwal,
                'current_stock'   => fake()->numberBetween(10, $stokAwal),
                'expiration_date' => fake()->dateTimeBetween('+1 days', '+30 days')->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: batch yang akan kadaluarsa dalam 31–60 hari ke depan.
     * Skenario: PERHATIKAN — mulai masuk periode pemantauan.
     */
    public function hampirKadaluarsa60(): static
    {
        return $this->state(function (array $attributes) {
            $stokAwal = fake()->numberBetween(50, 150);
            return [
                'initial_stock'   => $stokAwal,
                'current_stock'   => fake()->numberBetween(20, $stokAwal),
                'expiration_date' => fake()->dateTimeBetween('+31 days', '+60 days')->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: batch yang akan kadaluarsa dalam 61–90 hari ke depan.
     * Skenario: Masih aman, namun perlu dipantau.
     */
    public function hampirKadaluarsa90(): static
    {
        return $this->state(function (array $attributes) {
            $stokAwal = fake()->numberBetween(50, 200);
            return [
                'initial_stock'   => $stokAwal,
                'current_stock'   => fake()->numberBetween(25, $stokAwal),
                'expiration_date' => fake()->dateTimeBetween('+61 days', '+90 days')->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: batch yang sudah melewati tanggal kadaluarsa.
     * Skenario: BAHAYA — tidak boleh dijual, harus dikarantina atau dimusnahkan.
     * Digunakan untuk menguji peringatan di dasbor apoteker.
     */
    public function kadaluarsa(): static
    {
        return $this->state(function (array $attributes) {
            $stokAwal = fake()->numberBetween(10, 50);
            return [
                'initial_stock'   => $stokAwal,
                'current_stock'   => fake()->numberBetween(1, $stokAwal), // Masih ada sisa yang tidak terjual
                'expiration_date' => fake()->dateTimeBetween('-6 months', '-1 days')->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: batch dengan stok yang sudah habis (terjual semua atau dibuang).
     * Skenario: Riwayat batch lama, current_stock = 0.
     */
    public function stokHabis(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock'   => 0,
            'expiration_date' => fake()->dateTimeBetween('+1 months', '+18 months')->format('Y-m-d'),
        ]);
    }
}
