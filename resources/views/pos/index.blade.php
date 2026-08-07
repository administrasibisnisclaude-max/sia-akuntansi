@extends('layouts.app')
@section('title', 'Point of Sales')

@push('styles')
<style>
/* Hide sidebar for full-screen POS */
#layoutSidenav_nav { display: none !important; }
#layoutSidenav_content { margin-left: 0 !important; }
#layoutSidenav_content > main > .container-fluid { padding: 0 !important; max-width: 100% !important; }
h1.mt-4, ol.breadcrumb { display: none !important; }
.alert { margin: 8px 12px 0 !important; border-radius: 6px; }

/* Layout */
.pos-wrapper { height: calc(100vh - 56px); display: flex; flex-direction: column; background: #f1f5f9; }

/* Top bar */
.pos-topbar {
    background: #fff; border-bottom: 1px solid #dee2e6;
    padding: 10px 16px; display: flex; align-items: center; gap: 12px;
}
.pos-topbar input { max-width: 320px; }
.cart-fab {
    position: relative; margin-left: auto;
    background: #198754; color: #fff; border: none;
    border-radius: 10px; padding: 8px 20px; font-weight: 700;
    font-size: .95rem; cursor: pointer; display: flex; align-items: center; gap: 8px;
    transition: background .15s;
}
.cart-fab:hover { background: #157347; }
.cart-badge {
    background: #fff; color: #198754; border-radius: 50px;
    font-size: .75rem; font-weight: 800; min-width: 20px; height: 20px;
    display: inline-flex; align-items: center; justify-content: center; padding: 0 5px;
}

/* Product grid */
.pos-grid-area { flex: 1; overflow-y: auto; padding: 14px 16px; }
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}
.product-card {
    background: #fff; border: 2px solid #e9ecef; border-radius: 12px;
    padding: 14px 12px; cursor: pointer; transition: all .15s;
    display: flex; flex-direction: column; gap: 4px;
    user-select: none;
}
.product-card:hover { border-color: #0d6efd; box-shadow: 0 2px 10px rgba(13,110,253,.15); transform: translateY(-1px); }
.product-card:active { transform: scale(.97); }
.product-card.out-of-stock { opacity: .5; cursor: not-allowed; }
.product-card .pc-name { font-weight: 700; font-size: .9rem; line-height: 1.3; }
.product-card .pc-code { font-size: .72rem; color: #6c757d; }
.product-card .pc-price { font-size: .95rem; font-weight: 700; color: #198754; margin-top: 6px; }
.product-card .pc-stock { font-size: .72rem; color: #6c757d; }
.product-card .pc-stock.low { color: #dc3545; }
.no-results { text-align: center; color: #adb5bd; padding: 60px 0; }

/* Cart Modal */
.cart-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 1050; display: none; align-items: stretch; justify-content: flex-end;
}
.cart-modal-overlay.open { display: flex; }
.cart-panel {
    width: 100%; max-width: 520px; background: #fff;
    display: flex; flex-direction: column; height: 100%;
    box-shadow: -4px 0 24px rgba(0,0,0,.2);
}
.cart-panel-header {
    background: #212529; color: #fff; padding: 14px 18px;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.cart-panel-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.cart-close { background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; line-height: 1; }

/* Cart body */
.cart-body { flex: 1; overflow-y: auto; }
.cart-empty-state { display: flex; flex-direction: column; align-items: center;
    justify-content: center; height: 100%; color: #adb5bd; }
.cart-table { width: 100%; border-collapse: collapse; }
.cart-table th { background: #f8f9fa; padding: 8px 10px; font-size: .78rem;
    text-transform: uppercase; letter-spacing: .03em; border-bottom: 2px solid #dee2e6;
    position: sticky; top: 0; }
.cart-table td { padding: 8px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; font-size: .88rem; }
.qty-control { display: flex; align-items: center; gap: 4px; }
.qty-control input { width: 46px; text-align: center; border: 1px solid #dee2e6; border-radius: 6px; padding: 3px 4px; font-size: .85rem; }
.qty-btn { border: 1px solid #dee2e6; background: #f8f9fa; border-radius: 6px; width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center; cursor: pointer; }
.qty-btn:hover { background: #e9ecef; }
.disc-input { width: 50px; border: 1px solid #dee2e6; border-radius: 6px; padding: 3px 4px; font-size: .82rem; text-align: center; }
.btn-remove { border: none; background: none; color: #dc3545; font-size: 1rem; cursor: pointer; padding: 2px 5px; border-radius: 4px; }
.btn-remove:hover { background: #ffe5e5; }

/* Cart summary */
.cart-summary { padding: 14px 16px; border-top: 1px solid #dee2e6; background: #f8f9fa; flex-shrink: 0; }
.sum-row { display: flex; justify-content: space-between; font-size: .88rem; margin-bottom: 6px; }
.sum-total { font-size: 1.2rem; font-weight: 800; }
.cart-notes { padding: 0 16px 10px; flex-shrink: 0; }

/* Payment section */
.cart-payment { padding: 12px 16px; border-top: 1px solid #dee2e6; flex-shrink: 0; }
.pay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; margin-bottom: 12px; }
.pay-btn { border: 2px solid #dee2e6; background: #f8f9fa; border-radius: 8px; padding: 8px 4px;
    text-align: center; cursor: pointer; font-size: .82rem; font-weight: 600; transition: all .15s; }
.pay-btn:hover { border-color: #0d6efd; background: #e8f0ff; }
.pay-btn.active { border-color: #0d6efd; background: #0d6efd; color: #fff; }
.paid-input { font-size: 1.15rem; font-weight: 700; border: 2px solid #dee2e6; border-radius: 8px;
    padding: 7px 12px; width: 100%; text-align: right; margin-bottom: 8px; }
.paid-input:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 3px rgba(13,110,253,.12); }
.change-row { display: flex; justify-content: space-between; align-items: center; }
.change-display { font-size: 1.3rem; font-weight: 800; color: #198754; }
.quick-amounts { display: flex; gap: 5px; margin-bottom: 10px; flex-wrap: wrap; }
.quick-btn { flex: 1; min-width: 70px; padding: 4px; font-size: .75rem; border: 1px solid #dee2e6;
    border-radius: 6px; background: #f8f9fa; cursor: pointer; text-align: center; }
.quick-btn:hover { background: #e9ecef; }

/* Process button */
.cart-action { padding: 12px 16px; flex-shrink: 0; }
.btn-process { width: 100%; padding: 13px; font-size: 1.05rem; font-weight: 700;
    border-radius: 10px; border: none; background: #198754; color: #fff; cursor: pointer; transition: background .15s; }
.btn-process:hover:not(:disabled) { background: #157347; }
.btn-process:disabled { background: #6c757d; cursor: not-allowed; }

/* Success Modal */
.modal-success .modal-header { background: #198754; color: #fff; }
</style>
@endpush

@section('content')
<div class="pos-wrapper">

    {{-- Top bar --}}
    <div class="pos-topbar">
        <i class="fas fa-cash-register text-success fs-5"></i>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Cari produk (nama / kode)..." autocomplete="off">
        <button class="cart-fab" onclick="openCart()">
            <i class="fas fa-shopping-cart"></i>
            Keranjang
            <span class="cart-badge" id="cartBadge">0</span>
        </button>
    </div>

    {{-- Product Grid --}}
    <div class="pos-grid-area">
        <div class="product-grid" id="productGrid">
            @forelse($items as $item)
            <div class="product-card {{ $item['type'] === 'product' && $item['stock'] <= 0 ? 'out-of-stock' : '' }}"
                 data-id="{{ $item['id'] }}"
                 data-name="{{ $item['name'] }}"
                 data-code="{{ $item['item_code'] }}"
                 data-price="{{ $item['sell_price'] }}"
                 data-unit="{{ $item['unit'] }}"
                 data-type="{{ $item['type'] }}"
                 data-stock="{{ $item['stock'] ?? '' }}"
                 onclick="cardClick(this)">
                <div class="pc-code">{{ $item['item_code'] }}</div>
                <div class="pc-name">{{ $item['name'] }}</div>
                <div class="pc-price">Rp {{ number_format($item['sell_price'], 0, ',', '.') }}</div>
                @if($item['type'] === 'product')
                <div class="pc-stock {{ ($item['stock'] ?? 0) <= 5 ? 'low' : '' }}">
                    Stok: {{ number_format($item['stock'] ?? 0, 0, ',', '.') }} {{ $item['unit'] }}
                </div>
                @else
                <div class="pc-stock">{{ $item['unit'] }}</div>
                @endif
            </div>
            @empty
            <div class="no-results" style="grid-column:1/-1">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <div>Belum ada produk aktif</div>
            </div>
            @endforelse
        </div>
        <div class="no-results" id="noResults" style="display:none;">
            <i class="fas fa-search fa-3x mb-3"></i>
            <div>Produk tidak ditemukan</div>
        </div>
    </div>
</div>

{{-- ===== CART MODAL (slide-in panel) ===== --}}
<div class="cart-modal-overlay" id="cartOverlay" onclick="overlayClick(event)">
    <div class="cart-panel">

        <div class="cart-panel-header">
            <h5><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h5>
            <button class="cart-close" onclick="closeCart()">&times;</button>
        </div>

        {{-- Cart items --}}
        <div class="cart-body" id="cartBody">
            <div class="cart-empty-state" id="cartEmpty">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <div class="fw-semibold">Keranjang kosong</div>
                <small class="text-muted">Pilih produk dari grid</small>
            </div>
            <table class="cart-table" id="cartTable" style="display:none;">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="width:110px">Qty</th>
                        <th style="width:70px">Disc%</th>
                        <th style="width:90px;text-align:right">Subtotal</th>
                        <th style="width:30px"></th>
                    </tr>
                </thead>
                <tbody id="cartRows"></tbody>
            </table>
        </div>

        {{-- Notes --}}
        <div class="cart-notes">
            <textarea id="notesInput" class="form-control form-control-sm" rows="2"
                      placeholder="Catatan transaksi..."></textarea>
        </div>

        {{-- Summary --}}
        <div class="cart-summary">
            <div class="sum-row"><span class="text-muted">Subtotal</span><span id="sumSubtotal">Rp 0</span></div>
            <div class="sum-row">
                <span class="text-muted">Diskon</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width:110px">
                        <input type="number" id="globalDiscountPct" class="form-control text-end"
                               value="0" min="0" max="100" step="0.5">
                        <span class="input-group-text">%</span>
                    </div>
                    <span id="sumDiscount" class="text-danger">- Rp 0</span>
                </div>
            </div>
            <div class="sum-row">
                <span class="text-muted">Pajak</span>
                <div class="d-flex align-items-center gap-2">
                    <select id="taxPercent" class="form-select form-select-sm" style="width:80px">
                        <option value="0">0%</option>
                        <option value="11">11%</option>
                        <option value="10">10%</option>
                        <option value="12">12%</option>
                    </select>
                    <span id="sumTax" class="text-muted">Rp 0</span>
                </div>
            </div>
            <hr class="my-2">
            <div class="sum-row sum-total"><span>TOTAL</span><span id="sumTotal" class="text-success">Rp 0</span></div>
        </div>

        {{-- Payment --}}
        <div class="cart-payment">
            <div class="fw-semibold small text-muted text-uppercase mb-2">Metode Pembayaran</div>
            <div class="pay-grid">
                <div class="pay-btn active" data-method="tunai" onclick="setPayMethod(this)">&#x1F4B5; Tunai</div>
                <div class="pay-btn" data-method="qris" onclick="setPayMethod(this)">&#x1F4F1; QRIS</div>
                <div class="pay-btn" data-method="transfer" onclick="setPayMethod(this)">&#x1F3E6; Transfer</div>
                <div class="pay-btn" data-method="ewallet" onclick="setPayMethod(this)">&#x1F4B3; E-Wallet</div>
            </div>

            <div id="cashPanel">
                <input type="number" id="paidAmount" class="paid-input" value="0" min="0"
                       step="1000" oninput="calcChange()" placeholder="Uang dibayar...">
                <div class="quick-amounts" id="quickAmounts"></div>
                <div class="change-row">
                    <span class="text-muted small">Kembalian</span>
                    <span class="change-display" id="changeDisplay">Rp 0</span>
                </div>
            </div>
            <div id="noncashPanel" style="display:none;" class="text-center py-2 text-success fw-semibold">
                <i class="fas fa-check-circle me-1"></i><span id="noncashLabel">Pembayaran dikonfirmasi</span>
            </div>
        </div>

        {{-- Action --}}
        <div class="cart-action">
            <button class="btn-process" id="btnProcess" onclick="processPayment()" disabled>
                <i class="fas fa-cash-register me-2"></i>PROSES BAYAR
            </button>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-success">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Transaksi Berhasil</h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="fs-5 fw-bold mb-1" id="successNumber"></div>
                <div class="text-muted mb-1">Total: <strong id="successTotal"></strong></div>
                <div class="text-muted mb-3" id="successChange"></div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button class="btn btn-outline-secondary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>Cetak Struk
                </button>
                <button class="btn btn-success" onclick="newTransaction()">
                    <i class="fas fa-plus me-1"></i>Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ===== STATE =====
let cart      = [];
let payMethod = 'tunai';
let lastTxId  = null;

// ===== SEARCH FILTER =====
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    const cards = document.querySelectorAll('#productGrid .product-card');
    let visible = 0;
    cards.forEach(card => {
        const match = !q
            || card.dataset.name.toLowerCase().includes(q)
            || card.dataset.code.toLowerCase().includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('noResults').style.display = (visible === 0 && q) ? 'block' : 'none';
});

// ===== PRODUCT CARD CLICK =====
function cardClick(el) {
    if (el.classList.contains('out-of-stock')) return;
    addToCart({
        id:         parseInt(el.dataset.id),
        name:       el.dataset.name,
        sell_price: parseFloat(el.dataset.price),
        unit:       el.dataset.unit,
        type:       el.dataset.type,
        stock:      el.dataset.stock !== '' ? parseFloat(el.dataset.stock) : null,
    });
    openCart();
}

// ===== CART =====
function addToCart(item) {
    const existing = cart.findIndex(c => c.item_id === item.id);
    if (existing >= 0) {
        cart[existing].qty++;
        calcLineSubtotal(existing);
    } else {
        cart.push({
            item_id:          item.id,
            description:      item.name,
            qty:              1,
            unit_price:       item.sell_price,
            discount_percent: 0,
            subtotal:         item.sell_price,
            unit:             item.unit,
        });
    }
    renderCart();
    calcTotals();
}

function calcLineSubtotal(i) {
    const c = cart[i];
    cart[i].subtotal = round2(c.qty * c.unit_price * (1 - c.discount_percent / 100));
}

function updateQty(i, val) {
    const qty = parseFloat(val);
    if (isNaN(qty) || qty <= 0) { removeFromCart(i); return; }
    cart[i].qty = qty;
    calcLineSubtotal(i);
    renderCart();
    calcTotals();
}

function changeQty(i, delta) {
    const nq = (cart[i].qty || 1) + delta;
    if (nq <= 0) { removeFromCart(i); return; }
    cart[i].qty = nq;
    calcLineSubtotal(i);
    renderCart();
    calcTotals();
}

function updateDiscount(i, val) {
    cart[i].discount_percent = Math.min(100, Math.max(0, parseFloat(val) || 0));
    calcLineSubtotal(i);
    renderCart();
    calcTotals();
}

function removeFromCart(i) {
    cart.splice(i, 1);
    renderCart();
    calcTotals();
}

function renderCart() {
    const empty = document.getElementById('cartEmpty');
    const table = document.getElementById('cartTable');
    const rows  = document.getElementById('cartRows');
    const badge = document.getElementById('cartBadge');

    badge.textContent = cart.reduce((s, c) => s + c.qty, 0);

    if (!cart.length) {
        empty.style.display = 'flex';
        table.style.display = 'none';
        document.getElementById('btnProcess').disabled = true;
        return;
    }

    empty.style.display = 'none';
    table.style.display = 'table';

    rows.innerHTML = cart.map((c, i) => `
        <tr>
            <td>
                <div class="fw-semibold">${c.description}</div>
                <small class="text-muted">${formatRp(c.unit_price)} / ${c.unit || 'pcs'}</small>
            </td>
            <td>
                <div class="qty-control">
                    <span class="qty-btn" onclick="changeQty(${i},-1)">−</span>
                    <input type="number" value="${c.qty}" min="0.01" step="0.01"
                           onchange="updateQty(${i},this.value)" onclick="this.select()">
                    <span class="qty-btn" onclick="changeQty(${i},1)">+</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="number" class="disc-input" value="${c.discount_percent}"
                           min="0" max="100" step="0.5"
                           onchange="updateDiscount(${i},this.value)" onclick="this.select()">
                    <span class="ms-1" style="font-size:.75rem">%</span>
                </div>
            </td>
            <td class="text-end fw-semibold">${formatRp(c.subtotal)}</td>
            <td><button class="btn-remove" onclick="removeFromCart(${i})">×</button></td>
        </tr>`).join('');
}

// ===== TOTALS =====
function calcTotals() {
    const subtotal   = round2(cart.reduce((s, c) => s + c.subtotal, 0));
    const discPct    = parseFloat(document.getElementById('globalDiscountPct').value) || 0;
    const discAmount = round2(subtotal * discPct / 100);
    const taxPct     = parseFloat(document.getElementById('taxPercent').value) || 0;
    const taxAmount  = round2((subtotal - discAmount) * taxPct / 100);
    const total      = round2(subtotal - discAmount + taxAmount);

    document.getElementById('sumSubtotal').textContent = formatRp(subtotal);
    document.getElementById('sumDiscount').textContent = `- ${formatRp(discAmount)}`;
    document.getElementById('sumTax').textContent      = formatRp(taxAmount);
    document.getElementById('sumTotal').textContent    = formatRp(total);

    buildQuickAmounts(total);
    calcChange();

    document.getElementById('btnProcess').disabled = cart.length === 0 || total <= 0;
}

function parseTotalNumber() {
    return parseInt(document.getElementById('sumTotal').textContent.replace(/\D/g, ''), 10) || 0;
}

function calcChange() {
    const total = parseTotalNumber();
    const paid  = parseFloat(document.getElementById('paidAmount').value) || 0;
    document.getElementById('changeDisplay').textContent = formatRp(Math.max(0, round2(paid - total)));
}

function buildQuickAmounts(total) {
    const rounds = [1000,2000,5000,10000,20000,50000,100000];
    const unique = [...new Set([total, ...rounds.map(r => Math.ceil(total/r)*r).filter(v => v >= total)])].slice(0,5);
    document.getElementById('quickAmounts').innerHTML = unique.map(a =>
        `<div class="quick-btn" onclick="setPaid(${a})">${formatRp(a)}</div>`).join('');
}

function setPaid(a) { document.getElementById('paidAmount').value = a; calcChange(); }

// ===== PAYMENT METHOD =====
function setPayMethod(el) {
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    payMethod = el.dataset.method;
    const isCash = payMethod === 'tunai';
    document.getElementById('cashPanel').style.display    = isCash ? '' : 'none';
    document.getElementById('noncashPanel').style.display = isCash ? 'none' : '';
    const labels = { qris:'QRIS dikonfirmasi', transfer:'Transfer bank dikonfirmasi', ewallet:'E-Wallet dikonfirmasi' };
    document.getElementById('noncashLabel').textContent = labels[payMethod] || '';
}

// ===== CART OVERLAY =====
function openCart()  { document.getElementById('cartOverlay').classList.add('open'); }
function closeCart() { document.getElementById('cartOverlay').classList.remove('open'); }
function overlayClick(e) { if (e.target === document.getElementById('cartOverlay')) closeCart(); }

// ===== PROCESS =====
function processPayment() {
    const total     = parseTotalNumber();
    const subtotal  = round2(cart.reduce((s, c) => s + c.subtotal, 0));
    const discPct   = parseFloat(document.getElementById('globalDiscountPct').value) || 0;
    const discAmt   = round2(subtotal * discPct / 100);
    const taxPct    = parseFloat(document.getElementById('taxPercent').value) || 0;
    const taxAmt    = round2((subtotal - discAmt) * taxPct / 100);
    const paidInput = parseFloat(document.getElementById('paidAmount').value) || 0;
    const paid      = payMethod === 'tunai' ? paidInput : total;
    const change    = round2(Math.max(0, paid - total));

    if (!cart.length) { alert('Keranjang kosong!'); return; }
    if (payMethod === 'tunai' && paid < total) { alert('Uang yang dibayar kurang!'); return; }

    const btn = document.getElementById('btnProcess');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

    fetch('{{ route('pos.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            items: cart.map(c => ({
                item_id: c.item_id, description: c.description, qty: c.qty,
                unit_price: c.unit_price, discount_percent: c.discount_percent, subtotal: c.subtotal,
            })),
            subtotal, discount_amount: discAmt, tax_percent: taxPct, tax_amount: taxAmt,
            total, payment_method: payMethod, paid_amount: paid, change_amount: change,
            notes: document.getElementById('notesInput').value,
        }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            lastTxId = res.id;
            closeCart();
            document.getElementById('successNumber').textContent = res.number;
            document.getElementById('successTotal').textContent  = formatRp(total);
            document.getElementById('successChange').textContent = payMethod === 'tunai'
                ? `Kembalian: ${formatRp(change)}` : `Dibayar via ${payMethod.toUpperCase()}`;
            new bootstrap.Modal(document.getElementById('successModal')).show();
        } else {
            alert('Terjadi kesalahan. Silakan coba lagi.');
        }
    })
    .catch(() => alert('Koneksi gagal. Silakan coba lagi.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cash-register me-2"></i>PROSES BAYAR';
    });
}

function printReceipt() {
    if (lastTxId) window.open(`/pos/receipt/${lastTxId}`, '_blank', 'width=400,height=700');
}

function newTransaction() {
    bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
    cart = []; lastTxId = null;
    document.getElementById('globalDiscountPct').value = '0';
    document.getElementById('taxPercent').value        = '0';
    document.getElementById('paidAmount').value        = '0';
    document.getElementById('notesInput').value        = '';
    renderCart();
    calcTotals();
}

// ===== HELPERS =====
function formatRp(n) { return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID'); }
function round2(n)   { return Math.round(n * 100) / 100; }

// Init
document.getElementById('globalDiscountPct').addEventListener('input', calcTotals);
document.getElementById('taxPercent').addEventListener('change', calcTotals);
renderCart();
calcTotals();
</script>
@endpush
