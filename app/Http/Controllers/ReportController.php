<?php

namespace App\Http\Controllers;

use App\Exports\PenjualanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction; // Pastikan model Transaction di-import (ganti dari TransactionDetail)
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // Fungsi bantuan untuk mengambil dan memfilter data
    private function getFilteredData(Request $request)
    {
        // Ubah query utama ke model Transaction, dan muat relasi details & product
        // Tambahkan relasi 'user' jika nama kasir diambil dari tabel users
        $query = Transaction::with(['details.product'])->latest();
        
        $filter = $request->filter ?? 'today'; // Default hari ini
        $title = 'Laporan Penjualan';

        if ($filter == 'today') {
            $query->whereDate('created_at', Carbon::today());
            $title = 'Laporan Penjualan Hari Ini (' . Carbon::today()->format('d M Y') . ')';
        } elseif ($filter == 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $title = 'Laporan Penjualan Minggu Ini';
        } elseif ($filter == 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
            $title = 'Laporan Penjualan Bulan ' . Carbon::now()->translatedFormat('F Y');
        } elseif ($filter == 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
            $title = 'Laporan Penjualan Tahun ' . Carbon::now()->year;
        } elseif ($filter == 'custom') {
            if ($request->start_date && $request->end_date) {
                $start = $request->start_date . ' 00:00:00';
                $end = $request->end_date . ' 23:59:59';
                $query->whereBetween('created_at', [$start, $end]);
                $title = 'Laporan Penjualan: ' . $request->start_date . ' s/d ' . $request->end_date;
            }
        }

        $transactions = $query->get();

        // Hitung Total Laba / Omzet
        $totalOmzet = 0;

        foreach ($transactions as $t) {
            // Gunakan kolom total dari tabel transaksi jika ada (misal: total_price atau total)
            // Jika tidak ada, kita kalkulasi dari harga * qty di detailnya
            $omzet = $t->total_price ?? $t->total ?? $t->details->sum(function($d) {
                return $d->price;
            });
            
            $totalOmzet += $omzet;
        }

        // Return 'transactions', bukan 'details'
        return compact('transactions', 'totalOmzet', 'filter', 'title', 'request');
    }

    // Fungsi menampilkan halaman web
    public function index(Request $request)
    {
        $data = $this->getFilteredData($request);
        return view('admin.report.index', $data);
    }

    // Fungsi Export ke PDF
    public function exportPdf(Request $request)
    {
        $data = $this->getFilteredData($request);
        
        // Load view khusus PDF
        $pdf = Pdf::loadView('admin.report.pdf', $data);
        
        // Unduh file PDF
        return $pdf->download('Laporan_Penjualan_' . date('Ymd') . '.pdf');
    }

    // Fungsi Export ke Excel
    public function exportExcel(Request $request)
    {
        $data = $this->getFilteredData($request);
        
        // Lempar $transactions DAN $title ke dalam PenjualanExport
        return Excel::download(new PenjualanExport($data['transactions'], $data['title']), 'Laporan_Penjualan_' . date('Ymd') . '.xlsx');
    }
}