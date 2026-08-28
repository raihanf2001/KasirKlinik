<?php

namespace App\Http\Controllers;

use App\Exports\PenjualanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // Fungsi bantuan untuk mengambil dan memfilter data
    private function getFilteredData(Request $request)
    {
        $query = TransactionDetail::with(['product', 'transaction'])->latest();
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

        $details = $query->get();

        // Hitung Total Laba
        $totalOmzet = 0;

        foreach ($details as $d) {
            $omzet = $d->price;
            
            $totalOmzet += $omzet;
        }

        return compact('details', 'totalOmzet', 'filter', 'title', 'request');
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
        
        // Load view khusus PDF (tanpa CSS kompleks)
        $pdf = Pdf::loadView('admin.report.pdf', $data);
        
        // Unduh file PDF
        return $pdf->download('Laporan_Penjualan_' . date('Ymd') . '.pdf');
    }
     public function exportExcel(Request $request)
    {
        $data = $this->getFilteredData($request);
        // Lempar $details DAN $title ke dalam PenjualanExport
        return Excel::download(new PenjualanExport($data['details'], $data['title']), 'Laporan_Penjualan_' . date('Ymd') . '.xlsx');
    }
}
