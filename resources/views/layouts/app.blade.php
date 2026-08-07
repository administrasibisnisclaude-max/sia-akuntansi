<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title', 'Dashboard') - SIA Akuntansi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="{{ asset('css/sb-admin.css') }}" rel="stylesheet" />
    <style>
        .badge-draft     { background: #e2e8f0; color: #475569; }
        .badge-posted    { background: #dcfce7; color: #16a34a; }
        .badge-sent      { background: #dbeafe; color: #1d4ed8; }
        .badge-partial   { background: #fef9c3; color: #92400e; }
        .badge-paid      { background: #dcfce7; color: #15803d; }
        .badge-received  { background: #e0f2fe; color: #0369a1; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }
        .badge-accepted  { background: #d1fae5; color: #065f46; }
        .badge-expired   { background: #f3f4f6; color: #6b7280; }
        .sb-sidenav-menu .nav-link { font-size: 0.875rem; }
        @media print {
            #layoutSidenav_nav, .sb-topnav, .no-print { display: none !important; }
            #layoutSidenav_content { margin-left: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="sb-nav-fixed">

    {{-- Top Navbar --}}
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-line me-2"></i>{{ $company['company_name'] ?? 'SIA Akuntansi' }}
        </a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <span class="ms-1 small d-none d-md-inline">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt fa-fw me-2"></i>Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        {{-- Sidebar --}}
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">

                        @if(auth()->user()->isAdmin())
                        {{-- Utama --}}
                        <div class="sb-sidenav-menu-heading">Utama</div>
                        <a class="nav-link @active('dashboard')" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>

                        {{-- Master Data --}}
                        <div class="sb-sidenav-menu-heading">Master Data</div>
                        <a class="nav-link @active('accounts.*')" href="{{ route('accounts.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-list"></i></div>
                            Akun (CoA)
                        </a>
                        <a class="nav-link @active('customers.*')" href="{{ route('customers.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Pelanggan
                        </a>
                        <a class="nav-link @active('vendors.*')" href="{{ route('vendors.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                            Vendor
                        </a>
                        <a class="nav-link @active('items.*')" href="{{ route('items.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Item / Produk
                        </a>
                        <a class="nav-link @active('payment-methods.*')" href="{{ route('payment-methods.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                            Metode Pembayaran
                        </a>

                        @endif {{-- end admin-only block sebelum POS --}}

                        {{-- Transaksi --}}
                        <div class="sb-sidenav-menu-heading">Transaksi</div>

                        {{-- POS — kasir & admin --}}
                        <a class="nav-link @active('pos.index')" href="{{ route('pos.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                            Point of Sales
                        </a>
                        <a class="nav-link @active('pos.history')" href="{{ route('pos.history') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                            Riwayat Transaksi
                        </a>

                        @if(auth()->user()->isAdmin())
                        {{-- Jurnal --}}
                        <a class="nav-link collapsed @active('journals.*')" href="#collapseJurnal"
                           data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('journals.*') ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                            Jurnal
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('journals.*') ? 'show' : '' }}" id="collapseJurnal" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('journals.*')" href="{{ route('journals.index') }}">Jurnal Umum</a>
                            </nav>
                        </div>

                        {{-- Piutang (AR) --}}
                        <a class="nav-link collapsed @active('ar.*')" href="#collapseAr"
                           data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('ar.*') ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            Piutang (AR)
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('ar.*') ? 'show' : '' }}" id="collapseAr" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('ar.invoices.*')" href="{{ route('ar.invoices.index') }}">Invoice AR</a>
                            </nav>
                        </div>

                        {{-- Hutang (AP) --}}
                        <a class="nav-link collapsed @active('ap.*')" href="#collapseAp"
                           data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('ap.*') ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Hutang (AP)
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('ap.*') ? 'show' : '' }}" id="collapseAp" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('ap.bills.*')" href="{{ route('ap.bills.index') }}">Tagihan AP</a>
                            </nav>
                        </div>

                        {{-- Penjualan --}}
                        <a class="nav-link collapsed @active('sales.*')" href="#collapseSales"
                           data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('sales.*') ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Penjualan
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('sales.*') ? 'show' : '' }}" id="collapseSales" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('sales.quotations.*')" href="{{ route('sales.quotations.index') }}">Penawaran Harga</a>
                                <a class="nav-link @active('sales.invoices.*')" href="{{ route('sales.invoices.index') }}">Faktur Penjualan</a>
                                <a class="nav-link @active('sales.receipts.*')" href="{{ route('sales.receipts.index') }}">Bukti Penerimaan</a>
                            </nav>
                        </div>

                        {{-- Inventori --}}
                        <a class="nav-link @active('inventory.*')" href="{{ route('inventory.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>
                            Inventori
                        </a>

                        {{-- Laporan --}}
                        <div class="sb-sidenav-menu-heading">Laporan</div>

                        {{-- Laporan Keuangan --}}
                        <a class="nav-link collapsed @active('reports.trial-balance') @active('reports.balance-sheet') @active('reports.income-statement') @active('reports.general-ledger') @active('reports.cash-flow')"
                           href="#collapseReportKeuangan" data-bs-toggle="collapse"
                           aria-expanded="{{ request()->routeIs(['reports.trial-balance','reports.balance-sheet','reports.income-statement','reports.general-ledger','reports.cash-flow']) ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                            Laporan Keuangan
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs(['reports.trial-balance','reports.balance-sheet','reports.income-statement','reports.general-ledger','reports.cash-flow']) ? 'show' : '' }}"
                             id="collapseReportKeuangan" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('reports.trial-balance')" href="{{ route('reports.trial-balance') }}">Neraca Saldo</a>
                                <a class="nav-link @active('reports.balance-sheet')" href="{{ route('reports.balance-sheet') }}">Neraca</a>
                                <a class="nav-link @active('reports.income-statement')" href="{{ route('reports.income-statement') }}">Laba Rugi</a>
                                <a class="nav-link @active('reports.general-ledger')" href="{{ route('reports.general-ledger') }}">Buku Besar</a>
                                <a class="nav-link @active('reports.cash-flow')" href="{{ route('reports.cash-flow') }}">Arus Kas</a>
                            </nav>
                        </div>

                        {{-- Laporan Operasional --}}
                        <a class="nav-link collapsed @active('reports.sold-products') @active('reports.stock-opname') @active('reports.pajak') @active('reports.payment-methods-report') @active('reports.customer-spent')"
                           href="#collapseReportOps" data-bs-toggle="collapse"
                           aria-expanded="{{ request()->routeIs(['reports.sold-products','reports.stock-opname','reports.pajak','reports.payment-methods-report','reports.customer-spent']) ? 'true' : 'false' }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-pie"></i></div>
                            Laporan Operasional
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs(['reports.sold-products','reports.stock-opname','reports.pajak','reports.payment-methods-report','reports.customer-spent']) ? 'show' : '' }}"
                             id="collapseReportOps" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link @active('reports.sold-products')" href="{{ route('reports.sold-products') }}">Produk Terjual</a>
                                <a class="nav-link @active('reports.stock-opname')" href="{{ route('reports.stock-opname') }}">Stock Opname</a>
                                <a class="nav-link @active('reports.pajak')" href="{{ route('reports.pajak') }}">Laporan Pajak</a>
                                <a class="nav-link @active('reports.payment-methods-report')" href="{{ route('reports.payment-methods-report') }}">Metode Pembayaran</a>
                                <a class="nav-link @active('reports.customer-spent')" href="{{ route('reports.customer-spent') }}">Customer Spent</a>
                            </nav>
                        </div>

                        {{-- Laporan Lingkungan --}}
                        <a class="nav-link @active('reports.environmental')" href="{{ route('reports.environmental') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-leaf"></i></div>
                            Laporan Lingkungan
                        </a>

                        {{-- Sistem --}}
                        <div class="sb-sidenav-menu-heading">Sistem</div>
                        <a class="nav-link @active('settings.*')" href="{{ route('settings.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                            Pengaturan Perusahaan
                        </a>
                        <a class="nav-link @active('users.*')" href="{{ route('users.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                            Manajemen Pengguna
                        </a>
                        @endif {{-- end admin-only --}}

                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Login sebagai:</div>
                    {{ auth()->user()->name }}
                    <div class="mt-1">
                        <span class="badge {{ auth()->user()->isAdmin() ? 'bg-primary' : 'bg-success' }}">
                            {{ auth()->user()->isAdmin() ? 'Admin' : 'Kasir' }}
                        </span>
                    </div>
                </div>
            </nav>
        </div>

        {{-- Main Content --}}
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">@yield('title', 'Dashboard')</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                    </ol>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach($errors->all() as $error){{ $error }}<br>@endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">SIA Akuntansi &copy; {{ date('Y') }}</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/sb-admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
