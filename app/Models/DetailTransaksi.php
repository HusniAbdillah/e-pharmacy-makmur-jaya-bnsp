<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        "transaksi_id",
        "obat_id",
        "batch_stok_id",
        "quantity",
        "unit_price",
    ];

    /**
     * Konversi tipe data atribut secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "unit_price" => "decimal:2", // Snapshot harga, tidak berubah setelah disimpan
        ];
    }

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Detail ini adalah bagian dari satu transaksi induk.
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * Detail ini merujuk ke satu jenis obat.
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    /**
     * Detail ini mengambil stok dari batch tertentu (kunci pelacakan FIFO).
     * Nullable saat pertama dibuat; diisi oleh Job ProcessBatchStockUpdate.
     */
    public function batchStok(): BelongsTo
    {
        return $this->belongsTo(BatchStok::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Hitung subtotal baris ini: jumlah × harga satuan.
     * Digunakan untuk menampilkan rincian faktur.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }
}
