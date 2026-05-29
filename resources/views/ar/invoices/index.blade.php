@extends('layouts.app')
@section('title', 'Faktur Penjualan (AR)')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt me-2"></i>Faktur Penjualan (Piutang)</span>
        <a href="{{ route('ar.invoices.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Buat Faktur</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>No. Faktur</th><th>Pelanggan</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Total</th><th>Terbayar</th><th>Sisa</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td><a href="{{ route('ar.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ $inv->date->format('d/m/Y') }}</td>
                    <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td class="text-end">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($inv->paid, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($inv->remaining, 0, ',', '.') }}</td>
                    <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('ar.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-3">Belum ada faktur</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $invoices->links() }}</div>
</div>
@endsection
