<?php

namespace App\Livewire\Admin;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenSupplier extends Component
{
    use WithPagination;

    public string $search = "";
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = "";
    public string $phone = "";
    public string $email = "";
    public string $address = "";

    protected $queryString = ["search"];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->reset(["editId", "name", "phone", "email", "address"]);

        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->editId = $supplier->id;
            $this->name = $supplier->name;
            $this->phone = $supplier->phone ?? "";
            $this->email = $supplier->email ?? "";
            $this->address = $supplier->address ?? "";
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function simpan(): void
    {
        $this->validate([
            "name" => "required|string|max:255",
            "phone" => "nullable|string|max:20",
            "email" => "nullable|email|max:100",
            "address" => "nullable|string|max:500",
        ], [
            "name.required" => "Nama supplier wajib diisi.",
            "email.email" => "Format email tidak valid.",
        ]);

        $data = [
            "name" => $this->name,
            "phone" => $this->phone ?: null,
            "email" => $this->email ?: null,
            "address" => $this->address ?: null,
        ];

        if ($this->editId) {
            Supplier::findOrFail($this->editId)->update($data);
            session()->flash("success", "Data supplier berhasil diperbarui.");
        } else {
            Supplier::create($data);
            session()->flash("success", "Supplier baru berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function hapus(int $id): void
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->obats()->exists()) {
            session()->flash("error", "Supplier '{$supplier->name}' tidak dapat dihapus karena masih memasok data obat.");
            return;
        }

        $supplier->delete();
        session()->flash("success", "Supplier berhasil dihapus.");
    }

    public function render()
    {
        $suppliers = Supplier::withCount("obats")
            ->when(
                $this->search,
                fn($q) => $q->where("name", "like", "%{$this->search}%")
                    ->orWhere("email", "like", "%{$this->search}%"),
            )
            ->paginate(10);

        return view("livewire.admin.manajemen-supplier", [
            "suppliers" => $suppliers,
        ]);
    }
}
