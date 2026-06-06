<?php

namespace Tests\Unit;

use App\Models\Obat;
use App\Models\BatchStok;
use App\Models\KategoriObat;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogikaFifoInventoryTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private Obat $obat;
    private BatchStok $batchA;
    private BatchStok $batchB;

    protected function setUp(): void
    {
        parent::setUp();

        // Inisialisasi service utama
        $this->service = app(InventoryService::class);

        // Buat kategori obat dummy
        $kategori = KategoriObat::create([
            'name' => 'Tablet',
            'description' => 'Obat tablet',
        ]);

        // Buat obat dummy
        $this->obat = Obat::create([
            'kategori_obat_id' => $kategori->id,
            'name' => 'Paracetamol',
            'composition' => 'Paracetamol 500mg',
            'dose' => '3x1',
            'side_effects' => 'Mengantuk',
            'price' => 5000,
            'requires_prescription' => false,
            'minimum_stock' => 5,
        ]);

        // Batch A: kadaluarsa 10
        $this->batchA = BatchStok::create([
            'obat_id' => $this->obat->id,
            'batch_number' => 'BATCH-A',
            'initial_stock' => 5,
            'current_stock' => 5,
            'expiration_date' => now()->addDays(10)->toDateString(),
        ]);

        // Batch B: kadaluarsa 30
        $this->batchB = BatchStok::create([
            'obat_id' => $this->obat->id,
            'batch_number' => 'BATCH-B',
            'initial_stock' => 10,
            'current_stock' => 10,
            'expiration_date' => now()->addDays(30)->toDateString(),
        ]);
    }

    /**
     * Prioritas batch kadaluarsa terdekat.
     */
    public function test_deduct_stock_prioritizes_nearest_expiration(): void
    {
        // Potong stok sebanyak 3
        $this->service->deductStockFIFO($this->obat->id, 3);

        // Segarkan data dari database
        $this->batchA->refresh();
        $this->batchB->refresh();

        // Pastikan Batch A berkurang
        $this->assertEquals(2, $this->batchA->current_stock);
        $this->assertEquals(10, $this->batchB->current_stock);
    }

    /**
     * Potong batch berikutnya jika kosong.
     */
    public function test_deduct_stock_moves_to_next_batch_if_empty(): void
    {
        // Potong stok sebanyak 7
        $this->service->deductStockFIFO($this->obat->id, 7);

        // Segarkan data dari database
        $this->batchA->refresh();
        $this->batchB->refresh();

        // Pastikan Batch B terpotong
        $this->assertEquals(0, $this->batchA->current_stock);
        $this->assertEquals(8, $this->batchB->current_stock);
    }

    /**
     * Pengecualian jika stok tidak cukup.
     */
    public function test_exception_thrown_if_stock_insufficient(): void
    {
        // Harus melempar exception
        $this->expectException(\Exception::class);

        // Potong stok melebihi batas
        $this->service->deductStockFIFO($this->obat->id, 20);
    }
}
