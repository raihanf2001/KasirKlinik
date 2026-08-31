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
        
        <div style="display: flex; gap: 10px;">
            <a href="/admin/report/pdf?{{ http_build_query(request()->all()) }}" style="padding: 10px 20px; background: #dc2626; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('report.exportExcel', request()->query()) }}" style="background: #10b981; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
        </div>
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
        <table id="laporan-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px; width: 50px;"></th>
                    <th style="padding: 15px;">Tgl Transaksi</th>
                    <th style="padding: 15px;">Nama Kasir</th>
                    <th style="padding: 15px;">Metode Pembayaran</th>
                    <th style="padding: 15px;">Total Transaksi</th>
                </tr>
            </thead>
            <tbody id="report-tbody">
                {{-- Asumsi dari controller Anda mengirimkan variabel $transactions --}}
                @forelse($transactions as $t)
                <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.3s;" onclick="toggleDetail('{{ $t->id }}')" class="hover-bg-gray">
                    <td style="padding: 15px; text-align: center;">
                        <i class="fa-solid fa-chevron-down" id="icon-{{ $t->id }}" style="color: #64748b; transition: transform 0.3s;"></i>
                    </td>
                    <td style="padding: 15px; font-size: 0.85rem;">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <?php
                       $detail = App\Models\TransactionDetail::where('transaction_id',$t->id)->first();
                    ?>
                    <td style="padding: 15px; font-weight: 600;">{{ $detail->nama_user ?? ($t->user->name ?? 'Kasir') }}</td>
                    <td style="padding: 15px; font-weight: 600;">{{ $t->payment_method }}</td>
                    <td style="padding: 15px; font-weight: bold; color: #10b981;">Rp {{ number_format($t->grand_total ?? $t->total, 0, ',', '.') }}</td>
                </tr>
                
                {{-- Baris Detail Transaksi (Awalnya disembunyikan) --}}
                <tr id="detail-{{ $t->id }}" style="display: none; background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <td colspan="5" style="padding: 20px;">
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #e2e8f0; font-size: 0.85rem;">
                                        <th style="padding: 10px 15px;">Nama Barang</th>
                                        <th style="padding: 10px 15px;">Varian</th>
                                        <th style="padding: 10px 15px;">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($t->details as $d)
                                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.85rem;">
                                        <td style="padding: 10px 15px;">{{ $d->product->name ?? $d->product_id ?? 'Produk Dihapus' }}</td>
                                        <td style="padding: 10px 15px; color: #64748b;">{{ $d->variant->name ?? $d->variant_id ?? '-' }}</td>
                                        <td style="padding: 10px 15px;">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">Tidak ada data penjualan pada periode ini.</td>
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
    .hover-bg-gray:hover {
        background-color: #f8fafc;
    }
    .rotate-180 {
        transform: rotate(180deg);
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

    // Menyimpan state (ID) baris detail mana saja yang sedang terbuka
    let openDetailIds = [];

    // Fitur untuk Toggle Accordion Detail Transaksi
    function toggleDetail(transactionId) {
        const detailRow = document.getElementById('detail-' + transactionId);
        const icon = document.getElementById('icon-' + transactionId);
        
        if (detailRow.style.display === 'none' || detailRow.style.display === '') {
            detailRow.style.display = 'table-row';
            icon.classList.add('rotate-180');
            if(!openDetailIds.includes(transactionId)) openDetailIds.push(transactionId);
        } else {
            detailRow.style.display = 'none';
            icon.classList.remove('rotate-180');
            openDetailIds = openDetailIds.filter(id => id !== transactionId);
        }
    }

    // Fitur Live Update
    setInterval(() => {
        let currentUrl = window.location.href;

        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');

                // Update omzet
                document.getElementById('val-omzet').innerHTML = doc.getElementById('val-omzet').innerHTML;
                
                // Update isi tabel utama
                document.getElementById('report-tbody').innerHTML = doc.getElementById('report-tbody').innerHTML;

                // Mengembalikan state baris detail yang sebelumnya terbuka agar tidak tertutup otomatis setelah fetch
                openDetailIds.forEach(id => {
                    const row = document.getElementById('detail-' + id);
                    const icon = document.getElementById('icon-' + id);
                    if(row) {
                        row.style.display = 'table-row';
                        if(icon) icon.classList.add('rotate-180');
                    }
                });
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