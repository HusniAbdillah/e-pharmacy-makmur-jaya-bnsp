<?php

namespace App\Livewire\Admin;

use App\Models\KategoriObat;
use Livewire\Component;

class ManajemenKategori extends Component
{
    public string $search = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $namaKategori = '';

    // Buka modal tambah/edit
    public function openModal(?int $id = null): void
    {
        if ($id) {
            // Muat data kategori
            $kategori = KategoriObat::findOrFail($id);
            $this->editId = $id;
            $this->namaKategori = $kategori->name;
        }

        $this->showModal = true;
    }

    // Tutup & reset modal
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
        $this->namaKategori = '';
    }

    // Simpan atau perbarui kategori
    public function simpan(): void
    {
        $this->validate([
            'namaKategori' => 'required|string|max:100',
        ], [
            'namaKategori.required' => 'Nama kategori wajib diisi.',
            'namaKategori.max'      => 'Nama maksimal 100 karakter.',
        ]);

        if ($this->editId) {
            KategoriObat::findOrFail($this->editId)->update(['name' => $this->namaKategori]);
            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            KategoriObat::create(['name' => $this->namaKategori]);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    // Hapus kategori jika kosong
    public function hapus(int $id): void
    {
        $kategori = KategoriObat::findOrFail($id);

        if ($kategori->obats()->exists()) {
            session()->flash('error', 'Kategori masih digunakan obat.');
            return;
        }

        $kategori->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function render()
    {
        // Query dengan jumlah obat
        $kategoris = KategoriObat::withCount('obats')
            ->when(
                $this->search,
                fn($q) => $q->where('name', 'like', "%{$this->search}%"),
            )
            ->latest()
            ->get();

        return view('livewire.admin.manajemen-kategori', [
            'kategoris' => $kategoris,
        ]);
    }
}
