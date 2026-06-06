<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #111111;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #111111;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20pt;
            margin: 0 0 5px 0;
            font-weight: normal;
            color: #111111;
        }
        .header p {
            margin: 3px 0;
            font-size: 9pt;
            color: #626260;
        }
        .meta {
            margin-bottom: 20px;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background-color: #111111;
            color: #ffffff;
        }
        table thead th {
            padding: 8px;
            text-align: left;
            font-weight: 600;
            font-size: 9pt;
        }
        table tbody tr {
            border-bottom: 1px solid #dedbd6;
        }
        table tbody tr:nth-child(even) {
            background-color: #faf9f6;
        }
        table tbody td {
            padding: 8px;
            font-size: 9pt;
        }
        table tfoot {
            border-top: 2px solid #111111;
        }
        table tfoot td {
            padding: 10px 8px;
            font-weight: bold;
            font-size: 10pt;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #7b7b78;
            border-top: 1px solid #dedbd6;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Klinik Makmur Jaya</h1>
        <p>Laporan Penjualan Obat</p>
    </div>

    <div class="meta">
        <p><strong>Tanggal Generate:</strong> {{ $tanggalGenerate }}</p>
        <p><strong>Status Transaksi:</strong> Selesai</p>
        <p><strong>Total Transaksi:</strong> {{ $transaksis->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 20%;">Invoice</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Pasien</th>
                <th style="width: 15%;" class="text-center">Jumlah Item</th>
                <th style="width: 15%;" class="text-right">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $transaksi)
                <tr>
                    <td>{{ $transaksi->id }}</td>
                    <td style="font-family: 'Courier New', monospace; font-size: 8pt;">{{ $transaksi->invoice_number }}</td>
                    <td>{{ $transaksi->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaksi->user->name }}</td>
                    <td class="text-center">{{ $transaksi->detailTransaksis->count() }}</td>
                    <td class="text-right">Rp {{ number_format($transaksi->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #7b7b78;">
                        Tidak ada transaksi selesai dalam periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">TOTAL PENDAPATAN:</td>
                <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem E-Pharmacy Makmur Jaya</p>
        <p>&copy; {{ now()->year }} Klinik Makmur Jaya. Semua hak dilindungi.</p>
    </div>
</body>
</html>
