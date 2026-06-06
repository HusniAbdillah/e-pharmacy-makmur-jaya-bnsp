<div class="min-h-screen bg-cream py-8">
    {{-- Leaflet.js CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

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

        {{-- Grid: Grafik & Peta Spasial --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Grafik Penjualan Bulanan --}}
            <div class="bg-white border border-oat rounded-lg p-5" wire:ignore>
                <h3 class="text-2xl font-normal text-black mb-1">Penjualan Bulanan</h3>
                <p class="text-sm text-gray-50 mb-4">Jumlah transaksi selesai per minggu (4 minggu terakhir)</p>
                <canvas id="salesChart" style="max-height: 280px;"></canvas>
            </div>

            {{-- Peta Spasial Gudang --}}
            <div class="bg-white border border-oat rounded-lg p-5" wire:ignore>
                <h3 class="text-2xl font-normal text-black mb-1">Peta Jaringan Gudang</h3>
                <p class="text-sm text-gray-50 mb-4">Lokasi gudang penyimpanan suplai obat Klinik Makmur Jaya</p>
                <div id="warehouseMap" style="height: 280px;" class="rounded-md border border-oat"></div>
            </div>
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

{{-- Leaflet.js JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
{{-- Chart.js via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    (function () {
        // 1. Inisialisasi Chart Penjualan
        var salesData = {!! $monthlySalesData !!};
        var ctx = document.getElementById('salesChart');
        if (ctx) {
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
                                    return ' ' + ctx.parsed.y + ' transaksi selesai';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Inisialisasi Peta Spasial Gudang (Leaflet.js)
        var mapContainer = document.getElementById('warehouseMap');
        if (mapContainer) {
            // Koordinat Jakarta Pusat
            var map = L.map('warehouseMap').setView([-6.2088, 106.8456], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Daftar gudang penyimpan
            var warehouses = [
                {
                    name: "Gudang Farmasi Pusat (Makmur Jaya)",
                    lat: -6.1824,
                    lng: 106.8291,
                    desc: "Pusat penyimpanan & distribusi utama obat resep & alat medis."
                },
                {
                    name: "Gudang Cabang Jakarta Barat",
                    lat: -6.1683,
                    lng: 106.7869,
                    desc: "Penyimpanan buffer stok obat bebas & suplemen wilayah barat."
                },
                {
                    name: "Gudang Cabang Jakarta Timur",
                    lat: -6.2250,
                    lng: 106.9000,
                    desc: "Pusat logistik pengemasan & peracikan sekunder."
                }
            ];

            warehouses.forEach(function (w) {
                L.marker([w.lat, w.lng]).addTo(map)
                    .bindPopup("<strong class='text-sm block mb-1'>" + w.name + "</strong><span class='text-xs text-gray-600'>" + w.desc + "</span>");
            });
        }
    })();
</script>
