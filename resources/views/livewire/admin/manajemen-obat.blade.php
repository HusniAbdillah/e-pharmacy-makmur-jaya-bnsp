<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul halaman --}}
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Manajemen Obat</h1>
            <p class="text-gray-50">Kelola master data produk obat dan batch stok</p>
        </div>

        {{-- Pesan sukses/error --}}
        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6 flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-semantic-danger/10 border border-semantic-danger/30 text-semantic-danger rounded-sm p-4 mb-6 flex items-center gap-2">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter pencarian --}}
        <div class="bg-white border border-oat rounded-lg p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Cari Obat</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-50" />
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Ketik nama obat..."
                            class="form-input pl-9"
                        >
                    </div>
                </div>
                <div>
                    {{-- Filter kategori --}}
                    <label class="form-label">Kategori</label>
                    <select wire:model.live="kategoriFilter" class="form-input">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabel daftar obat --}}
        <div class="bg-white border border-oat rounded-lg overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cream border-b border-oat">
                        <tr class="text-left">
                            <th class="p-4 font-medium text-gray-50 w-16">Gambar</th>
                            <th class="p-4 font-medium text-gray-50">Obat</th>
                            <th class="p-4 font-medium text-gray-50">Kategori</th>
                            <th class="p-4 font-medium text-gray-50">Harga</th>
                            <th class="p-4 font-medium text-gray-50">Stok Aktif</th>
                            <th class="p-4 font-medium text-gray-50 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @forelse($obats as $obat)
                            <tr class="hover:bg-cream/50 transition-colors">

                                {{-- Kolom gambar --}}
                                <td class="p-4">
                                    @if($obat->gambar_obat)
                                        <img
                                            src="{{ asset('storage/' . $obat->gambar_obat) }}"
                                            alt="{{ $obat->name }}"
                                            class="w-12 h-12 object-cover rounded-sm border border-oat"
                                        >
                                    @else
                                        {{-- Ikon placeholder gambar --}}
                                        <div class="w-12 h-12 rounded-sm border border-oat bg-cream flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-6 h-6 text-gray-50" />
                                        </div>
                                    @endif
                                </td>

                                {{-- Nama + deskripsi + badge resep --}}
                                <td class="p-4 max-w-xs">
                                    <p class="font-medium text-black">{{ $obat->name }}</p>
                                    @if($obat->description)
                                        <p class="text-xs text-gray-50 mt-0.5 line-clamp-2">
                                            {{ Str::limit($obat->description, 80) }}
                                        </p>
                                    @endif
                                    @if($obat->requires_prescription)
                                        {{-- Badge butuh resep --}}
                                        <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-semantic-warning/10 text-semantic-warning text-xs rounded-sm">
                                            <x-heroicon-o-document-text class="w-3 h-3" />
                                            Resep
                                        </span>
                                    @endif
                                </td>

                                {{-- Kategori obat --}}
                                <td class="p-4">
                                    <span class="text-sm text-black">{{ $obat->kategoriObat->name ?? '-' }}</span>
                                </td>

                                {{-- Harga satuan --}}
                                <td class="p-4 whitespace-nowrap">
                                    <span class="text-sm text-black">
                                        Rp {{ number_format($obat->price, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Stok aktif dari batch --}}
                                <td class="p-4">
                                    @php $stokAktif = $obat->batchStoks->sum('current_stock'); @endphp
                                    <span class="px-2 py-1 text-xs rounded-sm font-medium
                                        {{ $stokAktif <= ($obat->minimum_stock ?? 0)
                                            ? 'bg-semantic-danger/10 text-semantic-danger'
                                            : 'bg-semantic-success/10 text-semantic-success' }}">
                                        {{ $stokAktif }} unit
                                    </span>
                                </td>

                                {{-- Tombol aksi --}}
                                <td class="p-4">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Buka modal gambar --}}
                                        <button
                                            wire:click="openGambarModal({{ $obat->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                                        >
                                            <x-heroicon-o-photo class="w-3.5 h-3.5" />
                                            Gambar
                                        </button>
                                        {{-- Buka modal batch --}}
                                        <button
                                            wire:click="openBatchModal({{ $obat->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-black rounded-sm bg-black text-white hover:bg-black/80 transition-colors"
                                        >
                                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                            Batch
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <x-heroicon-o-archive-box class="w-10 h-10 text-gray-50 mx-auto mb-3" />
                                    <p class="text-gray-50">Tidak ada obat ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginasi hasil --}}
        <div class="mt-4">
            {{ $obats->links() }}
        </div>

    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Tambah Batch Stok                                      --}}
{{-- ============================================================ --}}
@if($showBatchModal)
    {{-- Overlay modal --}}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        wire:click="closeBatchModal"
    >
        {{-- Kartu modal --}}
        <div
            class="bg-white border border-oat rounded-lg w-full max-w-md mx-4 p-6 shadow-lg"
            wire:click.stop
        >
            {{-- Judul modal --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-normal text-black">Tambah Batch Stok</h2>
                <button
                    wire:click="closeBatchModal"
                    class="text-gray-50 hover:text-black transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4">

                {{-- Nomor batch --}}
                <div>
                    <label class="form-label">Nomor Batch</label>
                    <input
                        type="text"
                        wire:model="batchNumber"
                        placeholder="Contoh: BTH-2025-001"
                        class="form-input"
                    >
                    @error('batchNumber')
                        <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Stok awal batch --}}
                <div>
                    <label class="form-label">Stok Awal</label>
                    <input
                        type="number"
                        wire:model="initialStock"
                        placeholder="0"
                        min="1"
                        class="form-input"
                    >
                    @error('initialStock')
                        <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tanggal kedaluwarsa --}}
                <div>
                    <label class="form-label">Tanggal Kedaluwarsa</label>
                    <input
                        type="date"
                        wire:model="expirationDate"
                        class="form-input"
                    >
                    @error('expirationDate')
                        <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Tombol aksi modal --}}
            <div class="flex items-center gap-3 mt-6">
                <button
                    wire:click="closeBatchModal"
                    class="flex-1 px-4 py-2 text-sm border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                >
                    Batal
                </button>
                <button
                    wire:click="tambahBatch"
                    wire:loading.attr="disabled"
                    wire:target="tambahBatch"
                    class="flex-1 px-4 py-2 text-sm rounded-sm bg-black text-white hover:bg-black/80 transition-colors disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="tambahBatch">Simpan</span>
                    <span wire:loading wire:target="tambahBatch">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ============================================================ --}}
{{-- MODAL: Upload Gambar Obat                                     --}}
{{-- ============================================================ --}}
@if($showGambarModal)
    {{-- Overlay modal --}}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        wire:click="closeGambarModal"
    >
        {{-- Kartu modal --}}
        <div
            class="bg-white border border-oat rounded-lg w-full max-w-md mx-4 p-6 shadow-lg"
            wire:click.stop
        >
            {{-- Judul modal --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-normal text-black">Upload Gambar Obat</h2>
                <button
                    wire:click="closeGambarModal"
                    class="text-gray-50 hover:text-black transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4">

                {{-- Preview gambar live --}}
                @if($gambarObat)
                    <div class="flex justify-center">
                        <img
                            src="{{ $gambarObat->temporaryUrl() }}"
                            alt="Preview gambar"
                            class="w-40 h-40 object-cover rounded-lg border border-oat"
                        >
                    </div>
                @else
                    {{-- Placeholder sebelum upload --}}
                    <div class="flex justify-center">
                        <div class="w-40 h-40 rounded-lg border border-oat bg-cream flex flex-col items-center justify-center gap-2">
                            <x-heroicon-o-photo class="w-10 h-10 text-gray-50" />
                            <p class="text-xs text-gray-50">Belum ada gambar</p>
                        </div>
                    </div>
                @endif

                {{-- Input file gambar --}}
                <div>
                    <label class="form-label">Pilih Gambar</label>
                    <input
                        type="file"
                        wire:model="gambarObat"
                        accept="image/*"
                        class="form-input"
                    >
                    @error('gambarObat')
                        <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span>
                    @enderror
                    {{-- Indikator upload berjalan --}}
                    <div wire:loading wire:target="gambarObat" class="text-xs text-gray-50 mt-1">
                        Mengunggah gambar...
                    </div>
                </div>
            </div>

            {{-- Tombol aksi modal --}}
            <div class="flex items-center gap-3 mt-6">
                <button
                    wire:click="closeGambarModal"
                    class="flex-1 px-4 py-2 text-sm border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                >
                    Batal
                </button>
                <button
                    wire:click="simpanGambar"
                    wire:loading.attr="disabled"
                    wire:target="simpanGambar"
                    class="flex-1 px-4 py-2 text-sm rounded-sm bg-black text-white hover:bg-black/80 transition-colors disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="simpanGambar">Simpan</span>
                    <span wire:loading wire:target="simpanGambar">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
@endif
