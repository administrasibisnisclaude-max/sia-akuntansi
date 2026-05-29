@extends('layouts.app')
@section('title', 'Tagihan Pembelian (AP)')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text me-2"></i>Tagihan Pembelian (Utang)</span>
        <a href="{{ route('ap.bills.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Buat Tagihan</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>No. Tagihan</th><th>Vendor</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Total</th><th>Terbayar</th><th>Sisa</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td><a href="{{ route('ap.bills.show', $bill) }}">{{ $bill->bill_number }}</a></td>
                    <td>{{ $bill->vendor->name }}</td>
                    <td>{{ $bill->date->format('d/m/Y') }}</td>
                    <td>{{ $bill->due_date->format('d/m/Y') }}</td>
                    <td class="text-end">Rp {{ number_format($bill->total, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($bill->paid, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($bill->remaining, 0, ',', '.') }}</td>
                    <td><span class="badge badge-{{ $bill->status }}">{{ ucfirst($bill->status) }}</span></td>
                    <td><a href="{{ route('ap.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-3">Belum ada tagihan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $bills->links() }}</div>
</div>
@endsection
