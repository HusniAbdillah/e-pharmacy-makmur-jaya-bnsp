<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Layout dua kolom --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- ================================================ --}}
            {{-- KOLOM KIRI (span 2)                               --}}
            {{-- ================================================ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Judul halaman --}}
                <div>
                    <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">POS Konter</h1>
                    <p class="text-gray-50">Proses penjualan langsung di konter apotik</p>
                </div>

                {{-- Pesan sukses/error --}}
                @if(session('success'))
                    <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 flex items-center gap-2">
                        <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-semantic-danger/10 border border-semantic-danger/30 text-semantic-danger rounded-sm p-4 flex items-center gap-2">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Kartu pencarian obat --}}
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-normal text-black mb-3 flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-50" />
                        Cari Obat
                    </h2>

                    {{-- Input cari dengan autocomplete --}}
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-50 pointer-events-none" />
                        <input
                            type="text"
                            wire:model.live.debounce.200ms="cariObat"
                            placeholder="Ketik nama obat untuk mencari..."
                            class="form-input pl-9"
                            autocomplete="off"
                        >

                        {{-- Dropdown hasil autocomplete --}}
                        @if(!empty($hasilCari))
                            <div class="absolute top-full left-0 right-0 z-20 mt-1 bg-white border border-oat rounded-lg shadow-lg overflow-hidden">
                                @foreach($hasilCari as $item)
                                    {{-- Item hasil pencarian --}}
                                    <button
                                        wire:click="tambahKePos({{ $item['id'] }})"
                                        type="button"
                                        class="w-full flex items-center justify-between px-4 py-3 text-sm text-left hover:bg-cream transition-colors border-b border-oat last:border-b-0"
                                    >
                                        <span class="font-medium text-black">{{ $item['name'] }}</span>
                                        <span class="text-gray-50 ml-4 shrink-0">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kartu keranjang belanja --}}
                <div class="bg-white border border-oat rounded-lg overflow-hidden">
                    <div class="p-5 border-b border-oat flex items-center gap-2">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-50" />
                        <h2 class="text-lg font-normal text-black">Keranjang</h2>
                        @if(!empty($posCart))
                            {{-- Jumlah item keranjang --}}
                            <span class="ml-auto px-2 py-0.5 bg-black text-white text-xs rounded-sm">
                                {{ count($posCart) }} item
                            </span>
                        @endif
                    </div>

                    @if(empty($posCart))
                        {{-- Keranjang kosong --}}
                        <div class="p-12 text-center">
                            <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-50 mx-auto mb-3" />
                            <p class="text-gray-50 text-sm">Keranjang masih kosong.</p>
                            <p class="text-gray-50 text-xs mt-1">Cari obat di atas untuk menambahkan.</p>
                        </div>
                    @else
                        {{-- Daftar item keranjang --}}
                        <div class="divide-y divide-oat">
                            @foreach($posCart as $obatId => $item)
                                <div class="p-4 flex items-center gap-4">

                                    {{-- Nama dan harga satuan --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-black text-sm truncate">{{ $item['name'] }}</p>
                                        <p class="text-xs text-gray-50 mt-0.5">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }} / unit
                                        </p>
                                    </div>

                                    {{-- Kontrol kuantitas --}}
                                    <div class="flex items-center gap-1 shrink-0">
                                        {{-- Kurangi satu --}}
                                        <button
                                            wire:click="kurangiItem({{ $item['obat_id'] }})"
                                            type="button"
                                            class="w-7 h-7 flex items-center justify-center border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                                        >
                                            <x-heroicon-o-minus class="w-3 h-3" />
                                        </button>

                                        {{-- Jumlah saat ini --}}
                                        <span class="w-9 text-center text-sm font-medium text-black">
                                            {{ $item['quantity'] }}
                                        </span>

                                        {{-- Tambah satu --}}
                                        <button
                                            wire:click="tambahKePos({{ $item['obat_id'] }})"
                                            type="button"
                                            class="w-7 h-7 flex items-center justify-center border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                                        >
                                            <x-heroicon-o-plus class="w-3 h-3" />
                                        </button>
                                    </div>

                                    {{-- Total harga baris --}}
                                    <div class="w-28 text-right shrink-0">
                                        <span class="text-sm font-medium text-black">
                                            Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Hapus item --}}
                                    <button
                                        wire:click="hapusItem({{ $item['obat_id'] }})"
                                        type="button"
                                        class="text-gray-50 hover:text-semantic-danger transition-colors shrink-0"
                                    >
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Subtotal bawah tabel --}}
                        <div class="p-4 bg-cream border-t border-oat flex justify-end gap-4">
                            <span class="text-sm text-gray-50">Subtotal</span>
                            <span class="text-sm font-medium text-black">
                                Rp {{ number_format($totalPos, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>

            </div>{{-- /kolom kiri --}}

            {{-- ================================================ --}}
            {{-- KOLOM KANAN (span 1, sticky)                      --}}
            {{-- ================================================ --}}
            <div class="lg:col-span-1 sticky top-4 space-y-4">

                {{-- Kartu ringkasan transaksi --}}
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h2 class="text-lg font-normal text-black mb-4 flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-gray-50" />
                        Ringkasan
                    </h2>

                    {{-- Info jumlah item --}}
                    <div class="flex justify-between items-center py-2 border-b border-oat">
                        <span class="text-sm text-gray-50">Jumlah Item</span>
                        <span class="text-sm font-medium text-black">
                            {{ array_sum(array_column($posCart, 'quantity')) }} unit
                        </span>
                    </div>

                    {{-- Jenis produk berbeda --}}
                    <div class="flex justify-between items-center py-2 border-b border-oat">
                        <span class="text-sm text-gray-50">Jenis Produk</span>
                        <span class="text-sm font-medium text-black">{{ count($posCart) }}</span>
                    </div>

                    {{-- Total pembayaran --}}
                    <div class="flex justify-between items-center pt-3 mt-1">
                        <span class="text-base font-medium text-black">Total</span>
                        <span class="text-xl font-normal text-black">
                            Rp {{ number_format($totalPos, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Kartu metode pembayaran --}}
                <div class="bg-white border border-oat rounded-lg p-5">
                    <h3 class="text-base font-normal text-black mb-3">Metode Pembayaran</h3>

                    {{-- Pilihan metode bayar --}}
                    <div>
                        <label class="form-label">Pilih Metode</label>
                        <select wire:model="metodePembayaran" class="form-input">
                            <option value="tunai">Tunai</option>
                            <option value="debit">Kartu Debit</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                {{-- Tombol proses pembayaran --}}
                <button
                    wire:click="bayar"
                    wire:loading.attr="disabled"
                    wire:target="bayar"
                    @if(empty($posCart)) disabled @endif
                    type="button"
                    class="btn-accent w-full flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{-- Label normal --}}
                    <span wire:loading.remove wire:target="bayar" class="flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                        Proses Pembayaran
                    </span>
                    {{-- Label loading --}}
                    <span wire:loading wire:target="bayar" class="flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 animate-pulse" />
                        Memproses...
                    </span>
                </button>

                {{-- Tombol kosongkan keranjang --}}
                @if(!empty($posCart))
                    {{-- Reset seluruh keranjang --}}
                    <button
                        wire:click="$set('posCart', [])"
                        type="button"
                        class="w-full px-4 py-2 text-sm border border-oat rounded-sm bg-white text-gray-50 hover:text-semantic-danger hover:border-semantic-danger/30 transition-colors"
                    >
                        Kosongkan Keranjang
                    </button>
                @endif

            </div>{{-- /kolom kanan --}}

        </div>{{-- /grid --}}
    </div>
</div>
