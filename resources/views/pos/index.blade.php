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
    
    #paymentModal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 999; }
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
        <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
            <input type="text" id="posSearch" placeholder="Cari kode/nama (Panah ⬇️⬆️ & Enter = Pilih | F8 = Bayar)" style="flex: 1; padding:12px; border-radius:10px; border:1px solid #ddd; outline: none; font-size: 1rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
            
            <!-- Tambahan Tampilan Nama Kasir di Header -->
            <div style="background: #fff; border: 1px solid #ddd; padding: 8px 15px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 8px; color: #374151;">
                <i class="fa-solid fa-user-circle" style="color: {{ $appSetting->theme_color ?? '#4361ee' }};"></i>
                Kasir: {{ auth()->user()->name ?? 'Guest' }}
            </div>

            <div style="background: #e0e7ff; color: #4361ee; padding: 8px 15px; border-radius: 10px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                <span style="width: 10px; height: 10px; background: #4361ee; border-radius: 50%; display: inline-block; animation: blink 1s infinite;"></span> Data Live
            </div>
        </div>
        
        <div class="product-grid" id="posProductGrid">
            @foreach($products as $p)
                @foreach($p->variants as $v)
                <div class="p-card" onclick="addToCart({ id: {{$v->id}}, product_id: {{$p->id}}, name: '{{ addslashes($p->nama_barang) }}', variant: '{{ addslashes($v->keterangan) }}', price: {{$v->harga}} })">
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
        
        <div id="cartItems" style="flex: 1; overflow-y: auto; padding: 20px;">
        </div>

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
            <button onclick="closePayment()" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-weight: 600;">Batal [ESC]</button>
            <button onclick="submitPayment()" id="btnSubmit" style="flex: 2; padding: 12px; border-radius: 8px; background: {{ $appSetting->theme_color ?? '#4361ee' }}; color: white; border: none; cursor: pointer; font-weight: 700;">KONFIRMASI [ENTER]</button>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let total = 0;
    let paymentMethod = '';
    let currentFocus = -1; 
    
    // --- AMBIL DATA KASIR DI SINI AGAR TIDAK PERLU DIULANG-ULANG ---
    const cashierName = '{{ addslashes(auth()->user()->name ?? "Guest") }}';
    const cashierId = '{{ auth()->user()->id ?? null }}';

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
        const isModalOpen = paymentModal.style.display === 'flex';

        if (e.key === 'F8') {
            e.preventDefault();
            if (!isModalOpen) openPayment();
        }

        if (isModalOpen) {
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
                        products.forEach(p => {
                            p.variants.forEach(v => {
                                let priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v.harga);
                                html += `
                                <div class="p-card" onclick="addToCart({ id: ${v.id}, product_id: ${p.id}, name: '${p.nama_barang.replace(/'/g, "\\'")}', variant: '${v.keterangan.replace(/'/g, "\\'")}', price: ${v.harga} })">
                                    <div style="font-weight: 600; font-size: 0.95rem;">${p.nama_barang}</div>
                                    <div class="variant-badge">${v.keterangan}</div>
                                    <div style="color: {{ $appSetting->sidebar_color ?? '#111827' }}; font-weight: 700; margin-top: 10px;">${priceFormatted}</div>
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
                                    <div class="p-card" onclick="addToCart({ id: ${varItem.id}, product_id: ${prod.id}, name: '${prod.nama_barang.replace(/'/g, "\\'")}', variant: '${varItem.keterangan.replace(/'/g, "\\'")}', price: ${varItem.harga} })">
                                        <div style="font-weight: 600; font-size: 0.95rem;">${prod.nama_barang}</div>
                                        <div style="font-weight: 600; font-size: 0.70rem; color: {{ $appSetting->theme_color ?? '#4361ee' }};">${prod.kode_barang}</div>
                                        <div class="variant-badge">${varItem.keterangan}</div>
                                        <div style="color: {{ $appSetting->sidebar_color ?? '#111827' }}; font-weight: 700; margin-top: 10px;">${priceFormatted}</div>
                                    </div>`;
                                });
                            });

                            if (totalVariants === 1) {
                                addToCart({ 
                                    id: singleVariantObj.id, 
                                    product_id: singleProductObj.id, 
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
        let item = cart.find(i => i.variant_id === v.id);
        
        if(item) {
            item.qty++;
        } else {
            cart.push({ 
                variant_id: v.id, 
                product_id: v.product_id, 
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
                            <div style="font-weight:600; color:{{ $appSetting->sidebar_color ?? '#111827' }}; font-size:0.85rem;">Rp ${item.price.toLocaleString('id-ID')}</div>
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

    async function submitPayment() {
        if(!paymentMethod) return alert('Pilih metode pembayaran!');
        
        let paid = document.getElementById('inputPaid').value || 0;
        if(paymentMethod === 'CASH' && paid < total) return alert('Uang tunai kurang dari total tagihan!');

        let response = await fetch('/admin/pos/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                cart, 
                subtotal: total, 
                tax: 0, 
                grand_total: total,
                payment_method: paymentMethod, 
                amount_paid: paid,
                change_amount: (paid - total > 0) ? (paid - total) : 0,
                // --- PENGIRIMAN DATA KASIR DISINI ---
                cashier_name: cashierName,
                cashier_id: cashierId
            })
        });

        let res = await response.json();
        if(res.status === 'success') {
            window.open('/admin/pos/receipt/' + res.transaction_id, '_blank');
            alert('Pembayaran Berhasil! Mencetak struk...');
            location.reload();
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