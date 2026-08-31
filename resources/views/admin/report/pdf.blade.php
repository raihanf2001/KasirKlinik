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
        .bg-light { background-color: #e9ecef; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <?php
        $setting = App\Models\Setting::first();    
    ?>
    <div class="header">
        <h2>LAPORAN PENJUALAN {{ strtoupper($setting->app_name ?? "") }}</h2>
        <p>{{ $title }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td style="color: #004085; background: #cce5ff;">
                <span class="summary-title">Total Pendapatan (Omzet)</span>
                Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Tgl Transaksi / Nota</th>
                <th style="width: 25%;">Nama Kasir</th>
                <th style="width: 35%;">Detail Barang (Varian)</th>
                <th class="text-right" style="width: 15%;">Harga</th>
            </tr>
        </thead>
        <tbody>
            {{-- Mengambil data dari variabel $transactions yang dikirim Controller --}}
            @forelse($transactions as $t)
                
                {{-- Baris Induk: Info Transaksi --}}
                <tr class="bg-light">
                    <td>
                        <div class="font-bold">{{ $t->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <?php
                       $detail = App\Models\TransactionDetail::where('transaction_id',$t->id)->first();
                    ?>
                    <td class="font-bold">{{ $detail->nama_user ?? ($t->user->name ?? 'Kasir') }}</td>
                    <td class="text-right font-bold">Total Transaksi:</td>
                    <td class="text-right font-bold">Rp {{ number_format($t->grand_total ?? $t->total, 0, ',', '.') }}</td>
                </tr>

                {{-- Baris Anak: Rincian Barang --}}
                @foreach($t->details as $d)
                <tr>
                    <td colspan="2" style="border-top: none; border-bottom: none;"></td> {{-- Kolom kosong untuk identasi --}}
                    <td>
                        • {{ $d->product->name ?? $d->product_id ?? 'Produk Dihapus' }} 
                        <span style="font-size: 10px; color: #666;">({{ $d->variant->name ?? $d->variant_id ?? '-' }})</span>
                    </td>
                    <td class="text-right">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach

            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Tidak ada data penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align: right; margin-top: 30px; font-size: 10px; color: #666;">
        Dicetak pada: {{ date('d M Y, H:i') }}
    </p>

</body>
</html>