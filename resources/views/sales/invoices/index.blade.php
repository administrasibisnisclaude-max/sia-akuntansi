@extends('layouts.app')
@section('title', 'Faktur Penjualan')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt-cutoff me-2"></i>Faktur Penjualan</span>
        <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Buat Faktur
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Sisa</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td><a href="{{ route('sales.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ $inv->date->format('d/m/Y') }}</td>
                    <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                    <td class="text-end">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                    <td class="text-end {{ $inv->remaining > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($inv->remaining, 0, ',', '.') }}
                    </td>
                    <td><a href="{{ route('sales.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Belum ada faktur penjualan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
