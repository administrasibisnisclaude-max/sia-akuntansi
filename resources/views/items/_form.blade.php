<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Nama Item <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3"><label class="form-label">Tipe <span class="text-danger">*</span></label>
    <select name="type" class="form-select" required>
        <option value="product" @selected(old('type', $item->type ?? 'product') == 'product')>Produk</option>
        <option value="service" @selected(old('type', $item->type ?? '') == 'service')>Jasa</option>
    </select></div>
    <div class="col-md-3"><label class="form-label">Satuan <span class="text-danger">*</span></label>
    <input type="text" name="unit" class="form-control" value="{{ old('unit', $item->unit ?? 'pcs') }}" required></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4"><label class="form-label">Harga Beli</label>
    <input type="number" name="buy_price" class="form-control" value="{{ old('buy_price', $item->buy_price ?? 0) }}" min="0" step="1"></div>
    <div class="col-md-4"><label class="form-label">Harga Jual</label>
    <input type="number" name="sell_price" class="form-control" value="{{ old('sell_price', $item->sell_price ?? 0) }}" min="0" step="1"></div>
    <div class="col-md-4"><label class="form-label">Kategori</label>
    <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}"></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Akun HPP</label>
    <select name="cogs_account_id" class="form-select"><option value="">—</option>
    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('cogs_account_id', $item->cogs_account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Akun Penjualan</label>
    <select name="sales_account_id" class="form-select"><option value="">—</option>
    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('sales_account_id', $item->sales_account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach</select></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Akun Pembelian</label>
    <select name="purchase_account_id" class="form-select"><option value="">—</option>
    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('purchase_account_id', $item->purchase_account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Akun Persediaan</label>
    <select name="inventory_account_id" class="form-select"><option value="">—</option>
    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('inventory_account_id', $item->inventory_account_id ?? '') == $a->id)>{{ $a->account_code }} - {{ $a->name }}</option>@endforeach</select></div>
</div>
<div class="form-check"><input type="hidden" name="is_active" value="0">
<input type="checkbox" name="is_active" value="1" class="form-check-input" id="ia" @checked(old('is_active', $item->is_active ?? true))>
<label class="form-check-label" for="ia">Aktif</label></div>
