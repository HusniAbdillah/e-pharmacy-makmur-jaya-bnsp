<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriObat extends Model
{
    use HasFactory;
    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = ["name"];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Satu kategori dapat memiliki banyak obat.
     * Foreign key: obats.kategori_obat_id
     */
    public function obats(): HasMany
    {
        return $this->hasMany(Obat::class, "kategori_obat_id");
    }
}
