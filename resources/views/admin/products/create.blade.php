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
    <h2>Tambah Data Barang</h2>

    <form action="/admin/products" method="POST" id="formTambah" style="background: white; padding: 30px; border-radius: 15px; max-width: 700px; margin-top: 20px; border: 1px solid #e5e7eb;">
        @csrf
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 2;">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
            </div>
        </div>
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">

        <div style="margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label style="font-weight: bold;">Daftar Varian Jenis & harga</label>
                <button type="button" onclick="addVariantRow()" style="padding: 5px 10px; background: #16a34a; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem;">
                    + Tambah Varian
                </button>
            </div>

            <div id="variant-container">
                <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" name="variants[0][keterangan]" placeholder="Contoh: Merah - XL" required style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <input type="number" name="variants[0][harga]" placeholder="Jumlah harga" value="0" required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <button type="button" onclick="removeRow(this)" style="padding: 10px; background: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer;">X</button>
                </div>
            </div>
        </div>

        <button type="submit" style="width: 100%; padding: 12px; background: {{ $appSetting->theme_color ?? '#4361ee' }}; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1.1rem;">Simpan Barang</button>
    </form>
</div>

<script>
    // --- FITUR SCAN BARCODE ---
    const inputKodeBarang = document.getElementById('kode_barang');
    const inputNamaBarang = document.getElementById('nama_barang');

    if (inputKodeBarang) {
        inputKodeBarang.addEventListener('keydown', function(e) {
            // Jika yang ditekan adalah tombol ENTER (oleh scanner)
            if (e.key === 'Enter') {
                e.preventDefault(); // Cegah form agar tidak langsung tersubmit
                
                // Pindahkan kursor ke input Nama Barang secara otomatis
                if (inputNamaBarang) {
                    inputNamaBarang.focus();
                }
            }
        });
    }

    // --- FITUR TAMBAH VARIAN ---
    let variantIndex = 1;

    function addVariantRow() {
        const container = document.getElementById('variant-container');
        const row = document.createElement('div');
        row.className = 'variant-row';
        row.style = 'display: flex; gap: 10px; margin-bottom: 10px;';
        
        row.innerHTML = `
            <input type="text" name="variants[${variantIndex}][keterangan]" placeholder="Warna / Ukuran" required style="flex: 2; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
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