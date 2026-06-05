<?php

namespace App\Enums;

/**
 * Peran (role) pengguna dalam sistem e-pharmacy.
 * Digunakan sebagai cast pada Model User untuk validasi nilai yang ketat.
 */
enum RolePengguna: string
{
    case Admin     = 'admin';
    case Apoteker  = 'apoteker';
    case Kasir     = 'kasir';
    case Pasien    = 'pasien';

    /**
     * Kembalikan label ramah-baca dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match($this) {
            RolePengguna::Admin    => 'Administrator',
            RolePengguna::Apoteker => 'Apoteker',
            RolePengguna::Kasir    => 'Kasir',
            RolePengguna::Pasien   => 'Pasien',
        };
    }

    /**
     * Kembalikan kelas Tailwind badge sesuai peran untuk tampilan UI.
     */
    public function badgeClass(): string
    {
        return match($this) {
            RolePengguna::Admin    => 'bg-black text-white',
            RolePengguna::Apoteker => 'bg-semantic-info text-black',
            RolePengguna::Kasir    => 'bg-semantic-lime text-black',
            RolePengguna::Pasien   => 'bg-oat text-black',
        };
    }
}
