<div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label>
<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $paymentMethod->name ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Tipe <span class="text-danger">*</span></label>
<select name="type" class="form-select" required>
    <option value="cash"  @selected(old('type', $paymentMethod->type ?? '') == 'cash') >Kas</option>
    <option value="bank"  @selected(old('type', $paymentMethod->type ?? '') == 'bank') >Bank</option>
    <option value="other" @selected(old('type', $paymentMethod->type ?? '') == 'other')>Lainnya</option>
</select></div>
<div class="mb-3"><label class="form-label">Akun Kas/Bank</label>
<select name="account_id" class="form-select"><option value="">—</option>
@foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('account_id', $paymentMethod->account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach</select></div>
<div class="form-check"><input type="hidden" name="is_active" value="0">
<input type="checkbox" name="is_active" value="1" class="form-check-input" id="pma" @checked(old('is_active', $paymentMethod->is_active ?? true))>
<label class="form-check-label" for="pma">Aktif</label></div>
