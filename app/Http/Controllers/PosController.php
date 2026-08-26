<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with(['variants'])->latest()->get();
        return view('pos.index', compact('products'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

       $products = Product::with(['variants', 'category'])
            // 1. Cari berdasarkan nama_barang di tabel products
            ->where('nama_barang', 'LIKE', "%{$query}%")
            // 2. ATAU cari berdasarkan keterangan di tabel product_variants
            ->orWhereHas('variants', function ($q) use ($query) {
                $q->where('keterangan', 'LIKE', "%{$query}%");
            })
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan Transaksi Utama
            $transaction = transaction::create([
                'total_amount'   => $request->subtotal,
                'grand_total'    => $request->grand_total,
                'payment_method' => $request->payment_method,
                'amount_paid'    => $request->amount_paid,
                'change_amount'  => $request->change_amount,
            ]);

            // 2. Simpan Detail (Tanpa Qty dan Tanpa Potong Stok)
            foreach ($request->cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'nama_user' => $request->cashier_name ?? Auth::user()->name,
                    'product_id'     => $item['product_id'],
                    'variant_id'     => $item['variant_id'],
                    'price'          => $item['price'],
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'transaction_id' => $transaction->id 
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function receipt($id)
    {
        $transaction = transaction::with(['details.product', 'details.variant'])->findOrFail($id);
        
        return view('pos.receipt', compact('transaction'));
    }
}