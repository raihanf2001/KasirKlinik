<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $transaction->id }}</title>
    <style>
        @page { margin: 0; }
        body { 
            margin: 0; 
            padding: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            width: 58mm; /* Ukuran standar kertas thermal */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        @media print {
            body { width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    @php
        // Mengambil data pengaturan toko
        $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'app_name' => 'KASIR PINTAR',
            'phone' => '-',
            'address' => '-'
        ]);
    @endphp

    <div class="text-center" style="margin-bottom: 10px;">
        
        @if($appSetting->logo_path)
            <img src="{{ asset($appSetting->logo_path) }}" alt="Logo" style="max-width: 60px; margin-bottom: 5px; filter: grayscale(100%);">
        @endif

        <h2 style="margin: 0; font-size: 16px;">{{ strtoupper($appSetting->app_name) }}</h2>
        
        <p style="margin: 2px 0; font-size: 10px; line-height: 1.2;">
            {!! nl2br(e($appSetting->address)) !!}<br>
            Telp: {{ $appSetting->phone }}
        </p>
    </div>

    <div class="divider"></div>

    <table style="font-size: 10px; margin-bottom: 5px;">
        <tr>
            <td>No. TRX</td>
            <td class="text-right">#TRX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td class="text-right">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td class="text-right">{{ auth()->user()->name ?? 'Admin' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        @foreach($transaction->details as $item)
        <tr>
            <td colspan="3" style="padding-bottom: 2px;">
                <span class="font-bold">{{ $item->product->nama_barang }}</span> 
                @if($item->variant)
                    ({{ $item->variant->keterangan }})
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 20%;">{{ $item->qty }}x</td>
            <td class="text-right font-bold">{{ number_format($item->price, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Total</td>
            <td class="text-right font-bold">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Metode</td>
            <td class="text-right">{{ $transaction->payment_method }}</td>
        </tr>
        @if($transaction->payment_method == 'CASH')
        <tr>
            <td>Tunai</td>
            <td class="text-right">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="divider" style="margin-bottom: 10px;"></div>

    <div class="text-center" style="font-size: 10px; margin-bottom: 20px;">
        <p style="margin: 0;">Terima kasih atas kunjungan Anda!</p>
        <p style="margin: 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
            // Menutup tab otomatis setelah dialog print selesai (opsional)
            setTimeout(function() {
                window.close();
            }, 500);
        }
    </script>

</body>
</html>