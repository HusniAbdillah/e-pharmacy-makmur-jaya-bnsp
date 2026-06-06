<?php

namespace App\Livewire\Kasir;

use App\Enums\StatusTransaksi;
use App\Jobs\ProcessBatchStockUpdate;
use App\Models\DetailTransaksi;
use App\Models\Obat;
use App\Models\Transaksi;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosKonter extends Component
{
    // Pencarian obat
    public string $cariObat = '';
    public array $hasilCari = [];

    // Keranjang POS (array lokal, bukan session)
    public array $posCart = [];

    // Metode pembayaran
    public string $metodePembayaran = 'tunai';

    public function updatedCariObat(): void
    {
        // Autocomplete: cari obat dari DB
        if (strlen($this->cariObat) < 2) {
            $this->hasilCari = [];
            return;
        }

        $this->hasilCari = Obat::where('name', 'like', "%{$this->cariObat}%")
            ->take(8)
            ->get(['id', 'name', 'price'])
            ->map(fn($o) => [
                'id'    => $o->id,
                'name'  => $o->name,
                'price' => (float) $o->price,
            ])
            ->toArray();
    }

    public function tambahKePos(int $obatId): void
    {
        $obat             = Obat::findOrFail($obatId);
        $inventoryService = app(InventoryService::class);

        if ($inventoryService->getTotalStokTersedia($obatId) <= 0) {
            session()->flash('error', "Stok {$obat->name} habis.");
            return;
        }

        $key = (string) $obatId;

        if (isset($this->posCart[$key])) {
            $this->posCart[$key]['quantity']++;
        } else {
            $this->posCart[$key] = [
                'obat_id'  => $obat->id,
                'name'     => $obat->name,
                'price'    => (float) $obat->price,
                'quantity' => 1,
            ];
        }

        // Tutup dropdown pencarian
        $this->cariObat  = '';
        $this->hasilCari = [];
    }

    public function kurangiItem(int $obatId): void
    {
        $key = (string) $obatId;

        if (!isset($this->posCart[$key])) {
            return;
        }

        if ($this->posCart[$key]['quantity'] <= 1) {
            unset($this->posCart[$key]);
        } else {
            $this->posCart[$key]['quantity']--;
        }
    }

    public function hapusItem(int $obatId): void
    {
        unset($this->posCart[(string) $obatId]);
    }

    public function bayar(): void
    {
        if (empty($this->posCart)) {
            session()->flash('error', 'Keranjang POS kosong.');
            return;
        }

        $inventoryService = app(InventoryService::class);

        // Validasi stok semua item
        foreach ($this->posCart as $item) {
            if (!$inventoryService->cekKecukupanStok($item['obat_id'], $item['quantity'])) {
                session()->flash('error', "Stok {$item['name']} tidak mencukupi.");
                return;
            }
        }

        DB::transaction(function () use ($inventoryService): void {
            $total = collect($this->posCart)->sum(fn($i) => $i['price'] * $i['quantity']);

            // Buat transaksi kasir (offline, langsung selesai)
            $transaksi = Transaksi::create([
                'invoice_number' => 'POS-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                'user_id'        => Auth::id(),
                'total_price'    => $total,
                'status'         => StatusTransaksi::Selesai,
                'is_online'      => false,
            ]);

            // Buat detail & potong stok FIFO inline
            foreach ($this->posCart as $item) {
                $detail = DetailTransaksi::create([
                    'transaksi_id'  => $transaksi->id,
                    'obat_id'       => $item['obat_id'],
                    'batch_stok_id' => null,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['price'],
                ]);

                // Potong stok langsung (sinkron) untuk POS
                $inventoryService->deductStockFIFO(
                    obatId:            $item['obat_id'],
                    quantityToDeduct:  $item['quantity'],
                    detailTransaksiId: $detail->id,
                );
            }

            // Dispatch job sebagai catatan audit (akan skip karena status sudah selesai)
            ProcessBatchStockUpdate::dispatch($transaksi->id);

            session()->flash('success', "Transaksi berhasil! Invoice: {$transaksi->invoice_number}");
        });

        // Kosongkan keranjang POS
        $this->posCart          = [];
        $this->metodePembayaran = 'tunai';
    }

    public function render()
    {
        // Total belanja saat ini
        $totalPos = collect($this->posCart)->sum(fn($i) => $i['price'] * $i['quantity']);

        return view('livewire.kasir.pos-konter', [
            'totalPos' => $totalPos,
        ]);
    }
}
