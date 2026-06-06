<?php

namespace App\Models;

use App\Enums\StatusTransaksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        "invoice_number",
        "user_id",
        "total_price",
        "status",
        "is_online",
        "resep_path",
    ];

    /**
     * Konversi tipe data atribut secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "total_price" => "decimal:2",
            "status" => StatusTransaksi::class, // Cast ke PHP Enum untuk keamanan tipe
            "is_online" => "boolean",
        ];
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Transaksi ini dilakukan oleh satu pengguna (kasir input atau pasien online).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaksi ini memiliki satu atau lebih item (detail baris).
     */
    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Filter berdasarkan status transaksi.
     */
    public function scopeByStatus($query, StatusTransaksi $status)
    {
        return $query->where("status", $status->value);
    }

    /**
     * Scope: Hanya transaksi dari kanal online.
     */
    public function scopeOnline($query)
    {
        return $query->where("is_online", true);
    }

    /**
     * Scope: Hanya transaksi dari kasir (offline).
     */
    public function scopeOffline($query)
    {
        return $query->where("is_online", false);
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    /**
     * Cek apakah transaksi masih bisa diubah statusnya.
     * Transaksi yang sudah Selesai atau Dibatalkan adalah final.
     */
    public function bisaDiubah(): bool
    {
        return !in_array($this->status, [
            StatusTransaksi::Selesai,
            StatusTransaksi::Dibatalkan,
        ]);
    }
}
