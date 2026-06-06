<?php

namespace App\Livewire\Admin;

use App\Models\BatchStok;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Supplier;
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

    // Modal CRUD Obat
    public bool $showFormModal = false;
    public ?int $editObatId = null;
    public ?int $kategoriObatId = null;
    public ?int $supplierId = null;
    public string $name = "";
    public string $description = "";
    public string $composition = "";
    public string $dose = "";
    public string $side_effects = "";
    public float $price = 0.0;
    public int $minimum_stock = 10;
    public bool $requires_prescription = false;

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
    // CRUD OBAT FORM MODAL
    // =========================================================================

    public function openFormModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->reset([
            "editObatId",
            "kategoriObatId",
            "supplierId",
            "name",
            "description",
            "composition",
            "dose",
            "side_effects",
            "price",
            "minimum_stock",
            "requires_prescription"
        ]);

        if ($id) {
            $obat = Obat::findOrFail($id);
            $this->editObatId = $obat->id;
            $this->kategoriObatId = $obat->kategori_obat_id;
            $this->supplierId = $obat->supplier_id;
            $this->name = $obat->name;
            $this->description = $obat->description ?? "";
            $this->composition = $obat->composition ?? "";
            $this->dose = $obat->dose;
            $this->side_effects = $obat->side_effects ?? "";
            $this->price = (float) $obat->price;
            $this->minimum_stock = $obat->minimum_stock;
            $this->requires_prescription = (bool) $obat->requires_prescription;
        }

        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
    }

    public function simpanObat(): void
    {
        $this->validate([
            "kategoriObatId" => "required|exists:kategori_obats,id",
            "supplierId" => "nullable|exists:suppliers,id",
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "composition" => "nullable|string",
            "dose" => "required|string|max:100",
            "side_effects" => "nullable|string",
            "price" => "required|numeric|min:0",
            "minimum_stock" => "required|integer|min:0",
            "requires_prescription" => "boolean",
        ], [
            "kategoriObatId.required" => "Kategori wajib dipilih.",
            "name.required" => "Nama obat wajib diisi.",
            "dose.required" => "Dosis wajib diisi.",
            "price.required" => "Harga wajib diisi.",
            "price.numeric" => "Harga harus berupa angka.",
            "price.min" => "Harga minimal Rp 0.",
            "minimum_stock.required" => "Stok minimum wajib diisi.",
            "minimum_stock.integer" => "Stok minimum harus berupa angka.",
            "minimum_stock.min" => "Stok minimum minimal 0.",
        ]);

        $data = [
            "kategori_obat_id" => $this->kategoriObatId,
            "supplier_id" => $this->supplierId,
            "name" => $this->name,
            "description" => $this->description ?: null,
            "composition" => $this->composition ?: null,
            "dose" => $this->dose,
            "side_effects" => $this->side_effects ?: null,
            "price" => $this->price,
            "minimum_stock" => $this->minimum_stock,
            "requires_prescription" => $this->requires_prescription,
        ];

        if ($this->editObatId) {
            Obat::findOrFail($this->editObatId)->update($data);
            session()->flash("success", "Obat berhasil diperbarui.");
        } else {
            Obat::create($data);
            session()->flash("success", "Obat baru berhasil ditambahkan.");
        }

        // Pemicu event asinkron agar toast mendeteksi jika ada stok kritis baru
        $this->dispatch('obat-updated');

        $this->closeFormModal();
    }

    public function hapusObat(int $id): void
    {
        $obat = Obat::findOrFail($id);

        // Periksa apakah obat memiliki relasi transaksi
        if ($obat->detailTransaksis()->exists()) {
            session()->flash("error", "Obat '{$obat->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
            return;
        }

        // Periksa apakah obat memiliki sisa stok aktif
        if ($obat->batchStoks()->where('current_stock', '>', 0)->exists()) {
            session()->flash("error", "Obat '{$obat->name}' tidak dapat dihapus karena masih memiliki batch stok aktif.");
            return;
        }

        // Hapus batch stok kosong
        $obat->batchStoks()->delete();
        $obat->delete();

        session()->flash("success", "Obat berhasil dihapus dari sistem.");
        $this->dispatch('obat-updated');
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
        $this->dispatch('obat-updated');
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
        $suppliers = Supplier::all();

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
            "suppliers" => $suppliers,
        ]);
    }
}
