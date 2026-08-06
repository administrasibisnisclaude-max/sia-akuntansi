@extends('layouts.app')
@section('title', 'Point of Sales')

@push('styles')
<style>
/* Hide sidebar for full-screen POS */
#layoutSidenav_nav { display: none !important; }
#layoutSidenav_content { margin-left: 0 !important; }
#layoutSidenav_content > main > .container-fluid { padding: 0 !important; max-width: 100% !important; }
h1.mt-4, ol.breadcrumb { display: none !important; }
.alert.alert-success, .alert.alert-danger { margin: 8px 12px 0 !important; border-radius: 6px; }

/* POS Layout */
.pos-wrapper { height: calc(100vh - 56px); display: flex; overflow: hidden; background: #f8f9fa; }
.pos-left    { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.pos-right   { width: 380px; min-width: 380px; background: #fff; display: flex; flex-direction: column; border-left: 1px solid #dee2e6; }

/* Search */
.pos-search  { padding: 12px; background: #fff; border-bottom: 1px solid #dee2e6; position: relative; }
.search-dropdown { position: absolute; left: 12px; right: 12px; top: 100%; z-index: 1000;
    background: #fff; border: 1px solid #dee2e6; border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12); max-height: 340px; overflow-y: auto; }
.search-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f0;
    display: flex; justify-content: space-between; align-items: center; }
.search-item:last-child { border-bottom: none; }
.search-item:hover { background: #e8f4fd; }
.search-item .item-name  { font-weight: 600; font-size: .9rem; }
.search-item .item-code  { font-size: .75rem; color: #6c757d; }
.search-item .item-price { font-weight: 700; color: #198754; white-space: nowrap; margin-left: 12px; }
.search-item .item-stock { font-size: .75rem; color: #6c757d; }

/* Cart */
.pos-cart-area { flex: 1; overflow-y: auto; padding: 12px; }
.cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; color: #adb5bd; }
.cart-table  { width: 100%; border-collapse: collapse; }
.cart-table th { background: #f8f9fa; padding: 8px 10px; font-size: .8rem; text-transform: uppercase;
    letter-spacing: .03em; border-bottom: 2px solid #dee2e6; position: sticky; top: 0; }
.cart-table td { padding: 8px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
.cart-table tr:last-child td { border-bottom: none; }
.qty-control { display: flex; align-items: center; gap: 4px; }
.qty-control input { width: 52px; text-align: center; border: 1px solid #dee2e6; border-radius: 6px;
    padding: 3px 4px; font-size: .9rem; }
.qty-btn { border: 1px solid #dee2e6; background: #f8f9fa; border-radius: 6px; width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem;
    color: #495057; transition: background .15s; }
.qty-btn:hover { background: #e9ecef; }
.disc-input { width: 54px; border: 1px solid #dee2e6; border-radius: 6px; padding: 3px 4px;
    font-size: .85rem; text-align: center; }
.btn-remove { border: none; background: none; color: #dc3545; font-size: 1.1rem; cursor: pointer;
    padding: 2px 6px; border-radius: 4px; transition: background .15s; }
.btn-remove:hover { background: #ffe5e5; }

/* Right panel */
.pos-summary    { padding: 16px; border-bottom: 1px solid #dee2e6; }
.pos-summary .row-item { display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px; font-size: .9rem; }
.pos-summary .total-row { font-size: 1.3rem; font-weight: 700; margin-top: 8px; }
.pos-notes { padding: 10px 16px; border-bottom: 1px solid #dee2e6; }
.pos-payment   { padding: 14px 16px; border-bottom: 1px solid #dee2e6; }
.pay-method-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px; }
.pay-btn { border: 2px solid #dee2e6; background: #f8f9fa; border-radius: 8px; padding: 10px 6px;
    text-align: center; cursor: pointer; transition: all .15s; font-size: .88rem; font-weight: 600; }
.pay-btn:hover { border-color: #0d6efd; background: #e8f0ff; }
.pay-btn.active { border-color: #0d6efd; background: #0d6efd; color: #fff; }
.paid-input { font-size: 1.25rem; font-weight: 600; border: 2px solid #dee2e6; border-radius: 8px;
    padding: 8px 12px; width: 100%; text-align: right; }
.paid-input:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
.change-display { font-size: 1.5rem; font-weight: 800; color: #198754; }
.pos-action { padding: 14px 16px; margin-top: auto; }
.btn-process { width: 100%; padding: 14px; font-size: 1.1rem; font-weight: 700; letter-spacing: .03em;
    border-radius: 10px; border: none; background: #198754; color: #fff; cursor: pointer;
    transition: background .15s; }
.btn-process:hover:not(:disabled) { background: #157347; }
.btn-process:disabled { background: #6c757d; cursor: not-allowed; }

/* Quick amount buttons */
.quick-amounts { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
.quick-btn { flex: 1; min-width: 70px; padding: 5px 4px; font-size: .8rem; border: 1px solid #dee2e6;
    border-radius: 6px; background: #f8f9fa; cursor: pointer; text-align: center; }
.quick-btn:hover { background: #e9ecef; }

/* Success Modal */
.modal-success .modal-header { background: #198754; color: #fff; border-radius: 8px 8px 0 0; }

@media print { body { display: none; } }
</style>
@endpush

@section('content')
<div class="pos-wrapper">

    {{-- ===== LEFT: CART ===== --}}
    <div class="pos-left">

        {{-- Search Bar --}}
        <div class="pos-search">
            <input type="text" id="searchInput" class="form-control form-control-lg"
                   placeholder="&#xF52A; Cari produk (nama / kode)..." autocomplete="off">
            <div id="searchDropdown" class="search-dropdown" style="display:none;"></div>
        </div>

        {{-- Cart Area --}}
        <div class="pos-cart-area">
            <div id="cartEmpty" class="cart-empty">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <div class="fw-semibold">Keranjang kosong</div>
                <small>Cari produk di atas untuk mulai transaksi</small>
            </div>
            <table class="cart-table" id="cartTable" style="display:none;">
                <thead>
                    <tr>
                        <th style="width:38%">Produk</th>
                        <th style="width:18%">Harga</th>
                        <th style="width:18%">Qty</th>
                        <th style="width:12%">Disc%</th>
                        <th style="width:14%;text-align:right">Subtotal</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="cartBody"></tbody>
            </table>
        </div>
    </div>

    {{-- ===== RIGHT: CHECKOUT ===== --}}
    <div class="pos-right">

        {{-- Summary --}}
        <div class="pos-summary">
            <div class="row-item">
                <span class="text-muted">Subtotal</span>
                <span id="sumSubtotal">Rp 0</span>
            </div>
            <div class="row-item">
                <span class="text-muted">Diskon</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width:120px;">
                        <input type="number" id="globalDiscountPct" class="form-control text-end"
                               value="0" min="0" max="100" step="0.5" placeholder="0">
                        <span class="input-group-text">%</span>
                    </div>
                    <span id="sumDiscount" class="text-danger">- Rp 0</span>
                </div>
            </div>
            <div class="row-item">
                <span class="text-muted">Pajak</span>
                <div class="d-flex align-items-center gap-2">
                    <select id="taxPercent" class="form-select form-select-sm" style="width:90px;">
                        <option value="0">0%</option>
                        <option value="11">11%</option>
                        <option value="10">10%</option>
                        <option value="12">12%</option>
                    </select>
                    <span id="sumTax" class="text-muted">Rp 0</span>
                </div>
            </div>
            <hr class="my-2">
            <div class="row-item total-row">
                <span>TOTAL</span>
                <span id="sumTotal" class="text-success">Rp 0</span>
            </div>
        </div>

        {{-- Notes --}}
        <div class="pos-notes">
            <textarea id="notesInput" class="form-control form-control-sm" rows="2"
                      placeholder="Catatan transaksi..."></textarea>
        </div>

        {{-- Payment Method --}}
        <div class="pos-payment">
            <div class="fw-semibold mb-2 small text-muted text-uppercase">Metode Pembayaran</div>
            <div class="pay-method-grid">
                <div class="pay-btn active" data-method="tunai" onclick="setPayMethod(this)">
                    <div>💵</div><div>Tunai</div>
                </div>
                <div class="pay-btn" data-method="qris" onclick="setPayMethod(this)">
                    <div>📱</div><div>QRIS</div>
                </div>
                <div class="pay-btn" data-method="transfer" onclick="setPayMethod(this)">
                    <div>🏦</div><div>Transfer</div>
                </div>
                <div class="pay-btn" data-method="ewallet" onclick="setPayMethod(this)">
                    <div>💳</div><div>E-Wallet</div>
                </div>
            </div>

            {{-- Cash panel --}}
            <div id="cashPanel">
                <label class="form-label small fw-semibold">Uang Dibayar</label>
                <input type="number" id="paidAmount" class="paid-input" value="0" min="0" step="1000"
                       oninput="calcChange()" placeholder="0">
                <div class="quick-amounts" id="quickAmounts"></div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted small">Kembalian</span>
                    <span class="change-display" id="changeDisplay">Rp 0</span>
                </div>
            </div>

            {{-- Non-cash panel --}}
            <div id="noncashPanel" style="display:none;" class="text-center py-3 text-muted">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i><br>
                <span id="noncashLabel">Pembayaran dikonfirmasi</span>
            </div>
        </div>

        {{-- Process Button --}}
        <div class="pos-action">
            <button class="btn-process" id="btnProcess" onclick="processPayment()" disabled>
                <i class="fas fa-cash-register me-2"></i>PROSES BAYAR
            </button>
            <div class="text-center mt-2">
                <small class="text-muted" id="itemCountLabel">0 item di keranjang</small>
            </div>
        </div>
    </div>
</div>

{{-- ===== SUCCESS MODAL ===== --}}
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
let cart     = [];
let payMethod = 'tunai';
let lastTxId  = null;
let searchTimeout = null;

// ===== SEARCH =====
const searchInput    = document.getElementById('searchInput');
const searchDropdown = document.getElementById('searchDropdown');

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (q.length < 1) { searchDropdown.style.display = 'none'; return; }
    searchTimeout = setTimeout(() => fetchItems(q), 280);
});

searchInput.addEventListener('keydown', e => {
    if (e.key === 'Escape') { searchDropdown.style.display = 'none'; searchInput.value = ''; }
});

document.addEventListener('click', e => {
    if (!e.target.closest('.pos-search')) searchDropdown.style.display = 'none';
});

function fetchItems(q) {
    fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(items => {
        if (!items.length) {
            searchDropdown.innerHTML = '<div class="search-item text-muted">Produk tidak ditemukan</div>';
        } else {
            searchDropdown.innerHTML = items.map(item => `
                <div class="search-item" onclick="addToCart(${JSON.stringify(item).replace(/"/g,'&quot;')})">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-code">${item.item_code}
                            ${item.stock !== null ? `<span class="ms-2">Stok: ${item.stock}</span>` : ''}
                        </div>
                    </div>
                    <div class="item-price">${formatRp(item.sell_price)}<br>
                        <small class="text-muted fw-normal">/${item.unit}</small>
                    </div>
                </div>`).join('');
        }
        searchDropdown.style.display = 'block';
    })
    .catch(() => { searchDropdown.style.display = 'none'; });
}

// ===== CART =====
function addToCart(item) {
    searchDropdown.style.display = 'none';
    searchInput.value = '';
    searchInput.focus();

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
            stock:            item.stock,
            unit:             item.unit,
        });
    }
    renderCart();
    calcTotals();
}

function calcLineSubtotal(i) {
    const c = cart[i];
    const gross = c.qty * c.unit_price;
    c.subtotal  = round2(gross * (1 - c.discount_percent / 100));
}

function updateQty(i, val) {
    const qty = parseFloat(val);
    if (isNaN(qty) || qty <= 0) { removeFromCart(i); return; }
    cart[i].qty = qty;
    calcLineSubtotal(i);
    renderCart();
    calcTotals();
}

function updateDiscount(i, val) {
    const d = Math.min(100, Math.max(0, parseFloat(val) || 0));
    cart[i].discount_percent = d;
    calcLineSubtotal(i);
    renderCart();
    calcTotals();
}

function changeQty(i, delta) {
    const newQty = (cart[i].qty || 1) + delta;
    if (newQty <= 0) { removeFromCart(i); return; }
    cart[i].qty = newQty;
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
    const tbody = document.getElementById('cartBody');
    const empty = document.getElementById('cartEmpty');
    const table = document.getElementById('cartTable');

    if (!cart.length) {
        empty.style.display = 'flex';
        table.style.display = 'none';
        document.getElementById('itemCountLabel').textContent = '0 item di keranjang';
        return;
    }

    empty.style.display = 'none';
    table.style.display = 'table';
    document.getElementById('itemCountLabel').textContent = `${cart.length} item di keranjang`;

    tbody.innerHTML = cart.map((c, i) => `
        <tr>
            <td>
                <div class="fw-semibold" style="font-size:.88rem;">${c.description}</div>
                <div class="text-muted" style="font-size:.75rem;">${formatRp(c.unit_price)} / ${c.unit || 'pcs'}</div>
            </td>
            <td style="font-size:.88rem;">${formatRp(c.unit_price)}</td>
            <td>
                <div class="qty-control">
                    <span class="qty-btn" onclick="changeQty(${i}, -1)">−</span>
                    <input type="number" class="qty-input" value="${c.qty}" min="0.01" step="0.01"
                           onchange="updateQty(${i}, this.value)" onclick="this.select()">
                    <span class="qty-btn" onclick="changeQty(${i}, 1)">+</span>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="number" class="disc-input" value="${c.discount_percent}"
                           min="0" max="100" step="0.5"
                           onchange="updateDiscount(${i}, this.value)" onclick="this.select()">
                    <span class="ms-1" style="font-size:.8rem;">%</span>
                </div>
            </td>
            <td class="text-end fw-semibold" style="font-size:.9rem;">${formatRp(c.subtotal)}</td>
            <td><button class="btn-remove" onclick="removeFromCart(${i})" title="Hapus">×</button></td>
        </tr>`).join('');
}

// ===== TOTALS =====
function calcTotals() {
    const subtotal    = round2(cart.reduce((s, c) => s + c.subtotal, 0));
    const discPct     = parseFloat(document.getElementById('globalDiscountPct').value) || 0;
    const discAmount  = round2(subtotal * discPct / 100);
    const taxPct      = parseFloat(document.getElementById('taxPercent').value) || 0;
    const taxAmount   = round2((subtotal - discAmount) * taxPct / 100);
    const total       = round2(subtotal - discAmount + taxAmount);

    document.getElementById('sumSubtotal').textContent = formatRp(subtotal);
    document.getElementById('sumDiscount').textContent = `- ${formatRp(discAmount)}`;
    document.getElementById('sumTax').textContent      = formatRp(taxAmount);
    document.getElementById('sumTotal').textContent    = formatRp(total);

    buildQuickAmounts(total);
    calcChange();

    const canProcess = cart.length > 0 && total > 0;
    document.getElementById('btnProcess').disabled = !canProcess;
}

function calcChange() {
    const total  = parseTotalNumber();
    const paid   = parseFloat(document.getElementById('paidAmount').value) || 0;
    const change = Math.max(0, round2(paid - total));
    document.getElementById('changeDisplay').textContent = formatRp(change);
}

function parseTotalNumber() {
    const txt = document.getElementById('sumTotal').textContent;
    return parseFloat(txt.replace(/[^0-9.]/g, '')) || 0;
}

function buildQuickAmounts(total) {
    const amounts = [total, ...quickRounds(total)];
    const unique  = [...new Set(amounts)].slice(0, 5);
    document.getElementById('quickAmounts').innerHTML = unique.map(a =>
        `<div class="quick-btn" onclick="setPaid(${a})">${formatRp(a)}</div>`).join('');
}

function quickRounds(n) {
    const rounds = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
    const results = [];
    for (const r of rounds) {
        const ceil = Math.ceil(n / r) * r;
        if (ceil >= n && results.length < 4) results.push(ceil);
    }
    return results;
}

function setPaid(amount) {
    document.getElementById('paidAmount').value = amount;
    calcChange();
}

// ===== PAYMENT METHOD =====
function setPayMethod(el) {
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    payMethod = el.dataset.method;

    const isCash = payMethod === 'tunai';
    document.getElementById('cashPanel').style.display    = isCash ? 'block' : 'none';
    document.getElementById('noncashPanel').style.display = isCash ? 'none'  : 'block';

    const labels = { qris: 'QRIS dikonfirmasi', transfer: 'Transfer bank dikonfirmasi', ewallet: 'E-Wallet dikonfirmasi' };
    document.getElementById('noncashLabel').textContent = labels[payMethod] || '';
}

// ===== PROCESS PAYMENT =====
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
    if (payMethod === 'tunai' && paid < total) {
        alert('Uang yang dibayar kurang!'); return;
    }

    const btn = document.getElementById('btnProcess');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

    fetch('{{ route('pos.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            items:           cart.map(c => ({
                item_id:          c.item_id,
                description:      c.description,
                qty:              c.qty,
                unit_price:       c.unit_price,
                discount_percent: c.discount_percent,
                subtotal:         c.subtotal,
            })),
            subtotal:        subtotal,
            discount_amount: discAmt,
            tax_percent:     taxPct,
            tax_amount:      taxAmt,
            total:           total,
            payment_method:  payMethod,
            paid_amount:     paid,
            change_amount:   change,
            notes:           document.getElementById('notesInput').value,
        }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            lastTxId = res.id;
            document.getElementById('successNumber').textContent = res.number;
            document.getElementById('successTotal').textContent  = formatRp(total);
            document.getElementById('successChange').textContent =
                payMethod === 'tunai' ? `Kembalian: ${formatRp(change)}` : `Dibayar via ${payMethod.toUpperCase()}`;
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
    cart = [];
    lastTxId = null;
    document.getElementById('globalDiscountPct').value = '0';
    document.getElementById('taxPercent').value        = '0';
    document.getElementById('paidAmount').value        = '0';
    document.getElementById('notesInput').value        = '';
    renderCart();
    calcTotals();
    searchInput.focus();
}

// ===== HELPERS =====
function formatRp(n) {
    return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
}
function round2(n) { return Math.round(n * 100) / 100; }

// Init
document.getElementById('globalDiscountPct').addEventListener('input', calcTotals);
document.getElementById('taxPercent').addEventListener('change', calcTotals);
renderCart();
calcTotals();
searchInput.focus();
</script>
@endpush
