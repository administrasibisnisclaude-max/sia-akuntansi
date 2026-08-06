<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $transaction->transaction_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Courier New', monospace; font-size: 12px; background: #fff;
         width: 300px; margin: 0 auto; padding: 8px; color: #000; }
  .center  { text-align: center; }
  .right   { text-align: right; }
  .bold    { font-weight: bold; }
  .divider { border-top: 1px dashed #000; margin: 6px 0; }
  .row     { display: flex; justify-content: space-between; margin-bottom: 2px; }
  .row .name { flex: 1; padding-right: 6px; word-break: break-word; }
  .row .amount { white-space: nowrap; }
  .total-row { font-size: 14px; font-weight: bold; }
  .footer  { margin-top: 10px; text-align: center; font-size: 11px; }
  @media print {
    @page { margin: 0; size: 80mm auto; }
    body  { width: 80mm; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

<div class="center bold" style="font-size:14px;">{{ $company['company_name'] ?? 'SIA Akuntansi' }}</div>
@if(!empty($company['company_address']))
<div class="center" style="font-size:11px;">{{ $company['company_address'] }}</div>
@endif
@if(!empty($company['company_phone']))
<div class="center" style="font-size:11px;">Telp: {{ $company['company_phone'] }}</div>
@endif

<div class="divider"></div>

<div class="row"><span>No</span><span>{{ $transaction->transaction_number }}</span></div>
<div class="row"><span>Tanggal</span><span>{{ $transaction->date->format('d/m/Y H:i') }}</span></div>
<div class="row"><span>Kasir</span><span>{{ $transaction->cashier->name }}</span></div>
<div class="row"><span>Pembayaran</span><span>{{ $transaction->paymentMethodLabel() }}</span></div>

<div class="divider"></div>

@foreach($transaction->lines as $line)
<div class="bold" style="margin-bottom:1px;">{{ $line->description }}</div>
<div class="row" style="margin-left:4px;">
    <span class="name">
        {{ number_format($line->qty, 0, ',', '.') }} x
        {{ number_format($line->unit_price, 0, ',', '.') }}
        @if($line->discount_percent > 0)
            (Disc {{ number_format($line->discount_percent, 0) }}%)
        @endif
    </span>
    <span class="amount">{{ number_format($line->subtotal, 0, ',', '.') }}</span>
</div>
@endforeach

<div class="divider"></div>

<div class="row"><span>Subtotal</span><span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
@if($transaction->discount_amount > 0)
<div class="row"><span>Diskon</span><span>- {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span></div>
@endif
@if($transaction->tax_amount > 0)
<div class="row"><span>Pajak ({{ number_format($transaction->tax_percent, 0) }}%)</span><span>{{ number_format($transaction->tax_amount, 0, ',', '.') }}</span></div>
@endif

<div class="divider"></div>

<div class="row total-row">
    <span>TOTAL</span>
    <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
</div>

@if($transaction->payment_method === 'tunai')
<div class="row"><span>Dibayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
<div class="row bold"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
@else
<div class="row"><span>Dibayar</span><span>{{ $transaction->paymentMethodLabel() }}</span></div>
@endif

@if($transaction->notes)
<div class="divider"></div>
<div style="font-size:11px;">Catatan: {{ $transaction->notes }}</div>
@endif

<div class="divider"></div>

<div class="footer">
    Terima kasih atas kunjungan Anda!<br>
    Barang yang sudah dibeli tidak dapat dikembalikan.
</div>

<div class="no-print" style="text-align:center; margin-top:16px;">
    <button onclick="window.print()" style="padding:8px 20px; cursor:pointer; margin-right:8px;">Cetak</button>
    <button onclick="window.close()" style="padding:8px 20px; cursor:pointer;">Tutup</button>
</div>

<script>
    window.onload = () => { window.print(); }
</script>
</body>
</html>
