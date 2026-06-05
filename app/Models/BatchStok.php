<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchStok extends Model
{
    use HasFactory;
    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        "obat_id",
        "batch_number",
        "initial_stock",
        "current_stock",
        "expiration_date",
    ];

    /**
     * Konversi tipe data atribut secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "expiration_date" => "date", // Otomatis menjadi Carbon instance
        ];
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Batch ini milik satu jenis obat.
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    /**
     * Batch ini digunakan dalam banyak detail transaksi (untuk audit trail FIFO).
     */
    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Ambil hanya batch yang masih memiliki stok.
     */
    public function scopeTersedia($query)
    {
        return $query->where("current_stock", ">", 0);
    }

    /**
     * Scope: Ambil batch yang sudah kadaluarsa.
     */
    public function scopeKadaluarsa($query)
    {
        return $query->where("expiration_date", "<", now()->toDateString());
    }

    /**
     * Scope: Ambil batch yang akan kadaluarsa dalam N hari ke depan.
     */
    public function scopeHampirKadaluarsa($query, int $hari = 30)
    {
        return $query->whereBetween("expiration_date", [
            now()->toDateString(),
            now()->addDays($hari)->toDateString(),
        ]);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Cek apakah batch ini sudah melewati tanggal kadaluarsa.
     */
    public function getIsKadaluarsaAttribute(): bool
    {
        return $this->expiration_date->isPast();
    }

    /**
     * Cek apakah batch ini akan kadaluarsa dalam 30 hari ke depan.
     */
    public function getIsHampirKadaluarsaAttribute(): bool
    {
        return !$this->is_kadaluarsa &&
            now()->diffInDays($this->expiration_date) <= 30;
    }

    /**
     * Hitung persentase stok yang sudah terjual dari batch ini.
     */
    public function getPersentaseTerjualAttribute(): float
    {
        if ($this->initial_stock === 0) {
            return 0;
        }

        return round(
            (($this->initial_stock - $this->current_stock) /
                $this->initial_stock) *
                100,
            1,
        );
    }
}
