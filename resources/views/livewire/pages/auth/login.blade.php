<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout("layouts.guest")] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(
            default: route("dashboard", absolute: false),
            navigate: true,
        );
    }
};
?>

<div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-black tracking-tight">Masuk</h1>
        <p class="text-sm text-gray-50 mt-1">Selamat datang kembali di Makmur Jaya E-Pharmacy</p>
    </div>

    {{-- Status sesi (mis: setelah reset password) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">

        {{-- Alamat Email --}}
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input wire:model="form.email"
                          id="email"
                          type="email"
                          name="email"
                          required
                          autofocus
                          autocomplete="username"
                          placeholder="nama@contoh.com" />
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        {{-- Kata Sandi --}}
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input wire:model="form.password"
                          id="password"
                          type="password"
                          name="password"
                          required
                          autocomplete="current-password"
                          placeholder="Masukkan kata sandi" />
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        {{-- Ingat Saya & Lupa Kata Sandi --}}
        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="flex items-center gap-2 cursor-pointer select-none">
                <input wire:model="form.remember"
                       id="remember"
                       type="checkbox"
                       name="remember"
                       class="rounded-sm border-oat text-orange focus:ring-orange/30 focus:ring-offset-0">
                <span class="text-sm text-gray-50">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   wire:navigate
                   class="text-sm text-gray-50 hover:text-black hover:underline transition duration-150">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        {{-- Tombol Masuk --}}
        <x-primary-button class="w-full mt-2">
            Masuk
        </x-primary-button>

    </form>

    {{-- Tautan ke Halaman Daftar --}}
    <p class="mt-5 text-center text-sm text-gray-50">
        Belum punya akun?
        <a href="{{ route('register') }}"
           wire:navigate
           class="font-medium text-black hover:underline transition duration-150">
            Daftar sekarang
        </a>
    </p>

    {{-- Informasi Kredensial Pengujian --}}
    <div class="bg-gray-50 border border-gray-200 rounded-sm p-4 mt-6 text-sm text-gray-700">
        <p class="font-semibold text-black mb-2 flex items-center gap-1.5">
            <x-heroicon-o-information-circle class="w-4 h-4 text-orange shrink-0" />
            Informasi Kredensial Pengujian
        </p>
        <div class="space-y-1.5 font-mono text-xs">
            <div class="flex items-center justify-between gap-4">
                <span class="text-gray-50 shrink-0">Admin:</span>
                <span class="text-black font-semibold">admin@makmurjaya.id / Admin@123</span>
            </div>
            <div class="flex items-center justify-between gap-4">
                <span class="text-gray-50 shrink-0">Apoteker:</span>
                <span class="text-black font-semibold">apoteker@makmurjaya.id / Apoteker@123</span>
            </div>
            <div class="flex items-center justify-between gap-4">
                <span class="text-gray-50 shrink-0">Kasir:</span>
                <span class="text-black font-semibold">kasir@makmurjaya.id / Kasir@123</span>
            </div>
            <div class="flex items-center justify-between gap-4">
                <span class="text-gray-50 shrink-0">Pasien:</span>
                <span class="text-black font-semibold">pasien@makmurjaya.id / Pasien@123</span>
            </div>
        </div>
    </div>

</div>
