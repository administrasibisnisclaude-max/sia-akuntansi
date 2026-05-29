@extends('layouts.app')
@section('title', 'Laporan Pajak')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header">
        <i class="bi bi-funnel me-2"></i>Filter Laporan Pajak
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
                <label class="form-label">Sumber Dokumen</label>
                <select name="source_type" class="form-select form-select-sm">
                    <option value="all" @selected($sourceType === 'all')>Semua</option>
                    <option value="sales_invoice" @selected($sourceType === 'sales_invoice')>Faktur Penjualan</option>
                    <option value="ar_invoice" @selected($sourceType === 'ar_invoice')>Invoice AR</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('reports.pajak') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total DPP</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</div>
                <small class="text-muted">Dasar Pengenaan Pajak</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Pajak Dipungut</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-percent"></i></span>
                </div>
                <div class="fs-4 fw-bold text-warning">Rp {{ number_format($totalTax, 0, ',', '.') }}</div>
                <small class="text-muted">PPN / Pajak lainnya</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Jumlah Dokumen</span>
                    <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-file-text"></i></span>
                </div>
                <div class="fs-4 fw-bold text-info">{{ number_format($rows->count()) }}</div>
                <small class="text-muted">Faktur dengan pajak</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total dengan Pajak</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($totalWithTax, 0, ',', '.') }}</div>
                <small class="text-muted">DPP + Pajak</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-percent me-2 text-primary"></i>Detail Laporan Pajak</span>
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Pajak</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No. Dokumen</th>
                        <th>Sumber</th>
                        <th>Pelanggan</th>
                        <th class="text-end">DPP (Subtotal)</th>
                        <th class="text-end">Pajak</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($row->date)) }}</td>
                        <td><code>{{ $row->invoice_number }}</code></td>
                        <td>
                            @if($row->source === 'Faktur Penjualan')
                                <span class="badge bg-success-subtle text-success">{{ $row->source }}</span>
                            @else
                                <span class="badge bg-info-subtle text-info">{{ $row->source }}</span>
                            @endif
                        </td>
                        <td>{{ $row->customer_name }}</td>
                        <td class="text-end">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->tax_amount, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-2"></i>Tidak ada data pajak pada periode ini.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalTax, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalWithTax, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
