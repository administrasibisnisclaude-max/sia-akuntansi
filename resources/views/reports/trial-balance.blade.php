@extends('layouts.app')
@section('title', 'Neraca Saldo')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Neraca Saldo</span>
        <div class="d-flex gap-2 no-print">
            <form class="d-flex gap-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-3 border-bottom">
            <strong>Neraca Saldo</strong><br>
            <small class="text-muted">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th class="text-end">Debit</th><th class="text-end">Kredit</th></tr></thead>
                <tbody>
                @php $totalD = 0; $totalC = 0; @endphp
                @foreach($rows as $row)
                    <tr>
                        <td><code>{{ $row->account_code }}</code></td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->type }}</td>
                        <td class="text-end">Rp {{ number_format($row->total_debit, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->total_credit, 0, ',', '.') }}</td>
                    </tr>
                    @php $totalD += $row->total_debit; $totalC += $row->total_credit; @endphp
                @endforeach
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">Rp {{ number_format($totalD, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalC, 0, ',', '.') }}</td>
                    </tr>
                    @if(abs($totalD - $totalC) > 0.01)
                    <tr class="text-danger">
                        <td colspan="5" class="text-center">⚠ Neraca tidak seimbang! Selisih: Rp {{ number_format(abs($totalD-$totalC), 0, ',', '.') }}</td>
                    </tr>
                    @else
                    <tr class="text-success">
                        <td colspan="5" class="text-center">✓ Neraca seimbang</td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
