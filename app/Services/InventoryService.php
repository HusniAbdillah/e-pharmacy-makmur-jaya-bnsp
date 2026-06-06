<?php

namespace App\Services;

use App\Models\BatchStok;
use App\Models\DetailTransaksi;
use App\Models\Obat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Layanan Inti Inventori — Mesin FIFO (First-Expired, First-Out).
 *
 * Prinsip FIFO di sini menggunakan urutan TANGGAL KADALUARSA TERDEKAT,
 * bukan tanggal masuk. Ini adalah standar farmasi (FEFO: First-Expired, First-Out)
 * yang memastikan obat yang paling dekat kadaluarsanya dijual terlebih dahulu,
 * meminimalkan kerugian akibat obat kadaluarsa.
 *
 * Semua operasi pengurangan stok WAJIB melalui service ini — tidak boleh
 * dilakukan langsung dari Controller atau Livewire component.
 */
class InventoryService
{
    // =========================================================================
    // OPERASI UTAMA: PENGURANGAN STOK
    // =========================================================================

    /**
     * Potong stok obat menggunakan logika FIFO (kadaluarsa terdekat dahulu).
     *
     * Fungsi ini menggunakan pessimistic locking (SELECT FOR UPDATE) untuk
     * mencegah race condition pada pembelian paralel. Seluruh operasi dibungkus
     * dalam transaksi database untuk menjamin atomisitas.
     *
     * Jika kuantitas yang dibutuhkan tersebar di lebih dari satu batch,
     * fungsi ini akan memotong secara berurutan dari batch paling awal
     * kadaluarsanya hingga total terpenuhi.
     *
     * @param  int      $obatId          ID obat yang akan dipotong stoknya
     * @param  int      $quantityToDeduct Total unit yang harus dipotong
     * @param  int|null $detailTransaksiId ID baris detail transaksi yang akan
     *                                    diperbarui dengan batch_stok_id utama
     * @throws \Exception Jika stok tidak mencukupi atau obat tidak ditemukan
     */
    public function deductStockFIFO(
        int $obatId,
        int $quantityToDeduct,
        ?int $detailTransaksiId = null
    ): void {
        if ($quantityToDeduct <= 0) {
            throw new \InvalidArgumentException(
                "Jumlah yang akan dipotong harus lebih dari 0. Nilai diterima: {$quantityToDeduct}."
            );
        }

        DB::transaction(function () use ($obatId, $quantityToDeduct, $detailTransaksiId): void {
            // -------------------------------------------------------------------
            // KUNCI BATCH: Ambil batch yang tersedia, kunci baris untuk
            // mencegah pembaruan bersamaan (race condition).
            // -------------------------------------------------------------------
            $batches = BatchStok::where('obat_id', $obatId)
                ->where('current_stock', '>', 0)
                ->where('expiration_date', '>', now()->toDateString())
                ->orderBy('expiration_date', 'asc')   // FIFO: kadaluarsa terdekat didahulukan
                ->lockForUpdate()                       // Pessimistic lock selama transaksi
                ->get();

            // -------------------------------------------------------------------
            // VALIDASI KECUKUPAN STOK
            // -------------------------------------------------------------------
            $totalTersedia = $batches->sum('current_stock');

            if ($totalTersedia < $quantityToDeduct) {
                $namaObat = Obat::find($obatId)?->name ?? "ID #{$obatId}";

                throw new \Exception(
                    "Stok tidak mencukupi untuk obat '{$namaObat}'. " .
                    "Stok aktif tersedia: {$totalTersedia} unit, " .
                    "jumlah yang dibutuhkan: {$quantityToDeduct} unit."
                );
            }

            // -------------------------------------------------------------------
            // EKSEKUSI PEMOTONGAN FIFO
            // -------------------------------------------------------------------
            $sisaPotongan     = $quantityToDeduct;
            $batchPertamaUsed = null; // Batch pertama yang dipakai (untuk referensi DetailTransaksi)

            foreach ($batches as $batch) {
                if ($sisaPotongan <= 0) {
                    break; // Semua stok sudah terpenuhi, hentikan loop
                }

                // Tentukan berapa unit yang diambil dari batch ini
                $potonganDariBatch = min($batch->current_stock, $sisaPotongan);

                // Kurangi stok batch menggunakan decrement (atomic di level DB)
                $batch->decrement('current_stock', $potonganDariBatch);

                Log::info(
                    "[FIFO] Potong stok: obat_id={$obatId} | " .
                    "batch='{$batch->batch_number}' | " .
                    "kadaluarsa={$batch->expiration_date->format('d/m/Y')} | " .
                    "dipotong={$potonganDariBatch} | " .
                    "sisa_setelah=" . ($batch->current_stock - $potonganDariBatch)
                );

                // Catat batch pertama yang digunakan
                if ($batchPertamaUsed === null) {
                    $batchPertamaUsed = $batch;
                }

                $sisaPotongan -= $potonganDariBatch;
            }

            // -------------------------------------------------------------------
            // PERBARUI REFERENSI BATCH PADA DETAIL TRANSAKSI (JIKA DIBERIKAN)
            // Catatan: Jika pemotongan tersebar di lebih dari satu batch,
            // batch_stok_id diisi dengan batch pertama yang dipakai.
            // Untuk pelacakan multi-batch yang lengkap, gunakan hitungAlokasiStokFIFO()
            // dan buat baris DetailTransaksi terpisah per batch.
            // -------------------------------------------------------------------
            if ($detailTransaksiId !== null && $batchPertamaUsed !== null) {
                DetailTransaksi::where('id', $detailTransaksiId)
                    ->update(['batch_stok_id' => $batchPertamaUsed->id]);
            }

            Log::info(
                "[FIFO] Pemotongan selesai: obat_id={$obatId} | " .
                "total_dipotong={$quantityToDeduct} | " .
                "detail_transaksi_id=" . ($detailTransaksiId ?? 'N/A')
            );
        });
    }

    // =========================================================================
    // KALKULASI ALOKASI (TANPA MENGUBAH DATA)
    // =========================================================================

    /**
     * Hitung rencana alokasi stok FIFO tanpa melakukan pengurangan apapun.
     *
     * Gunakan fungsi ini SEBELUM membuat baris DetailTransaksi untuk mengetahui
     * batch mana saja yang akan digunakan dan berapa unit dari masing-masing batch.
     * Hasilnya digunakan untuk membuat baris DetailTransaksi yang akurat (satu baris
     * per batch) sebelum Job pengurangan stok dijalankan.
     *
     * @param  int $obatId          ID obat yang akan dialokasikan
     * @param  int $quantityNeeded  Total unit yang dibutuhkan
     * @return Collection<int, array{batch: BatchStok, quantity: int}>
     * @throws \Exception Jika stok tidak mencukupi
     */
    public function hitungAlokasiStokFIFO(int $obatId, int $quantityNeeded): Collection
    {
        if ($quantityNeeded <= 0) {
            throw new \InvalidArgumentException(
                "Jumlah yang diperlukan harus lebih dari 0. Nilai diterima: {$quantityNeeded}."
            );
        }

        $batches = BatchStok::where('obat_id', $obatId)
            ->where('current_stock', '>', 0)
            ->where('expiration_date', '>', now()->toDateString())
            ->orderBy('expiration_date', 'asc')
            ->get();

        $totalTersedia = $batches->sum('current_stock');

        if ($totalTersedia < $quantityNeeded) {
            $namaObat = Obat::find($obatId)?->name ?? "ID #{$obatId}";

            throw new \Exception(
                "Stok tidak mencukupi untuk obat '{$namaObat}'. " .
                "Stok aktif tersedia: {$totalTersedia} unit, " .
                "jumlah yang dibutuhkan: {$quantityNeeded} unit."
            );
        }

        $alokasi      = collect();
        $sisaDipenuhi = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($sisaDipenuhi <= 0) {
                break;
            }

            $jumlahDariBatch = min($batch->current_stock, $sisaDipenuhi);

            $alokasi->push([
                'batch'    => $batch,
                'quantity' => $jumlahDariBatch,
            ]);

            $sisaDipenuhi -= $jumlahDariBatch;
        }

        return $alokasi;
    }

    // =========================================================================
    // PENGECEKAN STOK
    // =========================================================================

    /**
     * Dapatkan total stok aktif (belum kadaluarsa) untuk satu obat.
     *
     * @param  int $obatId ID obat yang akan dicek
     * @return int Total unit stok yang tersedia dan belum kadaluarsa
     */
    public function getTotalStokTersedia(int $obatId): int
    {
        return (int) BatchStok::where('obat_id', $obatId)
            ->where('current_stock', '>', 0)
            ->where('expiration_date', '>', now()->toDateString())
            ->sum('current_stock');
    }

    /**
     * Cek apakah stok obat mencukupi untuk memenuhi jumlah yang diminta.
     * Digunakan untuk validasi cepat sebelum proses checkout, tanpa mengunci DB.
     *
     * @param  int $obatId   ID obat yang akan dicek
     * @param  int $quantity Jumlah unit yang dibutuhkan
     * @return bool true jika stok mencukupi, false jika tidak
     */
    public function cekKecukupanStok(int $obatId, int $quantity): bool
    {
        return $this->getTotalStokTersedia($obatId) >= $quantity;
    }

    /**
     * Kembalikan daftar obat yang stok aktifnya di bawah atau sama dengan
     * batas minimum yang telah ditetapkan. Digunakan untuk widget dasbor
     * apoteker dan admin.
     *
     * @return Collection<int, Obat> Koleksi obat dengan status stok kritis
     */
    public function getObatStokKritis(): Collection
    {
        // Ambil semua obat beserta jumlah stok aktifnya saat ini
        return Obat::withSum(
            ['batchStoks as total_stok_aktif' => function ($query): void {
                $query->where('current_stock', '>', 0)
                      ->where('expiration_date', '>', now()->toDateString());
            }],
            'current_stock'
        )
        ->whereRaw('(SELECT COALESCE(SUM(bs.current_stock), 0) FROM batch_stoks bs WHERE bs.obat_id = obats.id AND bs.current_stock > 0 AND bs.expiration_date > ?) <= obats.minimum_stock', [now()->toDateString()])
        ->with('kategoriObat')
        ->get();
    }

    /**
     * Kembalikan daftar batch yang sudah kadaluarsa namun masih memiliki stok
     * (belum dimusnahkan). Digunakan untuk laporan dan peringatan apoteker.
     *
     * @return Collection<int, BatchStok> Koleksi batch kadaluarsa dengan sisa stok
     */
    public function getBatchKadaluarsaBersisa(): Collection
    {
        return BatchStok::where('expiration_date', '<', now()->toDateString())
            ->where('current_stock', '>', 0)
            ->with('obat.kategoriObat')
            ->orderBy('expiration_date', 'asc')
            ->get();
    }

    /**
     * Kembalikan daftar batch yang akan kadaluarsa dalam N hari ke depan.
     * Default 30 hari sesuai standar pemantauan farmasi.
     *
     * @param  int $hari Jumlah hari ke depan untuk pemantauan (default: 30)
     * @return Collection<int, BatchStok>
     */
    public function getBatchHampirKadaluarsa(int $hari = 30): Collection
    {
        return BatchStok::whereBetween('expiration_date', [
                now()->toDateString(),
                now()->addDays($hari)->toDateString(),
            ])
            ->where('current_stock', '>', 0)
            ->with('obat.kategoriObat')
            ->orderBy('expiration_date', 'asc')
            ->get();
    }
}
