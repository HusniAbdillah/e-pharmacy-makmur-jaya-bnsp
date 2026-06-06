<div class="min-h-screen bg-cream py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Checkout</h1>
            <p class="text-gray-50">Konfirmasi pesanan Anda</p>
        </div>

        @if(session('error'))
            <div class="bg-semantic-danger/10 border border-semantic-danger/30 text-semantic-danger rounded-sm p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(empty($cart))
            <div class="bg-white border border-oat rounded-lg p-12 text-center">
                <p class="text-gray-50 text-lg mb-4">Keranjang kosong. Tidak ada yang perlu di-checkout.</p>
                <a href="{{ route('katalog') }}" class="btn-dark">Kembali ke Katalog</a>
            </div>
        @else
            {{-- Ringkasan Pesanan --}}
            <div class="bg-white border border-oat rounded-lg p-5 mb-6">
                <h3 class="text-2xl font-normal text-black mb-4">Ringkasan Pesanan</h3>
                <table class="w-full text-sm">
                    <thead class="border-b border-oat">
                        <tr class="text-left">
                            <th class="pb-3 font-medium">Produk</th>
                            <th class="pb-3 font-medium text-center">Qty</th>
                            <th class="pb-3 font-medium text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @foreach($cart as $item)
                            <tr>
                                <td class="py-3">
                                    {{ $item['name'] }}
                                    @if(isset($item['requires_prescription']) && $item['requires_prescription'])
                                        <span class="ml-1 text-xs bg-semantic-warning/10 text-semantic-warning border border-semantic-warning/30 px-1 rounded-sm">Resep</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">{{ $item['quantity'] }}</td>
                                <td class="py-3 text-right">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-black">
                        <tr>
                            <td colspan="2" class="pt-4 text-lg font-medium">Total Pembayaran</td>
                            <td class="pt-4 text-right text-2xl font-normal text-black">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Upload Resep (jika ada obat butuh resep) --}}
            @if($butuhResep)
                <div class="bg-semantic-warning/10 border border-semantic-warning/30 rounded-lg p-5 mb-6">
                    <h3 class="text-lg font-medium text-black mb-2 flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-semantic-warning" />
                        Upload Resep Dokter
                    </h3>
                    <p class="text-sm text-gray-50 mb-4">
                        Pesanan Anda mengandung obat keras yang memerlukan resep dokter.
                        Upload file resep (JPG, PNG, atau PDF, maks 2 MB).
                    </p>
                    <div>
                        <label class="form-label">File Resep <span class="text-semantic-danger">*</span></label>
                        <input type="file"
                               wire:model="resep"
                               accept=".jpg,.jpeg,.png,.pdf"
                               class="form-input">
                        @error('resep')
                            <span class="text-xs text-semantic-danger mt-1 block">{{ $message }}</span>
                        @enderror

                        {{-- Preview jika gambar --}}
                        @if($resep && in_array($resep->getClientOriginalExtension(), ['jpg','jpeg','png']))
                            <img src="{{ $resep->temporaryUrl() }}"
                                 alt="Preview resep"
                                 class="mt-3 h-32 object-contain border border-oat rounded-sm">
                        @endif
                    </div>
                </div>
            @endif

            {{-- Konfirmasi & Bayar --}}
            <div class="bg-white border border-oat rounded-lg p-5">
                <p class="text-sm text-gray-50 mb-4">
                    Dengan melanjutkan, Anda menyetujui bahwa pesanan akan diproses secara otomatis.
                    Stok akan dipotong menggunakan sistem FIFO (kadaluarsa terdekat terlebih dahulu).
                </p>
                <button wire:click="prosesPembayaran"
                        wire:loading.attr="disabled"
                        class="btn-accent w-full flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="prosesPembayaran">
                        Konfirmasi Pembayaran
                    </span>
                    <span wire:loading wire:target="prosesPembayaran">
                        Memproses...
                    </span>
                </button>
            </div>
        @endif
    </div>
</div>
