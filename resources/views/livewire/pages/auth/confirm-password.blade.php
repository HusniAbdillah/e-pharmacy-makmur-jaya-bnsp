<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout("layouts.guest")] class extends Component {
    public string $password = "";

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            "password" => ["required", "string"],
        ]);

        if (
            !Auth::guard("web")->validate([
                "email" => Auth::user()->email,
                "password" => $this->password,
            ])
        ) {
            throw ValidationException::withMessages([
                "password" => __("auth.password"),
            ]);
        }

        session(["auth.password_confirmed_at" => time()]);

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
        <h1 class="text-2xl font-semibold text-black tracking-tight">Konfirmasi Kata Sandi</h1>
        <p class="text-sm text-gray-50 mt-1">
            Ini adalah area aman. Konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-4">

        {{-- Kata Sandi --}}
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input wire:model="password"
                          id="password"
                          type="password"
                          name="password"
                          required
                          autocomplete="current-password"
                          placeholder="Masukkan kata sandi Anda" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Tombol Konfirmasi --}}
        <x-primary-button class="w-full mt-2">
            Konfirmasi
        </x-primary-button>

    </form>

</div>
