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
    <button onclick="window.print()" style="padding:8px 20px; cursor:pointer; margin-right:6px; border:1px solid #ccc; border-radius:4px; background:#fff;">
        🖨️ Cetak
    </button>
    <button id="btPrintBtn" onclick="btPrint()" style="padding:8px 16px; cursor:pointer; margin-right:6px; background:#6f42c1; color:#fff; border:none; border-radius:4px; font-weight:600;">
        🔵 Cetak Bluetooth
    </button>
    <button onclick="window.close()" style="padding:8px 20px; cursor:pointer; border:1px solid #ccc; border-radius:4px; background:#fff;">
        Tutup
    </button>
    <div id="btMsg" style="margin-top:8px; font-size:11px; color:#555; min-height:16px;"></div>
</div>

<script>
    window.onload = () => { window.print(); }

    // ── Printer config from server settings ──────────────────────────────────
    const PRINTER_CFG = {
        paperWidth: '{{ $company["printer_paper_width"] ?? "80" }}',
        serviceUuid: '{{ addslashes($company["printer_bt_service_uuid"] ?? "") }}',
        charUuid:    '{{ addslashes($company["printer_bt_char_uuid"] ?? "") }}',
    };

    // ── Transaction data ──────────────────────────────────────────────────────
    const TX = {
        number:  '{{ $transaction->transaction_number }}',
        date:    '{{ $transaction->date->format("d/m/Y") }} {{ $transaction->date->format("H:i") }}',
        cashier: '{{ addslashes($transaction->cashier->name) }}',
        payment: '{{ $transaction->paymentMethodLabel() }}',
        payment_method: '{{ $transaction->payment_method }}',
        lines: [
            @foreach($transaction->lines as $line)
            {
                desc: '{{ addslashes($line->description) }}',
                qty:  {{ (float) $line->qty }},
                unit_price: {{ (float) $line->unit_price }},
                discount_pct: {{ (float) $line->discount_percent }},
                subtotal: {{ (float) $line->subtotal }},
            },
            @endforeach
        ],
        subtotal:  {{ (float) $transaction->subtotal }},
        discount:  {{ (float) $transaction->discount_amount }},
        tax_pct:   {{ (float) $transaction->tax_percent }},
        tax:       {{ (float) $transaction->tax_amount }},
        total:     {{ (float) $transaction->total }},
        paid:      {{ (float) $transaction->paid_amount }},
        change:    {{ (float) $transaction->change_amount }},
        notes:     '{{ addslashes($transaction->notes ?? "") }}',
    };
    const COMPANY = {
        name:    '{{ addslashes($company["company_name"] ?? "SIA Akuntansi") }}',
        address: '{{ addslashes($company["company_address"] ?? "") }}',
        phone:   '{{ addslashes($company["company_phone"] ?? "") }}',
    };

    // ── ESC/POS Builder ───────────────────────────────────────────────────────
    function buildEscPos(tx, company, paperWidth) {
        const CHARS = paperWidth === '58' ? 32 : 48;
        const ESC = 0x1B, GS = 0x1D, LF = 0x0A;
        const bytes = [];
        const enc   = new TextEncoder();

        const push  = (...b) => bytes.push(...b);
        const text  = (s)    => enc.encode(s).forEach(b => bytes.push(b));
        const nl    = (n=1)  => { for (let i=0;i<n;i++) push(LF); };
        const line  = (s)    => { text(s); push(LF); };
        const align = (a)    => push(ESC, 0x61, a==='c'?1:a==='r'?2:0);
        const bold  = (on)   => push(ESC, 0x45, on?1:0);
        const dbl   = (on)   => push(GS, 0x21, on?0x11:0x00);
        const divider = ()   => line('-'.repeat(CHARS));
        const fmtN  = (n)    => Math.round(n).toLocaleString('id-ID');
        const fmtRp = (n)    => 'Rp ' + fmtN(n);
        const row   = (l, r) => {
            const sp = CHARS - l.length - r.length;
            line(sp > 0 ? l + ' '.repeat(sp) + r : l.slice(0, CHARS-r.length-1) + ' ' + r);
        };

        // Init
        push(ESC, 0x40);

        // Header
        align('c'); bold(true); dbl(true);
        line(company.name);
        dbl(false); bold(false);
        if (company.address) line(company.address);
        if (company.phone)   line('Telp: ' + company.phone);
        nl(); divider();

        // Info
        align('l');
        row('No:', tx.number);
        row('Tanggal:', tx.date);
        row('Kasir:', tx.cashier);
        row('Pembayaran:', tx.payment);
        divider();

        // Items
        tx.lines.forEach(l => {
            bold(true); line(l.desc); bold(false);
            const detail = '  ' + fmtN(l.qty) + ' x ' + fmtN(l.unit_price)
                         + (l.discount_pct > 0 ? ' (Disc ' + l.discount_pct + '%)' : '');
            row(detail, fmtN(l.subtotal));
        });
        divider();

        // Totals
        row('Subtotal', fmtRp(tx.subtotal));
        if (tx.discount > 0) row('Diskon', '- ' + fmtRp(tx.discount));
        if (tx.tax > 0)      row('Pajak (' + tx.tax_pct + '%)', fmtRp(tx.tax));
        divider();
        bold(true); row('TOTAL', fmtRp(tx.total)); bold(false);

        if (tx.payment_method === 'tunai') {
            row('Dibayar', fmtRp(tx.paid));
            bold(true); row('Kembalian', fmtRp(tx.change)); bold(false);
        } else {
            row('Dibayar', tx.payment);
        }

        if (tx.notes) { divider(); line('Catatan: ' + tx.notes); }
        divider();
        align('c');
        line('Terima kasih atas kunjungan Anda!');
        line('Barang yang sudah dibeli tidak dapat dikembalikan.');
        nl(4);
        push(GS, 0x56, 0x41, 0x10); // cut

        return new Uint8Array(bytes);
    }

    // ── Web Bluetooth ─────────────────────────────────────────────────────────
    const BT_SERVICES = [
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        '0000ff00-0000-1000-8000-00805f9b34fb',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
        '000018f0-0000-1000-8000-00805f9b34fb',
    ];
    const BT_CHARS = {
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2': 'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f',
        '0000ff00-0000-1000-8000-00805f9b34fb': '0000ff02-0000-1000-8000-00805f9b34fb',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455': '49535343-1e4d-4bd9-ba61-23c647249616',
    };

    async function getWriteCharacteristic(server, svcId, charId) {
        try {
            const svc = await server.getPrimaryService(svcId);
            if (charId) {
                try { return await svc.getCharacteristic(charId); } catch {}
            }
            const fallbackChar = BT_CHARS[svcId];
            if (fallbackChar) {
                try { return await svc.getCharacteristic(fallbackChar); } catch {}
            }
            const all = await svc.getCharacteristics();
            return all.find(c => c.properties.write || c.properties.writeWithoutResponse) || null;
        } catch { return null; }
    }

    async function sendBytes(char, bytes) {
        const CHUNK = 200;
        const useWR = char.properties.writeWithoutResponse;
        for (let i = 0; i < bytes.length; i += CHUNK) {
            const chunk = bytes.slice(i, i + CHUNK);
            if (useWR) await char.writeValueWithoutResponse(chunk);
            else       await char.writeValue(chunk);
            await new Promise(r => setTimeout(r, 40));
        }
    }

    async function btPrint() {
        const btn = document.getElementById('btPrintBtn');
        const msg = document.getElementById('btMsg');

        if (!navigator.bluetooth) {
            msg.style.color = '#dc3545';
            msg.textContent = 'Web Bluetooth tidak didukung. Gunakan Chrome atau Edge.';
            return;
        }

        btn.disabled = true;
        btn.textContent = '⏳ Mencari printer...';
        msg.style.color = '#555'; msg.textContent = '';

        const cfgSvc  = PRINTER_CFG.serviceUuid || '';
        const cfgChar = PRINTER_CFG.charUuid || '';
        const svcList = [...new Set([...(cfgSvc ? [cfgSvc] : []), ...BT_SERVICES])];

        try {
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: svcList,
            });
            btn.textContent = '⏳ Menghubungkan...';
            const server = await device.gatt.connect();

            let btChar = null;
            for (const svc of svcList) {
                btChar = await getWriteCharacteristic(server, svc, cfgChar);
                if (btChar) break;
            }
            if (!btChar) throw new Error('Karakteristik printer tidak ditemukan. Coba konfigurasi UUID di Pengaturan.');

            btn.textContent = '⏳ Mencetak...';
            const bytes = buildEscPos(TX, COMPANY, PRINTER_CFG.paperWidth);
            await sendBytes(btChar, bytes);

            msg.style.color = '#198754';
            msg.textContent = '✓ Struk berhasil dicetak via ' + (device.name || 'Printer BT');
            btn.textContent = '✓ Tercetak';
            device.gatt.disconnect();
        } catch (e) {
            msg.style.color = '#dc3545';
            msg.textContent = e.name === 'NotFoundError' ? 'Pencarian dibatalkan.' : '✗ ' + e.message;
            btn.disabled = false;
            btn.textContent = '🔵 Cetak Bluetooth';
        }
    }
</script>
</body>
</html>
