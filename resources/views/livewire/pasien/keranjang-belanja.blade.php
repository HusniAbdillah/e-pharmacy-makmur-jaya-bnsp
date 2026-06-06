<div class="min-h-screen bg-cream py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Keranjang Belanja</h1>
            <p class="text-gray-50">Tinjau pesanan Anda sebelum checkout</p>
        </div>

        @if(session('success'))
            <div class="bg-semantic-success/10 border border-semantic-success/30 text-semantic-success rounded-sm p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(empty($cart))
            <div class="bg-white border border-oat rounded-lg p-12 text-center">
                <p class="text-gray-50 text-lg mb-4">Keranjang Anda kosong.</p>
                <a href="{{ route('katalog') }}" class="btn-dark">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="bg-white border border-oat rounded-lg p-5 mb-6">
                <table class="w-full">
                    <thead class="border-b border-oat">
                        <tr class="text-left">
                            <th class="pb-3 font-medium">Produk</th>
                            <th class="pb-3 font-medium text-right">Harga</th>
                            <th class="pb-3 font-medium text-center">Jumlah</th>
                            <th class="pb-3 font-medium text-right">Subtotal</th>
                            <th class="pb-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-oat">
                        @foreach($cart as $obatId => $item)
                            <tr>
                                <td class="py-4">
                                    <p class="font-medium text-black">{{ $item['name'] }}</p>
                                </td>
                                <td class="py-4 text-right text-gray-50">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </td>
                                <td class="py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="decrement({{ $obatId }})" class="w-8 h-8 bg-oat text-black rounded-sm hover:bg-black hover:text-white transition">
                                            −
                                        </button>
                                        <span class="w-12 text-center font-medium">{{ $item['quantity'] }}</span>
                                        <button wire:click="increment({{ $obatId }})" class="w-8 h-8 bg-oat text-black rounded-sm hover:bg-black hover:text-white transition">
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 text-right font-medium text-black">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 text-right">
                                    <button wire:click="remove({{ $obatId }})" class="text-semantic-danger hover:underline text-sm">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white border border-oat rounded-lg p-5">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-2xl font-normal text-black">Total</p>
                    <p class="text-4xl font-normal text-black">Rp {{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <div class="flex gap-4">
                    <button wire:click="clearCart" class="btn-outline flex-1">
                        Kosongkan Keranjang
                    </button>
                    <a href="{{ route('checkout') }}" class="btn-accent flex-1 text-center">
                        Lanjut Pembayaran
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
