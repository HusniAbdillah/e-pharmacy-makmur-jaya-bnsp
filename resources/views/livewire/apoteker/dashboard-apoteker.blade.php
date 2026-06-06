<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Dashboard Apoteker</h1>
            <p class="text-gray-50">Pemantauan stok dan kadaluarsa obat</p>
        </div>

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

        {{-- Verifikasi Resep --}}
        @if($transaksiMenungguVerifikasi->isNotEmpty())
            <div class="bg-white border border-oat rounded-lg p-5 mb-6">
                <div class="flex items-start gap-3 mb-4">
                    <x-heroicon-o-document-check class="w-6 h-6 text-black flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-2xl font-normal text-black mb-1">
                            Verifikasi Resep ({{ $transaksiMenungguVerifikasi->count() }})
                        </h3>
                        <p class="text-sm text-gray-50">
                            Transaksi berikut memerlukan verifikasi apoteker.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/50 border-b border-oat">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Invoice</th>
                                <th class="p-3 font-medium">Pasien</th>
                                <th class="p-3 font-medium">Tanggal</th>
                                <th class="p-3 font-medium text-right">Total</th>
                                <th class="p-3 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @foreach($transaksiMenungguVerifikasi as $transaksi)
                                <tr>
                                    <td class="p-3 font-mono text-xs">{{ $transaksi->invoice_number }}</td>
                                    <td class="p-3">{{ $transaksi->user->name }}</td>
                                    <td class="p-3">{{ $transaksi->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="p-3 text-right">Rp {{ number_format($transaksi->total_price, 0, ',', '.') }}</td>
                                    <td class="p-3 text-center">
                                        <button wire:click="setujuiResep({{ $transaksi->id }})" class="inline-flex items-center gap-1 px-3 py-1 bg-semantic-success text-white rounded-sm hover:scale-105 transition">
                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                            Setujui
                                        </button>
                                    </td>
                                </tr>
            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Alert Kadaluarsa --}}
        @if($batchKadaluarsa->isNotEmpty())
            <div class="bg-semantic-danger/10 border border-semantic-danger rounded-lg p-5 mb-6">
                <div class="flex items-start gap-3 mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-semantic-danger flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-xl font-normal text-semantic-danger mb-1">
                            Batch Kadaluarsa ({{ $batchKadaluarsa->count() }})
                        </h3>
                        <p class="text-sm text-semantic-danger">
                            Batch berikut sudah melewati tanggal kadaluarsa. Segera karantina atau musnahkan.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-semantic-danger/20">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Batch</th>
                                <th class="p-3 font-medium">Kadaluarsa</th>
                                <th class="p-3 font-medium text-right">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-oat">
                            @foreach($batchKadaluarsa->take(10) as $batch)
                                <tr>
                                    <td class="p-3">{{ $batch->obat->name }}</td>
                                    <td class="p-3 font-mono text-xs">{{ $batch->batch_number }}</td>
                                    <td class="p-3 text-semantic-danger font-medium">
                                        {{ $batch->expiration_date->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3 text-right text-semantic-danger font-medium">
                                        {{ $batch->current_stock }} unit
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Alert Hampir Kadaluarsa --}}
        @if($batchHampirKadaluarsa->isNotEmpty())
            <div class="bg-semantic-warning/10 border border-semantic-warning rounded-lg p-5 mb-6">
                <div class="flex items-start gap-3 mb-4">
                    <x-heroicon-o-clock class="w-6 h-6 text-semantic-warning flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-xl font-normal text-semantic-warning mb-1">
                            Batch Hampir Kadaluarsa ({{ $batchHampirKadaluarsa->count() }})
                        </h3>
                        <p class="text-sm text-semantic-warning">
                            Batch berikut akan kadaluarsa dalam 90 hari. Prioritaskan penjualan FIFO.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-semantic-warning/20">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Kategori</th>
                                <th class="p-3 font-medium">Batch</th>
                                <th class="p-3 font-medium">Kadaluarsa</th>
                                <th class="p-3 font-medium text-right">Sisa Stok</th>
                                <th class="p-3 font-medium text-center">Sisa Hari</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-oat">
                            @foreach($batchHampirKadaluarsa as $batch)
                                <tr>
                                    <td class="p-3">{{ $batch->obat->name }}</td>
                                    <td class="p-3">{{ $batch->obat->kategoriObat->name }}</td>
                                    <td class="p-3 font-mono text-xs">{{ $batch->batch_number }}</td>
                                    <td class="p-3 text-semantic-warning font-medium">
                                        {{ $batch->expiration_date->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3 text-right">{{ $batch->current_stock }} unit</td>
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-1 bg-semantic-warning/10 text-semantic-warning text-xs rounded-sm whitespace-nowrap">
                                            @php
                                                $diff = now()->diff($batch->expiration_date);
                                                $days = $diff->days;
                                                $hours = $diff->h;
                                            @endphp
                                            @if($days > 0)
                                                {{ $days }} hari {{ $hours }} jam
                                            @else
                                                {{ $hours }} jam
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Obat Stok Kritis --}}
        @if($obatStokKritis->isNotEmpty())
            <div class="bg-white border border-oat rounded-lg p-5">
                <div class="flex items-start gap-3 mb-4">
                    <x-heroicon-o-archive-box-x-mark class="w-6 h-6 text-semantic-danger flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-2xl font-normal text-black mb-1">
                            Obat Stok Kritis ({{ $obatStokKritis->count() }})
                        </h3>
                        <p class="text-sm text-gray-50">
                            Obat berikut memiliki stok di bawah batas minimum.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/50 border-b border-oat">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Kategori</th>
                                <th class="p-3 font-medium text-right">Stok Saat Ini</th>
                                <th class="p-3 font-medium text-right">Batas Minimum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @foreach($obatStokKritis as $obat)
                                <tr>
                                    <td class="p-3">{{ $obat->name }}</td>
                                    <td class="p-3">{{ $obat->kategoriObat->name }}</td>
                                    <td class="p-3 text-right text-semantic-danger font-medium">
                                        {{ $obat->total_stok_aktif ?? 0 }} unit
                                    </td>
                                    <td class="p-3 text-right">{{ $obat->minimum_stock }} unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-semantic-success/10 border border-semantic-success rounded-lg p-5 flex items-center gap-3">
                <x-heroicon-o-check-circle class="w-6 h-6 text-semantic-success flex-shrink-0" />
                <p class="text-semantic-success">Semua stok obat dalam kondisi aman (di atas batas minimum).</p>
            </div>
        @endif
    </div>
</div>
