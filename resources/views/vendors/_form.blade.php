<div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label>
<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $vendor->name ?? '') }}" required>
@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label">Email</label>
<input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email ?? '') }}"></div>
<div class="mb-3"><label class="form-label">Telepon</label>
<input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone ?? '') }}"></div>
<div class="mb-3"><label class="form-label">Alamat</label>
<textarea name="address" class="form-control" rows="2">{{ old('address', $vendor->address ?? '') }}</textarea></div>
<div class="mb-3"><label class="form-label">Akun Utang</label>
<select name="ap_account_id" class="form-select">
<option value="">— Default —</option>
@foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('ap_account_id', $vendor->ap_account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach
</select></div>
<div class="form-check"><input type="hidden" name="is_active" value="0">
<input type="checkbox" name="is_active" value="1" class="form-check-input" id="va" @checked(old('is_active', $vendor->is_active ?? true))>
<label class="form-check-label" for="va">Aktif</label></div>
