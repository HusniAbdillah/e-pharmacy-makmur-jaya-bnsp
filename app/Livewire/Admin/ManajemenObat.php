<?php

namespace App\Livewire\Admin;

use App\Models\BatchStok;
use App\Models\KategoriObat;
use App\Models\Obat;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManajemenObat extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = "";
    public ?int $kategoriFilter = null;

    // Modal tambah batch
    public bool $showBatchModal = false;
    public ?int $selectedObatId = null;
    public string $batchNumber = "";
    public int $initialStock = 0;
    public string $expirationDate = "";

    // Modal upload gambar
    public bool $showGambarModal = false;
    public ?int $gambarObatId = null;
    public $gambarObat = null;

    protected $queryString = ["search", "kategoriFilter"];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKategoriFilter(): void
    {
        $this->resetPage();
    }

    // =========================================================================
    // BATCH MODAL
    // =========================================================================

    public function openBatchModal(int $obatId): void
    {
        $this->selectedObatId = $obatId;
        $this->showBatchModal = true;
        $this->reset(["batchNumber", "initialStock", "expirationDate"]);
    }

    public function closeBatchModal(): void
    {
        $this->showBatchModal = false;
        $this->reset([
            "selectedObatId",
            "batchNumber",
            "initialStock",
            "expirationDate",
        ]);
    }

    public function tambahBatch(): void
    {
        $this->validate(
            [
                "batchNumber" =>
                    "required|string|unique:batch_stoks,batch_number",
                "initialStock" => "required|integer|min:1",
                "expirationDate" => "required|date|after:today",
            ],
            [
                "batchNumber.required" => "Nomor batch wajib diisi.",
                "batchNumber.unique" => "Nomor batch sudah ada.",
                "initialStock.required" => "Stok awal wajib diisi.",
                "initialStock.min" => "Stok minimal 1 unit.",
                "expirationDate.required" => "Tanggal kadaluarsa wajib diisi.",
                "expirationDate.after" =>
                    "Tanggal kadaluarsa harus masa depan.",
            ],
        );

        BatchStok::create([
            "obat_id" => $this->selectedObatId,
            "batch_number" => $this->batchNumber,
            "initial_stock" => $this->initialStock,
            "current_stock" => $this->initialStock,
            "expiration_date" => $this->expirationDate,
        ]);

        session()->flash("success", "Batch stok berhasil ditambahkan.");
        $this->closeBatchModal();
    }

    // =========================================================================
    // GAMBAR MODAL
    // =========================================================================

    public function openGambarModal(int $obatId): void
    {
        $this->gambarObatId = $obatId;
        $this->showGambarModal = true;
        $this->gambarObat = null;
    }

    public function closeGambarModal(): void
    {
        $this->showGambarModal = false;
        $this->reset(["gambarObatId", "gambarObat"]);
    }

    public function simpanGambar(): void
    {
        $this->validate(
            [
                "gambarObat" =>
                    "required|image|mimes:jpg,jpeg,png,webp|max:2048",
            ],
            [
                "gambarObat.required" => "Pilih file gambar.",
                "gambarObat.image" => "File harus berupa gambar.",
                "gambarObat.max" => "Ukuran maksimal 2 MB.",
            ],
        );

        // Simpan ke storage/app/public/obat
        $path = $this->gambarObat->store("obat", "public");

        Obat::findOrFail($this->gambarObatId)->update(["gambar_obat" => $path]);

        session()->flash("success", "Gambar obat berhasil diperbarui.");
        $this->closeGambarModal();
    }

    public function render()
    {
        $kategoris = KategoriObat::all();

        $obats = Obat::with(["kategoriObat", "batchStoks"])
            ->when(
                $this->search,
                fn($q) => $q->where("name", "like", "%{$this->search}%"),
            )
            ->when(
                $this->kategoriFilter,
                fn($q) => $q->where("kategori_obat_id", $this->kategoriFilter),
            )
            ->paginate(15);

        return view("livewire.admin.manajemen-obat", [
            "obats" => $obats,
            "kategoris" => $kategoris,
        ]);
    }
}
