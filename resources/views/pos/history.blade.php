@extends('layouts.app')
@section('title', 'Riwayat POS')
@section('content')
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 me-1">Tanggal</label>
            <input type="date" name="date" class="form-control form-control-sm" style="width:160px;"
                   value="{{ request('date', today()->toDateString()) }}">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <div class="ms-auto">
                <span class="text-muted small">Total Hari Ini:</span>
                <strong class="ms-1 text-success">Rp {{ number_format($todayTotal, 0, ',', '.') }}</strong>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-cash-register me-2"></i>Riwayat Transaksi POS</span>
        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus me-1"></i>Transaksi Baru
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Pembayaran</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Dibayar</th>
                    <th class="text-end">Kembalian</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td><code>{{ $tx->transaction_number }}</code></td>
                    <td>{{ $tx->date->format('d/m/Y') }}</td>
                    <td>{{ $tx->cashier->name }}</td>
                    <td>
                        <span class="badge rounded-pill
                            {{ match($tx->payment_method) {
                                'tunai'    => 'bg-success-subtle text-success',
                                'qris'     => 'bg-primary-subtle text-primary',
                                'transfer' => 'bg-info-subtle text-info',
                                'ewallet'  => 'bg-warning-subtle text-warning',
                                default    => 'bg-secondary-subtle text-secondary',
                            } }}">
                            {{ $tx->paymentMethodLabel() }}
                        </span>
                    </td>
                    <td class="text-end fw-semibold">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($tx->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($tx->change_amount, 0, ',', '.') }}</td>
                    <td class="text-end">
                        <a href="{{ route('pos.receipt', $tx) }}" target="_blank"
                           class="btn btn-xs btn-sm btn-outline-secondary py-0 px-2">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi POS.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $transactions->withQueryString()->links() }}</div>
</div>
@endsection
