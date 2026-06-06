<?php

namespace App\Livewire\Admin;

use App\Enums\StatusTransaksi;
use App\Models\Transaksi;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenTransaksi extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Modal ubah status
    public bool $showStatusModal = false;
    public ?int $selectedTransaksiId = null;
    public string $newStatus = '';

    // Sinkronisasi URL query string
    protected $queryString = ['search', 'statusFilter'];

    // Reset halaman saat cari
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Reset halaman saat filter
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function editStatus(int $transaksiId): void
    {
        $transaksi = Transaksi::findOrFail($transaksiId);
        $this->selectedTransaksiId = $transaksiId;
        $this->newStatus = $transaksi->status->value;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->reset(['selectedTransaksiId', 'newStatus']);
    }

    public function updateStatus(): void
    {
        $transaksi = Transaksi::findOrFail($this->selectedTransaksiId);
        $transaksi->update(['status' => $this->newStatus]);

        session()->flash('success', 'Status transaksi berhasil diperbarui.');
        $this->closeStatusModal();
    }

    public function render()
    {
        // Tangkap lokal untuk closure
        $search = $this->search;

        $query = Transaksi::with('user')
            ->when(
                $search,
                fn($q) => $q->where(function ($subq) use ($search) {
                    // Filter invoice atau nama
                    $subq->where('invoice_number', 'like', "%{$search}%")
                         ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                }),
            )
            ->when(
                $this->statusFilter,
                fn($q) => $q->where('status', $this->statusFilter),
            );

        // Hitung total pendapatan terfilter
        $totalHarga = $query->sum('total_price');
        $transaksis = $query->latest()->paginate(15);

        return view('livewire.admin.manajemen-transaksi', [
            'transaksis'    => $transaksis,
            'totalHarga'    => $totalHarga,
            'statusOptions' => StatusTransaksi::cases(),
            'selectedTransaksi' => $this->selectedTransaksiId ? Transaksi::find($this->selectedTransaksiId) : null,
        ]);
    }
}
