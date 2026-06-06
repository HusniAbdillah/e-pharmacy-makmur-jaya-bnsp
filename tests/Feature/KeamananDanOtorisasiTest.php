<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\RolePengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class KeamananDanOtorisasiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tamu dilarang masuk.
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        // Akses dasbor admin
        $response = $this->get('/admin/dashboard');

        // Harus dialihkan ke login
        $response->assertRedirect('/login');
    }

    /**
     * Kasir dilarang masuk admin.
     */
    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        // Buat user kasir
        $user = User::factory()->create([
            'role' => RolePengguna::Kasir,
        ]);

        // Login sebagai kasir
        $this->actingAs($user);

        // Akses dasbor admin
        $response = $this->get('/admin/dashboard');

        // Harus respon terlarang
        $response->assertStatus(403);
    }

    /**
     * Password lemah ditolak.
     */
    public function test_password_must_meet_strong_requirements(): void
    {
        // Masukkan data registrasi
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '123456')
            ->set('password_confirmation', '123456');

        // Panggil fungsi register
        $component->call('register');

        // Harus ada error
        $component->assertHasErrors(['password']);
    }
}
