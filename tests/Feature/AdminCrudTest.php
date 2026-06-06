<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\KategoriObat;
use App\Models\Supplier;
use App\Models\Obat;
use App\Enums\RolePengguna;
use App\Livewire\Admin\ManajemenObat;
use App\Livewire\Admin\ManajemenSupplier;
use App\Livewire\Admin\ManajemenPelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => RolePengguna::Admin,
        ]);
    }

    /**
     * Uji CRUD obat oleh admin.
     */
    public function test_admin_can_manage_obat(): void
    {
        $kategori = KategoriObat::create(['name' => 'Tablet']);
        $supplier = Supplier::create(['name' => 'PT. Supplier A']);

        $this->actingAs($this->adminUser);

        // Uji komponen Livewire ManajemenObat
        Livewire::test(ManajemenObat::class)
            ->call('openFormModal')
            ->set('name', 'Paracetamol Forte')
            ->set('kategoriObatId', $kategori->id)
            ->set('supplierId', $supplier->id)
            ->set('price', 8500)
            ->set('minimum_stock', 50)
            ->set('dose', '3x1 tablet')
            ->call('simpanObat')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('obats', [
            'name' => 'Paracetamol Forte',
            'price' => 8500.00,
            'supplier_id' => $supplier->id
        ]);
    }

    /**
     * Uji CRUD supplier oleh admin.
     */
    public function test_admin_can_manage_supplier(): void
    {
        $this->actingAs($this->adminUser);

        // Uji komponen Livewire ManajemenSupplier
        Livewire::test(ManajemenSupplier::class)
            ->call('openModal')
            ->set('name', 'PT. Kalbe Medika')
            ->set('phone', '021-998822')
            ->set('email', 'sales@kalbe.id')
            ->set('address', 'Cikarang')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'name' => 'PT. Kalbe Medika',
            'phone' => '021-998822'
        ]);
    }

    /**
     * Uji CRUD pelanggan oleh admin.
     */
    public function test_admin_can_manage_pelanggan(): void
    {
        $this->actingAs($this->adminUser);

        // Uji komponen Livewire ManajemenPelanggan
        Livewire::test(ManajemenPelanggan::class)
            ->call('openModal')
            ->set('name', 'Pelanggan Baru')
            ->set('email', 'pelanggan@mail.com')
            ->set('password', 'Rahasia@123')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@mail.com',
            'role' => RolePengguna::Pasien
        ]);
    }

    /**
     * Uji API katalog obat.
     */
    public function test_api_obat_endpoint(): void
    {
        $kategori = KategoriObat::create(['name' => 'Tablet']);
        Obat::create([
            'kategori_obat_id' => $kategori->id,
            'name' => 'Vitamin C 500mg',
            'price' => 2000,
            'dose' => '1x1',
            'minimum_stock' => 10
        ]);

        $response = $this->getJson('/api/obat');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Vitamin C 500mg',
                'price' => 2000.00
            ]);
    }
}
