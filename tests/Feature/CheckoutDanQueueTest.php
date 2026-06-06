<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Obat;
use App\Models\KategoriObat;
use App\Models\BatchStok;
use App\Enums\RolePengguna;
use App\Jobs\ProcessBatchStockUpdate;
use App\Livewire\Pasien\CheckoutProses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutDanQueueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checkout men-dispatch job FIFO.
     */
    public function test_checkout_dispatches_fifo_job(): void
    {
        // Palsukan antrean
        Queue::fake();

        // Buat user pasien
        $user = User::factory()->create([
            'role' => RolePengguna::Pasien,
        ]);

        // Buat kategori obat
        $kategori = KategoriObat::create([
            'name' => 'Tablet',
            'description' => 'Obat tablet',
        ]);

        // Buat obat dummy
        $obat = Obat::create([
            'kategori_obat_id' => $kategori->id,
            'name' => 'Paracetamol',
            'composition' => 'Paracetamol 500mg',
            'dose' => '3x1',
            'side_effects' => 'Mengantuk',
            'price' => 5000,
            'requires_prescription' => false,
            'minimum_stock' => 5,
        ]);

        // Buat batch stok mencukupi
        BatchStok::create([
            'obat_id' => $obat->id,
            'batch_number' => 'BATCH-A',
            'initial_stock' => 10,
            'current_stock' => 10,
            'expiration_date' => now()->addDays(10)->toDateString(),
        ]);

        // Simulasikan keranjang di session
        session(['cart' => [
            $obat->id => [
                'obat_id' => $obat->id,
                'name' => $obat->name,
                'price' => $obat->price,
                'quantity' => 2,
            ]
        ]]);

        // Login sebagai pasien
        $this->actingAs($user);

        // Uji komponen checkout
        Livewire::test(CheckoutProses::class)
            ->call('prosesPembayaran')
            ->assertHasNoErrors();

        // Pastikan job dikirim
        Queue::assertPushed(ProcessBatchStockUpdate::class);
    }
}
