@extends('layouts.app')
@section('title', 'Laporan Customer Spent')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter Laporan Customer Spent</div>
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
            <div class="col-md-4">
                <label class="form-label">Pelanggan</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('reports.customer-spent') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Pelanggan Aktif</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-people"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($merged->count()) }}</div>
                <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Tagihan</span>
                    <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-file-invoice"></i></span>
                </div>
                <div class="fs-4 fw-bold text-info">Rp {{ number_format($grandBilled, 0, ',', '.') }}</div>
                <small class="text-muted">Semua invoice</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Dibayar</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check-circle"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($grandPaid, 0, ',', '.') }}</div>
                <small class="text-muted">Dari pembayaran tercatat</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Outstanding</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div class="fs-4 fw-bold text-warning">Rp {{ number_format($grandOutstanding, 0, ',', '.') }}</div>
                <small class="text-muted">Belum terbayar</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-lines-fill me-2 text-primary"></i>Detail Customer Spent</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print">
            <i class="bi bi-printer me-1"></i>Cetak
        </button>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Customer Spent</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Pelanggan</th>
                        <th>Alamat</th>
                        <th class="text-end">Jml Invoice</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Total Dibayar</th>
                        <th class="text-end">Outstanding</th>
                        <th>Progres</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($merged as $i => $row)
                    <tr class="{{ $row->outstanding > 0 ? 'table-warning' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $row->customer_code }}</code></td>
                        <td class="fw-semibold">{{ $row->customer_name }}</td>
                        <td class="text-muted small">{{ Str::limit($row->address ?? '-', 30) }}</td>
                        <td class="text-end">{{ number_format($row->invoice_count) }}</td>
                        <td class="text-end">Rp {{ number_format($row->total_billed, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($row->total_paid, 0, ',', '.') }}</td>
                        <td class="text-end {{ $row->outstanding > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            Rp {{ number_format($row->outstanding, 0, ',', '.') }}
                        </td>
                        <td style="min-width:100px">
                            @php $pct = $row->total_billed > 0 ? min(100, $row->total_paid / $row->total_billed * 100) : 0; @endphp
                            <div class="progress" style="height:8px" title="{{ number_format($pct, 1) }}%">
                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-warning' }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($pct, 0) }}%</small>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-2"></i>Tidak ada data pelanggan pada periode ini.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="4" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($merged->sum('invoice_count')) }}</td>
                        <td class="text-end">Rp {{ number_format($grandBilled, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($grandPaid, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">Rp {{ number_format($grandOutstanding, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
