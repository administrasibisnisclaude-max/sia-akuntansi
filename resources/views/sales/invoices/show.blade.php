@extends('layouts.app')
@section('title', 'Detail Faktur Penjualan')
@section('content')
<div class="row g-3">
<div class="col-md-8">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt-cutoff me-2"></i>{{ $invoice->invoice_number }}</span>
        <span class="badge badge-{{ $invoice->status }} fs-6">{{ ucfirst($invoice->status) }}</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col"><strong>Pelanggan:</strong> {{ $invoice->customer->name }}</div>
            <div class="col"><strong>Tanggal:</strong> {{ $invoice->date->format('d F Y') }}</div>
            <div class="col"><strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d F Y') }}</div>
        </div>
        @if($invoice->quotation)
        <p class="mb-2"><strong>Dari Penawaran:</strong>
            <a href="{{ route('sales.quotations.show', $invoice->quotation) }}">{{ $invoice->quotation->quotation_number }}</a>
        </p>
        @endif
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>#</th><th>Deskripsi</th><th class="text-end">Qty</th><th class="text-end">Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                @foreach($invoice->lines as $i => $line)
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
                    <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
                    @if(($invoice->discount_amount ?? 0) > 0)
                    <tr><td colspan="4" class="text-end">Diskon</td><td class="text-end text-danger">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td></tr>
                    @endif
                    <tr><td colspan="4" class="text-end">PPN</td><td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td class="text-end">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td></tr>
                    <tr><td colspan="4" class="text-end">Terbayar</td><td class="text-end text-success">Rp {{ number_format($invoice->paidAmount(), 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold text-danger"><td colspan="4" class="text-end">Sisa</td><td class="text-end">Rp {{ number_format($invoice->remainingAmount(), 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>
        @if($invoice->notes)<p><strong>Catatan:</strong> {{ $invoice->notes }}</p>@endif
    </div>
    <div class="card-footer d-flex gap-2 no-print">
        @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('sales.invoices.post', $invoice) }}">
                @csrf<button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Posting Jurnal</button>
            </form>
            <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn btn-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('sales.invoices.destroy', $invoice) }}" onsubmit="return confirm('Hapus faktur ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Hapus</button>
            </form>
        @endif
        <a href="{{ route('sales.invoices.pdf', $invoice) }}" target="_blank" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i>Cetak PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i></button>
        <a href="{{ route('sales.invoices.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

@if($invoice->receipts->count())
<div class="card mt-3">
    <div class="card-header">Riwayat Penerimaan</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>No. Kwitansi</th><th>Tanggal</th><th>Metode</th><th>Ref.</th><th class="text-end">Jumlah</th></tr></thead>
            <tbody>
            @foreach($invoice->receipts as $r)
                <tr>
                    <td><a href="{{ route('sales.receipts.show', $r) }}">{{ $r->receipt_number }}</a></td>
                    <td>{{ $r->date->format('d/m/Y') }}</td>
                    <td>{{ $r->paymentMethod->name }}</td>
                    <td>{{ $r->reference_number }}</td>
                    <td class="text-end">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
</div>

@if(in_array($invoice->status, ['posted','partial']))
<div class="col-md-4">
    <div class="card">
        <div class="card-header"><i class="bi bi-cash-coin me-2"></i>Catat Penerimaan</div>
        <div class="card-body">
            <form method="POST" action="{{ route('sales.invoices.payment', $invoice) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method_id" class="form-select" required>
                        <option value="">— Pilih —</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" value="{{ number_format($invoice->remainingAmount(), 2, '.', '') }}" min="0.01" max="{{ round($invoice->remainingAmount(), 2) }}" step="1" required>
                    <small class="text-muted">Sisa: Rp {{ number_format($invoice->remainingAmount(), 0, ',', '.') }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Referensi</label>
                    <input type="text" name="reference_number" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <button class="btn btn-primary w-100">Simpan Penerimaan</button>
            </form>
        </div>
    </div>
</div>
@endif
</div>
@endsection
