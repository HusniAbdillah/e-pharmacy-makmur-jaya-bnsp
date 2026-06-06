<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class LaporanPenjualanController extends Controller
{
    /**
     * Generate PDF laporan penjualan selesai
     */
    public function exportPdf(): Response
    {
        $transaksis = Transaksi::with(['user', 'detailTransaksis.obat'])
            ->where('status', 'selesai')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPendapatan = $transaksis->sum('total_price');

        $pdf = Pdf::loadView('pdf.laporan-penjualan', [
            'transaksis'       => $transaksis,
            'totalPendapatan'  => $totalPendapatan,
            'tanggalGenerate'  => now()->format('d F Y H:i'),
        ]);

        return $pdf->download('laporan-penjualan-' . now()->format('Ymd-His') . '.pdf');
    }
}
