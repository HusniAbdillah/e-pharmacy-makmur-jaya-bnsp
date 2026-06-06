<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul halaman --}}
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Manajemen Transaksi</h1>
            <p class="text-gray-50">Pantau dan kelola seluruh transaksi penjualan</p>
        </div>

        {{-- Pesan sukses/error --}}
        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6 flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                {{ session('success') }}
            </div>
        @endif

        {{-- Statistik ringkasan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white border border-oat rounded-lg p-5 shadow-sm">
                <span class="text-xs text-gray-50 uppercase tracking-wider block">Total Transaksi</span>
                <span class="text-3xl font-normal text-black">{{ $transaksis->total() }}</span>
            </div>
            <div class="bg-white border border-oat rounded-lg p-5 shadow-sm">
                <span class="text-xs text-gray-50 uppercase tracking-wider block">Total Nominal</span>
                <span class="text-3xl font-normal text-black">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Bar filter pencarian --}}
        <div class="bg-white border border-oat rounded-lg p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Cari invoice / pelanggan --}}
                <div>
                    <label class="form-label">Cari Transaksi</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-50" />
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="No. invoice atau nama pelanggan..."
                            class="form-input pl-9"
                        >
                    </div>
                </div>

                {{-- Filter status transaksi --}}
                <div>
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-input">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Tabel transaksi --}}
        <div class="bg-white border border-oat rounded-lg overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cream border-b border-oat">
                        <tr class="text-left">
                            <th class="p-4 font-medium text-gray-50">No. Invoice</th>
                            <th class="p-4 font-medium text-gray-50">Pelanggan</th>
                            <th class="p-4 font-medium text-gray-50">Tanggal</th>
                            <th class="p-4 font-medium text-gray-50">Total</th>
                            <th class="p-4 font-medium text-gray-50">Status</th>
                            <th class="p-4 font-medium text-gray-50">Kanal</th>
                            <th class="p-4 font-medium text-gray-50 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @forelse($transaksis as $transaksi)
                            <tr class="hover:bg-cream/50 transition-colors">

                                {{-- Nomor invoice --}}
                                <td class="p-4">
                                    <span class="font-mono text-xs text-black tracking-wide">
                                        {{ $transaksi->invoice_number }}
                                    </span>
                                </td>

                                {{-- Nama pelanggan --}}
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-user class="w-4 h-4 text-gray-50 shrink-0" />
                                        <span class="text-black">
                                            {{ $transaksi->user?->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Tanggal transaksi --}}
                                <td class="p-4 text-gray-50">
                                    <div class="flex items-center gap-1.5">
                                        <x-heroicon-o-calendar class="w-4 h-4 shrink-0" />
                                        {{ $transaksi->created_at->format('d M Y') }}
                                    </div>
                                </td>

                                {{-- Total harga --}}
                                <td class="p-4 font-medium text-black">
                                    Rp {{ number_format($transaksi->total_price, 0, ',', '.') }}
                                </td>

                                {{-- Badge status --}}
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-sm text-xs font-medium {{ $transaksi->status->badgeClass() }}">
                                        {{ $transaksi->status->label() }}
                                    </span>
                                </td>

                                {{-- Badge kanal --}}
                                <td class="p-4">
                                    @if($transaksi->is_online)
                                        {{-- Kanal online --}}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-sm text-xs font-medium bg-semantic-info/10 text-semantic-info border border-semantic-info/30">
                                            <x-heroicon-o-globe-alt class="w-3.5 h-3.5" />
                                            Online
                                        </span>
                                    @else
                                        {{-- Kanal konter --}}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-sm text-xs font-medium bg-gray-100 text-gray-60 border border-oat">
                                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5" />
                                            Konter
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="p-4 text-right">
                                    @if($transaksi->bisaDiubah())
                                        <button wire:click="editStatus({{ $transaksi->id }})" class="btn-outline text-xs px-2 py-1">
                                            Ubah Status
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-50">-</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            {{-- State kosong --}}
                            <tr>
                                <td colspan="7" class="p-12 text-center">
                                    <x-heroicon-o-document-text class="w-10 h-10 text-gray-50 mx-auto mb-3" />
                                    <p class="text-gray-50 text-sm">Belum ada transaksi ditemukan.</p>
                                    @if($search || $statusFilter)
                                        <p class="text-gray-50 text-xs mt-1">Coba ubah kata kunci atau filter.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginasi --}}
            @if($transaksis->hasPages())
                <div class="px-5 py-4 border-t border-oat">
                    {{ $transaksis->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- Modal ubah status --}}
    @if($showStatusModal && $selectedTransaksi)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" wire:click="closeStatusModal()"></div>
            <div class="relative bg-white border border-oat rounded-lg w-full max-w-md shadow-lg p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-medium text-black">Update Status Transaksi</h2>
                    <button wire:click="closeStatusModal()" class="text-gray-50 hover:text-black">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <div class="space-y-4 mb-6">
                    <div>
                        <span class="text-xs text-gray-50 uppercase tracking-wider block">No. Invoice</span>
                        <span class="font-mono text-sm font-medium text-black">{{ $selectedTransaksi->invoice_number }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-50 uppercase tracking-wider block">Pelanggan</span>
                        <span class="text-sm text-black">{{ $selectedTransaksi->user?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <label class="form-label">Pilih Status Baru</label>
                        <select wire:model="newStatus" class="form-input">
                            @foreach($statusOptions as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button wire:click="closeStatusModal()" class="btn-outline">Batal</button>
                    <button wire:click="updateStatus()" class="btn-accent">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
