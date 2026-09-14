@extends('layout')
@section('container')
@php
    $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
        'app_name' => 'KASIR PINTAR',
        'phone' => '-',
        'address' => '-'
    ]);
@endphp 
<style>
    .pos-container { display: flex; height: calc(100vh - 100px); gap: 20px; padding: 20px; overflow: hidden; }
    .product-list { flex: 7; overflow-y: auto; padding-right: 10px; }
    .cart-panel { flex: 3; background: white; border-radius: 15px; display: flex; flex-direction: column; border: 1px solid #e5e7eb; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
    
    .p-card { background: white; border-radius: 12px; padding: 15px; border: 2px solid #e5e7eb; cursor: pointer; transition: 0.2s; }
    .p-card:hover { border-color: #4361ee; transform: translateY(-3px); }
    .p-card.focused { border-color: #4361ee; background-color: #f0f4ff; transform: translateY(-3px); box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15); }
    .variant-badge { font-size: 0.75rem; background: #f3f4f6; padding: 2px 8px; border-radius: 10px; margin-top: 5px; display: inline-block; }
    
    #paymentModal, #customItemModal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 999; }
    .modal-content { background: white; padding: 30px; border-radius: 20px; width: 450px; position: relative;}
    .method-btn { padding: 20px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; text-align: center; flex: 1; transition: 0.2s; }
    .method-btn.active { border-color: #4361ee; background: #f0f3ff; box-shadow: 0 0 0 3px rgba(67,97,238,0.2); }

    .product-list::-webkit-scrollbar { width: 6px; }
    .product-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0; }
        100% { opacity: 1; }
    }
</style>

<div class="pos-container">
    <div class="product-list">
        <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">            
            <div style="background: #fff; border: 1px solid #ddd; padding: 8px 15px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 8px; color: #374151; white-space: nowrap;">
                <i class="fa-solid fa-user-circle" style="color: {{ $appSetting->theme_color ?? '#4361ee' }};"></i>
                Kasir: {{ auth()->user()->name ?? 'Guest' }}
            </div>

            <!-- TOMBOL KONEKSI PRINTER BLUETOOTH -->
            <button onclick="connectPrinter()" id="btnConnectPrinter" style="background: #3b82f6; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">
                <i class="fa-brands fa-bluetooth"></i> <span id="printerStatusText">Hubungkan Printer</span>
            </button>

            <button onclick="openCustomModal()" style="background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap; transition: 0.3s;">
                <i class="fa-solid fa-plus"></i> Item Manual
            </button>
        </div>
        
        <div class="product-grid" id="posProductGrid">
            @foreach($products as $p)
                @foreach($p->variants as $v)
                <div class="p-card" onclick="addToCart({ name: '{{ addslashes($p->nama_barang) }}', variant: '{{ addslashes($v->keterangan) }}', price: {{$v->harga}} })">
                    <div style="font-weight: 600; font-size: 0.95rem;">{{$p->nama_barang}}</div>
                    <div class="variant-badge">{{$v->keterangan}}</div>
                    <div style="color: {{ $appSetting->sidebar_color ?? '#111827' }}; font-weight: 700; margin-top: 10px;">Rp {{number_format($v->harga,0,',','.')}}</div>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="cart-panel">
        <div style="padding: 20px; border-bottom: 1px solid #eee; font-weight: 700; font-size: 1.1rem; display: flex; justify-content: space-between;">
            <span>Keranjang Belanja</span>
            <i class="fa-solid fa-cart-shopping" style="color: {{ $appSetting->theme_color ?? '#4361ee' }}"></i>
        </div>
        
        <div id="cartItems" style="flex: 1; overflow-y: auto; padding: 20px;"></div>

        <div style="padding: 20px; background: #f9fafb; border-radius: 0 0 15px 15px; border-top: 1px solid #eee;">
            <div style="display:flex; justify-content:space-between; margin-bottom: 15px; font-weight: 700; color: {{ $appSetting->sidebar_color ?? '#111827' }}; font-size: 1.3rem;">
                <span>Total Bayar</span>
                <span id="txtTotal">Rp 0</span>
            </div>
            <button onclick="openPayment()" style="width: 100%; padding: 15px; background: {{ $appSetting->theme_color ?? '#4361ee' }}; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 1rem; transition: 0.3s; display: flex; justify-content: space-between; align-items: center;">
                <span>PROSES PEMBAYARAN</span>
                <span style="background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 5px; font-size: 0.8rem;">[ F8 ]</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL PEMBAYARAN -->
<div id="paymentModal">
    <div class="modal-content">
        <h3 style="margin-bottom: 10px; text-align: center;">Konfirmasi Pembayaran</h3>
        <p style="text-align: center; font-size: 0.8rem; color: #6b7280; margin-bottom: 20px;">Gunakan panah ⬅️ ➡️ untuk ganti metode. Tekan <b>ENTER</b> untuk simpan.</p>
        
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <div class="method-btn" onclick="selectMethod('CASH')" id="btnCash">
                <i class="fa-solid fa-money-bill-wave" style="font-size: 2rem; color: #16a34a; margin-bottom: 5px;"></i><br><b>TUNAI (CASH)</b>
            </div>
            <div class="method-btn" onclick="selectMethod('QRIS')" id="btnQRIS">
                <i class="fa-solid fa-qrcode" style="font-size: 2rem; color: #4361ee; margin-bottom: 5px;"></i><br><b>QRIS</b>
            </div>
        </div>

        <div id="sectionCash" style="display: none; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <label style="font-weight: 600;">Uang Tunai Diterima:</label>
            <input type="number" id="inputPaid" oninput="calculateChange()" placeholder="Ketik nominal & tekan ENTER..." style="width:100%; padding:12px; margin-top:8px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1.2rem; font-weight: 700; outline: none;">
            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 500;">Kembalian:</span>
                <b id="txtChange" style="color: #ef4444; font-size: 1.2rem;">Rp 0</b>
            </div>
        </div>

        <div id="sectionQRIS" style="display: none; text-align: center; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PEMBAYARAN_KASIR" width="160" style="border-radius: 10px; border: 5px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <p style="margin-top: 15px; color: #64748b; font-weight: 500;">Arahkan kamera pembeli ke kode QR di atas.<br>Tekan <b>ENTER</b> jika sudah dibayar.</p>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button onclick="closePayment()" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-weight: 600;">Batal</button>
            <button onclick="submitPayment()" id="btnSubmit" style="flex: 2; padding: 12px; border-radius: 8px; background: {{ $appSetting->theme_color ?? '#4361ee' }}; color: white; border: none; cursor: pointer; font-weight: 700;">KONFIRMASI</button>
        </div>
    </div>
</div>

<!-- MODAL ITEM MANUAL -->
<div id="customItemModal">
    <div class="modal-content" style="width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-bottom: 20px; text-align: center; color: #1f2937;">Tambah Item Manual</h3>
        
        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; font-size: 0.9rem; color: #4b5563;">Nama Barang / Jasa:</label>
            <input type="text" id="customItemName" placeholder="Contoh: Biaya Admin / Ongkir" style="width: 100%; padding: 12px; margin-top: 8px; border-radius: 10px; border: 1px solid #cbd5e1; outline: none; font-size: 1rem;">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="font-weight: 600; font-size: 0.9rem; color: #4b5563;">Harga (Rp):</label>
            <input type="number" id="customItemPrice" placeholder="Contoh: 15000" style="width: 100%; padding: 12px; margin-top: 8px; border-radius: 10px; border: 1px solid #cbd5e1; outline: none; font-size: 1rem;">
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button onclick="closeCustomModal()" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-weight: 600; color: #4b5563;">Batal</button>
            <button onclick="addCustomItemToCart()" style="flex: 1; padding: 12px; border-radius: 10px; background: #10b981; color: white; border: none; cursor: pointer; font-weight: 700;">Tambahkan</button>
        </div>
    </div>
</div>
<script>
    let cart = [];
    let total = 0;
    let paymentMethod = '';
    let currentFocus = -1;

    // FIX: printCharacteristic must be a real global so submitPayment() can see it.
    let printCharacteristic = null;
    let printerDevice = null;

    const cashierName = '{{ addslashes(auth()->user()->name ?? "Guest") }}';

    // ---- Logo & nama toko untuk struk ----
    // Pastikan controller/view meneruskan $appSetting ke halaman POS ini
    // (sama seperti halaman struk), supaya URL logo & nama toko tersedia di sini.
    const STORE_LOGO_URL = '{{ isset($appSetting) && $appSetting->logo_path ? asset($appSetting->logo_path) : "" }}';
    const STORE_NAME = '{{ addslashes(strtoupper($appSetting->app_name ?? "TOKO")) }}';
    const STORE_ADDRESS = '{{ addslashes($appSetting->address ?? "") }}';
    const STORE_PHONE = '{{ addslashes($appSetting->phone ?? "") }}';
    // Lebar cetak dalam dot. 58mm printer umumnya ~384 dot, 80mm ~576 dot.
    const PRINTER_WIDTH_DOTS = 384;
    // Logo dicetak 1/4 dari lebar kertas (sebelumnya 1/2, sekarang diperkecil setengah lagi).
    const LOGO_WIDTH_DOTS = Math.floor(PRINTER_WIDTH_DOTS / 4);

    let cachedLogoRaster = null;
    let logoLoadFailed = false;

    async function connectPrinter() {
        try {
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'] // UUID Standard Printer Thermal
            });

            await bindPrinterDevice(device);

            alert('Berhasil terhubung ke Printer: ' + device.name);
        } catch (error) {
            console.error(error);
            alert('Gagal menghubungkan printer. Pastikan Bluetooth aktif dan web diakses via HTTPS.');
        }
    }

    // Menyambungkan GATT server + characteristic untuk device yang sudah dipilih,
    // dan memasang listener disconnect. Dipakai baik saat pairing pertama kali
    // maupun saat auto-reconnect (tanpa requestDevice lagi).
    async function bindPrinterDevice(device) {
        const server = await device.gatt.connect();
        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
        printCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
        printerDevice = device;

        // Pasang listener hanya sekali per device (hindari listener dobel saat reconnect)
        if (!device.__disconnectListenerAttached) {
            device.addEventListener('gattserverdisconnected', onPrinterDisconnected);
            device.__disconnectListenerAttached = true;
        }

        let btn = document.getElementById('btnConnectPrinter');
        if (btn) btn.style.background = '#10b981';
        let statusEl = document.getElementById('printerStatusText');
        if (statusEl) statusEl.innerText = 'Printer Terhubung: ' + device.name;
    }

    function onPrinterDisconnected() {
        // Jangan null-kan printerDevice di sini. GATT server thermal printer sering
        // idle-disconnect setelah beberapa saat tidak ada aktivitas, tapi device
        // (hasil pairing Bluetooth) tetap valid dan bisa disambungkan ulang lewat
        // device.gatt.connect() TANPA memunculkan dialog pairing lagi.
        printCharacteristic = null;

        let btn = document.getElementById('btnConnectPrinter');
        if (btn) btn.style.background = '#f59e0b';
        let statusEl = document.getElementById('printerStatusText');
        if (statusEl) statusEl.innerText = 'Printer idle/terputus sementara (akan otomatis tersambung lagi saat mencetak)';
    }

    // Dipanggil sebelum tiap kali cetak. Kalau koneksi GATT sempat putus,
    // sambungkan ulang otomatis pakai device yang sama - hanya pairing sekali di awal.
    async function ensurePrinterConnected() {
        if (!printerDevice) {
            throw new Error('Printer belum pernah dipasangkan. Klik tombol Hubungkan Printer terlebih dahulu.');
        }
        if (printerDevice.gatt.connected && printCharacteristic) {
            return;
        }
        await bindPrinterDevice(printerDevice);
    }

    // ---------- Logo -> ESC/POS raster bitmap ----------

    function loadImageElement(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error('Gagal memuat logo dari ' + url));
            img.src = url;
        });
    }

    // Mengubah gambar menjadi perintah raster ESC/POS (GS v 0) dengan dithering
    // Floyd-Steinberg supaya logo tetap terlihat jelas di printer hitam-putih.
    function imageToEscPosRaster(img, maxWidthDots) {
        const srcW = img.naturalWidth || img.width;
        const srcH = img.naturalHeight || img.height;

        let targetWidth = Math.min(maxWidthDots, srcW);
        targetWidth = targetWidth - (targetWidth % 8); // harus kelipatan 8
        if (targetWidth <= 0) targetWidth = 8;

        const scale = targetWidth / srcW;
        const targetHeight = Math.max(1, Math.round(srcH * scale));

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, targetWidth, targetHeight);
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

        const imageData = ctx.getImageData(0, 0, targetWidth, targetHeight);
        const pixels = imageData.data;

        const gray = new Float32Array(targetWidth * targetHeight);
        for (let i = 0; i < targetWidth * targetHeight; i++) {
            const r = pixels[i * 4], g = pixels[i * 4 + 1], b = pixels[i * 4 + 2];
            gray[i] = 0.299 * r + 0.587 * g + 0.114 * b;
        }

        const bw = new Uint8Array(targetWidth * targetHeight); // 0 = hitam, 255 = putih
        for (let y = 0; y < targetHeight; y++) {
            for (let x = 0; x < targetWidth; x++) {
                const idx = y * targetWidth + x;
                const oldPixel = gray[idx];
                const newPixel = oldPixel < 128 ? 0 : 255;
                bw[idx] = newPixel;
                const err = oldPixel - newPixel;

                if (x + 1 < targetWidth) gray[idx + 1] += err * 7 / 16;
                if (y + 1 < targetHeight) {
                    if (x - 1 >= 0) gray[idx + targetWidth - 1] += err * 3 / 16;
                    gray[idx + targetWidth] += err * 5 / 16;
                    if (x + 1 < targetWidth) gray[idx + targetWidth + 1] += err * 1 / 16;
                }
            }
        }

        const bytesPerRow = targetWidth / 8;
        const raster = new Uint8Array(bytesPerRow * targetHeight);

        for (let y = 0; y < targetHeight; y++) {
            for (let byteX = 0; byteX < bytesPerRow; byteX++) {
                let byteVal = 0;
                for (let bit = 0; bit < 8; bit++) {
                    const x = byteX * 8 + bit;
                    if (bw[y * targetWidth + x] === 0) {
                        byteVal |= (1 << (7 - bit));
                    }
                }
                raster[y * bytesPerRow + byteX] = byteVal;
            }
        }

        const xL = bytesPerRow & 0xFF;
        const xH = (bytesPerRow >> 8) & 0xFF;
        const yL = targetHeight & 0xFF;
        const yH = (targetHeight >> 8) & 0xFF;

        const GS = 0x1D;
        const header = [GS, 0x76, 0x30, 0x00, xL, xH, yL, yH]; // GS v 0, mode normal
        return header.concat(Array.from(raster));
    }

    async function getLogoRasterBytes() {
        if (cachedLogoRaster) return cachedLogoRaster;
        if (logoLoadFailed) return null;
        if (!STORE_LOGO_URL) return null;

        try {
            const img = await loadImageElement(STORE_LOGO_URL);
            cachedLogoRaster = imageToEscPosRaster(img, LOGO_WIDTH_DOTS);
            return cachedLogoRaster;
        } catch (err) {
            console.error('Gagal memproses logo untuk cetak Bluetooth:', err);
            logoLoadFailed = true; // jangan coba berulang-ulang tiap transaksi kalau memang gagal
            return null;
        }
    }

    // ---------- ESC/POS receipt builder ----------

    async function buildReceiptData(payload, res) {
        const ESC = 0x1B, GS = 0x1D;
        const bytes = [];

        const push = (arr) => arr.forEach(b => bytes.push(b));
        const pushText = (str) => push(Array.from(new TextEncoder().encode(str)));

        const initPrinter   = () => push([ESC, 0x40]);
        const alignCenter   = () => push([ESC, 0x61, 0x01]);
        const alignLeft     = () => push([ESC, 0x61, 0x00]);
        const boldOn        = () => push([ESC, 0x45, 0x01]);
        const boldOff       = () => push([ESC, 0x45, 0x00]);
        const feed          = (n = 1) => push([ESC, 0x64, n]);
        const cutPaper      = () => push([GS, 0x56, 0x00]);
        const line          = (str = '') => pushText(str + '\n');
        const divider       = () => line('--------------------------------');

        const money = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
        const padRow = (left, right, width = 32) => {
            left = String(left);
            right = String(right);
            let space = width - left.length - right.length;
            if (space < 1) space = 1;
            return left + ' '.repeat(space) + right;
        };

        initPrinter();
        alignCenter();

        // Cetak logo (kalau tersedia dan berhasil dikonversi) sebelum teks header.
        const logoBytes = await getLogoRasterBytes();
        if (logoBytes) {
            push(logoBytes);
            line(''); // jarak antara logo dan nama toko
        }

        boldOn();
        line(STORE_NAME || 'TOKO');
        boldOff();
        line(''); // jarak antara nama toko dan alamat

        if (STORE_ADDRESS) {
            line(STORE_ADDRESS);
            line(''); // jarak antara alamat dan no. HP / baris berikutnya
        }

        if (STORE_PHONE) {
            line(STORE_PHONE);
            line(''); // jarak antara no. HP dan tanggal transaksi
        }

        line(new Date().toLocaleString('id-ID'));
        if (res && res.transaction_id) line('No. Transaksi: ' + res.transaction_id);
        line('Kasir: ' + cashierName);
        line(''); // jarak sebelum garis pemisah

        alignLeft();
        divider();

        payload.cart.forEach(item => {
            line(item.name + ' - ' + item.variant);
            line(padRow(item.qty + ' x ' + money(item.price), money(item.price * item.qty)));
        });

        divider();
        boldOn();
        line(padRow('TOTAL', money(payload.grand_total)));
        boldOff();
        line(padRow('Metode', payload.payment_method));

        if (payload.payment_method === 'CASH') {
            line(padRow('Dibayar', money(payload.amount_paid)));
            line(padRow('Kembali', money(payload.change_amount)));
        }

        divider();
        alignCenter();
        line('Terima kasih!');
        feed(3);
        cutPaper();

        return new Uint8Array(bytes);
    }

    async function printToBluetoothPrinter(payload, res) {
        // Pastikan tersambung dulu (auto-reconnect kalau sempat idle-disconnect,
        // tanpa perlu pairing ulang lewat requestDevice).
        await ensurePrinterConnected();

        const data = await buildReceiptData(payload, res);

        const CHUNK_SIZE = 100;
        const useNoResponse = printCharacteristic.properties &&
            printCharacteristic.properties.writeWithoutResponse;

        for (let offset = 0; offset < data.length; offset += CHUNK_SIZE) {
            const chunk = data.slice(offset, offset + CHUNK_SIZE);
            if (useNoResponse) {
                await printCharacteristic.writeValueWithoutResponse(chunk);
            } else {
                await printCharacteristic.writeValue(chunk);
            }
            await new Promise(r => setTimeout(r, 30));
        }
    }

    setInterval(() => {
        const searchInput = document.getElementById('posSearch');
        if (searchInput && searchInput.value.trim() !== '') return;

        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let newGrid = doc.getElementById('posProductGrid');
                let currentGrid = document.getElementById('posProductGrid');

                if (newGrid && currentGrid) {
                    currentGrid.innerHTML = newGrid.innerHTML;
                    currentFocus = -1;
                }
            })
            .catch(error => console.error('Gagal mengambil pembaruan produk:', error));
    }, 5000);

    document.addEventListener('keydown', function(e) {
        const paymentModal = document.getElementById('paymentModal');
        const customModal = document.getElementById('customItemModal');
        const isPaymentModalOpen = paymentModal.style.display === 'flex';
        const isCustomModalOpen = customModal.style.display === 'flex';

        if (e.key === 'F8') {
            e.preventDefault();
            if (!isPaymentModalOpen && !isCustomModalOpen) openPayment();
        }

        if (isPaymentModalOpen) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closePayment();
            }

            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                e.preventDefault();
                if (paymentMethod === 'CASH') selectMethod('QRIS');
                else selectMethod('CASH');
            }

            if (e.key === 'Enter' && paymentMethod === 'QRIS') {
                e.preventDefault();
                submitPayment();
            }
        }

        if (isCustomModalOpen && e.key === 'Escape') {
            e.preventDefault();
            closeCustomModal();
        }
    });

    const posSearchInput = document.getElementById('posSearch');
    let searchTimeout = null;

    if(posSearchInput) {
        posSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            let query = this.value;
            currentFocus = -1;

            if(query.trim() === '') return;

            searchTimeout = setTimeout(() => {
                fetch(`/admin/pos/search?query=${query}`)
                    .then(response => response.json())
                    .then(products => {
                        let html = '';
                        products.forEach(prod => {
                            prod.variants.forEach(varItem => {
                                let priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(varItem.harga);
                                html += `
                                <div class="p-card" onclick="addToCart({ name: '${prod.nama_barang.replace(/'/g, "\\'")}', variant: '${varItem.keterangan.replace(/'/g, "\\'")}', price: ${varItem.harga} })">
                                    <div style="font-weight: 600; font-size: 0.95rem;">${prod.nama_barang}</div>
                                    <div style="font-weight: 600; font-size: 0.70rem; color: #4361ee;">${prod.kode_barang}</div>
                                    <div class="variant-badge">${varItem.keterangan}</div>
                                    <div style="color: #111827; font-weight: 700; margin-top: 10px;">${priceFormatted}</div>
                                </div>`;
                            });
                        });
                        if (html === '') html = '<div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #9ca3af;">Barang tidak ditemukan...</div>';
                        document.getElementById('posProductGrid').innerHTML = html;
                    }).catch(err => console.error(err));
            }, 300);
        });

        posSearchInput.addEventListener('keydown', function(e) {
            let cards = document.getElementById("posProductGrid").getElementsByClassName("p-card");

            if (e.key === "ArrowDown") {
                currentFocus++;
                addActive(cards);
                e.preventDefault();
            } else if (e.key === "ArrowUp") {
                currentFocus--;
                addActive(cards);
                e.preventDefault();
            } else if (e.key === "Enter") {
                e.preventDefault();
                let query = this.value.trim();

                if (currentFocus > -1) {
                    if (cards[currentFocus]) cards[currentFocus].click();
                } else if (cards.length === 1) {
                    cards[0].click();
                } else if (query !== '') {
                    fetch(`/admin/pos/search?query=${query}`)
                        .then(response => response.json())
                        .then(products => {
                            let totalVariants = 0;
                            let singleVariantObj = null;
                            let singleProductObj = null;
                            let html = '';

                            products.forEach(prod => {
                                prod.variants.forEach(varItem => {
                                    totalVariants++;
                                    singleVariantObj = varItem;
                                    singleProductObj = prod;

                                    let priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(varItem.harga);
                                    html += `
                                    <div class="p-card" onclick="addToCart({ name: '${prod.nama_barang.replace(/'/g, "\\'")}', variant: '${varItem.keterangan.replace(/'/g, "\\'")}', price: ${varItem.harga} })">
                                        <div style="font-weight: 600; font-size: 0.95rem;">${prod.nama_barang}</div>
                                        <div style="font-weight: 600; font-size: 0.70rem; color: #4361ee;">${prod.kode_barang}</div>
                                        <div class="variant-badge">${varItem.keterangan}</div>
                                        <div style="color: #111827; font-weight: 700; margin-top: 10px;">${priceFormatted}</div>
                                    </div>`;
                                });
                            });

                            if (totalVariants === 1) {
                                addToCart({
                                    name: singleProductObj.nama_barang.replace(/'/g, "\\'"),
                                    variant: singleVariantObj.keterangan.replace(/'/g, "\\'"),
                                    price: singleVariantObj.harga
                                });
                            } else if (totalVariants > 1) {
                                document.getElementById('posProductGrid').innerHTML = html;
                                currentFocus = -1;
                            } else {
                                alert('Barcode / Jasa tidak ditemukan!');
                                posSearchInput.value = '';
                            }
                        }).catch(err => console.error(err));
                }
            }
        });
    }

    const inputPaid = document.getElementById('inputPaid');
    if(inputPaid) {
        inputPaid.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                submitPayment();
            }
        });
    }

    function openCustomModal() {
        document.getElementById('customItemModal').style.display = 'flex';
        document.getElementById('customItemName').value = '';
        document.getElementById('customItemPrice').value = '';
        setTimeout(() => document.getElementById('customItemName').focus(), 100);
    }

    function closeCustomModal() {
        document.getElementById('customItemModal').style.display = 'none';
        if(posSearchInput) posSearchInput.focus();
    }

    function addCustomItemToCart() {
        const name = document.getElementById('customItemName').value.trim();
        const price = parseFloat(document.getElementById('customItemPrice').value);

        if(!name) return alert('Nama barang/jasa tidak boleh kosong!');
        if(isNaN(price) || price < 0) return alert('Masukkan nominal harga yang valid!');

        addToCart({ name: name, variant: 'Manual Input', price: price });
        closeCustomModal();
    }

    const customItemPriceInput = document.getElementById('customItemPrice');
    if(customItemPriceInput) {
        customItemPriceInput.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                addCustomItemToCart();
            }
        });
    }

    function addActive(cards) {
        if (!cards || cards.length === 0) return false;
        removeActive(cards);

        if (currentFocus >= cards.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (cards.length - 1);

        cards[currentFocus].classList.add("focused");
        cards[currentFocus].scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    function removeActive(cards) {
        for (let i = 0; i < cards.length; i++) cards[i].classList.remove("focused");
    }

    function addToCart(v) {
        let item = cart.find(i => i.product_id === v.name && i.variant_id === v.variant);

        if(item) {
            item.qty++;
        } else {
            cart.push({
                product_id: v.name,
                variant_id: v.variant,
                name: v.name,
                variant: v.variant,
                price: v.price,
                qty: 1
            });
        }

        renderCart();

        if(posSearchInput) {
            posSearchInput.value = '';
            posSearchInput.focus();
            currentFocus = -1;
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        container.innerHTML = '';
        total = 0;

        if (cart.length === 0) {
            container.innerHTML = '<div style="text-align:center; color:#94a3b8; margin-top:50px;">Belum ada item dipilih</div>';
        }

        cart.forEach((item, index) => {
            let itemTotal = item.price * item.qty;
            total += itemTotal;

            container.innerHTML += `
                <div style="background: #fff; border: 1px solid #f1f5f9; padding: 12px; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="flex:1;">
                            <div style="font-weight:700; font-size:0.9rem; color:#1e293b;">${item.name}</div>
                            <div style="font-size:0.75rem; color:#64748b; margin-bottom:4px;">${item.variant}</div>
                            <div style="font-weight:600; color:#111827; font-size:0.85rem;">Rp ${item.price.toLocaleString('id-ID')}</div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; padding:4px 8px; border-radius:8px; border:1px solid #e2e8f0;">
                            <button onclick="updateQty(${index}, -1)" style="border:none; background:none; cursor:pointer; color:#ef4444; font-weight:bold; font-size:1rem;">-</button>
                            <span style="font-weight:700; width:20px; text-align:center;">${item.qty}</span>
                            <button onclick="updateQty(${index}, 1)" style="border:none; background:none; cursor:pointer; color:#16a34a; font-weight:bold; font-size:1rem;">+</button>
                        </div>
                    </div>
                </div>`;
        });

        document.getElementById('txtTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function updateQty(index, delta) {
        cart[index].qty += delta;
        if(cart[index].qty <= 0) cart.splice(index, 1);
        renderCart();
    }

    function openPayment() {
        if(cart.length === 0) {
            alert('Keranjang masih kosong!');
            posSearchInput.focus();
            return;
        }
        document.getElementById('paymentModal').style.display = 'flex';
        selectMethod('CASH');
        document.getElementById('txtChange').innerText = 'Rp 0';
    }

    function closePayment() {
        document.getElementById('paymentModal').style.display = 'none';
        paymentMethod = '';
        document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('sectionCash').style.display = 'none';
        document.getElementById('sectionQRIS').style.display = 'none';
        if(posSearchInput) posSearchInput.focus();
    }

    function selectMethod(m) {
        paymentMethod = m;
        document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('sectionCash').style.display = (m === 'CASH') ? 'block' : 'none';
        document.getElementById('sectionQRIS').style.display = (m === 'QRIS') ? 'block' : 'none';

        if(m === 'CASH') {
            document.getElementById('btnCash').classList.add('active');
            setTimeout(() => { document.getElementById('inputPaid').focus(); }, 100);
        } else {
            document.getElementById('btnQRIS').classList.add('active');
        }
    }

    function calculateChange() {
        let paid = document.getElementById('inputPaid').value;
        let change = paid - total;
        document.getElementById('txtChange').innerText = 'Rp ' + (change > 0 ? change.toLocaleString('id-ID') : 0);
    }

    // Reset tampilan POS setelah transaksi sukses TANPA reload seluruh halaman,
    // supaya koneksi Bluetooth printer (printerDevice/printCharacteristic) tetap
    // hidup dan tidak perlu pairing ulang di transaksi berikutnya.
    async function resetPOSStateAfterSale() {
        cart = [];
        total = 0;
        renderCart();
        closePayment();

        const inputPaidEl = document.getElementById('inputPaid');
        if (inputPaidEl) inputPaidEl.value = '';
        const txtChangeEl = document.getElementById('txtChange');
        if (txtChangeEl) txtChangeEl.innerText = 'Rp 0';

        // Refresh grid produk saja (stok bisa berubah), bukan seluruh halaman.
        try {
            const response = await fetch(window.location.href);
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('posProductGrid');
            const currentGrid = document.getElementById('posProductGrid');
            if (newGrid && currentGrid) {
                currentGrid.innerHTML = newGrid.innerHTML;
                currentFocus = -1;
            }
        } catch (err) {
            console.error('Gagal memperbarui daftar produk setelah transaksi:', err);
        }

        if (posSearchInput) posSearchInput.focus();
    }

    async function submitPayment() {
        if(!paymentMethod) return alert('Pilih metode pembayaran!');

        let paid = document.getElementById('inputPaid').value || 0;
        if(paymentMethod === 'CASH' && paid < total) return alert('Uang tunai kurang dari total tagihan!');

        const payload = {
            cart,
            subtotal: total,
            tax: 0,
            grand_total: total,
            payment_method: paymentMethod,
            amount_paid: paid,
            change_amount: (paid - total > 0) ? (paid - total) : 0,
            cashier_name: cashierName
        };

        let response = await fetch('/admin/pos/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        });

        let res = await response.json();
        if(res.status === 'success') {
            if (printerDevice) {
                try {
                    await printToBluetoothPrinter(payload, res);
                } catch (printErr) {
                    console.error(printErr);
                    alert('Transaksi berhasil, tetapi gagal mencetak struk otomatis: ' + printErr.message + '\nMembuka struk di tab baru sebagai cadangan.');
                    window.open('/admin/pos/receipt/' + res.transaction_id, '_blank');
                }
            } else {
                alert('Printer Bluetooth belum terhubung. Membuka struk di tab baru sebagai cadangan.');
                window.open('/admin/pos/receipt/' + res.transaction_id, '_blank');
            }

            alert('Pembayaran Berhasil!');
            await resetPOSStateAfterSale();
        } else {
            alert('Gagal memproses transaksi: ' + res.message);
        }
    }

    renderCart();

    window.onload = function() {
        if(posSearchInput) posSearchInput.focus();
    };
</script>
@endsection