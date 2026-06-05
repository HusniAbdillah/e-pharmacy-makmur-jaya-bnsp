<?php

namespace App\Jobs;

use App\Enums\StatusTransaksi;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Services\InventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job: Pemrosesan Pengurangan Stok Secara Asinkron via Antrian.
 *
 * Job ini memastikan bahwa pembelian dari banyak pengguna secara bersamaan
 * tidak menyebabkan kemacetan (bottleneck) pada proses utama. Ketika seorang
 * pasien checkout, transaksi dicatat dengan status 'menunggu', lalu Job ini
 * didispatch ke antrian (database queue) untuk memproses pemotongan stok
 * secara FIFO di latar belakang.
 *
 * Alur proses:
 *   1. Transaksi dibuat dengan status 'menunggu'
 *   2. ProcessBatchStockUpdate::dispatch($transaksi->id) dipanggil
 *   3. Worker antrian menjalankan Job ini secara asinkron
 *   4. Stok dipotong via InventoryService::deductStockFIFO()
 *   5. Status transaksi diperbarui ke 'diproses'
 *   6. Jika gagal: status diperbarui ke 'dibatalkan', error dicatat ke Log
 *
 * Konfigurasi antrian: Pastikan .env berisi QUEUE_CONNECTION=database
 * dan jalankan worker dengan: php artisan queue:work
 */
class ProcessBatchStockUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah maksimal percobaan ulang jika Job gagal.
     * Setelah 3 kali gagal, Job dipindahkan ke tabel failed_jobs.
     */
    public int $tries = 3;

    /**
     * Jeda (detik) sebelum percobaan ulang dilakukan (backoff eksponensial).
     * Percobaan ke-1: 10 detik, ke-2: 30 detik, ke-3: 60 detik.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Batas waktu eksekusi maksimal Job (detik).
     * Transaksi besar dengan banyak item harus selesai dalam 120 detik.
     */
    public int $timeout = 120;

    /**
     * Inisialisasi Job dengan ID transaksi yang akan diproses.
     *
     * @param  int $transaksiId ID transaksi yang stoknya akan dipotong
     */
    public function __construct(
        public readonly int $transaksiId
    ) {}

    /**
     * Jalankan logika utama Job: potong stok untuk setiap item transaksi.
     *
     * Menggunakan DB::transaction untuk memastikan seluruh pemotongan stok
     * bersifat atomik — jika salah satu item gagal, semua perubahan dibatalkan
     * dan transaksi dikembalikan ke status semula.
     *
     * @param  InventoryService $inventoryService Layanan inventori (auto-injected oleh container)
     */
    public function handle(InventoryService $inventoryService): void
    {
        Log::info("[JOB] Mulai memproses stok untuk transaksi #{$this->transaksiId}.");

        // Muat transaksi beserta semua item-nya
        $transaksi = Transaksi::with(['detailTransaksis.obat'])
            ->findOrFail($this->transaksiId);

        // Pastikan transaksi masih dalam status 'menunggu' sebelum diproses.
        // Cegah double-processing jika Job dijalankan lebih dari sekali.
        if ($transaksi->status !== StatusTransaksi::Menunggu) {
            Log::warning(
                "[JOB] Transaksi #{$this->transaksiId} dilewati. " .
                "Status saat ini: '{$transaksi->status->label()}' (bukan 'menunggu')."
            );
            return;
        }

        // Bungkus seluruh operasi dalam satu transaksi database untuk atomisitas
        DB::transaction(function () use ($transaksi, $inventoryService): void {
            /** @var DetailTransaksi $detail */
            foreach ($transaksi->detailTransaksis as $detail) {
                // Validasi awal sebelum mengunci baris di DB (non-blocking check)
                if (! $inventoryService->cekKecukupanStok($detail->obat_id, $detail->quantity)) {
                    $namaObat = $detail->obat->name ?? "ID #{$detail->obat_id}";

                    throw new \Exception(
                        "Transaksi #{$this->transaksiId} gagal: stok tidak mencukupi " .
                        "untuk obat '{$namaObat}' (dibutuhkan: {$detail->quantity} unit)."
                    );
                }

                // Jalankan pemotongan stok FIFO dan perbarui batch_stok_id di baris detail
                $inventoryService->deductStockFIFO(
                    obatId:           $detail->obat_id,
                    quantityToDeduct: $detail->quantity,
                    detailTransaksiId: $detail->id
                );
            }

            // Semua stok berhasil dipotong — perbarui status transaksi
            $transaksi->update(['status' => StatusTransaksi::Diproses]);

            Log::info(
                "[JOB] Transaksi #{$this->transaksiId} berhasil diproses. " .
                "Status diperbarui ke 'diproses'. " .
                "Jumlah item: {$transaksi->detailTransaksis->count()}."
            );
        });
    }

    /**
     * Tangani kegagalan Job setelah semua percobaan ulang habis.
     *
     * Transaksi dikembalikan ke status 'dibatalkan' agar tidak menggantung,
     * dan detail error dicatat untuk investigasi lebih lanjut.
     *
     * @param  Throwable $exception Pengecualian yang menyebabkan kegagalan
     */
    public function failed(Throwable $exception): void
    {
        Log::error(
            "[JOB] Gagal total memproses stok untuk transaksi #{$this->transaksiId}. " .
            "Semua percobaan ulang telah habis. " .
            "Error: {$exception->getMessage()}"
        );

        // Batalkan transaksi agar staf dapat menindaklanjuti secara manual
        Transaksi::find($this->transaksiId)?->update([
            'status' => StatusTransaksi::Dibatalkan,
        ]);

        Log::warning(
            "[JOB] Transaksi #{$this->transaksiId} otomatis dibatalkan " .
            "akibat kegagalan pemrosesan stok. Perlu tindak lanjut manual."
        );
    }
}
