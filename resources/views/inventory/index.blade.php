@extends('layouts.app')
@section('title', 'Inventori')
@section('content')
<div class="row g-3">
<div class="col-md-8">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-archive me-2"></i>Stok Barang</span>
        <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary btn-sm">Riwayat Pergerakan</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama Item</th><th>Satuan</th><th class="text-end">Stok</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><code>{{ $item->item_code }}</code></td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end fw-semibold {{ $item->stock <= 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($item->stock, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada item produk</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
<div class="col-md-4">
    <div class="card">
        <div class="card-header"><i class="bi bi-sliders me-2"></i>Penyesuaian Stok</div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.adjustment') }}">
                @csrf
                <div class="mb-3"><label class="form-label">Item <span class="text-danger">*</span></label>
                <select name="item_id" class="form-select" required><option value="">— Pilih —</option>
                @foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select></div>
                <div class="mb-3"><label class="form-label">Jumlah Penyesuaian <span class="text-danger">*</span></label>
                <input type="number" name="qty" class="form-control" step="0.01" required>
                <small class="text-muted">Gunakan nilai negatif untuk pengurangan</small></div>
                <div class="mb-3"><label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                <div class="mb-3"><label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-primary w-100">Simpan Penyesuaian</button>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
