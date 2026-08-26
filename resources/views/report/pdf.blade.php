<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary-table { width: 100%; margin-bottom: 20px; }
        .summary-table td { padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold; }
        .summary-title { display: block; font-size: 10px; font-weight: normal; color: #666; margin-bottom: 5px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .data-table th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .text-green { color: green; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN PENJUALAN TOKO</h2>
        <p>{{ $title }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td style="color: #004085; background: #cce5ff;">
                <span class="summary-title">Total Pendapatan (Omzet)</span>
                Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </td>
            <td style="color: #721c24; background: #f8d7da;">
                <span class="summary-title">Total Modal Keluar</span>
                Rp {{ number_format($totalModal, 0, ',', '.') }}
            </td>
            <td style="color: #155724; background: #d4edda;">
                <span class="summary-title">Keuntungan (Laba Bersih)</span>
                Rp {{ number_format($totalLaba, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Tgl Transaksi</th>
                <th>Nama Barang (Varian)</th>
                <th>Qty</th>
                <th class="text-right">Harga Modal</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Keuntungan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $d)
            @php
                $modalItem = $d->product->harga_modal ?? 0;
                $labaItem = ($d->price - $modalItem) * $d->qty;
            @endphp
            <tr>
                <td>{{ $d->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $d->product->nama_barang ?? 'Produk Dihapus' }} ({{ $d->variant->keterangan ?? '-' }})</td>
                <td style="text-align: center;">{{ $d->qty }}</td>
                <td class="text-right">Rp {{ number_format($modalItem, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                <td class="text-right text-green"><b>Rp {{ number_format($labaItem, 0, ',', '.') }}</b></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: right; margin-top: 30px; font-size: 10px; color: #666;">
        Dicetak pada: {{ date('d M Y, H:i') }}
    </p>

</body>
</html>