@extends('layout')
@section('container')
<div class="content-area" style="padding: 30px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="margin: 0; color: #f1f5f9">Data Laporan Penjualan</h2>
            <span style="background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: flex; align-items: center; gap: 5px;">
                <span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%; display: inline-block; animation: blink 1s infinite;"></span> Live
            </span>
        </div>
        
        <a href="/admin/report/pdf?{{ http_build_query(request()->all()) }}" style="padding: 10px 20px; background: #dc2626; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
            <i class="fa-solid fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('report.exportExcel', request()->query()) }}" style="background: #10b981; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
    </div>

    <form action="/admin/report" method="GET" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Pilih Periode</label>
            <select name="filter" id="filterSelect" onchange="toggleCustomDate()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="year" {{ $filter == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                <option value="custom" {{ $filter == 'custom' ? 'selected' : '' }}>Pilih Tanggal Manual</option>
            </select>
        </div>

        <div id="customDateSection" style="display: {{ $filter == 'custom' ? 'flex' : 'none' }}; gap: 15px; flex: 2; min-width: 300px;">
            <div style="flex: 1;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
        </div>

        <button type="submit" style="padding: 10px 20px; background: #4361ee; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; height: 42px;">
            Tampilkan Data
        </button>
    </form>

    <h4 style="margin-bottom: 15px; color: #f1f5f9">{{ $title }}</h4>

    <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px; background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #4361ee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-size: 0.9rem;">Total Pendapatan (Omzet)</div>
            <div id="val-omzet" style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>
    </div>

    <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #e5e7eb; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px;">Tgl Transaksi</th>
                    <th style="padding: 15px;">Nama Barang</th>
                    <th style="padding: 15px;">Nama Kasir</th>
                    <th style="padding: 15px;">Varian</th>
                    <th style="padding: 15px;">harga</th>
                </tr>
            </thead>
            <tbody id="report-tbody">
                @forelse($details as $d)
                @php
                    $modalItem = $d->product->harga_modal ?? 0;
                    $labaItem = ($d->price - $modalItem) * $d->qty;
                @endphp
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px; font-size: 0.85rem;">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                    <td style="padding: 15px; font-weight: 600;">{{ $d->product_id ?? 'Produk Dihapus' }}</td>
                    <td style="padding: 15px; font-weight: 600;">{{ $d->nama_user }}</td>
                    <td style="padding: 15px; font-size: 0.85rem; color: #64748b;">{{ $d->variant_id ?? '-' }}</td>
                    <td style="padding: 15px; color: #4361ee;">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #64748b;">Tidak ada data penjualan pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0; }
        100% { opacity: 1; }
    }
</style>

<script>
    // Fitur toggle tampilan form tanggal custom
    function toggleCustomDate() {
        const filter = document.getElementById('filterSelect').value;
        const customSection = document.getElementById('customDateSection');
        if (filter === 'custom') {
            customSection.style.display = 'flex';
        } else {
            customSection.style.display = 'none';
        }
    }
    setInterval(() => {
        let currentUrl = window.location.href;

        // Mengambil data HTML terbaru di latar belakang
        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                // Menerjemahkan teks HTML menjadi objek Document
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');

                // Menimpa nilai pada halaman saat ini dengan nilai dari data terbaru
                document.getElementById('val-omzet').innerHTML = doc.getElementById('val-omzet').innerHTML;
                
                // Menimpa isi tabel
                document.getElementById('report-tbody').innerHTML = doc.getElementById('report-tbody').innerHTML;
            })
            .catch(error => console.error('Gagal mengambil data live:', error));
    }, 3000);
</script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
function ExportToExcel(tableId, filename = 'Laporan.xlsx') {
    var table = document.getElementById(tableId);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet 1"});
    XLSX.writeFile(wb, filename);
}
</script>
@endsection