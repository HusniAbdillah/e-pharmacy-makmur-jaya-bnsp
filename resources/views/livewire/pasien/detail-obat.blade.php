<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-semantic-danger/10 border border-semantic-danger/30 text-semantic-danger rounded-sm p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-50 mb-4">
            <a href="{{ route('katalog') }}" wire:navigate class="hover:underline">Katalog</a>
            <x-heroicon-o-chevron-right class="w-4 h-4 shrink-0" />
            <span class="text-black">{{ $obat->name }}</span>
        </nav>

        {{-- Tombol kembali --}}
        <a href="{{ route('katalog') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-sm text-gray-50 hover:text-black mb-6">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Kembali ke Katalog
        </a>

        {{-- Layout dua kolom --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            {{-- Kiri: Gambar --}}
            <div class="bg-white border border-oat rounded-lg p-6 flex items-center justify-center min-h-64">
                @if($obat->gambar_obat)
                    <img src="{{ asset('storage/' . $obat->gambar_obat) }}"
                         alt="{{ $obat->name }}"
                         class="w-full max-h-80 object-contain rounded-sm">
                @else
                    {{-- Placeholder gambar --}}
                    <div class="flex flex-col items-center gap-3 text-gray-50">
                        <x-heroicon-o-photo class="w-24 h-24" />
                        <span class="text-sm">Belum ada gambar</span>
                    </div>
                @endif
            </div>

            {{-- Kanan: Info obat --}}
            <div class="flex flex-col gap-5">

                {{-- Nama obat --}}
                <h1 class="text-4xl font-normal leading-none tracking-tight text-black">
                    {{ $obat->name }}
                </h1>

                {{-- Badge kategori --}}
                <div>
                    <span class="inline-block px-3 py-1 text-sm bg-oat text-black rounded-sm">
                        {{ $obat->kategoriObat->name }}
                    </span>
                </div>

                {{-- Harga --}}
                <p class="text-3xl font-normal text-black">
                    Rp {{ number_format($obat->price, 0, ',', '.') }}
                </p>

                {{-- Indikator stok --}}
                <div>
                    @if($obat->stok_tersedia > 0)
                        <span class="inline-flex items-center gap-1.5 text-sm text-semantic-success font-medium">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Stok tersedia: {{ $obat->stok_tersedia }} unit
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-sm text-semantic-danger font-medium">
                            <x-heroicon-o-x-circle class="w-4 h-4" />
                            Stok Habis
                        </span>
                    @endif
                </div>

                {{-- Badge resep dokter --}}
                @if($obat->requires_prescription)
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-semantic-warning/10 border border-semantic-warning/30 text-semantic-warning rounded-sm">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                            Memerlukan Resep Dokter
                        </span>
                    </div>
                @endif

                {{-- Tombol tambah keranjang --}}
                <button
                    wire:click="tambahKeKeranjang"
                    @if($obat->stok_tersedia <= 0) disabled @endif
                    class="btn-accent w-full mt-auto @if($obat->stok_tersedia <= 0) opacity-50 cursor-not-allowed @endif">
                    @if($obat->stok_tersedia > 0)
                        Tambah ke Keranjang
                    @else
                        Tidak Tersedia
                    @endif
                </button>
            </div>
        </div>

        {{-- Kartu detail info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Deskripsi --}}
            @if(!empty($obat->description))
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-medium text-black mb-3 flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-gray-50" />
                        Deskripsi
                    </h2>
                    <p class="text-sm text-gray-50 leading-relaxed">{{ $obat->description }}</p>
                </div>
            @endif

            {{-- Komposisi --}}
            @if(!empty($obat->composition))
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-medium text-black mb-3 flex items-center gap-2">
                        <x-heroicon-o-beaker class="w-5 h-5 text-gray-50" />
                        Komposisi
                    </h2>
                    <p class="text-sm text-gray-50 leading-relaxed">{{ $obat->composition }}</p>
                </div>
            @endif

            {{-- Aturan Dosis --}}
            @if(!empty($obat->dose))
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-medium text-black mb-3 flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-gray-50" />
                        Aturan Dosis
                    </h2>
                    <p class="text-sm text-gray-50 leading-relaxed">{{ $obat->dose }}</p>
                </div>
            @endif

            {{-- Efek Samping --}}
            @if(!empty($obat->side_effects))
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-medium text-black mb-3 flex items-center gap-2">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-gray-50" />
                        Efek Samping
                    </h2>
                    <p class="text-sm text-gray-50 leading-relaxed">{{ $obat->side_effects }}</p>
                </div>
            @endif

        </div>
    </div>
</div>
