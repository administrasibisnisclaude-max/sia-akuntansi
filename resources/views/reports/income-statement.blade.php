@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up me-2"></i>Laporan Laba Rugi</span>
        <div class="d-flex gap-2 no-print">
            <form class="d-flex gap-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
        </div>
    </div>
    <div class="card-body" style="max-width:600px;margin:0 auto">
        <div class="text-center mb-3">
            <strong>Laporan Laba Rugi</strong><br>
            <small class="text-muted">{{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <h6 class="fw-bold text-success">PENDAPATAN</h6>
        @foreach($revenues as $r)
            @php $amt = $r->total_credit - $r->total_debit; @endphp
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span><code class="small text-muted">{{ $r->account_code }}</code> {{ $r->name }}</span>
                <span>Rp {{ number_format($amt, 0, ',', '.') }}</span>
            </div>
        @endforeach
        <div class="d-flex justify-content-between py-2 fw-bold border-bottom border-2">
            <span>Total Pendapatan</span>
            <span class="text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
        <h6 class="fw-bold text-danger mt-3">BEBAN</h6>
        @foreach($expenses as $e)
            @php $amt = $e->total_debit - $e->total_credit; @endphp
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span><code class="small text-muted">{{ $e->account_code }}</code> {{ $e->name }}</span>
                <span>Rp {{ number_format($amt, 0, ',', '.') }}</span>
            </div>
        @endforeach
        <div class="d-flex justify-content-between py-2 fw-bold border-bottom border-2">
            <span>Total Beban</span>
            <span class="text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex justify-content-between py-3 fw-bold fs-5 border-top border-2 mt-2">
            <span>{{ $netIncome >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</span>
            <span class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($netIncome), 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>
@endsection
