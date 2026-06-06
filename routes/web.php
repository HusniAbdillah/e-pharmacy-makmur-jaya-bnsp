<?php

use App\Http\Controllers\LaporanPenjualanController;
use App\Livewire\Admin\DashboardOverview;
use App\Livewire\Admin\ImportCsvObat;
use App\Livewire\Admin\LogAktivitas;
use App\Livewire\Admin\ManajemenObat;
use App\Livewire\Admin\ManajemenKategori;
use App\Livewire\Admin\ManajemenTransaksi;
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

require __DIR__ . "/auth.php";
