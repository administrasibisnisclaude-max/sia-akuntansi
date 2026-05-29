@extends('layouts.app')
@section('title', 'Bukti Penerimaan')
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-cash-coin me-2"></i>Bukti Penerimaan</div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>No. Kwitansi</th>
                    <th>Pelanggan</th>
                    <th>No. Faktur</th>
                    <th>Tanggal</th>
                    <th class="text-end">Jumlah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($receipts as $r)
                <tr>
                    <td><a href="{{ route('sales.receipts.show', $r) }}">{{ $r->receipt_number }}</a></td>
                    <td>{{ $r->customer->name }}</td>
                    <td>
                        @if($r->salesInvoice)
                            <a href="{{ route('sales.invoices.show', $r->salesInvoice) }}">{{ $r->salesInvoice->invoice_number }}</a>
                        @else —
                        @endif
                    </td>
                    <td>{{ $r->date->format('d/m/Y') }}</td>
                    <td class="text-end">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                    <td><a href="{{ route('sales.receipts.show', $r) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada bukti penerimaan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($receipts->hasPages())
    <div class="card-footer">{{ $receipts->links() }}</div>
    @endif
</div>
@endsection
