@extends('layouts.app')
@section('title', 'Penawaran Harga')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-check me-2"></i>Penawaran Harga</span>
        <a href="{{ route('sales.quotations.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Buat Penawaran
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>No. Penawaran</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Berlaku Hingga</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($quotations as $q)
                <tr>
                    <td><a href="{{ route('sales.quotations.show', $q) }}">{{ $q->quotation_number }}</a></td>
                    <td>{{ $q->customer->name }}</td>
                    <td>{{ $q->date->format('d/m/Y') }}</td>
                    <td>{{ $q->valid_until->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $q->status }}">{{ ucfirst($q->status) }}</span></td>
                    <td class="text-end">Rp {{ number_format($q->total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('sales.quotations.show', $q) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada penawaran harga.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($quotations->hasPages())
    <div class="card-footer">{{ $quotations->links() }}</div>
    @endif
</div>
@endsection
