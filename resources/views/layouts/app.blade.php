<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIA Akuntansi') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { font-size: 14px; background: #f8f9fa; }
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #1e293b;
            position: fixed;
            top: 0; left: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: .3s;
        }
        #sidebar .brand { padding: 1rem 1.25rem; border-bottom: 1px solid #334155; }
        #sidebar .nav-link { color: #94a3b8; padding: .5rem 1.25rem; border-radius: 0; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { color: #fff; background: #334155; }
        #sidebar .nav-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; color: #64748b; padding: .75rem 1.25rem .25rem; letter-spacing: .06em; }
        #main { margin-left: var(--sidebar-width); }
        #topbar { background: #fff; border-bottom: 1px solid #e2e8f0; }
        .card { border: 1px solid #e2e8f0; border-radius: .5rem; }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; }
        .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; background: #f8fafc; }
        .badge-draft    { background: #e2e8f0; color: #475569; }
        .badge-posted   { background: #dcfce7; color: #16a34a; }
        .badge-sent     { background: #dbeafe; color: #1d4ed8; }
        .badge-partial  { background: #fef9c3; color: #92400e; }
        .badge-paid     { background: #dcfce7; color: #15803d; }
        .badge-received { background: #e0f2fe; color: #0369a1; }
        .badge-cancelled{ background: #fee2e2; color: #b91c1c; }
        .badge-accepted { background: #d1fae5; color: #065f46; }
        .badge-expired  { background: #f3f4f6; color: #6b7280; }
        @media print { #sidebar, #topbar, .no-print { display: none !important; } #main { margin-left: 0 !important; } }
    </style>
    @stack('styles')
</head>
<body>
<div id="sidebar">
    <div class="brand d-flex align-items-center gap-2">
        <i class="bi bi-bar-chart-fill text-primary fs-5"></i>
        <span class="text-white fw-bold">SIA Akuntansi</span>
    </div>
    <nav class="py-2">
        <div class="nav-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-link @active('dashboard')">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>

        <div class="nav-label">Master Data</div>
        <a href="{{ route('accounts.index') }}" class="nav-link @active('accounts.*')">
            <i class="bi bi-list-columns me-2"></i>Akun (CoA)
        </a>
        <a href="{{ route('customers.index') }}" class="nav-link @active('customers.*')">
            <i class="bi bi-people me-2"></i>Pelanggan
        </a>
        <a href="{{ route('vendors.index') }}" class="nav-link @active('vendors.*')">
            <i class="bi bi-building me-2"></i>Vendor
        </a>
        <a href="{{ route('items.index') }}" class="nav-link @active('items.*')">
            <i class="bi bi-box-seam me-2"></i>Item
        </a>
        <a href="{{ route('payment-methods.index') }}" class="nav-link @active('payment-methods.*')">
            <i class="bi bi-credit-card me-2"></i>Metode Bayar
        </a>

        <div class="nav-label">Transaksi</div>
        <a href="{{ route('journals.index') }}" class="nav-link @active('journals.*')">
            <i class="bi bi-journal-text me-2"></i>Jurnal Umum
        </a>
        <a href="{{ route('ar.invoices.index') }}" class="nav-link @active('ar.*')">
            <i class="bi bi-receipt me-2"></i>Piutang (AR)
        </a>
        <a href="{{ route('ap.bills.index') }}" class="nav-link @active('ap.*')">
            <i class="bi bi-file-earmark-text me-2"></i>Utang (AP)
        </a>
        <a href="{{ route('sales.quotations.index') }}" class="nav-link @active('sales.quotations.*')">
            <i class="bi bi-file-earmark-check me-2"></i>Penawaran Harga
        </a>
        <a href="{{ route('sales.invoices.index') }}" class="nav-link @active('sales.invoices.*')">
            <i class="bi bi-receipt-cutoff me-2"></i>Faktur Penjualan
        </a>
        <a href="{{ route('sales.receipts.index') }}" class="nav-link @active('sales.receipts.*')">
            <i class="bi bi-cash-coin me-2"></i>Bukti Penerimaan
        </a>
        <a href="{{ route('inventory.index') }}" class="nav-link @active('inventory.*')">
            <i class="bi bi-archive me-2"></i>Inventori
        </a>

        <div class="nav-label">Laporan</div>
        <a href="{{ route('reports.trial-balance') }}" class="nav-link @active('reports.trial-balance')">
            <i class="bi bi-table me-2"></i>Neraca Saldo
        </a>
        <a href="{{ route('reports.balance-sheet') }}" class="nav-link @active('reports.balance-sheet')">
            <i class="bi bi-bar-chart me-2"></i>Neraca
        </a>
        <a href="{{ route('reports.income-statement') }}" class="nav-link @active('reports.income-statement')">
            <i class="bi bi-graph-up me-2"></i>Laba Rugi
        </a>
        <a href="{{ route('reports.general-ledger') }}" class="nav-link @active('reports.general-ledger')">
            <i class="bi bi-book me-2"></i>Buku Besar
        </a>
        <a href="{{ route('reports.cash-flow') }}" class="nav-link @active('reports.cash-flow')">
            <i class="bi bi-cash-stack me-2"></i>Arus Kas
        </a>
        <a href="{{ route('reports.environmental') }}" class="nav-link @active('reports.environmental')">
            <i class="bi bi-leaf me-2"></i>Laporan Lingkungan
        </a>
    </nav>
</div>

<div id="main">
    <nav id="topbar" class="navbar px-3 py-2 d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-secondary">@yield('title', 'Dashboard')</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-secondary small">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i>Keluar
                </button>
            </form>
        </div>
    </nav>

    <div class="p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
