<div class="mb-3">
    <label class="form-label">Kode Akun <span class="text-danger">*</span></label>
    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror"
        value="{{ old('account_code', $account->account_code ?? '') }}" required>
    @error('account_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Nama Akun <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $account->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-3 mb-3">
    <div class="col">
        <label class="form-label">Tipe <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach(['Asset','Liability','Equity','Revenue','Expense'] as $type)
                <option value="{{ $type }}" @selected(old('type', $account->type ?? '') == $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="col">
        <label class="form-label">Saldo Normal <span class="text-danger">*</span></label>
        <select name="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror" required>
            <option value="debit"  @selected(old('normal_balance', $account->normal_balance ?? '') == 'debit') >Debit</option>
            <option value="credit" @selected(old('normal_balance', $account->normal_balance ?? '') == 'credit')>Kredit</option>
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Akun Induk</label>
    <select name="parent_id" class="form-select">
        <option value="">— Tidak ada —</option>
        @foreach($parents as $p)
            <option value="{{ $p->id }}" @selected(old('parent_id', $account->parent_id ?? '') == $p->id)>
                {{ $p->account_code }} - {{ $p->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
        @checked(old('is_active', $account->is_active ?? true))>
    <label class="form-check-label" for="is_active">Aktif</label>
</div>
