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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="page-title">Data Barang & Stok</h2>
        <a href="/admin/products/create" style="padding: 10px 20px; background: {{ $appSetting->sidebar_color ?? '#4361ee' }}; color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">
            + Tambah Barang Baru
        </a>
    </div>
    <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="position: relative; flex: 1; max-width: 500px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a3aed1;"></i>
                
                <input type="text" id="searchInput" placeholder="Ketik nama atau kode barang untuk mencari..." 
                    style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; transition: all 0.3s; font-size: 0.95rem;"
                    onfocus="this.style.borderColor='#4361ee'; this.style.boxShadow='0 0 0 3px rgba(67, 97, 238, 0.1)';"
                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
            </div>
            
            <div style="color: #6b7280; font-size: 0.85rem;">
                <i class="fa-solid fa-circle-info"></i> Mencari secara otomatis...
            </div>
        </div>
    </div>
    @if(session('success'))
        <div id="success-alert" style="background: #dcfce7; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="table-container" style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #e5e7eb; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                    <th style="padding: 15px;">No</th>
                    <th style="padding: 15px;">Nama Barang</th>
                    <th style="padding: 15px;">Varian & Harga</th>
                    <th style="padding: 15px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @forelse($products as $p)
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 15px; font-weight: 500;">{{ $loop->iteration }}</td>
                    <td style="padding: 15px; font-weight: 500;">{{ $p->nama_barang }}</td>
                    <td style="padding: 15px;">
                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                            @forelse($p->variants as $v)
                                <span style="background: {{ $v->stok > 0 ? '#e0e7ff' : '#fee2e2' }}; color: {{ $v->stok > 0 ? '#3730a3' : '#991b1b' }}; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                                    {{ $v->keterangan }} : <b>Rp {{ number_format($v->harga, 0, ',', '.') }}</b>
                                </span>
                            @empty
                                <span style="color: #9ca3af; font-size: 0.85rem;">Tidak ada varian</span>
                            @endforelse
                        </div>
                    </td>
                    <td style="padding: 15px; display: flex; justify-content: center; gap: 10px;">
                        <a href="/admin/products/{{ $p->id }}/edit" style="padding: 8px 12px; background: #fef9c3; color: #ca8a04; border-radius: 6px; text-decoration: none;">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        
                        <form action="/admin/products/{{ $p->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus barang ini beserta semua variannya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding: 8px 12px; background: #fee2e2; color: #ef4444; border: none; border-radius: 6px; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #6b7280;">Belum ada data barang. Silakan tambah barang baru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('productTableBody');
    let timeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        let query = this.value;

        timeout = setTimeout(() => {
            // Debug: cek di console apakah script jalan saat mengetik
            console.log('Mencari:', query); 

            fetch(`/admin/products/search?query=${query}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Data diterima:', data); // Debug: cek data masuk
                    let html = '';
                    
                    if (data.length > 0) {
                        data.forEach((p, index) => {
                            // Render Varian
                            let variantsHtml = '';
                            if (p.variants && p.variants.length > 0) {
                                p.variants.forEach(v => {
                                    let badgeColor = v.stok > 0 ? '#e0e7ff' : '#fee2e2';
                                    let textColor = v.stok > 0 ? '#3730a3' : '#991b1b';
                                    let hargaFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v.harga);
                                    variantsHtml += `<span style="background:${badgeColor}; color:${textColor}; padding:4px 10px; border-radius:20px; font-size:0.85rem; margin-right:5px;">${v.keterangan}: <b>${hargaFormatted}</b></span>`;
                                });
                            }

                            html += `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 15px;">${index + 1}</td>
                                <td style="padding: 15px;">${p.nama_barang}</td>
                                <td style="padding: 15px;"><div style="display:flex; flex-wrap:wrap; gap:5px;">${variantsHtml}</div></td>
                                <td style="padding: 15px; display:flex; gap:10px; justify-content:center;">
                                    <a href="/admin/products/${p.id}/edit" style="color:#ca8a04;"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <form action="/admin/products/${p.id}" method="POST">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="7" style="padding:30px; text-align:center;">Barang tidak ditemukan...</td></tr>';
                    }
                    tableBody.innerHTML = html;
                })
                .catch(err => {
                    console.error("Gagal memuat data:", err);
                });
        }, 300);
    });
</script>
@endsection