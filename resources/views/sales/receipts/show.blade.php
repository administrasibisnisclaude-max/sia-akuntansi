@extends('layouts.app')
@section('title', 'Detail Bukti Penerimaan')
@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-coin me-2"></i>{{ $receipt->receipt_number }}</span>
        <span class="badge badge-received fs-6">Diterima</span>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-4">Pelanggan</dt>
            <dd class="col-sm-8">{{ $receipt->customer->name }}</dd>

            <dt class="col-sm-4">Faktur</dt>
            <dd class="col-sm-8">
                @if($receipt->salesInvoice)
                    <a href="{{ route('sales.invoices.show', $receipt->salesInvoice) }}">{{ $receipt->salesInvoice->invoice_number }}</a>
                @else —
                @endif
            </dd>

            <dt class="col-sm-4">Tanggal</dt>
            <dd class="col-sm-8">{{ $receipt->date->format('d F Y') }}</dd>

            <dt class="col-sm-4">Metode Pembayaran</dt>
            <dd class="col-sm-8">{{ $receipt->paymentMethod->name }}</dd>

            <dt class="col-sm-4">Jumlah</dt>
            <dd class="col-sm-8 fw-bold fs-5">Rp {{ number_format($receipt->amount, 0, ',', '.') }}</dd>

            @if($receipt->reference_number)
            <dt class="col-sm-4">No. Referensi</dt>
            <dd class="col-sm-8">{{ $receipt->reference_number }}</dd>
            @endif

            @if($receipt->notes)
            <dt class="col-sm-4">Catatan</dt>
            <dd class="col-sm-8">{{ $receipt->notes }}</dd>
            @endif

            @if($receipt->journal)
            <dt class="col-sm-4">No. Jurnal</dt>
            <dd class="col-sm-8">
                <a href="{{ route('journals.show', $receipt->journal) }}">{{ $receipt->journal->journal_number }}</a>
            </dd>
            @endif
        </dl>
    </div>
    <div class="card-footer d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i></button>
        <a href="{{ route('sales.receipts.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>
</div>
</div>
@endsection
