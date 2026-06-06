<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul Halaman --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Manajemen Supplier</h1>
                <p class="text-gray-50">Kelola master data rekanan pemasok (supplier) obat</p>
            </div>
            <div>
                <button
                    wire:click="openModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-black text-white hover:bg-black/80 rounded-sm transition-colors text-sm font-medium"
                >
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Tambah Supplier Baru
                </button>
            </div>
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
            <div>
                <label class="form-label">Cari Supplier</label>
                <div class="relative max-w-md">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-50" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Ketik nama atau email supplier..."
                        class="form-input pl-9"
                    >
                </div>
            </div>
        </div>

        {{-- Tabel daftar supplier --}}
        <div class="bg-white border border-oat rounded-lg overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cream border-b border-oat">
                        <tr class="text-left">
                            <th class="p-4 font-medium text-gray-50">Nama Supplier</th>
                            <th class="p-4 font-medium text-gray-50">Telepon</th>
                            <th class="p-4 font-medium text-gray-50">Email</th>
                            <th class="p-4 font-medium text-gray-50">Alamat</th>
                            <th class="p-4 font-medium text-gray-50">Total Produk</th>
                            <th class="p-4 font-medium text-gray-50 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-cream/50 transition-colors">
                                <td class="p-4 font-medium text-black">
                                    {{ $supplier->name }}
                                </td>
                                <td class="p-4 text-black">
                                    {{ $supplier->phone ?? '-' }}
                                </td>
                                <td class="p-4 text-black">
                                    {{ $supplier->email ?? '-' }}
                                </td>
                                <td class="p-4 text-black max-w-xs truncate">
                                    {{ $supplier->address ?? '-' }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-cream border border-oat text-xs rounded-sm font-medium text-black">
                                        {{ $supplier->obats_count }} obat
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            wire:click="openModal({{ $supplier->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-oat rounded-sm bg-white hover:bg-cream text-black transition-colors"
                                        >
                                            <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                            Edit
                                        </button>
                                        <button
                                            wire:click="hapus({{ $supplier->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus supplier ini?"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-semantic-danger/30 rounded-sm bg-semantic-danger/5 hover:bg-semantic-danger/10 text-semantic-danger transition-colors"
                                        >
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <x-heroicon-o-users class="w-10 h-10 text-gray-50 mx-auto mb-3" />
                                    <p class="text-gray-50">Tidak ada supplier ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginasi --}}
        <div class="mt-4">
            {{ $suppliers->links() }}
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Form Tambah / Edit Supplier                         --}}
    {{-- ============================================================ --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click="closeModal">
            <div class="bg-white border border-oat rounded-lg w-full max-w-md mx-4 p-6 shadow-lg" wire:click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-normal text-black">{{ $editId ? 'Edit Supplier' : 'Tambah Supplier Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-50 hover:text-black transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Nama Supplier --}}
                    <div>
                        <label class="form-label text-black font-medium text-xs mb-1 block">Nama Supplier <span class="text-semantic-danger">*</span></label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: PT. Kimia Farma"
                            class="form-input"
                        >
                        @error('name') <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Telepon --}}
                    <div>
                        <label class="form-label text-black font-medium text-xs mb-1 block">Nomor Telepon</label>
                        <input
                            type="text"
                            wire:model="phone"
                            placeholder="Contoh: 021-xxxxxxxx"
                            class="form-input"
                        >
                        @error('phone') <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="form-label text-black font-medium text-xs mb-1 block">Email</label>
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="Contoh: sales@kalbe.co.id"
                            class="form-input"
                        >
                        @error('email') <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="form-label text-black font-medium text-xs mb-1 block">Alamat Kantor/Gudang</label>
                        <textarea
                            wire:model="address"
                            placeholder="Ketik alamat lengkap..."
                            rows="3"
                            class="form-input"
                        ></textarea>
                        @error('address') <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-3 mt-6">
                    <button
                        wire:click="closeModal"
                        class="flex-1 px-4 py-2 text-sm border border-oat rounded-sm bg-white hover:bg-cream transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="simpan"
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2 text-sm rounded-sm bg-black text-white hover:bg-black/80 transition-colors disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
