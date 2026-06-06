<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Enums\RolePengguna;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManajemenPelanggan extends Component
{
    use WithPagination;

    public string $search = "";
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = "";
    public string $email = "";
    public string $password = "";

    protected $queryString = ["search"];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->reset(["editId", "name", "email", "password"]);

        if ($id) {
            $customer = User::findOrFail($id);
            $this->editId = $customer->id;
            $this->name = $customer->name;
            $this->email = $customer->email;
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
            "email" => [
                "required",
                "email",
                "max:255",
                Rule::unique("users", "email")->ignore($this->editId),
            ],
            "password" => $this->editId ? "nullable|string|min:8" : "required|string|min:8",
        ], [
            "name.required" => "Nama pelanggan wajib diisi.",
            "email.required" => "Email wajib diisi.",
            "email.email" => "Format email tidak valid.",
            "email.unique" => "Email sudah digunakan oleh akun lain.",
            "password.required" => "Kata sandi wajib diisi untuk pelanggan baru.",
            "password.min" => "Kata sandi minimal terdiri dari 8 karakter.",
        ]);

        if ($this->editId) {
            $customer = User::findOrFail($this->editId);
            $data = [
                "name" => $this->name,
                "email" => $this->email,
            ];
            if ($this->password) {
                $data["password"] = Hash::make($this->password);
            }
            $customer->update($data);
            session()->flash("success", "Profil pelanggan berhasil diperbarui.");
        } else {
            User::create([
                "name" => $this->name,
                "email" => $this->email,
                "password" => Hash::make($this->password),
                "role" => RolePengguna::Pasien,
                "email_verified_at" => now(), // Auto verify for admin-created customers
            ]);
            session()->flash("success", "Pelanggan baru berhasil didaftarkan.");
        }

        $this->closeModal();
    }

    public function hapus(int $id): void
    {
        $customer = User::findOrFail($id);

        // Validasi transaksi
        if ($customer->transaksis()->exists()) {
            session()->flash("error", "Pelanggan '{$customer->name}' tidak dapat dihapus karena memiliki riwayat transaksi belanja.");
            return;
        }

        $customer->delete();
        session()->flash("success", "Akun pelanggan berhasil dihapus.");
    }

    public function render()
    {
        $customers = User::where("role", RolePengguna::Pasien)
            ->withCount("transaksis")
            ->when(
                $this->search,
                fn($q) => $q->where(function ($query) {
                    $query->where("name", "like", "%{$this->search}%")
                          ->orWhere("email", "like", "%{$this->search}%");
                }),
            )
            ->latest()
            ->paginate(10);

        return view("livewire.admin.manajemen-pelanggan", [
            "customers" => $customers,
        ]);
    }
}
