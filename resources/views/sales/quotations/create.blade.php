@extends('layouts.app')
@section('title', 'Buat Penawaran Harga')
@push('styles')
<style>.line-row td{vertical-align:middle;}</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Buat Penawaran Harga</div>
    <div class="card-body">
        <form method="POST" action="{{ route('sales.quotations.store') }}">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Berlaku Hingga <span class="text-danger">*</span></label>
                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', date('Y-m-d', strtotime('+14 days'))) }}" required>
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Item</th><th>Deskripsi</th><th style="width:90px">Qty</th><th style="width:140px">Harga Satuan</th><th style="width:130px">Subtotal</th><th style="width:40px"></th></tr>
                    </thead>
                    <tbody id="linesBody">
                    <tr class="line-row">
                        <td><select name="lines[0][item_id]" class="form-select form-select-sm item-select">
                            <option value="">— Pilih Item —</option>
                            @foreach($items as $item)<option value="{{ $item->id }}" data-price="{{ $item->sell_price }}">{{ $item->name }}</option>@endforeach
                        </select></td>
                        <td><input type="text" name="lines[0][description]" class="form-control form-control-sm" required></td>
                        <td><input type="number" name="lines[0][qty]" class="form-control form-control-sm text-end line-qty" value="1" min="0.01" step="0.01"></td>
                        <td><input type="number" name="lines[0][unit_price]" class="form-control form-control-sm text-end line-price" value="0" min="0" step="1"></td>
                        <td><input type="number" name="lines[0][subtotal]" class="form-control form-control-sm text-end line-subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end fw-bold">Subtotal</td><td colspan="2" class="text-end" id="subTotal">Rp 0</td><td></td></tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">PPN (11%)</td>
                            <td colspan="2">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" id="taxCheck" class="form-check-input" onchange="toggleTax()">
                                    <input type="number" name="tax_amount" id="taxAmount" class="form-control form-control-sm text-end" value="0" readonly>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td colspan="2" class="text-end" id="grandTotal">Rp 0</td><td></td></tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addLine()"><i class="bi bi-plus me-1"></i>Tambah Baris</button>
            </div>
            <div class="mb-3" style="max-width:400px">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Simpan Penawaran</button>
                <a href="{{ route('sales.quotations.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const itemData = {
    @foreach($items as $item)
    {{ $item->id }}: { price: {{ $item->sell_price }}, name: '{{ addslashes($item->name) }}' },
    @endforeach
};
const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}" data-price="{{ $item->sell_price }}">{{ $item->name }}</option>@endforeach`;
let idx = 1;
let subtotalVal = 0;

function calcRow(tr) {
    const qty = parseFloat(tr.querySelector('.line-qty').value) || 0;
    const price = parseFloat(tr.querySelector('.line-price').value) || 0;
    tr.querySelector('.line-subtotal').value = (qty * price).toFixed(0);
    updateTotals();
}

function updateTotals() {
    subtotalVal = 0;
    document.querySelectorAll('.line-subtotal').forEach(i => subtotalVal += parseFloat(i.value) || 0);
    document.getElementById('subTotal').textContent = 'Rp ' + subtotalVal.toLocaleString('id-ID');
    const hasTax = document.getElementById('taxCheck').checked;
    const tax = hasTax ? subtotalVal * 0.11 : 0;
    document.getElementById('taxAmount').value = tax.toFixed(0);
    document.getElementById('grandTotal').textContent = 'Rp ' + (subtotalVal + tax).toLocaleString('id-ID');
}

function toggleTax() { updateTotals(); }

function addLine() {
    const tbody = document.getElementById('linesBody');
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
        <td><select name="lines[${idx}][item_id]" class="form-select form-select-sm item-select">
            <option value="">— Pilih Item —</option>${itemOptions}</select></td>
        <td><input type="text" name="lines[${idx}][description]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="lines[${idx}][qty]" class="form-control form-control-sm text-end line-qty" value="1" min="0.01" step="0.01"></td>
        <td><input type="number" name="lines[${idx}][unit_price]" class="form-control form-control-sm text-end line-price" value="0" min="0" step="1"></td>
        <td><input type="number" name="lines[${idx}][subtotal]" class="form-control form-control-sm text-end line-subtotal" value="0" readonly></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
    `;
    tbody.appendChild(tr);
    attachRow(tr);
    idx++;
}

function removeLine(btn) {
    if (document.querySelectorAll('#linesBody .line-row').length > 1) {
        btn.closest('tr').remove();
        updateTotals();
    }
}

function attachRow(tr) {
    tr.querySelector('.item-select')?.addEventListener('change', function() {
        const id = this.value;
        if (id && itemData[id]) {
            tr.querySelector('.line-price').value = itemData[id].price;
            if (!tr.querySelector('[name*="description"]').value) {
                tr.querySelector('[name*="description"]').value = itemData[id].name;
            }
            calcRow(tr);
        }
    });
    tr.querySelectorAll('.line-qty,.line-price').forEach(i => i.addEventListener('input', () => calcRow(tr)));
}

document.querySelectorAll('.line-row').forEach(attachRow);
</script>
@endpush
