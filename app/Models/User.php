<?php

namespace App\Models;

use App\Enums\RolePengguna;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang boleh diisi secara massal (mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = ["name", "email", "password", "role"];

    /**
     * Atribut yang disembunyikan dari serialisasi (JSON/array).
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Konversi (cast) tipe data atribut secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
            "role" => RolePengguna::class, // Cast ke PHP Enum untuk keamanan tipe
        ];
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Satu pengguna (kasir atau pasien) bisa memiliki banyak transaksi.
     */
    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    /**
     * Cek apakah pengguna memiliki peran tertentu.
     */
    public function hasRole(RolePengguna $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah pengguna adalah admin atau apoteker (akses backend).
     */
    public function isStaf(): bool
    {
        return in_array($this->role, [
            RolePengguna::Admin,
            RolePengguna::Apoteker,
            RolePengguna::Kasir,
        ]);
    }
}
