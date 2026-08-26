@extends('layout')
@section('container')
@php
        $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'app_name' => 'KASIR PINTAR',
            'phone' => '-',
            'address' => '-'
        ]);
    @endphp 
<div class="content-area" style="padding: 30px;">
    <h2>Edit Data Barang: {{ $product->nama_barang }}</h2>

    @if($errors->any())
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px solid #fecaca;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/admin/products/{{ $product->id }}" method="POST" id="formEdit" style="background: white; padding: 30px; border-radius: 15px; max-width: 700px; margin-top: 20px; border: 1px solid #e5e7eb;">
        @csrf
        @method('PUT') 
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 2;">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" value="{{ $product->nama_barang }}" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
            </div>
        </div>
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">

        <div style="margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label style="font-weight: bold;">Kelola Varian & Harga</label>
                <button type="button" onclick="addVariantRow()" style="padding: 5px 10px; background: #16a34a; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    + Tambah Varian Baru
                </button>
            </div>

            <div id="variant-container">
                @foreach($product->variants as $index => $v)
                <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $v->id }}">
                    
                    <input type="text" name="variants[{{ $index }}][keterangan]" value="{{ $v->keterangan }}" placeholder="Warna / Ukuran" required style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <input type="number" name="variants[{{ $index }}][harga]" value="{{ $v->harga }}" placeholder="harga" required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <button type="button" onclick="removeRow(this)" style="padding: 10px; background: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer;">X</button>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="/admin/products" style="flex: 1; padding: 12px; text-align: center; background: #f3f4f6; color: #374151; border-radius: 8px; text-decoration: none; font-weight: 600;">Batal</a>
            <button type="submit" style="flex: 2; padding: 12px; background: {{ $appSetting->theme_color ?? '#4361ee' }}; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1.1rem;">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
    // --- FITUR SCAN BARCODE (Mencegah submit otomatis) ---
    const inputKodeBarang = document.getElementById('kode_barang');
    const inputNamaBarang = document.getElementById('nama_barang');

    if (inputKodeBarang) {
        inputKodeBarang.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                if (inputNamaBarang) inputNamaBarang.focus();
            }
        });
    }

    // --- FITUR VARIAN DINAMIS ---
    let variantIndex = {{ $product->variants->count() }}; 

    function addVariantRow() {
        const container = document.getElementById('variant-container');
        const row = document.createElement('div');
        row.className = 'variant-row';
        row.style = 'display: flex; gap: 10px; margin-bottom: 10px;';
        
        row.innerHTML = `
            <input type="text" name="variants[${variantIndex}][keterangan]" placeholder="Contoh: Biru - M" required style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="number" name="variants[${variantIndex}][harga]" placeholder="harga" value="0" required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <button type="button" onclick="removeRow(this)" style="padding: 10px; background: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer;">X</button>
        `;
        
        container.appendChild(row);
        variantIndex++;
    }

    function removeRow(button) {
        button.parentElement.remove();
    }
</script>
@endsection