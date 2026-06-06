<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout("layouts.guest")] class extends Component {
    public string $email = "";

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            "email" => ["required", "string", "email"],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($this->only("email"));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError("email", __($status));

            return;
        }

        $this->reset("email");

        session()->flash("status", __($status));
    }
};
?>

<div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-black tracking-tight">Reset Kata Sandi</h1>
        <p class="text-sm text-gray-50 mt-1">
            Lupa kata sandi? Masukkan alamat email Anda dan kami akan mengirimkan
            tautan untuk membuat kata sandi baru.
        </p>
    </div>

    {{-- Status pengiriman email --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-4">

        {{-- Alamat Email --}}
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input wire:model="email"
                          id="email"
                          type="email"
                          name="email"
                          required
                          autofocus
                          autocomplete="username"
                          placeholder="nama@contoh.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Tombol Kirim Tautan --}}
        <x-primary-button class="w-full mt-2">
            Kirim Tautan Reset
        </x-primary-button>

    </form>

    {{-- Tautan kembali ke login --}}
    <p class="mt-5 text-center text-sm text-gray-50">
        Ingat kata sandi Anda?
        <a href="{{ route('login') }}"
           wire:navigate
           class="font-medium text-black hover:underline transition duration-150">
            Kembali ke halaman masuk
        </a>
    </p>

</div>
