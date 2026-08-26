<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        // Menyesuaikan relasi yang dipanggil (category dihilangkan jika tidak ada di tabel)
        $products = Product::with(['variants'])->latest()->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'nullable|string',
            'variants'    => 'required|array', 
            // Validasi field di dalam array varian
            'variants.*.keterangan' => 'required|string',
            'variants.*.harga'      => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Data Produk Utama
            $product = Product::create([
                'nama_barang' => $request->nama_barang,
            ]);

            // 2. Simpan Varian dan Harga-nya
            foreach ($request->variants as $variant) {
                if (!empty($variant['keterangan']) && isset($variant['harga'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'keterangan' => $variant['keterangan'],
                        'harga'      => $variant['harga'], 
                    ]);
                }
            }

            DB::commit();
            return redirect('/admin/products')->with('success', 'Barang dan varian berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        if(empty($query)) {
            $products = Product::with(['variants'])->latest()->limit(20)->get();
        } else {
            // Disesuaikan: hanya mencari berdasarkan nama_barang karena kode_barang sudah dihapus
            $products = Product::with(['variants'])
                ->where('nama_barang', 'LIKE', "%{$query}%")
                ->get();
        }

        return response()->json($products);
    }

    public function edit(Product $product)
    {
        $product->load('variants'); 
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'nama_barang' => 'nullable|string',
            'variants'    => 'required|array',
            'variants.*.keterangan' => 'required|string',
            'variants.*.harga'      => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Data Utama 
            $product->update([
                'nama_barang' => $request->nama_barang,
            ]);

            $keptVariantIds = [];

            // 2. Loop Varian dari Form
            foreach ($request->variants as $variant) {
                if (!empty($variant['keterangan']) && isset($variant['harga'])) {

                    if (isset($variant['id'])) {
                        // Varian LAMA -> UPDATE
                        $existingVariant = \App\Models\ProductVariant::find($variant['id']);
                        if ($existingVariant) {
                            $existingVariant->update([
                                'keterangan' => $variant['keterangan'],
                                'harga'      => $variant['harga'],       
                            ]);
                            $keptVariantIds[] = $existingVariant->id; 
                        }
                    } else {
                        // Varian BARU -> CREATE
                        $newVariant = \App\Models\ProductVariant::create([
                            'product_id' => $product->id,
                            'keterangan' => $variant['keterangan'],
                            'harga'      => $variant['harga'],       
                        ]);
                        $keptVariantIds[] = $newVariant->id; 
                    }
                }
            }

            // 3. Hapus varian yang dihapus oleh user
            $product->variants()->whereNotIn('id', $keptVariantIds)->delete();

            DB::commit();
            return redirect('/admin/products')->with('success', 'Data barang berhasil diperbarui!');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return back()->withErrors('Gagal memperbarui data varian karena terikat dengan data lain.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        Product::destroy($product->id);
        return back()->with('success', 'Barang dihapus!');
    }
}