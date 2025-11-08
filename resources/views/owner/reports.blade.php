<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports - ShoeVault Batangas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/owner.css') }}" rel="stylesheet">
    <style>
        body { overflow: hidden; }
        .logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
		.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
		.logout-btn i{font-size:1rem}
        /* Notifications */
        .notification-wrapper { position: relative; }
        .notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
        .notification-bell:hover { background:#f3f4f6; color:#1f2937; }
        .notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
        .notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:200; }
        .notification-wrapper.open .notification-dropdown { display:block; }
        .notification-list { max-height:300px; overflow-y:auto; }
        .notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }
        .switch-tabs { display:inline-flex; gap:6px; background:#eef4ff; padding:6px; border-radius:999px; box-shadow: inset 0 1px 0 rgba(255,255,255,0.6); }
        .switch-tab { border:0; background:transparent; padding:8px 14px; border-radius:999px; font-size:12px; font-weight:600; color:#3b5bfd; cursor:pointer; transition:all .2s ease; line-height:1; }
        .switch-tab:hover { background:#e0e9ff; }
        .switch-tab.active { background:#3b5bfd; color:#fff; box-shadow: 0 6px 14px -6px rgba(59,91,253,.6); }
        .section-header.with-left-actions { align-items:center; }
        .content-section .filters { margin-top: 8px; gap: 8px; flex-wrap: wrap; }
        /* Compact, modern filter inputs */
        .filters .search-input {
            height: 32px; padding: 6px 10px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff;
            font-size: 12px; color: #0f172a; outline: none; transition: box-shadow .15s ease, border-color .15s ease;
        }
        .filters .search-input::placeholder { color: #94a3b8; }
        .filters .search-input:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .filters .filter-select {
            -webkit-appearance: none; -moz-appearance: none; appearance: none; cursor: pointer;
            height: 32px; padding: 6px 28px 6px 12px; border-radius: 999px; border: 1px solid #e5e7eb;
            background:
                linear-gradient(#fff,#fff) padding-box,
                linear-gradient(135deg,#e0e7ff,#f0f9ff) border-box;
            color: #1e293b; font-size: 12px; font-weight: 600;
            box-shadow: 0 4px 10px -6px rgba(2,6,23,.12);
            transition: box-shadow .15s ease, border-color .15s ease, background .15s ease;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236480fd' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 8px center; background-size: 16px;
        }
        .filters .filter-select:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px -10px rgba(37,99,235,.35); }
        .filters .filter-select:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        /* Compact tables on reports page */
        .table-container { margin-top: 10px; }
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
        .data-table thead th { padding: 6px 8px; text-align: left; background: #f5f7fb; color: #334155; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        .data-table tbody td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .status-badge { display:inline-block; padding: 3px 6px; font-size: 10px; border-radius: 10px; color: #0f172a; background: #e2f6e9; }
        .status-badge.status-completed { background:#dcfce7; color:#166534; }
        .status-badge.status-pending { background:#fff7ed; color:#9a3412; }
    .status-badge.status-active { background:#e0f2fe; color:#075985; }
    .status-badge.status-inactive { background:#fee2e2; color:#991b1b; }
    /* Export buttons */
    .export-group { display:inline-flex; gap:8px; align-items:center; margin-left:10px; }
    .export-btn {
        -webkit-appearance: none; appearance: none; cursor: pointer;
        height: 32px; padding: 6px 10px; border-radius: 999px; border: 1px solid #e5e7eb;
        background:
            linear-gradient(#fff,#fff) padding-box,
            linear-gradient(135deg,#e0e7ff,#f0f9ff) border-box;
        color: #1e293b; font-size: 12px; font-weight: 800;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: 0 4px 10px -6px rgba(2,6,23,.12);
        transition: box-shadow .15s ease, border-color .15s ease, background .15s ease, transform .05s ease;
    }
    .export-btn:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px -10px rgba(37,99,235,.35); }
    .export-btn:active { transform: translateY(1px); }
    .hide-sm { display:inline; }
    @media (max-width: 720px) { .hide-sm { display:none; } }
    /* Sticky section header and filters */
    .content-grid { position: relative; }
    .content-section .section-header { position: sticky; top: 0; z-index: 30; background: #fff; padding-top: 6px; padding-bottom: 6px; }
    /* keep a small gap between header and filters when both are sticky */
    .content-section .filters { position: sticky; top: 55px; z-index: 29; background: #fff; padding: 6px; border-bottom: 1px solid #eef2f7; }
    /* Reservation cards */
    .resv-card-list { display:flex; flex-direction:column; gap:12px; margin-top:12px; }
    .resv-card { background:#fff; border-radius:14px; padding:16px 18px; border:1px solid #eef2f7; box-shadow: 0 8px 24px -18px rgba(2,6,23,.18); }
    .resv-grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:8px 18px; align-items:stretch; }
    .resv-field { display:flex; flex-direction:column; gap:2px; }
    .resv-label { font-size:11px; color:#64748b; }
    .resv-value { font-size:13px; font-weight:700; color:#0f172a; }
    .resv-badge { display:inline-block; padding:6px 10px; font-size:11px; border-radius:999px; }
    .resv-badge.completed { background:#dcfce7; color:#166534; }
    .resv-badge.cancelled { background:#fee2e2; color:#991b1b; }
    @media (max-width: 1100px) { .resv-grid { grid-template-columns: repeat(2, 1fr); } }
    /* Loading + animations */
    @keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .section-loader { position: absolute; inset: 0; background: rgba(255,255,255,0.85); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 20000; }
    .loader { width: 48px; height: 48px; border-radius: 50%; border: 3px solid #bfdbfe; border-top-color: #3b82f6; animation: spin 0.8s linear infinite; }
    .content-section { position: relative; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .skeleton { position: relative; overflow: hidden; background: #eef2f7; border-radius: 8px; }
    .skeleton::after { content: ""; position: absolute; inset: 0; background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.6) 40%, rgba(255,255,255,0) 80%); background-size: 1000px 100%; animation: shimmer 1.2s infinite; opacity: .7; }
    .skeleton.line { height: 12px; margin: 6px 0; }
    .skel-badge { width: 64px; height: 22px; border-radius: 999px; }
    .loading-scrim { position: absolute; inset: 0; background: rgba(255,255,255,.7); display: flex; align-items: center; justify-content: center; z-index: 10; }
    .animate-in { animation: fadeInUp .35s ease both; }
    .animate-stagger > * { animation: fadeInUp .4s ease both; }
    .animate-stagger > *:nth-child(1) { animation-delay: .02s; }
    .animate-stagger > *:nth-child(2) { animation-delay: .06s; }
    .animate-stagger > *:nth-child(3) { animation-delay: .10s; }
    .animate-stagger > *:nth-child(4) { animation-delay: .14s; }
    .animate-stagger > *:nth-child(5) { animation-delay: .18s; }
    .animate-stagger > *:nth-child(6) { animation-delay: .22s; }
    .animate-stagger > *:nth-child(7) { animation-delay: .26s; }
    .animate-stagger > *:nth-child(8) { animation-delay: .30s; }
    @media (prefers-reduced-motion: reduce) { .animate-in, .animate-stagger > * { animation: none !important; } }
    </style>
    <script>
        window.initialOwnerSection = 'reports-sales-history';
        window.laravelData = {
            dashboardData: @json($dashboardData ?? []),
            user: @json(auth()->user()),
            routes: {
                salesHistory: '{{ route("owner.sales-history") }}',
                reservationLogs: '{{ route("owner.reservation-logs") }}',
                supplyLogs: '{{ route("owner.supply-logs") }}',
                inventoryOverview: '{{ route("owner.inventory-overview") }}'
            }
        };
    </script>
</head>
<body>
<!-- section-scoped loaders are created dynamically inside the active section -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text"><h2>ShoeVault Batangas</h2></div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item" data-section="inventory-dashboard">
            <a href="{{ route('owner.dashboard') }}" class="nav-link">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item active" data-section="reports">
            <a href="#" class="nav-link" onclick="showSection('reports-sales-history'); return false;">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
        </li>
        <li class="nav-item" data-section="settings">
            <a href="{{ route('owner.settings') }}" class="nav-link">
                <i class="fas fa-cog"></i><span>Master Controls</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><img src="{{ asset('images/profile.png') }}" alt="Owner"></div>
            <div class="user-details"><h4>{{ auth()->user()->name ?? 'Owner' }}</h4></div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>

<main class="main-content">
    <header class="header">
        <div class="header-left"><h1 class="main-title" id="page-title">Reports</h1></div>
        <div class="header-right">
            <div class="time-display"><i class="fas fa-clock"></i><span id="current-time">Loading...</span></div>
            <div class="date-display"><i class="fas fa-calendar"></i><span id="current-date">Loading...</span></div>
            <div class="notification-wrapper">
                <button class="notification-bell" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count" style="display:none;">0</span>
                </button>
                <div class="notification-dropdown">
                    <div class="notification-list">
                        <div class="notification-empty"><i class="fas fa-inbox"></i> No new notifications</div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="content-grid">
        <!-- Sales History -->
        <section id="reports-sales-history" class="content-section active">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-receipt"></i> Sales History</h2>
                <div style="margin-left:auto;">
                    <div class="switch-tabs" role="tablist" aria-label="Reports switch">
                        <button type="button" class="switch-tab active" data-target="reports-sales-history" onclick="showSection('reports-sales-history');" aria-selected="true">Sales</button>
                        <button type="button" class="switch-tab" data-target="reports-reservation-logs" onclick="showSection('reports-reservation-logs');" aria-selected="false">Reservations</button>
                        <button type="button" class="switch-tab" data-target="reports-supply-logs" onclick="showSection('reports-supply-logs');" aria-selected="false">Supply</button>
                        <button type="button" class="switch-tab" data-target="reports-inventory-overview" onclick="showSection('reports-inventory-overview');" aria-selected="false">Inventory</button>
                    </div>
                </div>
            </div>
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="sales-search" placeholder="Search sales..." class="search-input" style="flex:1; min-width:180px;">
                <select id="sales-period-filter" class="filter-select">
                    <option value="">All Periods</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
                <select id="sales-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date (Newest)</option>
                    <option value="date-asc">Date (Oldest)</option>
                    <option value="amount-desc">Amount (High-Low)</option>
                    <option value="amount-asc">Amount (Low-High)</option>
                </select>
                <div class="export-group" aria-label="Export" style="margin-left:auto;">
                    <button type="button" class="export-btn" data-format="csv" title="Export CSV"><i class="fas fa-download"></i><span class="hide-sm">CSV</span></button>
                    <button type="button" class="export-btn" data-format="xls" title="Export Excel"><i class="fas fa-file-excel"></i><span class="hide-sm">Excel</span></button>
                </div>
            </div>
            <div class="table-container">
                <table id="sales-history-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Sale Type</th>
                            <th>Processed By</th>
                            <th>Products</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Amount Paid</th>
                            <th>Change</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody id="sales-history-tbody"></tbody>
                </table>
            </div>
        </section>

        <!-- Reservation Logs -->
        <section id="reports-reservation-logs" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-calendar-alt"></i> Reservation Logs</h2>
                <div style="margin-left:auto;">
                    <div class="switch-tabs" role="tablist" aria-label="Reports switch">
                        <button type="button" class="switch-tab" data-target="reports-sales-history" onclick="showSection('reports-sales-history');" aria-selected="false">Sales</button>
                        <button type="button" class="switch-tab active" data-target="reports-reservation-logs" onclick="showSection('reports-reservation-logs');" aria-selected="true">Reservations</button>
                        <button type="button" class="switch-tab" data-target="reports-supply-logs" onclick="showSection('reports-supply-logs');" aria-selected="false">Supply</button>
                        <button type="button" class="switch-tab" data-target="reports-inventory-overview" onclick="showSection('reports-inventory-overview');" aria-selected="false">Inventory</button>
                    </div>
                </div>
            </div>
            <!-- Status filter: All / Completed / Cancelled -->
            <div class="filters" style="display:flex; align-items:center; justify-content:space-between;">
                <div class="switch-tabs" id="reservation-status-switch" role="tablist" aria-label="Reservation status">
                    <button type="button" class="switch-tab active" data-status="all">All</button>
                    <button type="button" class="switch-tab" data-status="completed">Completed</button>
                    <button type="button" class="switch-tab" data-status="cancelled">Cancelled</button>
                </div>
                <div class="export-group" aria-label="Export">
                    <button type="button" class="export-btn" data-format="csv" title="Export CSV"><i class="fas fa-download"></i><span class="hide-sm">CSV</span></button>
                    <button type="button" class="export-btn" data-format="xls" title="Export Excel"><i class="fas fa-file-excel"></i><span class="hide-sm">Excel</span></button>
                </div>
            </div>
            <!-- Card list -->
            <div id="reservation-card-list" class="resv-card-list"></div>
        </section>

        <!-- Supply Logs -->
        <section id="reports-supply-logs" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-truck"></i> Supply Logs</h2>
                <div style="margin-left:auto;">
                    <div class="switch-tabs" role="tablist" aria-label="Reports switch">
                        <button type="button" class="switch-tab" data-target="reports-sales-history" onclick="showSection('reports-sales-history');" aria-selected="false">Sales</button>
                        <button type="button" class="switch-tab" data-target="reports-reservation-logs" onclick="showSection('reports-reservation-logs');" aria-selected="false">Reservations</button>
                        <button type="button" class="switch-tab active" data-target="reports-supply-logs" onclick="showSection('reports-supply-logs');" aria-selected="true">Supply</button>
                        <button type="button" class="switch-tab" data-target="reports-inventory-overview" onclick="showSection('reports-inventory-overview');" aria-selected="false">Inventory</button>
                    </div>
                </div>
            </div>
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="supply-search" placeholder="Search by supplier, country, brand, or size..." class="search-input" style="flex:1; min-width:220px;">
                <select id="supply-sort-filter" class="filter-select" style="min-width:220px;">
                    <option value="date-desc">Received Date (Newest)</option>
                    <option value="date-asc">Received Date (Oldest)</option>
                    <option value="id-desc">Log ID (Descending)</option>
                    <option value="id-asc">Log ID (Ascending)</option>
                </select>
                <div class="export-group" aria-label="Export" style="margin-left:auto;">
                    <button type="button" class="export-btn" data-format="csv" title="Export CSV"><i class="fas fa-download"></i><span class="hide-sm">CSV</span></button>
                    <button type="button" class="export-btn" data-format="xls" title="Export Excel"><i class="fas fa-file-excel"></i><span class="hide-sm">Excel</span></button>
                </div>
            </div>
            <div class="table-container">
                <table id="supply-logs-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Supplier</th>
                            <th>Country</th>
                            <th>Brand</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Received At</th>
                        </tr>
                    </thead>
                    <tbody id="supply-logs-tbody"></tbody>
                </table>
            </div>
        </section>

        <!-- Inventory Overview -->
        <section id="reports-inventory-overview" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-boxes"></i> Inventory Overview</h2>
                <div style="margin-left:auto;">
                    <div class="switch-tabs" role="tablist" aria-label="Reports switch">
                        <button type="button" class="switch-tab" data-target="reports-sales-history" onclick="showSection('reports-sales-history');" aria-selected="false">Sales</button>
                        <button type="button" class="switch-tab" data-target="reports-reservation-logs" onclick="showSection('reports-reservation-logs');" aria-selected="false">Reservations</button>
                        <button type="button" class="switch-tab" data-target="reports-supply-logs" onclick="showSection('reports-supply-logs');" aria-selected="false">Supply</button>
                        <button type="button" class="switch-tab active" data-target="reports-inventory-overview" onclick="showSection('reports-inventory-overview');" aria-selected="true">Inventory</button>
                    </div>
                </div>
            </div>
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="inventory-search" placeholder="Search inventory..." class="search-input" style="flex:1; min-width:180px;">
                <select id="inventory-source-filter" class="filter-select">
                    <option value="pos">POS Inventory</option>
                    <option value="reservation">Reservation Inventory</option>
                </select>
                <select id="inventory-category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="men">Men's Shoes</option>
                    <option value="women">Women's Shoes</option>
                    <option value="accessories">Accessories</option>
                </select>
                <select id="inventory-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="name-asc">Product Name (A-Z)</option>
                    <option value="name-desc">Product Name (Z-A)</option>
                    <option value="brand-asc">Brand (A-Z)</option>
                    <option value="brand-desc">Brand (Z-A)</option>
                    <option value="stock-desc">Stock (High-Low)</option>
                    <option value="stock-asc">Stock (Low-High)</option>
                </select>
                <div class="export-group" aria-label="Export" style="margin-left:auto;">
                    <button type="button" class="export-btn" data-format="csv" title="Export CSV"><i class="fas fa-download"></i><span class="hide-sm">CSV</span></button>
                    <button type="button" class="export-btn" data-format="xls" title="Export Excel"><i class="fas fa-file-excel"></i><span class="hide-sm">Excel</span></button>
                </div>
            </div>
            <style>
                /* Inventory Overview horizontal cards */
                .inv-list { display:flex; flex-direction:column; gap:12px; margin-top:10px; }
                .inv-card { display:grid; grid-template-columns: 112px 1fr auto; align-items:center; gap:16px; background:#fff; border:1px solid #eef2f7; border-radius:16px; padding:12px 16px; box-shadow:0 6px 20px -12px rgba(2,6,23,.18); }
                .inv-thumb { width:112px; height:84px; border-radius:12px; overflow:hidden; background:#f1f5f9; display:flex; align-items:center; justify-content:center; }
                .inv-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
                .inv-name { font-size:13px; font-weight:800; color:#0f172a; }
                .inv-brand { font-size:11px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:.5px; }
                .inv-stock { font-size:12px; color:#1e40af; font-weight:700; }
                .inv-sizes { display:flex; flex-wrap:wrap; gap:6px; }
                .inv-size { background:#e0e7ff; color:#1e3a8a; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:800; }
                .inv-badge { background:#111827; color:#fff; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:800; }
                .inv-badge.gray { background:#374151; }
                /* New 3-rows x 2-columns details grid */
                .inv-info { display:grid; grid-template-columns: 1fr 1fr; grid-auto-rows:minmax(22px, auto); gap:8px 16px; }
                .inv-field { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
                .inv-label { font-size:12px; color:#64748b; font-weight:700; }
                .inv-value { font-size:12px; color:#0f172a; font-weight:800; }
                .inv-right { display:flex; flex-direction:column; gap:10px; align-items:flex-end; }
                .inv-price { font-size:13px; font-weight:800; color:#0f172a; }
                .btn-view { padding:10px 16px; border-radius:10px; border:0; color:#fff; font-weight:700; cursor:pointer; background:linear-gradient(135deg,#3b82f6,#1d4ed8); box-shadow:0 6px 18px -8px rgba(29,78,216,.55); }
                .btn-view:hover { filter:brightness(1.05); box-shadow:0 10px 22px -8px rgba(29,78,216,.65); }
                @media (max-width: 900px) { .inv-card { grid-template-columns: 80px 1fr; } .inv-right { align-items:flex-start; } }
            </style>
            <div id="inventory-overview-list" class="inv-list"></div>

            <!-- Product Detail Modal -->
            <div id="product-detail-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;z-index:10000;">
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.18);width:min(920px,96vw);max-height:90vh;overflow:auto;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #eef2f7;">
                        <h3 style="margin:0;font-size:1.05rem;font-weight:800;">Product Details</h3>
                        <button id="pd-close" style="background:none;border:none;font-size:1.4rem;line-height:1;cursor:pointer;">&times;</button>
                    </div>
                    <div style="display:grid;grid-template-columns: 1fr 1fr;gap:18px;padding:18px;">
                        <div>
                            <div style="width:100%;aspect-ratio:1/1;background:#f1f5f9;border-radius:12px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <img id="pd-image" src="" alt="Product" style="max-width:100%;max-height:100%;object-fit:cover;display:block;" />
                            </div>
                        </div>
                        <div>
                            <div id="pd-name" style="font-size:1.1rem;font-weight:800;color:#0f172a;">Name</div>
                            <div id="pd-brand" style="font-size:.9rem;color:#334155;font-weight:700;margin-top:2px;text-transform:uppercase;">BRAND</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px;">
                                <div><div style="font-size:.85rem;color:#64748b;">Category:</div><div id="pd-category" style="font-weight:800;color:#0f172a;">-</div></div>
                                <div><div style="font-size:.85rem;color:#64748b;">Color:</div><div id="pd-color" style="font-weight:800;color:#0f172a;">-</div></div>
                                <div><div style="font-size:.85rem;color:#64748b;">Price:</div><div id="pd-price" style="font-weight:800;color:#0f172a;">-</div></div>
                                <div><div style="font-size:.85rem;color:#64748b;">Total Stock:</div><div id="pd-stock" style="font-weight:800;color:#16a34a;">-</div></div>
                            </div>
                            <div style="margin-top:12px;">
                                <div style="font-size:.85rem;color:#64748b;margin-bottom:6px;">Available Sizes:</div>
                                <div id="pd-sizes" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{ asset('js/owner.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
// Section-scoped loader helpers
        function getActiveSection(){
            const sections = Array.from(document.querySelectorAll('.content-section'));
            const shown = sections.find(s => s.style.display !== 'none');
            if (shown) return shown;
            return document.querySelector('.content-section.active') || sections[0];
        }
        function ensureSectionPosition(sec){ if (!sec) return; const pos = getComputedStyle(sec).position; if (pos === 'static') sec.style.position = 'relative'; }
        function showSectionLoader(){ const sec = getActiveSection(); if (!sec) return; ensureSectionPosition(sec); let loader = sec.querySelector('.section-loader'); if (!loader){ loader = document.createElement('div'); loader.className = 'section-loader'; loader.innerHTML = '<div class="loader" aria-label="Loading"></div>'; sec.appendChild(loader); } loader.style.display = 'flex'; }
        function hideSectionLoader(){ const sec = getActiveSection(); if (!sec) { document.querySelectorAll('.section-loader').forEach(l=>l.remove()); return; } const loader = sec.querySelector('.section-loader'); if (loader) loader.remove(); }
        showSectionLoader();
        function updateDateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
        // Notifications toggle
        (function initNotifications(){
            document.querySelectorAll('.notification-wrapper').forEach(wrapper => {
                const bell = wrapper.querySelector('.notification-bell');
                if (!bell) return;
                bell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    wrapper.classList.toggle('open');
                });
            });
            document.addEventListener('click', () => {
                document.querySelectorAll('.notification-wrapper.open').forEach(w => w.classList.remove('open'));
            });
        })();

    // Helper: skeletons/animations
        function injectSkeletonList(el, count = 6) {
            if (!el) return;
            el.classList.add('animate-stagger');
            el.innerHTML = Array.from({length: count}).map(()=> `
                <div class="resv-card">
                    <div class="resv-grid">
                        <div class="resv-field"><div class="skeleton line" style="width:120px"></div><div class="skeleton line" style="width:180px"></div></div>
                        <div class="resv-field"><div class="skeleton line" style="width:100px"></div><div class="skeleton line" style="width:140px"></div></div>
                        <div class="resv-field"><div class="skeleton line" style="width:90px"></div><div class="skeleton line" style="width:110px"></div></div>
                        <div class="resv-field" style="align-items:flex-end"><div class="skeleton skel-badge"></div></div>
                    </div>
                </div>
            `).join('');
        }
        function injectTableSkeleton(container) {
            if (!container) return;
            container.style.position = 'relative';
            if (!container.querySelector('.loading-scrim')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-scrim';
                overlay.innerHTML = '<div class="loader"></div>';
                container.appendChild(overlay);
            }
        }
        function removeTableSkeleton(container) {
            const overlay = container?.querySelector('.loading-scrim');
            if (overlay) overlay.remove();
        }
        function animateChildren(selector) {
            const root = document.querySelector(selector);
            if (!root) return;
            const kids = Array.from(root.children || []);
            kids.forEach((el, i) => { el.classList.add('animate-in'); el.style.animationDelay = `${i*40}ms`; });
        }

    // Reservation Logs: fetch and render completed/cancelled
        const resvListEl = document.getElementById('reservation-card-list');
        const statusSwitch = document.getElementById('reservation-status-switch');

        function fmtDate(value, withTime = false) {
            if (!value) return 'N/A';
            const d = new Date(value);
            const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            if (!withTime) return date;
            const time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return `${date} ${time}`;
        }

        function statusBadgeClass(status){
            return status === 'completed' ? 'resv-badge completed' : 'resv-badge cancelled';
        }

        function renderReservationCards(items){
            if (!resvListEl) return;
            if (!items || !items.length) {
                resvListEl.innerHTML = `
                    <div class="resv-card" style="text-align:center;">
                        <div style="color:#6b7280;">No reservations to show.</div>
                    </div>`;
                return;
            }
            resvListEl.innerHTML = items.map(r => {
                const name = r.customer_name || (r.customer && (r.customer.fullname || r.customer.name)) || 'N/A';
                const email = r.customer_email || (r.customer && r.customer.email) || 'N/A';
                const phone = r.customer_phone || (r.customer && (r.customer.phone_number || r.customer.phone)) || 'N/A';
                return `
                <div class="resv-card" data-res-id="${r.id}" data-status="${r.status}">
                    <div class="resv-grid">
                        <!-- Column 1: Reservation ID + Reservation Date -->
                        <div class="resv-field">
                            <div>
                                <div class="resv-label">Reservation ID</div>
                                <div class="resv-value">${r.reservation_id || 'N/A'}</div>
                            </div>
                            <div>
                                <div class="resv-label">Reservation Date</div>
                                <div class="resv-value">${fmtDate(r.created_at)}</div>
                            </div>
                        </div>
                        <!-- Column 2: Customer Name + Pickup Date -->
                        <div class="resv-field">
                            <div>
                                <div class="resv-label">Customer Name</div>
                                <div class="resv-value">${name}</div>
                            </div>
                            <div>
                                <div class="resv-label">Pickup Date</div>
                                <div class="resv-value">${fmtDate(r.pickup_date)}</div>
                            </div>
                        </div>
                        <!-- Column 3: Email + Phone -->
                        <div class="resv-field">
                            <div>
                                <div class="resv-label">Email</div>
                                <div class="resv-value">${email}</div>
                            </div>
                            <div>
                                <div class="resv-label">Phone</div>
                                <div class="resv-value">${phone}</div>
                            </div>
                        </div>
                        <!-- Column 4: Status only (single row) -->
                        <div class="resv-field" style="text-align:right; display:flex; justify-content:center; align-items:flex-end;">
                            <div>
                                <div class="resv-label">Status</div>
                                <div><span class="${statusBadgeClass(r.status)}">${(r.status||'').charAt(0).toUpperCase() + (r.status||'').slice(1)}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
            animateChildren('#reservation-card-list');
        }

        async function fetchReservationLogs(opts = {}){
            const { status = 'all', search = '', sort = 'date-desc' } = opts;
            const url = new URL(window.laravelData.routes.reservationLogs, window.location.origin);
            url.searchParams.set('status', status);
            if (search) url.searchParams.set('search', search);
            if (sort) url.searchParams.set('sort', sort);
            try {
                injectSkeletonList(resvListEl, 6);
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!data || data.success === false) throw new Error('Failed to load');
                renderReservationCards(data.reservations || []);
            } catch (e) {
                console.error(e);
                if (resvListEl) resvListEl.innerHTML = '<div class="resv-card"><div style="color:#ef4444;">Failed to load reservation logs.</div></div>';
            }
        }

        // Wire status tabs
        if (statusSwitch) {
            statusSwitch.querySelectorAll('.switch-tab').forEach(btn => {
                btn.addEventListener('click', () => {
                    statusSwitch.querySelectorAll('.switch-tab').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const status = btn.getAttribute('data-status') || 'all';
                    fetchReservationLogs({ status });
                });
            });
        }

        // Ensure data fetch when navigating to the Reservation Logs tab
        document.querySelectorAll('.switch-tab[data-target="reports-reservation-logs"]').forEach(btn => {
            btn.addEventListener('click', () => fetchReservationLogs({ status: 'all' }));
        });

        // Sales History filters wiring
        (function wireSalesFilters(){
            const searchEl = document.getElementById('sales-search');
            const periodEl = document.getElementById('sales-period-filter');
            const sortEl = document.getElementById('sales-sort-filter');
            const mapPeriod = (v)=> v==='today' ? 'daily' : (v==='week' ? 'weekly' : (v==='month' ? 'monthly' : 'weekly'));
            // Initial fetch
            if (typeof loadSalesHistory === 'function') {
                const cont = document.querySelector('#reports-sales-history .table-container');
                injectTableSkeleton(cont);
                const orig = loadSalesHistory;
                window.loadSalesHistory = async function(...args){
                    injectTableSkeleton(cont);
                    try {
                        const r = orig.apply(this, args);
                        if (r && typeof r.then === 'function') await r; else await new Promise(res=> setTimeout(res, 200));
                    } finally {
                        removeTableSkeleton(cont);
                        animateChildren('#reports-sales-history tbody');
                    }
                };
                loadSalesHistory(mapPeriod(periodEl?.value || ''));
            }
            const apply = ()=> {
                if (typeof applySalesFilters === 'function') applySalesFilters({
                    search: searchEl?.value || '',
                    sort: sortEl?.value || 'date-desc',
                    periodUi: (periodEl?.value || '')
                });
            };
            let t;
            searchEl?.addEventListener('input', ()=> { clearTimeout(t); t = setTimeout(apply, 200); });
            periodEl?.addEventListener('change', ()=> {
                if (typeof loadSalesHistory === 'function') loadSalesHistory(mapPeriod(periodEl.value));
                // apply after small delay to use latest cache
                setTimeout(apply, 150);
            });
            sortEl?.addEventListener('change', apply);
        })();

        // Supply Logs filters wiring
        (function wireSupplyFilters(){
            const searchEl = document.getElementById('supply-search');
            const sortEl = document.getElementById('supply-sort-filter');
            const tableCont = document.querySelector('#reports-supply-logs .table-container');
            const apply = ()=> { if (typeof applySupplyFilters === 'function') {
                injectTableSkeleton(tableCont);
                const r = applySupplyFilters({ search: searchEl?.value || '', sort: sortEl?.value || 'date-desc' });
                if (r && typeof r.then === 'function') r.finally(()=> { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); });
                else setTimeout(()=> { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); }, 250);
            }};
            // Initial fetch
            if (typeof loadSupplyLogs === 'function') {
                injectTableSkeleton(tableCont);
                const orig = loadSupplyLogs;
                window.loadSupplyLogs = async function(...args){
                    injectTableSkeleton(tableCont);
                    try { const r = orig.apply(this, args); if (r && typeof r.then === 'function') await r; else await new Promise(res=> setTimeout(res, 200)); }
                    finally { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); }
                };
                loadSupplyLogs();
            }

            let t;
            searchEl?.addEventListener('input', ()=> { clearTimeout(t); t = setTimeout(apply, 200); });
            sortEl?.addEventListener('change', apply);
        })();

        // Inventory Overview: hook filters and initial load
        (function wireInventoryFilters(){
            const sourceSel = document.getElementById('inventory-source-filter');
            const catSel = document.getElementById('inventory-category-filter');
            const searchEl = document.getElementById('inventory-search');
            const sortSel = document.getElementById('inventory-sort-filter');
            const list = document.getElementById('inventory-overview-list');
            const showInvSkeleton = ()=> { if (list) { list.classList.add('animate-stagger'); list.innerHTML = Array.from({length: 10}).map(()=> `<div class=\"inv-card\" style=\"border:1px solid #eef2f7; border-radius:12px; padding:16px;\">\n                <div class=\"skeleton line\" style=\"width:45%; height:14px;\"></div>\n                <div class=\"skeleton line\" style=\"width:70%; height:10px; margin-top:8px;\"></div>\n            </div>`).join(''); }};
            const apply = ()=> {
                if (typeof loadInventoryOverview === 'function') {
                    loadInventoryOverview(sourceSel?.value || 'pos', {
                        category: catSel?.value || '',
                        search: searchEl?.value || '',
                        sort: sortSel?.value || ''
                    });
                }
            };
            // Observe for content and animate
            if (list) {
                const mo = new MutationObserver(()=> {
                    const items = Array.from(list.children || []);
                    items.forEach((el, i)=> { el.classList.add('animate-in'); el.style.animationDelay = `${i*30}ms`; });
                });
                mo.observe(list, { childList: true });
            }
            sourceSel?.addEventListener('change', apply);
            catSel?.addEventListener('change', apply);
            sortSel?.addEventListener('change', apply);
            let ti;
            searchEl?.addEventListener('input', ()=> { clearTimeout(ti); ti = setTimeout(apply, 250); });
            // Initial load for POS inventory with current filters
            showInvSkeleton();
            apply();
        })();

    // Initial fetch (completed + cancelled) so content is ready even before switching tabs
    injectSkeletonList(resvListEl, 6);
    fetchReservationLogs({ status: 'all' }).finally(()=> setTimeout(hideSectionLoader, 300));

        // ===== Export utilities =====
        function filenameWithDate(base, ext){
            const d = new Date();
            const pad = (n)=> String(n).padStart(2,'0');
            const stamp = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}_${pad(d.getHours())}-${pad(d.getMinutes())}`;
            return `${base}_${stamp}.${ext}`;
        }

        function download(content, mime, filename){
            const blob = new Blob([content], { type: mime + ';charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = filename; document.body.appendChild(a); a.click();
            setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 100);
        }

        function toCSV(headers, rows){
            const esc = (v)=> {
                const s = (v==null? '': String(v));
                if (/[",\n]/.test(s)) return '"' + s.replace(/"/g,'""') + '"';
                return s;
            };
            const lines = [];
            if (headers && headers.length) lines.push(headers.map(esc).join(','));
            rows.forEach(r => lines.push(r.map(esc).join(',')));
            return lines.join('\n');
        }

        function toExcelHTML(headers, rows){
            const thead = headers && headers.length ? `<thead><tr>${headers.map(h=>`<th>${String(h).replace(/[<>]/g,'')}</th>`).join('')}</tr></thead>` : '';
            const tbody = `<tbody>${rows.map(r=>`<tr>${r.map(c=>`<td>${String(c??'').replace(/[<>]/g,'')}</td>`).join('')}</tr>`).join('')}</tbody>`;
            return `<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><table>${thead}${tbody}</table></body></html>`;
        }

        function tableToArrays(table){
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => Array.from(tr.children).map(td => td.textContent.trim()));
            return { headers, rows };
        }

        function exportSales(format){
            const table = document.getElementById('sales-history-table');
            if (!table) return alert('No sales table to export.');
            const { headers, rows } = tableToArrays(table);
            if (format === 'xls') download(toExcelHTML(headers, rows), 'application/vnd.ms-excel', filenameWithDate('sales_history', 'xls'));
            else download(toCSV(headers, rows), 'text/csv', filenameWithDate('sales_history', 'csv'));
        }

        function exportReservations(format){
            const cards = document.querySelectorAll('#reports-reservation-logs .resv-card');
            if (!cards.length) return alert('No reservations to export.');
            const headers = ['Reservation ID','Reservation Date','Customer Name','Pickup Date','Email','Phone','Status'];
            const rows = [];
            cards.forEach(card => {
                const labels = Array.from(card.querySelectorAll('.resv-label'));
                const values = Array.from(card.querySelectorAll('.resv-value'));
                const map = {};
                labels.forEach((lbl, i) => { map[lbl.textContent.trim()] = (values[i]?.textContent || '').trim(); });
                const status = (card.getAttribute('data-status') || map['Status'] || '').toString();
                rows.push([
                    map['Reservation ID'] || '',
                    map['Reservation Date'] || '',
                    map['Customer Name'] || '',
                    map['Pickup Date'] || '',
                    map['Email'] || '',
                    map['Phone'] || '',
                    status ? (status.charAt(0).toUpperCase()+status.slice(1)) : ''
                ]);
            });
            if (format === 'xls') download(toExcelHTML(headers, rows), 'application/vnd.ms-excel', filenameWithDate('reservation_logs', 'xls'));
            else download(toCSV(headers, rows), 'text/csv', filenameWithDate('reservation_logs', 'csv'));
        }

        function exportSupply(format){
            const table = document.getElementById('supply-logs-table');
            if (!table) return alert('No supply table to export.');
            const { headers, rows } = tableToArrays(table);
            if (format === 'xls') download(toExcelHTML(headers, rows), 'application/vnd.ms-excel', filenameWithDate('supply_logs', 'xls'));
            else download(toCSV(headers, rows), 'text/csv', filenameWithDate('supply_logs', 'csv'));
        }

        async function exportInventory(format){
            // Prefer API export based on current filters for accuracy
            const sourceSel = document.getElementById('inventory-source-filter');
            const catSel = document.getElementById('inventory-category-filter');
            const searchEl = document.getElementById('inventory-search');
            const source = sourceSel?.value || 'pos';
            const category = catSel?.value || '';
            const search = searchEl?.value || '';
            const url = new URL(window.laravelData?.routes?.inventoryOverview, window.location.origin);
            url.searchParams.set('source', source);
            if (category) url.searchParams.set('category', category);
            if (search) url.searchParams.set('search', search);
            try{
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const items = Array.isArray(data.items) ? data.items : [];
                const headers = ['Product','Brand','Category','Color','Price','Total Stock','Sizes'];
                const rows = items.map(i => [i.name||'', i.brand||'', i.category||'', i.color||'', i.price||'', i.total_stock||0, i.sizes||'']);
                if (format === 'xls') download(toExcelHTML(headers, rows), 'application/vnd.ms-excel', filenameWithDate('inventory_overview', 'xls'));
                else download(toCSV(headers, rows), 'text/csv', filenameWithDate('inventory_overview', 'csv'));
            }catch(e){
                // Fallback: try to scrape current cards
                const cards = document.querySelectorAll('#reports-inventory-overview .inv-card');
                if (!cards.length) return alert('No inventory to export.');
                const headers = ['Product','Brand','Details','Price','Stock'];
                const rows = Array.from(cards).map(c => [
                    (c.querySelector('.inv-name')?.textContent||'').trim(),
                    (c.querySelector('.inv-brand')?.textContent||'').trim(),
                    (c.querySelector('.inv-info')?.textContent||'').trim().replace(/\s+/g,' '),
                    (c.querySelector('.inv-price')?.textContent||'').trim(),
                    (c.querySelector('.inv-stock')?.textContent||'').trim(),
                ]);
                if (format === 'xls') download(toExcelHTML(headers, rows), 'application/vnd.ms-excel', filenameWithDate('inventory_overview', 'xls'));
                else download(toCSV(headers, rows), 'text/csv', filenameWithDate('inventory_overview', 'csv'));
            }
        }

        function activeSectionId(){
            const sections = Array.from(document.querySelectorAll('.content-section'));
            // Prefer displayed section
            const shown = sections.find(s => s.style.display !== 'none');
            if (shown) return shown.id;
            const active = sections.find(s => s.classList.contains('active'));
            return active ? active.id : '';
        }

        function exportActive(format){
            const id = activeSectionId();
            if (id === 'reports-sales-history') return exportSales(format);
            if (id === 'reports-reservation-logs') return exportReservations(format);
            if (id === 'reports-supply-logs') return exportSupply(format);
            if (id === 'reports-inventory-overview') return exportInventory(format);
            alert('No report section selected to export.');
        }

        // Wire buttons (all sections share the same handlers)
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const fmt = btn.getAttribute('data-format') || 'csv';
                exportActive(fmt);
            });
        });
    });
</script>

</body>
</html>