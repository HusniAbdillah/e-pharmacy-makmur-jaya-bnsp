<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Satu supplier dapat memasok banyak obat.
     */
    public function obats(): HasMany
    {
        return $this->hasMany(Obat::class, 'supplier_id');
    }
}
