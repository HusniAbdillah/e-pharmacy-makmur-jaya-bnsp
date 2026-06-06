<?php

namespace App\Livewire\Pasien;

use App\Enums\StatusTransaksi;
use App\Jobs\ProcessBatchStockUpdate;
use App\Models\DetailTransaksi;
use App\Models\Obat;
use App\Models\Transaksi;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckoutProses extends Component
{
    use WithFileUploads;

    // File upload resep
    public $resep = null;

    protected function rules(): array
    {
        // Validasi resep hanya jika ada item butuh resep
        $butuhResep = $this->cartButuhResep();

        return $butuhResep
            ? ["resep" => "required|file|mimes:jpg,jpeg,png,pdf|max:2048"]
            : ["resep" => "nullable"];
    }

    // Cek apakah ada item yang butuh resep
    protected function cartButuhResep(): bool
    {
        $cart = session()->get("cart", []);
        if (empty($cart)) {
            return false;
        }

        $obatIds = collect($cart)->pluck("obat_id")->toArray();

        return Obat::whereIn("id", $obatIds)
            ->where("requires_prescription", true)
            ->exists();
    }

    public function prosesPembayaran(): void
    {
        $cart = session()->get("cart", []);

        if (empty($cart)) {
            session()->flash("error", "Keranjang kosong.");
            return;
        }

        $this->validate();

        $inventoryService = app(InventoryService::class);

        // Validasi stok sebelum proses
        foreach ($cart as $item) {
            if (
                !$inventoryService->cekKecukupanStok(
                    $item["obat_id"],
                    $item["quantity"],
                )
            ) {
                session()->flash(
                    "error",
                    "Stok {$item["name"]} tidak mencukupi.",
                );
                return;
            }
        }

        // Simpan file resep jika ada
        $resepPath = null;
        if ($this->resep) {
            $resepPath = $this->resep->store("resep", "public");
        }

        DB::transaction(function () use ($cart, $resepPath): void {
            $total = collect($cart)->sum(
                fn($item) => $item["price"] * $item["quantity"],
            );

            // Buat header transaksi
            $transaksi = Transaksi::create([
                "invoice_number" =>
                    "INV-" . now()->format("Ymd") . "-" . strtoupper(uniqid()),
                "user_id" => Auth::id(),
                "total_price" => $total,
                "status" => $resepPath ? StatusTransaksi::MenungguVerifikasi : StatusTransaksi::MenungguPembayaran,
                "is_online" => true,
                "resep_path" => $resepPath,
            ]);

            // Buat detail transaksi
            foreach ($cart as $item) {
                DetailTransaksi::create([
                    "transaksi_id" => $transaksi->id,
                    "obat_id" => $item["obat_id"],
                    "batch_stok_id" => null,
                    "quantity" => $item["quantity"],
                    "unit_price" => $item["price"],
                ]);
            }

            // Dispatch Job FIFO asinkron
            ProcessBatchStockUpdate::dispatch($transaksi->id);

            session()->forget("cart");
            session()->flash(
                "success",
                "Pesanan berhasil! Invoice: {$transaksi->invoice_number}",
            );
            session()->flash("invoice", $transaksi->invoice_number);
        });

        $this->redirect(route("checkout.sukses"), navigate: true);
    }

    public function render()
    {
        $cart = session()->get("cart", []);
        $total = collect($cart)->sum(
            fn($item) => $item["price"] * $item["quantity"],
        );
        $butuhResep = $this->cartButuhResep();

        return view("livewire.pasien.checkout-proses", [
            "cart" => $cart,
            "total" => $total,
            "butuhResep" => $butuhResep,
        ]);
    }
}
