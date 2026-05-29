@extends('layouts.app')
@section('title', 'Edit Faktur')
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil me-2"></i>Edit Faktur: {{ $invoice->invoice_number }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('ar.invoices.update', $invoice) }}">
            @csrf @method('PUT')
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $c)<option value="{{ $c->id }}" @selected($c->id == $invoice->customer_id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $invoice->date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <thead class="table-light"><tr><th>Item</th><th>Deskripsi</th><th style="width:90px">Qty</th><th style="width:130px">Harga Satuan</th><th style="width:80px">PPN%</th><th style="width:120px">Subtotal</th><th style="width:40px"></th></tr></thead>
                    <tbody id="linesBody">
                    @foreach($invoice->lines as $i => $line)
                    <tr class="line-row">
                        <td><select name="lines[{{ $i }}][item_id]" class="form-select form-select-sm item-select">
                            <option value="">—</option>
                            @foreach($items as $item)<option value="{{ $item->id }}" @selected($item->id == $line->item_id) data-price="{{ $item->sell_price }}">{{ $item->name }}</option>@endforeach
                        </select></td>
                        <td><input type="text" name="lines[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $line->description }}" required></td>
                        <td><input type="number" name="lines[{{ $i }}][qty]" class="form-control form-control-sm text-end line-qty" value="{{ $line->qty }}" min="0.01" step="0.01"></td>
                        <td><input type="number" name="lines[{{ $i }}][unit_price]" class="form-control form-control-sm text-end line-price" value="{{ $line->unit_price }}" min="0" step="1"></td>
                        <td><input type="number" name="lines[{{ $i }}][tax_rate]" class="form-control form-control-sm text-end line-tax" value="{{ $line->tax_rate }}" min="0" max="100"></td>
                        <td><input type="number" name="lines[{{ $i }}][subtotal_display]" class="form-control form-control-sm text-end line-subtotal" value="{{ $line->subtotal }}" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end" id="grandTotal">0</td><td></td></tr></tfoot>
                </table>
            </div>
            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addLine()"><i class="bi bi-plus me-1"></i>Tambah Baris</button>
            </div>
            <div class="mb-3" style="max-width:400px">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ $invoice->notes }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Perbarui</button>
                <a href="{{ route('ar.invoices.show', $invoice) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const itemData = { @foreach($items as $item){{ $item->id }}: { price: {{ $item->sell_price }}, name: '{{ addslashes($item->name) }}' },@endforeach };
const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}" data-price="{{ $item->sell_price }}">{{ $item->name }}</option>@endforeach`;
let idx = {{ $invoice->lines->count() }};
function calcRow(tr){const qty=parseFloat(tr.querySelector('.line-qty').value)||0;const price=parseFloat(tr.querySelector('.line-price').value)||0;tr.querySelector('.line-subtotal').value=(qty*price).toFixed(0);updateGrand();}
function updateGrand(){let t=0;document.querySelectorAll('.line-subtotal').forEach(i=>t+=parseFloat(i.value)||0);document.getElementById('grandTotal').textContent='Rp '+t.toLocaleString('id-ID');}
function addLine(){const tbody=document.getElementById('linesBody');const tr=document.createElement('tr');tr.className='line-row';tr.innerHTML=`<td><select name="lines[${idx}][item_id]" class="form-select form-select-sm item-select"><option value="">—</option>${itemOptions}</select></td><td><input type="text" name="lines[${idx}][description]" class="form-control form-control-sm" required></td><td><input type="number" name="lines[${idx}][qty]" class="form-control form-control-sm text-end line-qty" value="1" min="0.01" step="0.01"></td><td><input type="number" name="lines[${idx}][unit_price]" class="form-control form-control-sm text-end line-price" value="0" min="0" step="1"></td><td><input type="number" name="lines[${idx}][tax_rate]" class="form-control form-control-sm text-end line-tax" value="0" min="0" max="100"></td><td><input type="number" name="lines[${idx}][subtotal_display]" class="form-control form-control-sm text-end line-subtotal" value="0" readonly></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>`;tbody.appendChild(tr);attachRow(tr);idx++;}
function removeLine(btn){if(document.querySelectorAll('#linesBody .line-row').length>1){btn.closest('tr').remove();updateGrand();}}
function attachRow(tr){tr.querySelector('.item-select')?.addEventListener('change',function(){const id=this.value;if(id&&itemData[id]){tr.querySelector('.line-price').value=itemData[id].price;calcRow(tr);}});tr.querySelectorAll('.line-qty,.line-price').forEach(i=>i.addEventListener('input',()=>calcRow(tr)));}
document.querySelectorAll('.line-row').forEach(attachRow);updateGrand();
</script>
@endpush
