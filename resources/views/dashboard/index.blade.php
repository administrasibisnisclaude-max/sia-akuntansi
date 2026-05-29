@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Piutang</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="fs-4 fw-bold text-primary">Rp {{ number_format($totalAr, 0, ',', '.') }}</div>
                <small class="text-muted">Belum terbayar</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Utang</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-file-earmark-text"></i></span>
                </div>
                <div class="fs-4 fw-bold text-danger">Rp {{ number_format($totalAp, 0, ',', '.') }}</div>
                <small class="text-muted">Belum terbayar</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Saldo Kas</span>
                    <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-cash"></i></span>
                </div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($cashBalance, 0, ',', '.') }}</div>
                <small class="text-muted">Akun Kas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Laba / Rugi</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-graph-up"></i></span>
                </div>
                <div class="fs-4 fw-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format(abs($netIncome), 0, ',', '.') }}
                </div>
                <small class="text-muted">{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }} berjalan</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Emisi Karbon YTD</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-cloud-haze2"></i></span>
                </div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($totalCarbonYtd, 2, ',', '.') }} kg CO₂e</div>
                <small class="text-muted"><a href="{{ route('reports.environmental') }}" class="text-muted">Lihat laporan lingkungan</a></small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Limbah YTD</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-trash3"></i></span>
                </div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($totalWasteYtd, 2, ',', '.') }} kg</div>
                <small class="text-muted"><a href="{{ route('reports.environmental') }}" class="text-muted">Lihat laporan lingkungan</a></small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Jurnal Terbaru</span>
        <a href="{{ route('journals.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>No. Jurnal</th><th>Tanggal</th><th>Keterangan</th><th>Status</th><th>Dibuat</th>
            </tr></thead>
            <tbody>
            @forelse($recentJournals as $j)
                <tr>
                    <td><a href="{{ route('journals.show', $j) }}">{{ $j->journal_number }}</a></td>
                    <td>{{ $j->date->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($j->description, 50) }}</td>
                    <td><span class="badge badge-{{ $j->status }}">{{ ucfirst($j->status) }}</span></td>
                    <td>{{ $j->createdBy->name }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada jurnal</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
