@extends('layouts.app')
@section('title', 'Neraca (Balance Sheet)')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart me-2"></i>Neraca (Balance Sheet)</span>
        <div class="d-flex gap-2 no-print">
            <form class="d-flex gap-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
                <button class="btn btn-sm btn-outline-primary">Lihat</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <strong>Neraca per {{ date('d F Y', strtotime($to)) }}</strong>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary border-bottom pb-1">ASET</h6>
                @php $totalAsset = 0; @endphp
                @foreach($assets as $a)
                    @php $bal = $a->normal_balance == 'debit' ? $a->net : -$a->net; $totalAsset += $bal; @endphp
                    <div class="d-flex justify-content-between py-1 border-bottom-dotted">
                        <span><code class="text-muted small">{{ $a->account_code }}</code> {{ $a->name }}</span>
                        <span>Rp {{ number_format(abs($bal), 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between py-2 fw-bold mt-2 border-top">
                    <span>Total Aset</span>
                    <span>Rp {{ number_format($totalAsset, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-danger border-bottom pb-1">KEWAJIBAN</h6>
                @php $totalLiab = 0; @endphp
                @foreach($liabilities as $a)
                    @php $bal = $a->normal_balance == 'credit' ? -$a->net : $a->net; $totalLiab += $bal; @endphp
                    <div class="d-flex justify-content-between py-1">
                        <span><code class="text-muted small">{{ $a->account_code }}</code> {{ $a->name }}</span>
                        <span>Rp {{ number_format(abs($bal), 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between py-2 fw-bold border-top">
                    <span>Total Kewajiban</span>
                    <span>Rp {{ number_format($totalLiab, 0, ',', '.') }}</span>
                </div>
                <h6 class="fw-bold text-success border-bottom pb-1 mt-3">EKUITAS</h6>
                @php $totalEq = 0; @endphp
                @foreach($equity as $a)
                    @php $bal = $a->normal_balance == 'credit' ? -$a->net : $a->net; $totalEq += $bal; @endphp
                    <div class="d-flex justify-content-between py-1">
                        <span><code class="text-muted small">{{ $a->account_code }}</code> {{ $a->name }}</span>
                        <span>Rp {{ number_format(abs($bal), 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between py-1">
                    <span>Laba Berjalan</span>
                    <span class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</span>
                </div>
                @php $totalEq += $netIncome; @endphp
                <div class="d-flex justify-content-between py-2 fw-bold border-top">
                    <span>Total Ekuitas</span>
                    <span>Rp {{ number_format($totalEq, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 fw-bold border-top border-2 mt-2">
                    <span>Total Kewajiban + Ekuitas</span>
                    <span>Rp {{ number_format($totalLiab + $totalEq, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
