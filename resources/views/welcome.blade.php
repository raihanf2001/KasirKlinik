@extends('layout')
@section('container')
    @php
        $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'app_name' => 'KASIR PINTAR',
            'phone' => '-',
            'address' => '-'
        ]);
    @endphp 
         <main class="content-area">
            <h2 class="page-title" style="color: {{ $appSetting->sidebar_color ?? '#111827' }}">Ringkasan Pendapatan</h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3>Total Pendapatan</h3>
                        <p>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #dcfce7; color: #16a34a;"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="stat-info">
                        <h3>Transaksi Sukses</h3>
                        <p>{{ $totalTransaksi }} Transaksi</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #fef9c3; color: #ca8a04;"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="stat-info">
                        <h3>Total Produk</h3>
                        <p>{{ $totalProduk }} Item</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">Transaksi Terbaru</h3>
                    <a href="/admin/report" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; cursor:pointer; text-decoration: none; color: #333; font-size: 0.9rem;">Lihat Semua</a>
                </div>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 12px;">ID Transaksi</th>
                            <th style="padding: 12px;">Waktu</th>
                            <th style="padding: 12px;">Metode</th>
                            <th style="padding: 12px;">Total Belanja</th>
                            <th style="padding: 12px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiTerbaru as $trx)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px; font-weight: 600; color: {{ $appSetting->theme_color ?? '#4361ee' }}">
                                #TRX-{{ str_pad($trx->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td style="padding: 12px; color: #64748b;">
                                {{ $trx->created_at->diffForHumans() }} <br>
                                <small>{{ $trx->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td style="padding: 12px; font-weight: 500;">
                                {{ $trx->payment_method }}
                            </td>
                            <td style="padding: 12px; font-weight: 600; color: {{ $appSetting->sidebar_color ?? '#111827' }}">
                                Rp {{ number_format($trx->grand_total, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px;">
                                <span style="background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Sukses</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">Belum ada transaksi sama sekali.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
@endsection