<div class="min-h-screen bg-cream py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul halaman --}}
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Transaksi Saya</h1>
            <p class="text-gray-50">Riwayat pembelian obat dan status resep Anda</p>
        </div>

        {{-- Daftar transaksi --}}
        <div class="space-y-6">
            @forelse($transaksis as $transaksi)
                <div class="bg-white border border-oat rounded-lg overflow-hidden shadow-sm">
                    
                    {{-- Header transaksi --}}
                    <div class="bg-cream border-b border-oat p-4 flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <span class="text-xs text-gray-50 uppercase tracking-wider block">No. Invoice</span>
                            <span class="font-mono text-sm font-medium text-black">{{ $transaksi->invoice_number }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-50 uppercase tracking-wider block">Tanggal</span>
                            <span class="text-sm text-black">{{ $transaksi->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-50 uppercase tracking-wider block">Total</span>
                            <span class="text-sm font-medium text-black">Rp {{ number_format($transaksi->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-sm text-xs font-medium {{ $transaksi->status->badgeClass() }}">
                                {{ $transaksi->status->label() }}
                            </span>
                        </div>
                    </div>

                    {{-- Detail item --}}
                    <div class="p-4">
                        <h3 class="text-sm font-medium text-black mb-3">Item Pembelian</h3>
                        <div class="divide-y divide-oat">
                            @foreach($transaksi->detailTransaksis as $detail)
                                <div class="py-3 flex justify-between items-center text-sm">
                                    <div>
                                        <p class="font-medium text-black">{{ $detail->obat->name ?? 'Obat Terhapus' }}</p>
                                        <p class="text-xs text-gray-50 mt-0.5">
                                            {{ $detail->quantity }} unit x Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <span class="font-medium text-black">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Link Resep --}}
                    @if($transaksi->resep_path)
                        <div class="px-4 py-3 bg-cream border-t border-oat text-right">
                            <a href="{{ asset('storage/' . $transaksi->resep_path) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs text-gray-50 hover:text-black transition-colors">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                Lihat Resep Dokter
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                {{-- State kosong --}}
                <div class="bg-white border border-oat rounded-lg p-12 text-center shadow-sm">
                    <x-heroicon-o-archive-box class="w-12 h-12 text-gray-50 mx-auto mb-3" />
                    <p class="text-gray-50 text-sm">Belum ada transaksi.</p>
                    <a href="{{ route('katalog') }}" wire:navigate class="btn-accent inline-block mt-4">
                        Mulai Belanja
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $transaksis->links() }}
        </div>
    </div>
</div>
