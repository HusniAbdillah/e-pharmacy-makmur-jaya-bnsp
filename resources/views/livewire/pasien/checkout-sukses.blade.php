<div class="min-h-screen bg-cream py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-oat rounded-lg p-12 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-semantic-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-semantic-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-normal text-black mb-2">Pesanan Berhasil!</h1>
                <p class="text-gray-50">Terima kasih atas pesanan Anda</p>
            </div>

            @if(session('invoice'))
                <div class="bg-cream rounded-sm p-4 mb-6">
                    <p class="text-sm text-gray-50 mb-1">Nomor Invoice</p>
                    <p class="text-2xl font-medium text-black font-mono">{{ session('invoice') }}</p>
                </div>
            @endif

            <p class="text-sm text-gray-50 mb-6">
                Pesanan Anda sedang diproses. Stok akan dikurangi secara otomatis menggunakan sistem FIFO.
                Anda dapat melacak status pesanan di halaman riwayat transaksi.
            </p>

            <div class="flex gap-4">
                <a href="{{ route('katalog') }}" class="btn-outline flex-1">
                    Kembali ke Katalog
                </a>
                <a href="{{ route('katalog') }}" class="btn-dark flex-1">
                    Lihat Riwayat Pesanan
                </a>
            </div>
        </div>
    </div>
</div>
