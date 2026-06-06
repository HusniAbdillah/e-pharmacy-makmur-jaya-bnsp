<?php

namespace App\Livewire\Apoteker;

use App\Enums\StatusTransaksi;
use App\Models\AuditLog;
use App\Models\Transaksi;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardApoteker extends Component
{
    public function setujuiResep(int $transaksiId): void
    {
        $transaksi = Transaksi::findOrFail($transaksiId);

        if ($transaksi->status !== StatusTransaksi::MenungguVerifikasi) {
            session()->flash(
                "error",
                "Transaksi bukan dalam status verifikasi.",
            );
            return;
        }

        $transaksi->update(["status" => StatusTransaksi::MenungguPembayaran]);

        // Log audit
        AuditLog::create([
            "user_id" => Auth::id(),
            "action" => "verify_prescription",
            "model" => "Transaksi",
            "model_id" => $transaksi->id,
            "changes" => [
                "from" => "menunggu_verifikasi",
                "to" => "menunggu_pembayaran",
            ],
        ]);

        session()->flash(
            "success",
            "Resep {$transaksi->invoice_number} disetujui.",
        );
    }

    public function render()
    {
        $inventoryService = app(InventoryService::class);

        // Transaksi menunggu verifikasi
        $transaksiMenungguVerifikasi = Transaksi::with("user")
            ->where("status", StatusTransaksi::MenungguVerifikasi)
            ->latest()
            ->get();

        // Batch hampir kadaluarsa 90 hari
        $batchHampirKadaluarsa = $inventoryService->getBatchHampirKadaluarsa(
            90,
        );

        // Batch sudah kadaluarsa
        $batchKadaluarsa = $inventoryService->getBatchKadaluarsaBersisa();

        // Obat stok kritis
        $obatStokKritis = $inventoryService->getObatStokKritis();

        return view("livewire.apoteker.dashboard-apoteker", [
            "transaksiMenungguVerifikasi" => $transaksiMenungguVerifikasi,
            "batchHampirKadaluarsa" => $batchHampirKadaluarsa,
            "batchKadaluarsa" => $batchKadaluarsa,
            "obatStokKritis" => $obatStokKritis,
        ]);
    }
}
