<?php

namespace App\Livewire\Pasien;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Services\InventoryService;
use Livewire\Component;
use Livewire\WithPagination;

class KatalogObat extends Component
{
    use WithPagination;

    public string $search = "";
    public ?int $kategoriFilter = null;
    // Pilihan pengurutan
    public string $sortOption = "name_asc";

    protected $queryString = ["search", "kategoriFilter", "sortOption"];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingKategoriFilter(): void
    {
        $this->resetPage();
    }
    public function updatingSortOption(): void
    {
        $this->resetPage();
    }

    public function tambahKeKeranjang(int $obatId): void
    {
        $obat = Obat::findOrFail($obatId);
        $inventoryService = app(InventoryService::class);

        if ($inventoryService->getTotalStokTersedia($obatId) <= 0) {
            session()->flash("error", "Stok {$obat->name} habis.");
            return;
        }

        $cart = session()->get("cart", []);

        if (isset($cart[$obatId])) {
            $cart[$obatId]["quantity"]++;
        } else {
            $cart[$obatId] = [
                "obat_id" => $obat->id,
                "name" => $obat->name,
                "price" => $obat->price,
                "quantity" => 1,
            ];
        }

        session()->put("cart", $cart);
        session()->flash("success", "{$obat->name} ditambahkan ke keranjang.");
    }

    public function render()
    {
        $kategoris = KategoriObat::all();
        $inventoryService = app(InventoryService::class);

        // Pecah pilihan pengurutan
        $parts = explode('_', $this->sortOption);
        $sortBy = $parts[0] ?? 'name';
        $sortDir = $parts[1] ?? 'asc';

        // Query dengan filter & urutan
        $obats = Obat::with("kategoriObat")
            ->when(
                $this->search,
                fn($q) => $q->where("name", "like", "%{$this->search}%"),
            )
            ->when(
                $this->kategoriFilter,
                fn($q) => $q->where("kategori_obat_id", $this->kategoriFilter),
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate(12);

        foreach ($obats as $obat) {
            $obat->stok_tersedia = $inventoryService->getTotalStokTersedia(
                $obat->id,
            );
        }

        return view("livewire.pasien.katalog-obat", [
            "obats" => $obats,
            "kategoris" => $kategoris,
        ]);
    }
}
