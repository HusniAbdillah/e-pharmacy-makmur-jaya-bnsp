<div class="min-h-screen bg-cream py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul & tombol tambah --}}
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Manajemen Kategori</h1>
                <p class="text-gray-50">Kelola kategori pengelompokan obat</p>
            </div>
            <button wire:click="openModal()" class="btn-accent shrink-0">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Tambah Kategori
            </button>
        </div>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6 flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                {{ session('success') }}
            </div>
        @endif

        {{-- Pesan error --}}
        @if(session('error'))
            <div class="bg-semantic-danger/10 border border-semantic-danger/30 text-semantic-danger rounded-sm p-4 mb-6 flex items-center gap-2">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
                {{ session('error') }}
            </div>
        @endif

        {{-- Bar pencarian --}}
        <div class="bg-white border border-oat rounded-lg p-5 mb-6">
            <label class="form-label">Cari Kategori</label>
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-50" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Ketik nama kategori..."
                    class="form-input pl-9"
                >
            </div>
        </div>

        {{-- Tabel kategori --}}
        <div class="bg-white border border-oat rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cream border-b border-oat">
                        <tr class="text-left">
                            <th class="p-4 font-medium text-gray-50">Nama Kategori</th>
                            <th class="p-4 font-medium text-gray-50">Jumlah Obat</th>
                            <th class="p-4 font-medium text-gray-50 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @forelse($kategoris as $kategori)
                            <tr class="hover:bg-cream/50 transition-colors">

                                {{-- Nama kategori --}}
                                <td class="p-4 font-medium text-black">
                                    {{ $kategori->name }}
                                </td>

                                {{-- Jumlah obat terkait --}}
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 text-gray-50">
                                        <x-heroicon-o-cube class="w-4 h-4" />
                                        {{ $kategori->obats_count }} obat
                                    </span>
                                </td>

                                {{-- Tombol aksi --}}
                                <td class="p-4">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit kategori --}}
                                        <button
                                            wire:click="openModal({{ $kategori->id }})"
                                            class="btn-outline text-sm px-3 py-2"
                                        >
                                            <x-heroicon-o-pencil class="w-4 h-4 mr-1.5" />
                                            Edit
                                        </button>

                                        {{-- Hapus kategori --}}
                                        <button
                                            wire:click="hapus({{ $kategori->id }})"
                                            wire:confirm="Yakin ingin menghapus kategori ini?"
                                            class="inline-flex items-center justify-center px-3 py-2 bg-semantic-danger/10 text-semantic-danger text-sm font-normal rounded-sm border border-semantic-danger/30 hover:bg-semantic-danger/20 transition-colors"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4 mr-1.5" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- State kosong --}}
                            <tr>
                                <td colspan="3" class="p-12 text-center">
                                    <x-heroicon-o-tag class="w-10 h-10 text-gray-50 mx-auto mb-3" />
                                    <p class="text-gray-50 text-sm">Belum ada kategori ditemukan.</p>
                                    @if($search)
                                        <p class="text-gray-50 text-xs mt-1">Coba kata kunci lain.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal tambah/edit kategori --}}
    @if($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            wire:key="modal-kategori"
        >
            {{-- Overlay latar --}}
            <div
                class="absolute inset-0 bg-black/40"
                wire:click="closeModal()"
            ></div>

            {{-- Kartu modal --}}
            <div class="relative bg-white border border-oat rounded-lg w-full max-w-md shadow-lg">

                {{-- Header modal --}}
                <div class="flex items-center justify-between p-5 border-b border-oat">
                    <h2 class="text-xl font-medium text-black leading-none">
                        @if($editId)
                            Edit Kategori
                        @else
                            Tambah Kategori
                        @endif
                    </h2>
                    <button wire:click="closeModal()" class="text-gray-50 hover:text-black transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Isi form modal --}}
                <div class="p-5">
                    <div class="mb-5">
                        <label for="namaKategori" class="form-label">Nama Kategori</label>
                        <input
                            type="text"
                            id="namaKategori"
                            wire:model="namaKategori"
                            placeholder="Contoh: Antibiotik"
                            class="form-input"
                            maxlength="100"
                        >
                        {{-- Pesan validasi --}}
                        @error('namaKategori')
                            <span class="text-xs text-semantic-danger mt-1 flex items-center gap-1">
                                <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 shrink-0" />
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Tombol aksi modal --}}
                    <div class="flex items-center justify-end gap-3">
                        <button
                            wire:click="closeModal()"
                            type="button"
                            class="btn-outline"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="simpan()"
                            type="button"
                            class="btn-accent"
                        >
                            <x-heroicon-o-check class="w-4 h-4 mr-2" />
                            Simpan
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>
