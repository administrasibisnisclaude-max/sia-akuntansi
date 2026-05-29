@extends('layouts.app')
@section('title', 'Edit Jurnal')
@push('styles')
<style>.line-row td{vertical-align:middle;}.btn-remove-line{visibility:hidden;}.line-row:hover .btn-remove-line{visibility:visible;}</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil me-2"></i>Edit Jurnal: {{ $journal->journal_number }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('journals.update', $journal) }}">
            @csrf @method('PUT')
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $journal->date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $journal->description) }}" required>
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered" id="linesTable">
                    <thead class="table-light">
                        <tr><th style="width:35%">Akun</th><th>Keterangan Baris</th><th style="width:15%">Debit (Rp)</th><th style="width:15%">Kredit (Rp)</th><th style="width:40px"></th></tr>
                    </thead>
                    <tbody id="linesBody">
                    @foreach($journal->lines as $i => $line)
                        <tr class="line-row">
                            <td><select name="lines[{{ $i }}][account_id]" class="form-select form-select-sm" required>
                                <option value="">— Pilih Akun —</option>
                                @foreach($accounts as $acc)<option value="{{ $acc->id }}" @selected($acc->id == $line->account_id)>{{ $acc->account_code }} - {{ $acc->name }}</option>@endforeach
                            </select></td>
                            <td><input type="text" name="lines[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $line->description }}"></td>
                            <td><input type="number" name="lines[{{ $i }}][debit]" class="form-control form-control-sm text-end line-debit" value="{{ $line->debit }}" min="0" step="1"></td>
                            <td><input type="number" name="lines[{{ $i }}][credit]" class="form-control form-control-sm text-end line-credit" value="{{ $line->credit }}" min="0" step="1"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr class="fw-bold"><td colspan="2" class="text-end">Total</td><td class="text-end" id="totalDebit">0</td><td class="text-end" id="totalCredit">0</td><td></td></tr></tfoot>
                </table>
            </div>
            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addLine()"><i class="bi bi-plus me-1"></i>Tambah Baris</button>
                <span id="balanceIndicator" class="ms-auto fw-semibold"></span>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('journals.show', $journal) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const accountOptions = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->name }}</option>@endforeach`;
let lineIndex = {{ $journal->lines->count() }};
function addLine(){const tbody=document.getElementById('linesBody');const tr=document.createElement('tr');tr.className='line-row';tr.innerHTML=`<td><select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm" required><option value="">— Pilih Akun —</option>${accountOptions}</select></td><td><input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm"></td><td><input type="number" name="lines[${lineIndex}][debit]" class="form-control form-control-sm text-end line-debit" value="0" min="0" step="1"></td><td><input type="number" name="lines[${lineIndex}][credit]" class="form-control form-control-sm text-end line-credit" value="0" min="0" step="1"></td><td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>`;tbody.appendChild(tr);lineIndex++;attachListeners();updateTotals();}
function removeLine(btn){const rows=document.querySelectorAll('#linesBody .line-row');if(rows.length>2){btn.closest('tr').remove();updateTotals();}}
function updateTotals(){let d=0,c=0;document.querySelectorAll('.line-debit').forEach(i=>d+=parseFloat(i.value)||0);document.querySelectorAll('.line-credit').forEach(i=>c+=parseFloat(i.value)||0);document.getElementById('totalDebit').textContent=d.toLocaleString('id-ID');document.getElementById('totalCredit').textContent=c.toLocaleString('id-ID');const ind=document.getElementById('balanceIndicator');if(Math.abs(d-c)<0.01&&d>0){ind.innerHTML='<span class="text-success"><i class="bi bi-check-circle me-1"></i>Seimbang</span>';}else{ind.innerHTML=`<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Selisih: ${(d-c).toLocaleString('id-ID')}</span>`;}}
function attachListeners(){document.querySelectorAll('.line-debit,.line-credit').forEach(i=>{i.removeEventListener('input',updateTotals);i.addEventListener('input',updateTotals);});}
attachListeners();updateTotals();
</script>
@endpush
