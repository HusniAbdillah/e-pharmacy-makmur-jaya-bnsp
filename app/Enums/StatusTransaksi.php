<?php

namespace App\Enums;

/**
 * Status alur transaksi penjualan obat.
 * Digunakan sebagai cast pada Model Transaksi untuk memastikan nilai selalu valid.
 */
enum StatusTransaksi: string
{
    case MenungguVerifikasi = "menunggu_verifikasi";
    case MenungguPembayaran = "menunggu_pembayaran";
    case Diproses = "diproses";
    case Selesai = "selesai";
    case Dibatalkan = "dibatalkan";

    /**
     * Kembalikan label tampilan dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            StatusTransaksi::MenungguVerifikasi => "Menunggu Verifikasi Resep",
            StatusTransaksi::MenungguPembayaran => "Menunggu Pembayaran",
            StatusTransaksi::Diproses => "Sedang Diproses",
            StatusTransaksi::Selesai => "Selesai",
            StatusTransaksi::Dibatalkan => "Dibatalkan",
        };
    }

    /**
     * Kembalikan kelas Tailwind badge warna sesuai status untuk tampilan UI.
     * Menggunakan palet warna semantic dari design system.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            StatusTransaksi::MenungguVerifikasi
                => "bg-semantic-warning/10 text-semantic-warning border border-semantic-warning/30",
            StatusTransaksi::MenungguPembayaran
                => "bg-semantic-info/10 text-semantic-info border border-semantic-info/30",
            StatusTransaksi::Diproses
                => "bg-semantic-info/10 text-semantic-info border border-semantic-info/30",
            StatusTransaksi::Selesai
                => "bg-semantic-success/10 text-semantic-success border border-semantic-success/30",
            StatusTransaksi::Dibatalkan
                => "bg-semantic-danger/10 text-semantic-danger border border-semantic-danger/30",
        };
    }

    /**
     * Kembalikan daftar transisi status yang diizinkan dari status saat ini.
     * Mencegah perubahan status yang tidak valid (mis: dari Selesai ke Menunggu).
     *
     * @return array<StatusTransaksi>
     */
    public function transisiDiizinkan(): array
    {
        return match ($this) {
            StatusTransaksi::MenungguVerifikasi => [
                StatusTransaksi::MenungguPembayaran,
                StatusTransaksi::Dibatalkan,
            ],
            StatusTransaksi::MenungguPembayaran => [
                StatusTransaksi::Diproses,
                StatusTransaksi::Dibatalkan,
            ],
            StatusTransaksi::Diproses => [
                StatusTransaksi::Selesai,
                StatusTransaksi::Dibatalkan,
            ],
            StatusTransaksi::Selesai => [],
            StatusTransaksi::Dibatalkan => [],
        };
    }
}
