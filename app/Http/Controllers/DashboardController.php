<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Total Pendapatan (Menjumlahkan kolom grand_total)
        $totalPendapatan = transaction::sum('grand_total');
        
        // 2. Hitung Total Transaksi
        $totalTransaksi = transaction::count();
        
        // 3. Hitung Total Produk
        $totalProduk = Product::count();
        
        // 4. Ambil 5 Transaksi Terbaru
        $transaksiTerbaru = transaction::latest()->take(5)->get();

        return view('welcome', compact(
            'totalPendapatan', 
            'totalTransaksi', 
            'totalProduk', 
            'transaksiTerbaru'
        ));
    }
}
