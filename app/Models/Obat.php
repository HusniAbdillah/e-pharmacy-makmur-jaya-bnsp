<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    use HasFactory;
    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        "kategori_obat_id",
        "name",
        "description",
        "composition",
        "dose",
        "side_effects",
        "price",
        "minimum_stock",
        "requires_prescription",
        "gambar_obat",
    ];

    /**
     * Konversi tipe data atribut secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "price" => "decimal:2",
            "requires_prescription" => "boolean",
        ];
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Obat ini termasuk dalam satu kategori obat.
     */
    public function kategoriObat(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, "kategori_obat_id");
    }

    /**
     * Obat ini memiliki banyak batch stok (untuk logika FIFO).
     */
    public function batchStoks(): HasMany
    {
        return $this->hasMany(BatchStok::class);
    }

    /**
     * Obat ini pernah muncul dalam banyak detail transaksi.
     */
    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    // =========================================================================
    // SCOPES & QUERY HELPERS
    // =========================================================================

    /**
     * Ambil batch stok yang masih tersedia, diurutkan dari kadaluarsa PALING AWAL (FIFO).
     * Digunakan oleh service layer saat memproses penjualan.
     */
    public function batchStoksTersedia(): HasMany
    {
        return $this->hasMany(BatchStok::class)
            ->where("current_stock", ">", 0)
            ->orderBy("expiration_date", "asc");
    }

    /**
     * Scope: Filter obat yang membutuhkan resep dokter.
     */
    public function scopeButuhResep($query)
    {
        return $query->where("requires_prescription", true);
    }

    /**
     * Scope: Filter obat berdasarkan kategori.
     */
    public function scopeByKategori($query, int $kategoriId)
    {
        return $query->where("kategori_obat_id", $kategoriId);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Hitung total stok aktif dari seluruh batch yang belum habis.
     * Digunakan untuk menampilkan stok di halaman produk.
     */
    public function getTotalStokAttribute(): int
    {
        return (int) $this->batchStoks()->sum("current_stock");
    }

    /**
     * Cek apakah total stok saat ini berada di bawah atau sama dengan batas minimum.
     */
    public function getIsStokKritisAttribute(): bool
    {
        return $this->total_stok <= $this->minimum_stock;
    }
}
