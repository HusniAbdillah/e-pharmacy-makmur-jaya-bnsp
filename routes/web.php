<?php

use App\Http\Controllers\LaporanPenjualanController;
use App\Livewire\Admin\DashboardOverview;
use App\Livewire\Admin\ImportCsvObat;
use App\Livewire\Admin\LogAktivitas;
use App\Livewire\Admin\ManajemenObat;
use App\Livewire\Admin\ManajemenKategori;
use App\Livewire\Admin\ManajemenTransaksi;
use App\Livewire\Admin\ManajemenSupplier;
use App\Livewire\Admin\ManajemenPelanggan;
use App\Livewire\Apoteker\DashboardApoteker;
use App\Livewire\Kasir\PosKonter;
use App\Livewire\Pasien\CheckoutProses;
use App\Livewire\Pasien\DetailObat;
use App\Livewire\Pasien\KatalogObat;
use App\Livewire\Pasien\KeranjangBelanja;
use App\Livewire\Pasien\TransaksiSaya;
use Illuminate\Support\Facades\Route;

// Katalog Obat Publik (Pasien)
Route::get("/", KatalogObat::class)->name("katalog");
Route::redirect("/katalog", "/");
Route::get("/katalog/{obat}", DetailObat::class)->name("katalog.detail");

// Keranjang & Checkout (Pasien)
Route::get("/keranjang", KeranjangBelanja::class)->name("keranjang");

// Pasien Routes (protected)
Route::middleware(["auth", "verified", "role:pasien"])->group(function () {
    Route::get("/checkout", CheckoutProses::class)->name("checkout");
    Route::view("/checkout/sukses", "livewire.pasien.checkout-sukses")->name("checkout.sukses");
    Route::get("/transaksi-saya", TransaksiSaya::class)->name("pasien.transaksi");
});

// Admin Routes (protected)
Route::middleware(["auth", "verified", "role:admin"])
    ->prefix("admin")
    ->group(function () {
        Route::get("/dashboard", DashboardOverview::class)->name("admin.dashboard");
        Route::get("/obat", ManajemenObat::class)->name("admin.obat");
        Route::get("/kategori", ManajemenKategori::class)->name("admin.kategori");
        Route::get("/supplier", ManajemenSupplier::class)->name("admin.supplier");
        Route::get("/pelanggan", ManajemenPelanggan::class)->name("admin.pelanggan");
        Route::get("/transaksi", ManajemenTransaksi::class)->name("admin.transaksi");
        Route::get("/log", LogAktivitas::class)->name("admin.log");
        Route::get("/import-csv", ImportCsvObat::class)->name("admin.import");
    });

// Apoteker Routes (protected)
Route::middleware(["auth", "verified", "role:apoteker"])
    ->prefix("apoteker")
    ->group(function () {
        Route::get("/dashboard", DashboardApoteker::class)->name("apoteker.dashboard");
        Route::get("/laporan/export-pdf", [LaporanPenjualanController::class, "exportPdf"])->name("laporan.pdf");
    });

// Kasir Routes (protected)
Route::middleware(["auth", "verified", "role:kasir"])
    ->prefix("kasir")
    ->group(function () {
        Route::get("/pos", PosKonter::class)->name("kasir.pos");
    });

// Redirect Dashboard berdasarkan role
Route::get("/dashboard", function () {
    $role = auth()->user()->role;
    return match ($role) {
        \App\Enums\RolePengguna::Admin => redirect()->route("admin.dashboard"),
        \App\Enums\RolePengguna::Apoteker => redirect()->route("apoteker.dashboard"),
        \App\Enums\RolePengguna::Kasir => redirect()->route("kasir.pos"),
        default => redirect()->route("katalog"),
    };
})->middleware(["auth", "verified"])->name("dashboard");

// Profile (existing)
Route::view("profile", "profile")
    ->middleware(["auth"])
    ->name("profile");

// =========================================================================
// API ENDPOINTS (FOR INTEGRATION & IN-APP TOAST ALERTS)
// =========================================================================
Route::prefix("api")->group(function () {
    // GET /api/obat - Get list of medicines for external integration
    Route::get("/obat", function () {
        $obats = \App\Models\Obat::with(["kategoriObat", "batchStoks"])->get()->map(function ($obat) {
            return [
                "id" => $obat->id,
                "name" => $obat->name,
                "kategori" => $obat->kategoriObat->name ?? null,
                "price" => (float) $obat->price,
                "requires_prescription" => (bool) $obat->requires_prescription,
                "total_stock" => (int) $obat->batchStoks->sum("current_stock"),
                "composition" => $obat->composition,
                "dose" => $obat->dose,
            ];
        });

        return response()->json([
            "status" => "success",
            "count" => $obats->count(),
            "data" => $obats,
        ]);
    });

    // GET /api/cek-stok-kritis - Cek asinkron stok kritis untuk alert toast
    Route::get("/cek-stok-kritis", function () {
        $inventoryService = app(\App\Services\InventoryService::class);
        
        $kritis = $inventoryService->getObatStokKritis()->map(function ($obat) {
            return [
                "id" => $obat->id,
                "name" => $obat->name,
                "stok" => (int) $obat->total_stok_aktif,
            ];
        });

        $hampirKadaluarsa = $inventoryService->getBatchHampirKadaluarsa(30)->map(function ($batch) {
            return [
                "batch_number" => $batch->batch_number,
                "name" => $batch->obat->name,
                "expiration_date" => $batch->expiration_date->format("d/m/Y"),
            ];
        });

        return response()->json([
            "kritis" => $kritis,
            "hampir_kadaluarsa" => $hampirKadaluarsa,
        ]);
    });
});

require __DIR__ . "/auth.php";
