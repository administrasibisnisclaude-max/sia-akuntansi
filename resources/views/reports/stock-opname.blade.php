@extends('layouts.app')
@section('title', 'Laporan Stock Opname')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filter Laporan Stock Opname
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Per Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Item</label>
                <select name="item_id" class="form-select form-select-sm">
                    <option value="">Semua Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('reports.stock-opname') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Item</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-box-seam"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($totalItems) }}</div>
                <small class="text-muted">Per tanggal {{ date('d/m/Y', strtotime($to)) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Nilai Stok</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                <small class="text-muted">Harga beli &times; stok akhir</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Item Stok Habis</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($emptyStock) }}</div>
                <small class="text-muted">Stok &le; 0</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard-data me-2 text-primary"></i>Detail Stock Opname</span>
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Stock Opname</strong><br>
            <small class="text-muted">Per Tanggal: {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Item</th>
                        <th>Nama Item</th>
                        <th>Satuan</th>
                        <th>Kategori</th>
                        <th class="text-end">Mutasi Masuk</th>
                        <th class="text-end">Mutasi Keluar</th>
                        <th class="text-end">Penyesuaian</th>
                        <th class="text-end">Stok Akhir</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Nilai Stok</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $i => $row)
                    <tr class="{{ $row->stock_current <= 0 ? 'table-danger' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $row->item_code }}</code></td>
                        <td>{{ $row->item_name }}</td>
                        <td>{{ $row->unit }}</td>
                        <td>{{ $row->category ?? '-' }}</td>
                        <td class="text-end">{{ number_format($row->total_in, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->total_out, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->total_adj, 2, ',', '.') }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->stock_current, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->buy_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->stock_value, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-2"></i>Tidak ada data item.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($rows->sum('total_in'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rows->sum('total_out'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rows->sum('total_adj'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rows->sum('stock_current'), 2, ',', '.') }}</td>
                        <td></td>
                        <td class="text-end">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
