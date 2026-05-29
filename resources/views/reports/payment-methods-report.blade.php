@extends('layouts.app')
@section('title', 'Laporan Metode Pembayaran')
@section('content')
<div class="card mb-4 no-print">
    <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter Laporan Metode Pembayaran</div>
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
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method_id" class="form-select form-select-sm">
                    <option value="">Semua Metode</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->id }}" @selected(request('payment_method_id') == $pm->id)>{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('reports.payment-methods-report') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Transaksi</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($grandTrx) }}</div>
                <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Penerimaan</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-arrow-down-circle"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($grandReceived, 0, ',', '.') }}</div>
                <small class="text-muted">AR + Sales</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Pengeluaran</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-arrow-up-circle"></i></span>
                </div>
                <div class="fs-4 fw-bold text-danger">Rp {{ number_format($grandDisbursed, 0, ',', '.') }}</div>
                <small class="text-muted">AP</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Net</span>
                    <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-balance-scale"></i></span>
                </div>
                @php $net = $grandReceived - $grandDisbursed; @endphp
                <div class="fs-4 fw-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($net, 0, ',', '.') }}
                </div>
                <small class="text-muted">Penerimaan - Pengeluaran</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-wallet2 me-2 text-primary"></i>Ringkasan per Metode Pembayaran</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print">
            <i class="bi bi-printer me-1"></i>Cetak
        </button>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Laporan Metode Pembayaran</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Metode Pembayaran</th>
                        <th>Tipe</th>
                        <th class="text-end">Jml Transaksi</th>
                        <th class="text-end">Total Penerimaan</th>
                        <th class="text-end">Total Pengeluaran</th>
                        <th class="text-end">Net</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($summary as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $row->pm_name }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($row->pm_type) }}</span></td>
                        <td class="text-end">{{ number_format($row->trx_count) }}</td>
                        <td class="text-end text-success">Rp {{ number_format($row->received, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">Rp {{ number_format($row->disbursed, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold {{ $row->net >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($row->net, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-2"></i>Tidak ada data transaksi pada periode ini.
                    </td></tr>
                @endforelse
                </tbody>
                <tfoot class="fw-bold table-light">
                    <tr>
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($grandTrx) }}</td>
                        <td class="text-end text-success">Rp {{ number_format($grandReceived, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">Rp {{ number_format($grandDisbursed, 0, ',', '.') }}</td>
                        <td class="text-end {{ ($grandReceived - $grandDisbursed) >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($grandReceived - $grandDisbursed, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-list-ul me-2"></i>Detail Transaksi per Channel</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Metode Pembayaran</th>
                        <th>Channel</th>
                        <th class="text-end">Jml Transaksi</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($detail as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->pm_name }}</td>
                        <td>
                            @if($row->channel === 'Pembayaran AP')
                                <span class="badge bg-danger-subtle text-danger">{{ $row->channel }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success">{{ $row->channel }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($row->trx_count) }}</td>
                        <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
