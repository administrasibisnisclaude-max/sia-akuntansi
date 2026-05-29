@extends('layouts.app')
@section('title', 'Pergerakan Inventori')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-arrow-left-right me-2"></i>Riwayat Pergerakan Stok</span>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2">
                <select name="item_id" class="form-select form-select-sm">
                    <option value="">Semua Item</option>
                    @foreach($items as $item)<option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
            </form>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">Stok</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tanggal</th><th>Item</th><th>Tipe</th><th class="text-end">Qty</th><th>Referensi</th><th>Catatan</th></tr></thead>
            <tbody>
            @forelse($movements as $m)
                <tr>
                    <td>{{ $m->date->format('d/m/Y') }}</td>
                    <td>{{ $m->item->name }}</td>
                    <td>
                        @if($m->type=='in')<span class="badge bg-success-subtle text-success">Masuk</span>
                        @elseif($m->type=='out')<span class="badge bg-danger-subtle text-danger">Keluar</span>
                        @else<span class="badge bg-info-subtle text-info">Penyesuaian</span>@endif
                    </td>
                    <td class="text-end fw-semibold {{ $m->type=='out' ? 'text-danger' : 'text-success' }}">
                        {{ $m->type == 'out' ? '-' : '+' }}{{ number_format(abs($m->qty), 2, ',', '.') }}
                    </td>
                    <td>{{ $m->reference_type ? "{$m->reference_type} #{$m->reference_id}" : '-' }}</td>
                    <td>{{ $m->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada pergerakan stok</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $movements->links() }}</div>
</div>
@endsection
