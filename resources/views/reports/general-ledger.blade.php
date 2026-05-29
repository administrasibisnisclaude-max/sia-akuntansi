@extends('layouts.app')
@section('title', 'Buku Besar')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-book me-2"></i>Buku Besar</span>
        <div class="d-flex gap-2 no-print">
            <form class="d-flex gap-2">
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">— Pilih Akun —</option>
                    @foreach($accounts as $acc)<option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->account_code }} - {{ $acc->name }}</option>@endforeach
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary">Tampilkan</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
        </div>
    </div>
    @if($account)
    <div class="card-body pb-0">
        <strong>{{ $account->account_code }} - {{ $account->name }}</strong>
        <span class="text-muted small ms-2">{{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Tanggal</th><th>No. Jurnal</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
            @php $balance = 0; $isDebitNormal = ($account->normal_balance == 'debit'); @endphp
            @forelse($lines as $line)
                @php
                    if ($isDebitNormal) {
                        $balance += $line->debit - $line->credit;
                    } else {
                        $balance += $line->credit - $line->debit;
                    }
                @endphp
                <tr>
                    <td>{{ date('d/m/Y', strtotime($line->date)) }}</td>
                    <td>{{ $line->journal_number }}</td>
                    <td>{{ $line->line_desc ?: $line->description }}</td>
                    <td class="text-end">{{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '' }}</td>
                    <td class="text-end">{{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '' }}</td>
                    <td class="text-end fw-semibold {{ $balance >= 0 ? '' : 'text-danger' }}">Rp {{ number_format(abs($balance), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body text-muted">Pilih akun untuk menampilkan buku besar.</div>
    @endif
</div>
@endsection
