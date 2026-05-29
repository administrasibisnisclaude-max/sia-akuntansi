@extends('layouts.app')
@section('title', 'Laporan Produk Terjual')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filter Laporan Produk Terjual
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
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
                <label class="form-label">Pelanggan</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('reports.sold-products') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Qty Terjual</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-box-seam"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($grandQty, 2, ',', '.') }}</div>
                <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Pendapatan</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</div>
                <small class="text-muted">Dari faktur penjualan & AR</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Jumlah Jenis Produk</span>
                    <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-grid"></i></span>
                </div>
                <div class="fs-4 fw-bold text-info">{{ number_format($merged->count()) }}</div>
                <small class="text-muted">Produk berbeda yang terjual</small>
            </div>
        </div>
    </div>
</div>

@if($topItems->isNotEmpty())
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-bar-chart-fill me-2"></i>Top 5 Produk &mdash; Qty Terjual Tertinggi
    </div>
    <div class="card-body">
        @php $maxQty = $topItems->max('total_qty'); @endphp
        @foreach($topItems as $t)
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold">{{ $t->item_name }}
                    @if($t->item_code)<code class="ms-1">{{ $t->item_code }}</code>@endif
                </span>
                <span class="small text-muted">{{ number_format($t->total_qty, 2, ',', '.') }} {{ $t->unit }}</span>
            </div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width: {{ $maxQty > 0 ? ($t->total_qty / $maxQty * 100) : 0 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cart-check me-2 text-primary"></i>Detail Produk Terjual</span>
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Produk Terjual</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Item</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th class="text-end">Total Qty</th>
                        <th class="text-end">Total Pendapatan</th>
                        <th class="text-end">Rata-rata Harga</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($merged as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $row->item_code }}</code></td>
                        <td>{{ $row->item_name }}</td>
                        <td>{{ $row->unit }}</td>
                        <td class="text-end">{{ number_format($row->total_qty, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ $row->total_qty > 0 ? number_format($row->total_revenue / $row->total_qty, 0, ',', '.') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-cart-x me-2"></i>Tidak ada data produk terjual pada periode ini.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="4" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($grandQty, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
