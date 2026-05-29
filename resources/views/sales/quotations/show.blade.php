@extends('layouts.app')
@section('title', 'Detail Penawaran')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-check me-2"></i>{{ $quotation->quotation_number }}</span>
        <span class="badge badge-{{ $quotation->status }} fs-6">{{ ucfirst($quotation->status) }}</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col"><strong>Pelanggan:</strong> {{ $quotation->customer->name }}</div>
            <div class="col"><strong>Tanggal:</strong> {{ $quotation->date->format('d F Y') }}</div>
            <div class="col"><strong>Berlaku Hingga:</strong> {{ $quotation->valid_until->format('d F Y') }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>#</th><th>Deskripsi</th><th class="text-end">Qty</th><th class="text-end">Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                @foreach($quotation->lines as $i => $line)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $line->description }}@if($line->item) <small class="text-muted">({{ $line->item->item_code }})</small>@endif</td>
                        <td class="text-end">{{ number_format($line->qty, 2, ',', '.') }} {{ $line->item?->unit }}</td>
                        <td class="text-end">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td></tr>
                    @if(($quotation->discount_amount ?? 0) > 0)
                    <tr><td colspan="4" class="text-end">Diskon</td><td class="text-end text-danger">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</td></tr>
                    @endif
                    <tr><td colspan="4" class="text-end">PPN</td><td class="text-end">Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td class="text-end">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>
        @if($quotation->notes)<p><strong>Catatan:</strong> {{ $quotation->notes }}</p>@endif
        @if($quotation->salesInvoices->count())
        <div class="alert alert-info mt-2 mb-0">
            Sudah dikonversi menjadi faktur:
            @foreach($quotation->salesInvoices as $inv)
                <a href="{{ route('sales.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a>
            @endforeach
        </div>
        @endif
    </div>
    <div class="card-footer d-flex gap-2 no-print">
        @if($quotation->status === 'draft')
            <form method="POST" action="{{ route('sales.quotations.send', $quotation) }}">
                @csrf<button class="btn btn-info btn-sm"><i class="bi bi-send me-1"></i>Kirim</button>
            </form>
            <a href="{{ route('sales.quotations.edit', $quotation) }}" class="btn btn-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('sales.quotations.destroy', $quotation) }}" onsubmit="return confirm('Hapus penawaran ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Hapus</button>
            </form>
        @endif
        @if($quotation->status === 'sent')
            <form method="POST" action="{{ route('sales.quotations.accept', $quotation) }}">
                @csrf<button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Terima</button>
            </form>
        @endif
        @if($quotation->status === 'accepted' && !$quotation->salesInvoices->count())
            <form method="POST" action="{{ route('sales.quotations.convert', $quotation) }}">
                @csrf<button class="btn btn-primary btn-sm"><i class="bi bi-arrow-right-circle me-1"></i>Buat Faktur</button>
            </form>
        @endif
        <a href="{{ route('sales.quotations.pdf', $quotation) }}" target="_blank" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i>Cetak PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i></button>
        <a href="{{ route('sales.quotations.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>
@endsection
