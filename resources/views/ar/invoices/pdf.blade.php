<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 20px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .company { margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .info-table { width: 100%; margin-bottom: 16px; }
        .info-table td { vertical-align: top; width: 50%; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.lines th { background: #f5f5f5; border: 1px solid #ccc; padding: 6px; text-align: left; }
        table.lines td { border: 1px solid #ddd; padding: 5px 6px; }
        .text-right { text-align: right; }
        .totals { width: 45%; margin-left: 55%; }
        .totals td { padding: 3px 6px; }
        .totals tr.grand td { font-weight: bold; font-size: 13px; border-top: 2px solid #333; padding-top: 5px; }
        .sign-area { margin-top: 40px; }
        .sign-box { display: inline-block; width: 45%; text-align: center; border-top: 1px solid #999; padding-top: 4px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="company">
        <h1>INVOICE AR</h1>
        <div>PT. SIA Akuntansi &nbsp;|&nbsp; Jl. Contoh No. 1, Jakarta</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>Kepada:</strong><br>
                {{ $invoice->customer->name }}<br>
                @if($invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                @if($invoice->customer->phone)Telp: {{ $invoice->customer->phone }}@endif
            </td>
            <td class="text-right">
                <table style="margin-left:auto;">
                    <tr><td style="padding:2px 8px;">No. Invoice</td><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                    <tr><td style="padding:2px 8px;">Tanggal</td><td>{{ $invoice->date->format('d/m/Y') }}</td></tr>
                    <tr><td style="padding:2px 8px;">Jatuh Tempo</td><td>{{ $invoice->due_date->format('d/m/Y') }}</td></tr>
                    <tr><td style="padding:2px 8px;">Status</td><td>{{ strtoupper($invoice->status) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th>Deskripsi</th>
                <th style="width:10%" class="text-right">Qty</th>
                <th style="width:10%">Sat.</th>
                <th style="width:8%" class="text-right">PPN (%)</th>
                <th style="width:16%" class="text-right">Harga Satuan</th>
                <th style="width:16%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->lines as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $line->description }}</td>
                <td class="text-right">{{ number_format($line->qty, 2, ',', '.') }}</td>
                <td>{{ $line->item->unit ?? '' }}</td>
                <td class="text-right">{{ $line->tax_rate > 0 ? number_format($line->tax_rate, 0) . '%' : '-' }}</td>
                <td class="text-right">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
        @if($invoice->discount_amount > 0)
        <tr><td>Diskon</td><td class="text-right">(Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }})</td></tr>
        @endif
        @if($invoice->tax_amount > 0)
        <tr><td>PPN</td><td class="text-right">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="grand"><td>TOTAL</td><td class="text-right">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td></tr>
    </table>

    @if($invoice->notes)
    <p style="margin-top:16px; font-style:italic;">Catatan: {{ $invoice->notes }}</p>
    @endif

    <div class="sign-area">
        <div style="float:left; width:45%; text-align:center;">
            <div style="margin-bottom:50px;">Hormat kami,</div>
            <div class="sign-box">( __________________ )<br>Dibuat Oleh</div>
        </div>
        <div style="float:right; width:45%; text-align:center;">
            <div style="margin-bottom:50px;">Penerima,</div>
            <div class="sign-box">( __________________ )<br>Disetujui</div>
        </div>
    </div>
</body>
</html>
