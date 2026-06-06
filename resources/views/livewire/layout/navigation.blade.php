<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect("/", navigate: true);
    }
};
?>

<nav x-data="{ open: false }" class="bg-white border-b border-oat">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Logo & nav utama --}}
            <div class="flex items-center">
                <div class="shrink-0 flex items-center me-6">
                    <a href="{{ route('katalog') }}" wire:navigate class="flex items-center gap-2">
                        <img src="{{ asset('e-pharmacy-logo.png') }}" alt="Makmur Jaya E-Pharmacy" class="h-8 w-auto">
                        <span class="font-semibold text-lg text-black tracking-tight">Makmur Jaya E-Pharmacy</span>
                    </a>
                </div>
            </div>

            {{-- Link desktop --}}
            <div class="flex items-center">
                <div class="hidden sm:flex sm:items-center sm:gap-1">

                    {{-- Katalog: semua peran --}}
                    <x-nav-link :href="route('katalog')" :active="request()->routeIs('katalog')" wire:navigate>
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-o-book-open class="w-4 h-4" />
                            Katalog
                        </span>
                    </x-nav-link>

                    @auth
                        @php $role = auth()->user()->role; @endphp

                        {{-- Admin --}}
                        @if($role === \App\Enums\RolePengguna::Admin)
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-home class="w-4 h-4" />
                                    Dashboard
                                </span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.obat')" :active="request()->routeIs('admin.obat')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-beaker class="w-4 h-4" />
                                    Manajemen Obat
                                </span>
                            </x-nav-link>
                            @if(Route::has('admin.kategori'))
                                <x-nav-link :href="route('admin.kategori')" :active="request()->routeIs('admin.kategori')" wire:navigate>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-tag class="w-4 h-4" />
                                        Kategori
                                    </span>
                                </x-nav-link>
                            @endif
                            @if(Route::has('admin.transaksi'))
                                <x-nav-link :href="route('admin.transaksi')" :active="request()->routeIs('admin.transaksi')" wire:navigate>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-credit-card class="w-4 h-4" />
                                        Transaksi
                                    </span>
                                </x-nav-link>
                            @endif
                            <x-nav-link :href="route('admin.import')" :active="request()->routeIs('admin.import')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-document-arrow-up class="w-4 h-4" />
                                    Import CSV
                                </span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.log')" :active="request()->routeIs('admin.log')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                    Log
                                </span>
                            </x-nav-link>
                            <x-nav-link href="/log-viewer" :active="request()->is('log-viewer*')">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                    Error Dashboard
                                </span>
                            </x-nav-link>
                        @endif

                        {{-- Apoteker --}}
                        @if($role === \App\Enums\RolePengguna::Apoteker)
                            <x-nav-link :href="route('apoteker.dashboard')" :active="request()->routeIs('apoteker.dashboard')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-home class="w-4 h-4" />
                                    Dashboard
                                </span>
                            </x-nav-link>
                            <x-nav-link :href="route('laporan.pdf')" :active="request()->routeIs('laporan.pdf')">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                    Laporan PDF
                                </span>
                            </x-nav-link>
                        @endif

                        {{-- Kasir --}}
                        @if($role === \App\Enums\RolePengguna::Kasir)
                            @if(Route::has('kasir.pos'))
                                <x-nav-link :href="route('kasir.pos')" :active="request()->routeIs('kasir.pos')" wire:navigate>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-building-storefront class="w-4 h-4" />
                                        POS Konter
                                    </span>
                                </x-nav-link>
                            @endif
                        @endif

                        {{-- Pasien: keranjang dengan badge --}}
                        @if($role === \App\Enums\RolePengguna::Pasien)
                            <x-nav-link :href="route('keranjang')" :active="request()->routeIs('keranjang')" wire:navigate>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                    Keranjang
                                    @php $cartCount = count(session()->get('cart', [])); @endphp
                                    @if($cartCount > 0)
                                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium bg-black text-white rounded-full">
                                            {{ $cartCount }}
                                        </span>
                                    @endif
                                </span>
                            </x-nav-link>
                            @if(Route::has('pasien.transaksi'))
                                <x-nav-link :href="route('pasien.transaksi')" :active="request()->routeIs('pasien.transaksi')" wire:navigate>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                        Transaksi Saya
                                    </span>
                                </x-nav-link>
                            @endif
                        @endif

                    @else
                        {{-- Guest: keranjang --}}
                        <x-nav-link :href="route('keranjang')" :active="request()->routeIs('keranjang')" wire:navigate>
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                Keranjang
                                @php $cartCount = count(session()->get('cart', [])); @endphp
                                @if($cartCount > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium bg-black text-white rounded-full">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </span>
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            {{-- Dropdown user (desktop) --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-sm text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-50 shrink-0" />
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                                     x-text="name"
                                     x-on:profile-updated.window="name = $event.detail.name">
                                </div>
                                <div class="ms-1">
                                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                Profil
                            </x-dropdown-link>
                            {{-- Tombol logout --}}
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    Keluar
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-50 hover:text-black transition duration-150">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" wire:navigate
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-orange hover:bg-orange/90 rounded-sm transition duration-150">
                                <x-heroicon-o-user-plus class="w-4 h-4" />
                                Daftar
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            {{-- Hamburger mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-sm text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <x-heroicon-o-bars-3 x-show="!open" class="h-6 w-6" />
                    <x-heroicon-o-x-mark x-show="open" class="h-6 w-6" style="display:none;" />
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            @guest
                {{-- Guest: daftar & masuk mobile --}}
                <div class="px-4 py-3 border-b border-oat flex gap-3">
                    <a href="{{ route('login') }}" wire:navigate
                       class="flex-1 text-center px-3 py-2 text-sm font-medium border border-oat rounded-sm bg-white text-black hover:bg-cream transition duration-150 flex items-center justify-center gap-1.5">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" wire:navigate
                           class="flex-1 text-center px-3 py-2 text-sm font-medium text-white bg-orange hover:bg-orange/90 rounded-sm transition duration-150 flex items-center justify-center gap-1.5">
                            <x-heroicon-o-user-plus class="w-4 h-4" />
                            Daftar
                        </a>
                    @endif
                </div>
            @endguest

            {{-- Katalog --}}
            <x-responsive-nav-link :href="route('katalog')" :active="request()->routeIs('katalog')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <x-heroicon-o-book-open class="w-4 h-4" />
                    Katalog
                </span>
            </x-responsive-nav-link>

            @auth
                @php $role = auth()->user()->role; @endphp

                {{-- Admin mobile --}}
                @if($role === \App\Enums\RolePengguna::Admin)
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-home class="w-4 h-4" />
                            Dashboard
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.obat')" :active="request()->routeIs('admin.obat')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-beaker class="w-4 h-4" />
                            Manajemen Obat
                        </span>
                    </x-responsive-nav-link>
                    @if(Route::has('admin.kategori'))
                        <x-responsive-nav-link :href="route('admin.kategori')" :active="request()->routeIs('admin.kategori')" wire:navigate>
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon-o-tag class="w-4 h-4" />
                                Kategori
                            </span>
                        </x-responsive-nav-link>
                    @endif
                    @if(Route::has('admin.transaksi'))
                        <x-responsive-nav-link :href="route('admin.transaksi')" :active="request()->routeIs('admin.transaksi')" wire:navigate>
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon-o-credit-card class="w-4 h-4" />
                                Transaksi
                            </span>
                        </x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('admin.import')" :active="request()->routeIs('admin.import')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-document-arrow-up class="w-4 h-4" />
                            Import CSV
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.log')" :active="request()->routeIs('admin.log')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                            Log
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="/log-viewer" :active="request()->is('log-viewer*')">
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                            Error Dashboard
                        </span>
                    </x-responsive-nav-link>
                @endif

                {{-- Apoteker mobile --}}
                @if($role === \App\Enums\RolePengguna::Apoteker)
                    <x-responsive-nav-link :href="route('apoteker.dashboard')" :active="request()->routeIs('apoteker.dashboard')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-home class="w-4 h-4" />
                            Dashboard
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('laporan.pdf')" :active="request()->routeIs('laporan.pdf')">
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                            Laporan PDF
                        </span>
                    </x-responsive-nav-link>
                @endif

                {{-- Kasir mobile --}}
                @if($role === \App\Enums\RolePengguna::Kasir)
                    @if(Route::has('kasir.pos'))
                        <x-responsive-nav-link :href="route('kasir.pos')" :active="request()->routeIs('kasir.pos')" wire:navigate>
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon-o-building-storefront class="w-4 h-4" />
                                POS Konter
                            </span>
                        </x-responsive-nav-link>
                    @endif
                @endif

                {{-- Pasien: keranjang mobile --}}
                @if($role === \App\Enums\RolePengguna::Pasien)
                    <x-responsive-nav-link :href="route('keranjang')" :active="request()->routeIs('keranjang')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-4 h-4" />
                            Keranjang
                            @php $cartCount = count(session()->get('cart', [])); @endphp
                            @if($cartCount > 0)
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium bg-black text-white rounded-full ms-1">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </span>
                    </x-responsive-nav-link>
                    @if(Route::has('pasien.transaksi'))
                        <x-responsive-nav-link :href="route('pasien.transaksi')" :active="request()->routeIs('pasien.transaksi')" wire:navigate>
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                Transaksi Saya
                            </span>
                        </x-responsive-nav-link>
                    @endif
                @endif

            @else
                {{-- Guest: keranjang mobile --}}
                <x-responsive-nav-link :href="route('keranjang')" :active="request()->routeIs('keranjang')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <x-heroicon-o-shopping-cart class="w-4 h-4" />
                        Keranjang
                    </span>
                </x-responsive-nav-link>
            @endauth
        </div>

        {{-- User info & logout mobile --}}
        @auth
            <div class="pt-4 pb-1 border-t border-oat">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800"
                         x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                         x-text="name"
                         x-on:profile-updated.window="name = $event.detail.name">
                    </div>
                    <div class="font-medium text-sm text-gray-50">{{ auth()->user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        Profil
                    </x-responsive-nav-link>
                    {{-- Logout mobile --}}
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            Keluar
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @endauth
    </div>
</nav>
