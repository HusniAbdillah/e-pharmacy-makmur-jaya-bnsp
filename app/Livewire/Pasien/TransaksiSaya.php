<?php

namespace App\Livewire\Pasien;

use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TransaksiSaya extends Component
{
    use WithPagination;

    public function render()
    {
        // Ambil transaksi milik user
        $transaksis = Transaksi::with(['detailTransaksis.obat'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.pasien.transaksi-saya', [
            'transaksis' => $transaksis,
        ]);
    }
}
