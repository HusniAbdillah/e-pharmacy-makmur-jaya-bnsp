<?php

namespace App\Livewire\Pasien;

use App\Models\Obat;
use App\Services\InventoryService;
use Livewire\Component;

class DetailObat extends Component
{
    // Model binding obat
    public Obat $obat;

    public function tambahKeKeranjang(): void
    {
        if (auth()->check() && auth()->user()->role !== \App\Enums\RolePengguna::Pasien) {
            session()->flash('error', "Hanya pasien yang dapat berbelanja di keranjang katalog.");
            return;
        }

        $inventoryService = app(InventoryService::class);

        // Cek stok tersedia
        if ($inventoryService->getTotalStokTersedia($this->obat->id) <= 0) {
            session()->flash('error', "Stok {$this->obat->name} habis.");
            return;
        }

        $cart   = session()->get('cart', []);
        $obatId = $this->obat->id;

        if (isset($cart[$obatId])) {
            $cart[$obatId]['quantity']++;
        } else {
            // Inisialisasi item keranjang
            $cart[$obatId] = [
                'obat_id'  => $this->obat->id,
                'name'     => $this->obat->name,
                'price'    => $this->obat->price,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);
        session()->flash('success', "{$this->obat->name} ditambahkan ke keranjang.");
    }

    public function render()
    {
        // Inject stok tersedia
        $this->obat->stok_tersedia = app(InventoryService::class)
            ->getTotalStokTersedia($this->obat->id);

        return view('livewire.pasien.detail-obat');
    }
}
