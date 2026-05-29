@extends('layouts.app')
@section('title', 'Detail Tagihan')
@section('content')
<div class="row g-3">
<div class="col-md-8">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text me-2"></i>{{ $bill->bill_number }}</span>
        <span class="badge badge-{{ $bill->status }} fs-6">{{ ucfirst($bill->status) }}</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col"><strong>Vendor:</strong> {{ $bill->vendor->name }}</div>
            <div class="col"><strong>Tanggal:</strong> {{ $bill->date->format('d F Y') }}</div>
            <div class="col"><strong>Jatuh Tempo:</strong> {{ $bill->due_date->format('d F Y') }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>#</th><th>Deskripsi</th><th class="text-end">Qty</th><th class="text-end">Harga Satuan</th><th class="text-end">PPN%</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                @foreach($bill->lines as $i => $line)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $line->description }}</td>
                        <td class="text-end">{{ number_format($line->qty, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end">{{ $line->tax_rate }}%</td>
                        <td class="text-end">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="5" class="text-end">Subtotal</td><td class="text-end">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</td></tr>
                    <tr><td colspan="5" class="text-end">PPN</td><td class="text-end">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end">Rp {{ number_format($bill->total, 0, ',', '.') }}</td></tr>
                    <tr><td colspan="5" class="text-end">Terbayar</td><td class="text-end text-success">Rp {{ number_format($bill->paidAmount(), 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold text-danger"><td colspan="5" class="text-end">Sisa</td><td class="text-end">Rp {{ number_format($bill->remainingAmount(), 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex gap-2 no-print">
        @if($bill->status == 'draft')
            <form method="POST" action="{{ route('ap.bills.post', $bill) }}">
                @csrf<button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Posting Jurnal</button>
            </form>
            <a href="{{ route('ap.bills.edit', $bill) }}" class="btn btn-secondary btn-sm">Edit</a>
        @endif
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i></button>
        <a href="{{ route('ap.bills.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>
@if($bill->payments->count())
<div class="card mt-3">
    <div class="card-header">Riwayat Pembayaran</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>No. Bayar</th><th>Tanggal</th><th>Metode</th><th class="text-end">Jumlah</th></tr></thead>
            <tbody>
            @foreach($bill->payments as $p)
                <tr><td>{{ $p->payment_number }}</td><td>{{ $p->date->format('d/m/Y') }}</td><td>{{ $p->paymentMethod->name }}</td><td class="text-end">Rp {{ number_format($p->amount, 0, ',', '.') }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
</div>
@if(!in_array($bill->status, ['paid','cancelled','draft']))
<div class="col-md-4">
    <div class="card">
        <div class="card-header"><i class="bi bi-cash me-2"></i>Bayar Tagihan</div>
        <div class="card-body">
            <form method="POST" action="{{ route('ap.bills.payment', $bill) }}">
                @csrf
                <div class="mb-3"><label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                <select name="payment_method_id" class="form-select" required><option value="">— Pilih —</option>
                @foreach($paymentMethods as $pm)<option value="{{ $pm->id }}">{{ $pm->name }}</option>@endforeach
                </select></div>
                <div class="mb-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="mb-3"><label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" value="{{ number_format($bill->remainingAmount(), 2, '.', '') }}" min="0.01" step="1" required>
                <small class="text-muted">Sisa: Rp {{ number_format($bill->remainingAmount(), 0, ',', '.') }}</small></div>
                <div class="mb-3"><label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-primary w-100">Simpan Pembayaran</button>
            </form>
        </div>
    </div>
</div>
@endif
</div>
@endsection
