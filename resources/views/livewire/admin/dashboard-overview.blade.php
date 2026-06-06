<div class="min-h-screen bg-cream py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-5xl font-normal leading-none tracking-tight text-black mb-2">Dashboard Admin</h1>
            <p class="text-gray-50">Ringkasan sistem E-Pharmacy Makmur Jaya</p>
        </div>

        {{-- Metrik Utama (4 Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white border border-oat rounded-lg p-5">
                <p class="text-sm text-gray-50 mb-1">Total Pengguna</p>
                <p class="text-4xl font-normal text-black">{{ number_format($totalPengguna) }}</p>
            </div>
            <div class="bg-white border border-oat rounded-lg p-5">
                <p class="text-sm text-gray-50 mb-1">Total Obat</p>
                <p class="text-4xl font-normal text-black">{{ number_format($totalObat) }}</p>
            </div>
            <div class="bg-white border border-oat rounded-lg p-5">
                <p class="text-sm text-gray-50 mb-1">Total Transaksi</p>
                <p class="text-4xl font-normal text-black">{{ number_format($totalTransaksi) }}</p>
            </div>
            <div class="bg-white border border-oat rounded-lg p-5">
                <p class="text-sm text-gray-50 mb-1">Pendapatan Bulan Ini</p>
                <p class="text-4xl font-normal text-black">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Grafik Penjualan Bulanan --}}
        <div class="bg-white border border-oat rounded-lg p-5 mb-8" wire:ignore>
            <h3 class="text-2xl font-normal text-black mb-4">Penjualan Bulanan</h3>
            <p class="text-sm text-gray-50 mb-4">Jumlah transaksi per minggu (bulan ini)</p>
            <canvas id="salesChart" style="max-height: 280px;"></canvas>
        </div>

        {{-- Alert: Batch Kadaluarsa --}}
        @if($batchKadaluarsa->isNotEmpty())
            <div class="bg-semantic-danger/10 border border-semantic-danger rounded-lg p-5 mb-6">
                <h3 class="text-xl font-normal text-semantic-danger mb-4 flex items-center gap-2">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5" />
                    Batch Kadaluarsa ({{ $batchKadaluarsa->count() }})
                </h3>
                <p class="text-sm text-semantic-danger mb-4">
                    Batch berikut sudah melewati tanggal kadaluarsa dan masih memiliki stok. Segera lakukan karantina atau pemusnahan.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-semantic-danger/20">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Batch</th>
                                <th class="p-3 font-medium">Kadaluarsa</th>
                                <th class="p-3 font-medium">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-oat">
                            @foreach($batchKadaluarsa->take(10) as $batch)
                                <tr>
                                    <td class="p-3">{{ $batch->obat->name }}</td>
                                    <td class="p-3 font-mono text-xs">{{ $batch->batch_number }}</td>
                                    <td class="p-3 text-semantic-danger font-medium">{{ $batch->expiration_date->format('d/m/Y') }}</td>
                                    <td class="p-3 text-semantic-danger font-medium">{{ $batch->current_stock }} unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Alert: Batch Hampir Kadaluarsa --}}
        @if($batchHampirKadaluarsa->isNotEmpty())
            <div class="bg-semantic-warning/10 border border-semantic-warning rounded-lg p-5 mb-6">
                <h3 class="text-xl font-normal text-semantic-warning mb-4 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5" />
                    Batch Hampir Kadaluarsa ({{ $batchHampirKadaluarsa->count() }})
                </h3>
                <p class="text-sm text-semantic-warning mb-4">
                    Batch berikut akan kadaluarsa dalam 30 hari ke depan. Prioritaskan penjualan.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-semantic-warning/20">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Batch</th>
                                <th class="p-3 font-medium">Kadaluarsa</th>
                                <th class="p-3 font-medium">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-oat">
                            @foreach($batchHampirKadaluarsa->take(10) as $batch)
                                <tr>
                                    <td class="p-3">{{ $batch->obat->name }}</td>
                                    <td class="p-3 font-mono text-xs">{{ $batch->batch_number }}</td>
                                    <td class="p-3 text-semantic-warning font-medium">{{ $batch->expiration_date->format('d/m/Y') }}</td>
                                    <td class="p-3">{{ $batch->current_stock }} unit</td>
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
                <h3 class="text-2xl font-normal text-black mb-4 flex items-center gap-2">
                    <x-heroicon-o-archive-box-x-mark class="w-6 h-6 text-semantic-danger" />
                    Obat Stok Kritis ({{ $obatStokKritis->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/50">
                            <tr class="text-left">
                                <th class="p-3 font-medium">Obat</th>
                                <th class="p-3 font-medium">Kategori</th>
                                <th class="p-3 font-medium">Stok Saat Ini</th>
                                <th class="p-3 font-medium">Batas Minimum</th>
                                <th class="p-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @foreach($obatStokKritis as $obat)
                                <tr>
                                    <td class="p-3">{{ $obat->name }}</td>
                                    <td class="p-3">{{ $obat->kategoriObat->name }}</td>
                                    <td class="p-3 text-semantic-danger font-medium">{{ $obat->total_stok_aktif ?? 0 }} unit</td>
                                    <td class="p-3">{{ $obat->minimum_stock }} unit</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 bg-semantic-danger/10 text-semantic-danger text-xs rounded-sm">KRITIS</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-semantic-success/10 border border-semantic-success rounded-lg p-5">
                <p class="text-semantic-success flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    Semua stok obat dalam kondisi aman (di atas batas minimum).
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Chart.js via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function () {
        // Data penjualan dari server
        var salesData = {!! $monthlySalesData !!};

        var ctx = document.getElementById('salesChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    label: 'Transaksi Selesai',
                    data: salesData,
                    backgroundColor: 'rgba(0, 0, 0, 0.08)',
                    borderColor: 'rgba(0, 0, 0, 0.7)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.parsed.y + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    })();
</script>
