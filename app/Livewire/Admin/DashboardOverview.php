<?php

namespace App\Livewire\Admin;

use App\Models\Transaksi;
use App\Models\User;
use App\Models\Obat;
use App\Services\InventoryService;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render()
    {
        $inventoryService = app(InventoryService::class);

        // Metrik utama
        $totalPengguna = User::count();
        $totalObat = Obat::count();
        $totalTransaksi = Transaksi::count();
        $pendapatanBulanIni = Transaksi::where("status", "selesai")
            ->whereMonth("created_at", now()->month)
            ->whereYear("created_at", now()->year)
            ->sum("total_price");

        // Grafik penjualan dummy (4 minggu)
        $monthlySalesData = json_encode([10, 20, 15, 30]);

        // Stok kritis & kadaluarsa
        $obatStokKritis = $inventoryService->getObatStokKritis();
        $batchKadaluarsa = $inventoryService->getBatchKadaluarsaBersisa();
        $batchHampirKadaluarsa = $inventoryService->getBatchHampirKadaluarsa(
            30,
        );

        return view("livewire.admin.dashboard-overview", [
            "totalPengguna" => $totalPengguna,
            "totalObat" => $totalObat,
            "totalTransaksi" => $totalTransaksi,
            "pendapatanBulanIni" => $pendapatanBulanIni,
            "monthlySalesData" => $monthlySalesData,
            "obatStokKritis" => $obatStokKritis,
            "batchKadaluarsa" => $batchKadaluarsa,
            "batchHampirKadaluarsa" => $batchHampirKadaluarsa,
        ]);
    }
}
