@extends('layouts.app')
@section('title', 'Laporan Dampak Lingkungan')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filter Laporan Lingkungan
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
            <div class="col-md-2">
                <label class="form-label">Kategori Limbah</label>
                <input type="text" name="waste_category" class="form-control form-control-sm"
                       value="{{ request('waste_category') }}" placeholder="Semua">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategori Karbon</label>
                <input type="text" name="carbon_category" class="form-control form-control-sm"
                       value="{{ request('carbon_category') }}" placeholder="Semua">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sumber</label>
                <select name="source_type" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="sales_invoice_line" @selected(request('source_type') == 'sales_invoice_line')>Faktur Penjualan</option>
                    <option value="ar_invoice_line" @selected(request('source_type') == 'ar_invoice_line')>Faktur AR</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Limbah</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-trash3"></i></span>
                </div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($totalWaste, 2, ',', '.') }} kg</div>
                <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Emisi Karbon</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-cloud-haze2"></i></span>
                </div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($totalCarbon, 2, ',', '.') }} kg CO₂e</div>
                <small class="text-muted">Emisi karbon dioksida ekuivalen</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Jumlah Transaksi</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">{{ number_format($totalTrx) }}</div>
                <small class="text-muted">Transaksi dengan dampak lingkungan</small>
            </div>
        </div>
    </div>
</div>

{{-- Warning: no items configured --}}
@if($configuredItems->isEmpty())
<div class="alert alert-warning d-flex gap-3 align-items-start mb-4 no-print">
    <i class="fas fa-exclamation-triangle fa-lg mt-1"></i>
    <div>
        <strong>Belum ada produk yang dikonfigurasi data lingkungan.</strong><br>
        Laporan ini hanya mencatat dampak dari produk yang memiliki nilai <strong>Limbah/Unit (kg)</strong> atau <strong>Karbon/Unit (kg CO₂e)</strong> lebih dari 0.
        Silakan buka halaman <a href="{{ route('items.index') }}">Item / Produk</a>, edit setiap produk, dan isi bagian <em>Data Lingkungan</em>.
    </div>
</div>
@elseif($rows->isEmpty())
<div class="alert alert-info d-flex gap-3 align-items-start mb-4 no-print">
    <i class="fas fa-info-circle fa-lg mt-1"></i>
    <div>
        <strong>Tidak ada transaksi dampak lingkungan pada periode ini.</strong><br>
        Terdapat <strong>{{ $configuredItems->count() }} produk</strong> yang sudah dikonfigurasi. Pastikan faktur yang menggunakan produk tersebut sudah diposting pada periode yang dipilih.
    </div>
</div>
@endif

{{-- Configured items summary --}}
@if($configuredItems->isNotEmpty())
<div class="card mb-4 no-print">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-leaf me-2 text-success"></i>Produk Terkonfigurasi Data Lingkungan ({{ $configuredItems->count() }})</span>
        @if($unconfiguredItems->isNotEmpty())
        <span class="badge bg-warning text-dark">{{ $unconfiguredItems->count() }} produk belum dikonfigurasi</span>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Kode</th><th>Nama Produk</th>
                    <th class="text-end">Limbah/Unit (kg)</th>
                    <th>Kat. Limbah</th>
                    <th class="text-end">Karbon/Unit (kg CO₂e)</th>
                    <th>Kat. Karbon</th>
                    <th class="no-print"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($configuredItems as $ci)
            <tr>
                <td><code>{{ $ci->item_code }}</code></td>
                <td>{{ $ci->name }}</td>
                <td class="text-end">{{ number_format($ci->waste_per_unit, 4, ',', '.') }}</td>
                <td>{{ $ci->waste_category ?: '-' }}</td>
                <td class="text-end">{{ number_format($ci->carbon_per_unit, 4, ',', '.') }}</td>
                <td>{{ $ci->carbon_category ?: '-' }}</td>
                <td class="no-print"><a href="{{ route('items.edit', $ci->id) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">Edit</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($topWaste->isNotEmpty())
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-bar-chart-fill me-2"></i>Top 5 Produk &mdash; Limbah Tertinggi
    </div>
    <div class="card-body">
        @php $maxWaste = $topWaste->max('total_waste'); @endphp
        @foreach($topWaste as $t)
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold">{{ $t->item_name }}
                    @if($t->item_code)<code class="ms-1">{{ $t->item_code }}</code>@endif
                </span>
                <span class="small text-muted">{{ number_format($t->total_waste, 2, ',', '.') }} kg</span>
            </div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-warning" role="progressbar"
                     style="width: {{ $maxWaste > 0 ? ($t->total_waste / $maxWaste * 100) : 0 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-leaf me-2 text-success"></i>Detail Dampak Lingkungan</span>
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Dampak Lingkungan</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Produk</th>
                        <th>Kategori Limbah</th>
                        <th>Kategori Karbon</th>
                        <th class="text-end">Total Qty Terjual</th>
                        <th class="text-end">Total Limbah (kg)</th>
                        <th class="text-end">Total Emisi (kg CO₂e)</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td><code>{{ $row->item_code }}</code></td>
                        <td>{{ $row->item_name }}</td>
                        <td>{{ $row->waste_category ?? '-' }}</td>
                        <td>{{ $row->carbon_category ?? '-' }}</td>
                        <td class="text-end">{{ number_format($row->total_qty, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->total_waste, 4, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->total_carbon, 4, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-leaf me-2 text-success"></i>Tidak ada data dampak lingkungan pada periode ini.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($totalWaste, 4, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalCarbon, 4, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
