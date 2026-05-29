@extends('layouts.app')
@section('title', 'Laporan Arus Kas')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-stack me-2"></i>Laporan Arus Kas</span>
        <div class="d-flex gap-2 no-print">
            <form class="d-flex gap-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
        </div>
    </div>
    <div class="card-body" style="max-width:500px;margin:0 auto">
        <div class="text-center mb-4">
            <strong>Laporan Arus Kas</strong><br>
            <small class="text-muted">{{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span>Total Penerimaan Kas</span>
            <span class="text-success fw-semibold">Rp {{ number_format($totalIn, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span>Total Pengeluaran Kas</span>
            <span class="text-danger fw-semibold">Rp {{ number_format($totalOut, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex justify-content-between py-3 fw-bold fs-5 border-top border-2 mt-2">
            <span>Arus Kas Bersih</span>
            <span class="{{ $netCash >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($netCash), 0, ',', '.') }}
            </span>
        </div>
        <div class="alert alert-info mt-3 small">
            <i class="bi bi-info-circle me-1"></i>
            Laporan ini mencatat semua pergerakan pada akun kas (kode 110x). Untuk laporan arus kas lengkap dengan metode tidak langsung, data jurnal dapat diekspor.
        </div>
    </div>
</div>
@endsection
