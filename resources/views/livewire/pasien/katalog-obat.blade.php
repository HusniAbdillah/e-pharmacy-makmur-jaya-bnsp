<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Katalog Obat</h1>
            <p class="text-gray-50">Temukan obat dan produk kesehatan yang Anda butuhkan</p>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white border border-oat rounded-lg p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Cari obat --}}
                <div>
                    <label class="form-label">Cari Obat</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Ketik nama obat..." class="form-input">
                </div>

                {{-- Filter kategori --}}
                <div>
                    <label class="form-label">Kategori</label>
                    <select wire:model.live="kategoriFilter" class="form-input">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Urutkan --}}
                <div class="md:col-span-2">
                    <label class="form-label">Urutkan</label>
                    <select wire:model.live="sortOption" class="form-input">
                        <option value="name_asc">Nama (A-Z)</option>
                        <option value="name_desc">Nama (Z-A)</option>
                        <option value="price_asc">Harga: Termurah</option>
                        <option value="price_desc">Harga: Termahal</option>
                    </select>
                </div>
            </div>
        </div>

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

        {{-- Grid obat --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @forelse($obats as $obat)
                <div class="bg-white border border-oat rounded-lg p-5 hover:shadow-md transition-shadow flex flex-col">

                    {{-- Gambar obat --}}
                    @if($obat->gambar_obat)
                        <img src="{{ asset('storage/' . $obat->gambar_obat) }}"
                             alt="{{ $obat->name }}"
                             class="w-full h-32 object-cover rounded-sm mb-3">
                    @else
                        {{-- Placeholder gambar --}}
                        <div class="w-full h-32 bg-oat/30 rounded-sm mb-3 flex items-center justify-center text-gray-50">
                            <x-heroicon-o-photo class="w-10 h-10" />
                        </div>
                    @endif

                    {{-- Nama obat --}}
                    <h3 class="text-xl font-normal leading-none tracking-tight text-black mb-2">
                        {{ $obat->name }}
                    </h3>

                    {{-- Badge kategori --}}
                    <span class="inline-block px-2 py-1 text-xs bg-oat text-black rounded-sm mb-3">
                        {{ $obat->kategoriObat->name }}
                    </span>

                    {{-- Deskripsi singkat --}}
                    <p class="text-sm text-gray-50 mb-3 line-clamp-2 flex-1">
                        {{ Str::limit($obat->description, 80) }}
                    </p>

                    {{-- Status stok --}}
                    <div class="mb-3">
                        @if($obat->stok_tersedia > 0)
                            <p class="text-sm text-semantic-success">
                                Stok: <strong>{{ $obat->stok_tersedia }} unit</strong>
                            </p>
                        @else
                            <p class="text-sm text-semantic-danger font-medium">Stok Habis</p>
                        @endif
                    </div>

                    {{-- Harga --}}
                    <p class="text-2xl font-normal text-black mb-4">
                        Rp {{ number_format($obat->price, 0, ',', '.') }}
                    </p>

                    {{-- Tombol tambah --}}
                    <button
                        wire:click="tambahKeKeranjang({{ $obat->id }})"
                        @if($obat->stok_tersedia <= 0) disabled @endif
                        class="btn-accent w-full @if($obat->stok_tersedia <= 0) opacity-50 cursor-not-allowed @endif mb-2">
                        @if($obat->stok_tersedia > 0)
                            Tambah ke Keranjang
                        @else
                            Tidak Tersedia
                        @endif
                    </button>

                    {{-- Detail button --}}
                    <a href="{{ route('katalog.detail', $obat->id) }}" wire:navigate
                        class="w-full text-center px-4 py-2 text-sm border border-oat rounded-sm bg-white text-black hover:bg-cream transition-colors block">
                        Detail
                    </a>

                    {{-- Badge resep dokter --}}
                    @if($obat->requires_prescription)
                        <p class="text-xs text-semantic-warning mt-2 text-center inline-flex items-center justify-center gap-1">
                            <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 inline" />
                            Memerlukan Resep Dokter
                        </p>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-50 text-lg">Tidak ada obat ditemukan.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="bg-white border border-oat rounded-lg p-5">
            {{ $obats->links() }}
        </div>
    </div>
</div>
