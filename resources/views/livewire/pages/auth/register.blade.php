<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout("layouts.guest")] class extends Component {
    public string $name = "";
    public string $email = "";
    public string $password = "";
    public string $password_confirmation = "";

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "lowercase",
                "email",
                "max:255",
                "unique:" . User::class,
            ],
            "password" => [
                "required",
                "string",
                "confirmed",
                Rules\Password::defaults(),
            ],
        ]);

        $validated["password"] = Hash::make($validated["password"]);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route("dashboard", absolute: false), navigate: true);
    }
};
?>

<div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-black tracking-tight">Buat Akun</h1>
        <p class="text-sm text-gray-50 mt-1">Daftarkan diri Anda di Makmur Jaya E-Pharmacy</p>
    </div>

    <form wire:submit="register" class="space-y-4">

        {{-- Nama Lengkap --}}
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input wire:model="name"
                          id="name"
                          type="text"
                          name="name"
                          required
                          autofocus
                          autocomplete="name"
                          placeholder="Nama lengkap Anda" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        {{-- Alamat Email --}}
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input wire:model="email"
                          id="email"
                          type="email"
                          name="email"
                          required
                          autocomplete="username"
                          placeholder="nama@contoh.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Kata Sandi --}}
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input wire:model="password"
                          id="password"
                          type="password"
                          name="password"
                          required
                          autocomplete="new-password"
                          placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Konfirmasi Kata Sandi --}}
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-text-input wire:model="password_confirmation"
                          id="password_confirmation"
                          type="password"
                          name="password_confirmation"
                          required
                          autocomplete="new-password"
                          placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        {{-- Tombol Daftar --}}
        <x-primary-button class="w-full mt-2">
            Daftar Sekarang
        </x-primary-button>

    </form>

    {{-- Tautan ke Halaman Masuk --}}
    <p class="mt-5 text-center text-sm text-gray-50">
        Sudah punya akun?
        <a href="{{ route('login') }}"
           wire:navigate
           class="font-medium text-black hover:underline transition duration-150">
            Masuk di sini
        </a>
    </p>

</div>
