@extends('layouts.app')
@section('title', 'Buat Faktur Penjualan')
@push('styles')
<style>.line-row td{vertical-align:middle;}</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Buat Faktur Penjualan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('ar.invoices.store') }}">
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
                    <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <thead class="table-light"><tr><th>Item</th><th>Deskripsi</th><th style="width:90px">Qty</th><th style="width:130px">Harga Satuan</th><th style="width:80px">PPN%</th><th style="width:120px">Subtotal</th><th style="width:40px"></th></tr></thead>
                    <tbody id="linesBody">
                    <tr class="line-row">
                        <td><select name="lines[0][item_id]" class="form-select form-select-sm item-select" data-index="0">
                            <option value="">— Pilih Item —</option>
                            @foreach($items as $item)<option value="{{ $item->id }}" data-price="{{ $item->sell_price }}" data-unit="{{ $item->unit }}">{{ $item->name }}</option>@endforeach
                        </select></td>
                        <td><input type="text" name="lines[0][description]" class="form-control form-control-sm" required></td>
                        <td><input type="number" name="lines[0][qty]" class="form-control form-control-sm text-end line-qty" value="1" min="0.01" step="0.01"></td>
                        <td><input type="number" name="lines[0][unit_price]" class="form-control form-control-sm text-end line-price" value="0" min="0" step="1"></td>
                        <td><input type="number" name="lines[0][tax_rate]" class="form-control form-control-sm text-end line-tax" value="0" min="0" max="100" step="0.01"></td>
                        <td><input type="number" name="lines[0][subtotal_display]" class="form-control form-control-sm text-end line-subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                    </tbody>
                    <tfoot><tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end" id="grandTotal">0</td><td></td></tr></tfoot>
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
                <button class="btn btn-primary">Simpan Faktur</button>
                <a href="{{ route('ar.invoices.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const itemData = {
    @foreach($items as $item)
    {{ $item->id }}: { price: {{ $item->sell_price }}, name: '{{ addslashes($item->name) }}', unit: '{{ $item->unit }}' },
    @endforeach
};
const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}" data-price="{{ $item->sell_price }}">{{ $item->name }}</option>@endforeach`;
let idx = 1;

function calcRow(tr) {
    const qty = parseFloat(tr.querySelector('.line-qty').value) || 0;
    const price = parseFloat(tr.querySelector('.line-price').value) || 0;
    const sub = qty * price;
    tr.querySelector('.line-subtotal').value = sub.toFixed(0);
    updateGrand();
}

function updateGrand() {
    let total = 0;
    document.querySelectorAll('.line-subtotal').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function addLine() {
    const tbody = document.getElementById('linesBody');
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
        <td><select name="lines[${idx}][item_id]" class="form-select form-select-sm item-select" data-index="${idx}">
            <option value="">— Pilih Item —</option>${itemOptions}</select></td>
        <td><input type="text" name="lines[${idx}][description]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="lines[${idx}][qty]" class="form-control form-control-sm text-end line-qty" value="1" min="0.01" step="0.01"></td>
        <td><input type="number" name="lines[${idx}][unit_price]" class="form-control form-control-sm text-end line-price" value="0" min="0" step="1"></td>
        <td><input type="number" name="lines[${idx}][tax_rate]" class="form-control form-control-sm text-end line-tax" value="0" min="0" max="100" step="0.01"></td>
        <td><input type="number" name="lines[${idx}][subtotal_display]" class="form-control form-control-sm text-end line-subtotal" value="0" readonly></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
    `;
    tbody.appendChild(tr);
    attachRow(tr);
    idx++;
}

function removeLine(btn) {
    if (document.querySelectorAll('#linesBody .line-row').length > 1) {
        btn.closest('tr').remove();
        updateGrand();
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
