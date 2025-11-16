<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports - ShoeVault Batangas</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">
    
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
        .notification-count { position: absolute; top: 2px; right: 2px; background: rgb(239, 68, 68); color: rgb(255, 255, 255); border-radius: 999px; padding: 2px 6px; font-size: 11px; display: inline-block; }
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
    .export-btn, .page-btn {
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
    .export-btn:hover, .page-btn:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px -10px rgba(37,99,235,.35); }
    .export-btn:active, .page-btn:active { transform: translateY(1px); }
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
        <img src="{{ asset('images/shoevault-logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text"><h2>ShoeVault Batangas</h2></div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="{{ route('owner.dashboard') }}" class="nav-link">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item active" data-section="reports">
            <a href="#" class="nav-link" onclick="showSection('reports-sales-history'); return false;">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
        </li>
        <li class="nav-item">
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
                    <i class="fas fa-bell" style="font-size: 1.5rem;"></i>
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
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px; flex-wrap:wrap;">
                <input type="text" id="sales-search" placeholder="Search sales..." class="search-input" style="flex:1; min-width:180px;">
                <input type="month" id="sales-month-filter" class="search-input" aria-label="Filter by month">
                <select id="sales-type-filter" class="filter-select" style="min-width:160px;">
                    <option value="all">All</option>
                    <option value="pos">POS</option>
                    <option value="reservation">Reservation</option>
                </select>
                <select id="sales-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date (Newest)</option>
                    <option value="date-asc">Date (Oldest)</option>
                    <option value="amount-desc">Amount (High-Low)</option>
                    <option value="amount-asc">Amount (Low-High)</option>
                </select>
                <div class="export-group" aria-label="Export" style="margin-left:auto;">
                    <button type="button" class="export-btn" id="open-export-modal" title="Export Sales Data"><i class="fas fa-download"></i><span class="hide-sm">Export</span></button>
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
                <div id="sales-pagination" style="display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:8px 0;">
                    <button type="button" id="sales-prev" class="page-btn" style="height:28px; padding:4px 10px;">Prev</button>
                    <span id="sales-page-info" style="font-size:12px; color:#64748b;">Page 1 of 1</span>
                    <button type="button" id="sales-next" class="page-btn" style="height:28px; padding:4px 10px;">Next</button>
                </div>
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
            <div class="filters" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; justify-content:space-between;">
                <div class="switch-tabs" id="reservation-status-switch" role="tablist" aria-label="Reservation status">
                    <button type="button" class="switch-tab active" data-status="all">All</button>
                    <button type="button" class="switch-tab" data-status="completed">Completed</button>
                    <button type="button" class="switch-tab" data-status="cancelled">Cancelled</button>
                </div>
                <div style="display:flex; gap:12px; align-items:center; flex:1; justify-content:flex-end; min-width:260px;">
                    <input type="text" id="reservation-search" placeholder="Search reservation ID, name, email, or phone..." class="search-input" style="flex:1; min-width:220px;">
                    <div class="export-group" aria-label="Export">
                        <button type="button" class="export-btn" id="open-reservation-export-modal" title="Export Reservation Data"><i class="fas fa-download"></i><span class="hide-sm">Export</span></button>
                    </div>
                </div>
            </div>
            <!-- Card list -->
            <div id="reservation-card-list" class="resv-card-list"></div>
            <div id="reservation-pagination" style="display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:8px 0;">
                <button type="button" id="reservation-prev" class="page-btn" style="height:28px; padding:4px 10px;">Prev</button>
                <span id="reservation-page-info" style="font-size:12px; color:#64748b;">Page 1 of 1</span>
                <button type="button" id="reservation-next" class="page-btn" style="height:28px; padding:4px 10px;">Next</button>
            </div>
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
                    <button type="button" class="export-btn" id="open-supply-export-modal" title="Export Supply Data"><i class="fas fa-download"></i><span class="hide-sm">Export</span></button>
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
                <div id="supply-pagination" style="display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:8px 0;">
                    <button type="button" id="supply-prev" class="page-btn" style="height:28px; padding:4px 10px;">Prev</button>
                    <span id="supply-page-info" style="font-size:12px; color:#64748b;">Page 1 of 1</span>
                    <button type="button" id="supply-next" class="page-btn" style="height:28px; padding:4px 10px;">Next</button>
                </div>
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
            <div id="inventory-pagination" style="display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:8px 4px;">
                <button type="button" id="inventory-prev" class="page-btn" style="height:28px; padding:4px 10px;">Prev</button>
                <span id="inventory-page-info" style="font-size:12px; color:#64748b;">Page 1 of 1</span>
                <button type="button" id="inventory-next" class="page-btn" style="height:28px; padding:4px 10px;">Next</button>
            </div>

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

    <!-- Export Sales Modal -->
    <div id="export-sales-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;z-index:10000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.3);width:min(600px,95vw);max-height:90vh;overflow:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 24px;border-bottom:1px solid #eef2f7;">
                <h3 style="margin:0;font-size:1.2rem;font-weight:800;color:#0f172a;">Export Sales Data</h3>
                <button id="close-export-modal" style="background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;color:#6b7280;padding:4px;">&times;</button>
            </div>
            <div style="padding:24px;">
                <form id="export-form">
                    <!-- Export All Checkbox -->
                    <div style="margin-bottom:20px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="export-all" checked style="width:16px;height:16px;">
                            <span style="font-weight:600;color:#0f172a;">Export All Transactions</span>
                        </label>
                        <p style="margin:6px 0 0 24px;font-size:12px;color:#64748b;">Check this to export all transaction data in the database</p>
                    </div>

                    <!-- Date Range Filter -->
                    <div id="date-range-filter" style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Date Range</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">Start Date</label>
                                <input type="date" id="export-start-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">End Date</label>
                                <input type="date" id="export-end-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                        </div>
                    </div>

                    <!-- Sale Type Filter -->
                    <div id="sale-type-filter" style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Sale Type</label>
                        <div style="display:flex;gap:16px;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="sale-type" value="both" checked style="width:16px;height:16px;">
                                <span style="color:#0f172a;">Both</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="sale-type" value="pos" style="width:16px;height:16px;">
                                <span style="color:#0f172a;">POS Only</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="sale-type" value="reservation" style="width:16px;height:16px;">
                                <span style="color:#0f172a;">Reservation Only</span>
                            </label>
                        </div>
                    </div>

                    <!-- Processed By Filter -->
                    <div id="processed-by-filter" style="margin-bottom:24px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Processed By</label>
                        <div id="users-list" style="max-height:120px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:8px;">
                            <!-- Users will be populated here via JavaScript -->
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #eef2f7;padding-top:20px;">
                        <button type="button" id="export-csv" class="export-btn" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-download"></i>
                            <span>Export CSV</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reservation Export Modal -->
    <div id="reservation-export-modal-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;z-index:1000;">
        <div id="reservation-export-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <div style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:#0f172a;">Export Reservation Data</h3>
                    <button type="button" id="close-reservation-export-modal" style="background:none;border:none;font-size:20px;color:#6b7280;cursor:pointer;padding:4px;display:flex;align-items:center;justify-content:center;">&times;</button>
                </div>
                <form id="reservation-export-form">
                    <!-- Export All Toggle -->
                    <div style="margin-bottom:20px;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="reservation-export-all" checked style="width:16px;height:16px;">
                            <span style="font-weight:600;color:#0f172a;">Export All Reservations</span>
                        </label>
                        <p style="margin:6px 0 0 24px;font-size:12px;color:#64748b;">Check this to export all reservation data in the database</p>
                    </div>

                    <!-- Date Range Filter -->
                    <div id="reservation-date-range-filter" style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Date Range (Reservation Date)</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">Start Date</label>
                                <input type="date" id="reservation-export-start-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">End Date</label>
                                <input type="date" id="reservation-export-end-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div id="reservation-status-filter" style="margin-bottom:24px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Status</label>
                        <div style="display:flex;gap:16px;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="reservation-status" value="all" checked style="width:16px;height:16px;">
                                <span style="color:#0f172a;">All</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="reservation-status" value="completed" style="width:16px;height:16px;">
                                <span style="color:#0f172a;">Completed</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="reservation-status" value="cancelled" style="width:16px;height:16px;">
                                <span style="color:#0f172a;">Cancelled</span>
                            </label>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #eef2f7;padding-top:20px;">
                        <button type="button" id="reservation-export-csv" class="export-btn" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-download"></i>
                            <span>Export CSV</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Supply Export Modal -->
    <div id="supply-export-modal-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;z-index:1000;">
        <div id="supply-export-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <div style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:#0f172a;">Export Supply Data</h3>
                    <button type="button" id="close-supply-export-modal" style="background:none;border:none;font-size:20px;color:#6b7280;cursor:pointer;padding:4px;display:flex;align-items:center;justify-content:center;">&times;</button>
                </div>
                <form id="supply-export-form">
                    <!-- Export All Toggle -->
                    <div style="margin-bottom:20px;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="supply-export-all" checked style="width:16px;height:16px;">
                            <span style="font-weight:600;color:#0f172a;">Export All Supply Logs</span>
                        </label>
                        <p style="margin:6px 0 0 24px;font-size:12px;color:#64748b;">Check this to export all supply log data in the database</p>
                    </div>

                    <!-- Date Range Filter -->
                    <div id="supply-date-range-filter" style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Date Range (Received Date)</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">Start Date</label>
                                <input type="date" id="supply-export-start-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;">End Date</label>
                                <input type="date" id="supply-export-end-date" style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Filter -->
                    <div id="supply-supplier-filter" style="margin-bottom:20px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Supplier</label>
                        <div style="margin-bottom:8px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" id="select-all-suppliers" checked style="width:16px;height:16px;">
                                <span style="font-weight:600;color:#0f172a;">Select All Suppliers</span>
                            </label>
                        </div>
                        <div id="suppliers-list" style="max-height:120px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:8px;">
                            <!-- Suppliers will be populated here via JavaScript -->
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div id="supply-brand-filter" style="margin-bottom:24px;">
                        <label style="display:block;font-weight:600;color:#0f172a;margin-bottom:8px;">Brand</label>
                        <div style="margin-bottom:8px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" id="select-all-brands" checked style="width:16px;height:16px;">
                                <span style="font-weight:600;color:#0f172a;">Select All Brands</span>
                            </label>
                        </div>
                        <div id="brands-list" style="max-height:120px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:8px;">
                            <!-- Brands will be populated here via JavaScript -->
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #eef2f7;padding-top:20px;">
                        <button type="button" id="supply-export-csv" class="export-btn" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-download"></i>
                            <span>Export CSV</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        const reservationSearchInput = document.getElementById('reservation-search');
        let reservationSearchTimer;

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
            const { status = 'all', search = '', sort = 'date-desc', page = 1 } = opts;
            const url = new URL(window.laravelData.routes.reservationLogs, window.location.origin);
            url.searchParams.set('status', status);
            if (search) url.searchParams.set('search', search);
            if (sort) url.searchParams.set('sort', sort);
            url.searchParams.set('page', page);
            try {
                injectSkeletonList(resvListEl, 6);
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!data || data.success === false) throw new Error('Failed to load');
                renderReservationCards(data.reservations || []);
                if (data.pagination) updateReservationPagination(data.pagination);
            } catch (e) {
                console.error(e);
                if (resvListEl) resvListEl.innerHTML = '<div class="resv-card"><div style="color:#ef4444;">Failed to load reservation logs.</div></div>';
            }
        }

        // Reservation pagination state & controls
        const reservationPageInfo = document.getElementById('reservation-page-info');
        const reservationPrev = document.getElementById('reservation-prev');
        const reservationNext = document.getElementById('reservation-next');
        const __reservationState = { page:1, total_pages:1, status:'all', sort:'date-desc', search:'' };
        function updateReservationPagination(p){
            __reservationState.page = p?.page || 1;
            __reservationState.total_pages = p?.total_pages || 1;
            if (reservationPageInfo) reservationPageInfo.textContent = `Page ${__reservationState.page} of ${__reservationState.total_pages}`;
            if (reservationPrev) reservationPrev.disabled = (__reservationState.page <= 1);
            if (reservationNext) reservationNext.disabled = (__reservationState.page >= __reservationState.total_pages);
        }
        function goReservation(delta){
            const target = __reservationState.page + delta;
            if (target < 1 || target > __reservationState.total_pages) return;
            fetchReservationLogs({
                status: __reservationState.status,
                sort: __reservationState.sort,
                search: __reservationState.search,
                page: target
            });
        }
        reservationPrev?.addEventListener('click', (e)=> { e.preventDefault(); e.stopPropagation(); goReservation(-1); });
        reservationNext?.addEventListener('click', (e)=> { e.preventDefault(); e.stopPropagation(); goReservation(1); });

        // Wire status tabs
        if (statusSwitch) {
            statusSwitch.querySelectorAll('.switch-tab').forEach(btn => {
                btn.addEventListener('click', () => {
                    statusSwitch.querySelectorAll('.switch-tab').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const status = btn.getAttribute('data-status') || 'all';
                    __reservationState.status = status;
                    fetchReservationLogs({
                        status,
                        page: 1,
                        search: __reservationState.search,
                        sort: __reservationState.sort
                    });
                });
            });
        }

        // Ensure data fetch when navigating to the Reservation Logs tab
        document.querySelectorAll('.switch-tab[data-target="reports-reservation-logs"]').forEach(btn => {
            btn.addEventListener('click', () => {
                __reservationState.status = 'all';
                fetchReservationLogs({
                    status: 'all',
                    page: 1,
                    search: __reservationState.search,
                    sort: __reservationState.sort
                });
            });
        });

        reservationSearchInput?.addEventListener('input', () => {
            clearTimeout(reservationSearchTimer);
            reservationSearchTimer = setTimeout(() => {
                __reservationState.search = reservationSearchInput.value.trim();
                fetchReservationLogs({
                    status: __reservationState.status,
                    sort: __reservationState.sort,
                    search: __reservationState.search,
                    page: 1
                });
            }, 200);
        });

        // Sales History filters wiring
            (function wireSalesFilters(){
            const searchEl = document.getElementById('sales-search');
            const monthEl = document.getElementById('sales-month-filter');
            const typeEl = document.getElementById('sales-type-filter');
            const dateEl = document.getElementById('sales-date-filter');
            const sortEl = document.getElementById('sales-sort-filter');
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
                loadSalesHistory({}); // default load
            }
            const apply = ()=> {
                if (typeof applySalesFilters === 'function') applySalesFilters({
                    search: searchEl?.value || '',
                    sort: sortEl?.value || 'date-desc',
                    type: typeEl?.value || 'all'
                });
            };
            let t;
            searchEl?.addEventListener('input', ()=> { clearTimeout(t); t = setTimeout(apply, 200); });
            monthEl?.addEventListener('change', ()=> { if (!dateEl?.value && typeof loadSalesHistory==='function') loadSalesHistory({ month: monthEl.value, type: typeEl?.value || 'all' }); setTimeout(apply, 150); });
            dateEl?.addEventListener('change', ()=> { if (typeof loadSalesHistory==='function') loadSalesHistory({ date: dateEl.value, type: typeEl?.value || 'all' }); setTimeout(apply, 150); });
            typeEl?.addEventListener('change', ()=> { apply(); });
            sortEl?.addEventListener('change', apply);
        })();

        // Supply Logs filters wiring
        (function wireSupplyFilters(){
            const searchEl = document.getElementById('supply-search');
            const sortEl = document.getElementById('supply-sort-filter');
            const tableCont = document.querySelector('#reports-supply-logs .table-container');
            const supplyPrev = document.getElementById('supply-prev');
            const supplyNext = document.getElementById('supply-next');
            const supplyPageInfo = document.getElementById('supply-page-info');
            const __supplyState = { page:1, total_pages:1, per_page:25, search:'', sort:'date-desc' };
            function updateSupplyPagination(p){
                __supplyState.page = p?.page || 1;
                __supplyState.total_pages = p?.total_pages || 1;
                __supplyState.per_page = p?.per_page || __supplyState.per_page;
                if (supplyPageInfo) supplyPageInfo.textContent = `Page ${__supplyState.page} of ${__supplyState.total_pages}`;
                if (supplyPrev) supplyPrev.disabled = (__supplyState.page <= 1);
                if (supplyNext) supplyNext.disabled = (__supplyState.page >= __supplyState.total_pages);
            }
            function goSupply(delta){
                const target = __supplyState.page + delta;
                if (target < 1 || target > __supplyState.total_pages) return;
                injectTableSkeleton(tableCont);
                const r = loadSupplyLogs({ page: target, perPage: __supplyState.per_page, search: __supplyState.search, sort: __supplyState.sort });
                if (r && typeof r.then === 'function') r.then(d => updateSupplyPagination(d?.pagination)).finally(()=> { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); });
            }
            supplyPrev?.addEventListener('click', (e)=> { e.preventDefault(); e.stopPropagation(); goSupply(-1); });
            supplyNext?.addEventListener('click', (e)=> { e.preventDefault(); e.stopPropagation(); goSupply(1); });
            const apply = ()=> { if (typeof applySupplyFilters === 'function') {
                __supplyState.search = searchEl?.value || '';
                __supplyState.sort = sortEl?.value || 'date-desc';
                __supplyState.page = 1;
                injectTableSkeleton(tableCont);
                const r = loadSupplyLogs({ page: 1, perPage: __supplyState.per_page, search: __supplyState.search, sort: __supplyState.sort });
                if (r && typeof r.then === 'function') r.then(d=> { updateSupplyPagination(d?.pagination); if (typeof applySupplyFilters==='function') applySupplyFilters({ search: __supplyState.search, sort: __supplyState.sort }); }).finally(()=> { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); });
                else setTimeout(()=> { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); }, 250);
            }};
            // Initial fetch
            if (typeof loadSupplyLogs === 'function') {
                injectTableSkeleton(tableCont);
                const orig = loadSupplyLogs;
                window.loadSupplyLogs = async function(opts = {}){
                    injectTableSkeleton(tableCont);
                    try { const r = orig.apply(this, [opts]); const data = (r && typeof r.then === 'function') ? await r : null; if (data?.pagination) updateSupplyPagination(data.pagination); }
                    finally { removeTableSkeleton(tableCont); animateChildren('#reports-supply-logs tbody'); }
                };
                loadSupplyLogs({ page: 1, perPage: __supplyState.per_page });
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
            const invPrev = document.getElementById('inventory-prev');
            const invNext = document.getElementById('inventory-next');
            const invPageInfo = document.getElementById('inventory-page-info');
            const __inventoryState = { page:1, total_pages:1, per_page:25, source:'pos', category:'', search:'', sort:'' };
            const showInvSkeleton = ()=> { if (list) { list.classList.add('animate-stagger'); list.innerHTML = Array.from({length: 10}).map(()=> `<div class=\"inv-card\" style=\"border:1px solid #eef2f7; border-radius:12px; padding:16px;\">\n                <div class=\"skeleton line\" style=\"width:45%; height:14px;\"></div>\n                <div class=\"skeleton line\" style=\"width:70%; height:10px; margin-top:8px;\"></div>\n            </div>`).join(''); }};
            const apply = ()=> {
                if (typeof loadInventoryOverview === 'function') {
                    __inventoryState.source = sourceSel?.value || 'pos';
                    __inventoryState.category = catSel?.value || '';
                    __inventoryState.search = searchEl?.value || '';
                    __inventoryState.sort = sortSel?.value || '';
                    loadInventoryOverview(__inventoryState.source, {
                        category: __inventoryState.category,
                        search: __inventoryState.search,
                        sort: __inventoryState.sort,
                        page: __inventoryState.page,
                        perPage: __inventoryState.per_page
                    }).then(d => { if (d?.pagination) updateInventoryPagination(d.pagination); });
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
            sourceSel?.addEventListener('change', ()=> { __inventoryState.page=1; apply(); });
            catSel?.addEventListener('change', ()=> { __inventoryState.page=1; apply(); });
            sortSel?.addEventListener('change', ()=> { apply(); });
            let ti;
            searchEl?.addEventListener('input', ()=> { clearTimeout(ti); ti = setTimeout(apply, 250); });
            function updateInventoryPagination(p){
                __inventoryState.page = p?.page || 1;
                __inventoryState.total_pages = p?.total_pages || 1;
                if (invPageInfo) invPageInfo.textContent = `Page ${__inventoryState.page} of ${__inventoryState.total_pages}`;
                if (invPrev) invPrev.disabled = (__inventoryState.page <= 1);
                if (invNext) invNext.disabled = (__inventoryState.page >= __inventoryState.total_pages);
            }
            function goInventory(delta){
                const target = __inventoryState.page + delta;
                if (target < 1 || target > __inventoryState.total_pages) return;
                __inventoryState.page = target;
                apply();
            }
            invPrev?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); goInventory(-1); });
            invNext?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); goInventory(1); });
            // Initial load for POS inventory with current filters
            showInvSkeleton();
            apply();
        })();

    // Initial fetch (completed + cancelled) so content is ready even before switching tabs
    injectSkeletonList(resvListEl, 6);
    fetchReservationLogs({ status: 'all', page: 1 }).finally(()=> setTimeout(hideSectionLoader, 300));

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



        function tableToArrays(table){
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => Array.from(tr.children).map(td => td.textContent.trim()));
            return { headers, rows };
        }

        function exportSales(format){
            const table = document.getElementById('sales-history-table');
            if (!table) return alert('No sales table to export.');
            const { headers, rows } = tableToArrays(table);
            download(toCSV(headers, rows), 'text/csv', filenameWithDate('sales_history', 'csv'));
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
            download(toCSV(headers, rows), 'text/csv', filenameWithDate('reservation_logs', 'csv'));
        }

        function exportSupply(format){
            const table = document.getElementById('supply-logs-table');
            if (!table) return alert('No supply table to export.');
            const { headers, rows } = tableToArrays(table);
            download(toCSV(headers, rows), 'text/csv', filenameWithDate('supply_logs', 'csv'));
        }

        function activeSectionId(){
            const sections = Array.from(document.querySelectorAll('.content-section'));
            // Prefer displayed section
            const shown = sections.find(s => s.style.display !== 'none');
            if (shown) return shown.id;
            const active = sections.find(s => s.classList.contains('active'));
            return active ? active.id : '';
        }

        function exportActive(){
            const id = activeSectionId();
            if (id === 'reports-sales-history') return exportSales();
            if (id === 'reports-reservation-logs') return exportReservations();
            if (id === 'reports-supply-logs') return exportSupply();
            alert('No report section selected to export.');
        }

        // ===== New Export Modal System =====
        
        // Updated filename function for the new format
        function generateExportFilename(ext) {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const dateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            return `ShoeVault_sales_record_${dateStr}.${ext}`;
        }

        // Load users for the processed by filter
        async function loadUsersForExport() {
            try {
                const response = await fetch('/owner/api/users-with-transactions', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                const usersList = document.getElementById('users-list');
                
                if (data && data.users && data.users.length > 0) {
                    usersList.innerHTML = data.users.map(user => `
                        <label style="display:flex;align-items:center;gap:8px;padding:6px;cursor:pointer;">
                            <input type="checkbox" value="${user.id}" class="user-checkbox" style="width:16px;height:16px;">
                            <span style="color:#0f172a;">${user.name} (${user.role || 'User'})</span>
                        </label>
                    `).join('');
                } else {
                    usersList.innerHTML = '<p style="color:#6b7280;text-align:center;padding:16px;">No users with transactions found</p>';
                }
            } catch (error) {
                console.error('Error loading users:', error);
                document.getElementById('users-list').innerHTML = '<p style="color:#ef4444;text-align:center;padding:16px;">Failed to load users</p>';
            }
        }

        // Export functionality with filters (CSV only)
        async function exportWithFilters() {
            console.log('Exporting filtered data to CSV');
            
            const exportAll = document.getElementById('export-all').checked;
            const startDate = document.getElementById('export-start-date').value;
            const endDate = document.getElementById('export-end-date').value;
            const saleType = document.querySelector('input[name="sale-type"]:checked').value;
            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            
            console.log('Export parameters set, processing request...');

            // Validate date range if not exporting all
            if (!exportAll && (!startDate || !endDate)) {
                alert('Please select both start and end dates, or check "Export All"');
                return;
            }

            if (!exportAll && new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be later than end date');
                return;
            }

            try {
                // Build the export URL
                const url = new URL('/owner/api/export-sales', window.location.origin);
                url.searchParams.set('format', 'csv');
                url.searchParams.set('export_all', exportAll ? '1' : '0');
                
                if (!exportAll) {
                    url.searchParams.set('start_date', startDate);
                    url.searchParams.set('end_date', endDate);
                }
                
                if (saleType !== 'both') {
                    url.searchParams.set('sale_type', saleType);
                }
                
                if (selectedUsers.length > 0) {
                    url.searchParams.set('users', selectedUsers.join(','));
                }

                // Show loading state
                const exportBtn = document.getElementById('export-csv');
                const originalText = exportBtn.innerHTML;
                exportBtn.innerHTML = '<div class="loader" style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top:2px solid #fff;border-radius:50%;animation:spin 1s linear infinite;"></div> Exporting...';
                exportBtn.disabled = true;

                try {
                    // Fetch the data
                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) {
                        throw new Error(`Export failed: ${response.statusText}`);
                    }

                    const data = await response.json();
                    
                    console.log('Export API response received');
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Export failed');
                    }

                    if (!data.transactions || data.transactions.length === 0) {
                        alert('No transactions found for the selected criteria.');
                        return;
                    }

                    console.log('Processing transactions for export...');

                    // Generate the file content matching the exact table structure
                    const headers = ['Transaction ID', 'Sale Type', 'Processed By', 'Products', 'Subtotal', 'Discount', 'Total', 'Amount Paid', 'Change', 'Date & Time'];
                    
                    // Format money values like the frontend does
                    const fmt = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
                    const money = (v) => {
                        const n = Number(v ?? 0);
                        try { return fmt.format(n); } catch { return `₱${n.toFixed(2)}`; }
                    };
                    const formatDateTime = (value) => {
                        if (!value) return 'N/A';
                        const d = new Date(value);
                        const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                        const time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        return `${date} ${time}`;
                    };
                    
                    const rows = data.transactions.map(t => [
                        t.transaction_id || '',                         // Transaction ID
                        (t.sale_type || '').toString().toUpperCase(),   // Sale Type  
                        t.cashier_name || '',                           // Processed By
                        t.products || '',                               // Products
                        money(t.subtotal),                              // Subtotal
                        money(t.discount_amount),                       // Discount
                        money(t.total_amount),                          // Total
                        money(t.amount_paid),                           // Amount Paid
                        money(t.change_given),                          // Change
                        formatDateTime(t.sale_datetime)                 // Date & Time
                    ]);

                    // Download the CSV file
                    const filename = generateExportFilename('csv');
                    download(toCSV(headers, rows), 'text/csv', filename);

                    // Close modal and show success
                    document.getElementById('export-sales-modal').style.display = 'none';
                    
                    // Show success message (you can customize this)
                    setTimeout(() => {
                        alert(`Export completed successfully! File: ${filename}`);
                    }, 100);

                } catch (error) {
                    console.error('Export error:', error);
                    alert('Export failed: ' + error.message);
                } finally {
                    // Reset button state
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                }

            } catch (outerError) {
                console.error('Export setup error:', outerError);
                alert('Export setup failed: ' + outerError.message);
            }
        }

        // Modal controls
        const exportModal = document.getElementById('export-sales-modal');
        const openModalBtn = document.getElementById('open-export-modal');
        const closeModalBtn = document.getElementById('close-export-modal');
        const exportAllCheckbox = document.getElementById('export-all');
        const dateRangeFilter = document.getElementById('date-range-filter');
        const saleTypeFilter = document.getElementById('sale-type-filter');
        const processedByFilter = document.getElementById('processed-by-filter');

        // Open modal
        openModalBtn?.addEventListener('click', () => {
            console.log('Opening export modal');
            exportModal.style.display = 'flex';
            loadUsersForExport();
            
            // Set default dates (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            document.getElementById('export-end-date').value = today.toISOString().split('T')[0];
            document.getElementById('export-start-date').value = thirtyDaysAgo.toISOString().split('T')[0];
            
            // Trigger the export-all checkbox change event to apply the initial state
            const exportAllEvent = new Event('change');
            exportAllCheckbox.dispatchEvent(exportAllEvent);
        });

        // Close modal
        closeModalBtn?.addEventListener('click', () => {
            exportModal.style.display = 'none';
        });

        // Close modal when clicking outside
        exportModal?.addEventListener('click', (e) => {
            if (e.target === exportModal) {
                exportModal.style.display = 'none';
            }
        });

        // Handle export all checkbox
        exportAllCheckbox?.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            console.log('Export all checkbox changed:', isChecked);
            
            dateRangeFilter.style.opacity = isChecked ? '0.5' : '1';
            saleTypeFilter.style.opacity = isChecked ? '0.5' : '1';
            processedByFilter.style.opacity = isChecked ? '0.5' : '1';
            
            // Disable/enable form elements
            document.getElementById('export-start-date').disabled = isChecked;
            document.getElementById('export-end-date').disabled = isChecked;
            document.querySelectorAll('input[name="sale-type"]').forEach(radio => radio.disabled = isChecked);
            document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.disabled = isChecked);
        });

        // Export button handler
        document.getElementById('export-csv')?.addEventListener('click', () => exportWithFilters());

        // ===== Reservation Export Modal System =====
        
        const reservationExportModal = document.getElementById('reservation-export-modal-overlay');
        const openReservationModalBtn = document.getElementById('open-reservation-export-modal');
        const closeReservationModalBtn = document.getElementById('close-reservation-export-modal');
        const reservationExportAllCheckbox = document.getElementById('reservation-export-all');
        const reservationDateRangeFilter = document.getElementById('reservation-date-range-filter');
        const reservationStatusFilter = document.getElementById('reservation-status-filter');
        
        // Generate filename for reservation export
        function generateReservationExportFilename(ext) {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const dateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            return `ShoeVault_reservation_record_${dateStr}.${ext}`;
        }



        // Export reservations with filters
        async function exportReservationsWithFilters() {
            const isExportAll = document.getElementById('reservation-export-all')?.checked;

            if (!isExportAll) {
                // Validate date range if not exporting all
                const startDate = document.getElementById('reservation-export-start-date')?.value;
                const endDate = document.getElementById('reservation-export-end-date')?.value;
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates.');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date cannot be later than end date.');
                    return;
                }
            }

            // Prepare API URL and parameters
            const url = new URL('/owner/api/export-reservations', window.location.origin);
            
            if (isExportAll) {
                url.searchParams.set('export_all', 'true');
            } else {
                // Date range
                const startDate = document.getElementById('reservation-export-start-date')?.value;
                const endDate = document.getElementById('reservation-export-end-date')?.value;
                if (startDate) url.searchParams.set('start_date', startDate);
                if (endDate) url.searchParams.set('end_date', endDate);
                
                // Status filter
                const selectedStatus = document.querySelector('input[name="reservation-status"]:checked')?.value;
                if (selectedStatus && selectedStatus !== 'all') {
                    url.searchParams.set('status', selectedStatus);
                }
            }

            // Show loading state
            const exportBtn = document.getElementById('reservation-export-csv');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<div class="loader" style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top:2px solid #fff;border-radius:50%;animation:spin 1s linear infinite;"></div> Exporting...';
            exportBtn.disabled = true;

            try {
                // Fetch the data
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error(`Export failed: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Export failed');
                }

                if (!data.reservations || data.reservations.length === 0) {
                    alert('No reservations found for the selected criteria.');
                    return;
                }

                console.log('Processing reservations for export...');

                // Generate the file content matching the table structure
                const headers = ['Reservation ID', 'Reservation Date', 'Customer Name', 'Pickup Date', 'Email', 'Phone', 'Status'];
                
                const formatDateTime = (value) => {
                    if (!value) return 'N/A';
                    const d = new Date(value);
                    const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    return `${date} ${time}`;
                };
                
                const formatDate = (value) => {
                    if (!value) return 'N/A';
                    const d = new Date(value);
                    return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                };
                
                const rows = data.reservations.map(reservation => [
                    reservation.reservation_id || 'N/A',
                    formatDateTime(reservation.reservation_date || reservation.created_at),
                    reservation.customer_name || 'N/A',
                    formatDate(reservation.pickup_date),
                    reservation.customer_email || 'N/A',
                    reservation.customer_phone || 'N/A',
                    (reservation.status || '').charAt(0).toUpperCase() + (reservation.status || '').slice(1)
                ]);

                // Generate and download CSV
                const csvContent = toCSV(headers, rows);
                const filename = generateReservationExportFilename('csv');
                
                download(csvContent, 'text/csv', filename);
                
                // Close modal
                reservationExportModal.style.display = 'none';
                
                console.log('Reservation export completed successfully');

            } catch (error) {
                console.error('Export error:', error);
                alert('Export failed: ' + error.message);
            } finally {
                // Reset button state
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            }
        }

        // Open reservation export modal
        openReservationModalBtn?.addEventListener('click', () => {
            console.log('Opening reservation export modal');
            reservationExportModal.style.display = 'flex';
            
            // Set default dates (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            document.getElementById('reservation-export-end-date').value = today.toISOString().split('T')[0];
            document.getElementById('reservation-export-start-date').value = thirtyDaysAgo.toISOString().split('T')[0];
            
            // Trigger the export-all checkbox change event to apply the initial state
            const exportAllEvent = new Event('change');
            reservationExportAllCheckbox.dispatchEvent(exportAllEvent);
        });

        // Close reservation export modal
        closeReservationModalBtn?.addEventListener('click', () => {
            reservationExportModal.style.display = 'none';
        });

        // Close modal when clicking outside
        reservationExportModal?.addEventListener('click', (e) => {
            if (e.target === reservationExportModal) {
                reservationExportModal.style.display = 'none';
            }
        });

        // Handle reservation export all checkbox
        reservationExportAllCheckbox?.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            console.log('Reservation export all checkbox changed:', isChecked);
            
            reservationDateRangeFilter.style.opacity = isChecked ? '0.5' : '1';
            reservationStatusFilter.style.opacity = isChecked ? '0.5' : '1';
            
            // Disable/enable form elements
            document.getElementById('reservation-export-start-date').disabled = isChecked;
            document.getElementById('reservation-export-end-date').disabled = isChecked;
            document.querySelectorAll('input[name="reservation-status"]').forEach(radio => radio.disabled = isChecked);
        });

        // Reservation export button handler
        document.getElementById('reservation-export-csv')?.addEventListener('click', () => exportReservationsWithFilters());

        // ===== Supply Export Modal System =====
        
        const supplyExportModal = document.getElementById('supply-export-modal-overlay');
        const openSupplyModalBtn = document.getElementById('open-supply-export-modal');
        const closeSupplyModalBtn = document.getElementById('close-supply-export-modal');
        const supplyExportAllCheckbox = document.getElementById('supply-export-all');
        const supplyDateRangeFilter = document.getElementById('supply-date-range-filter');
        const supplySupplierFilter = document.getElementById('supply-supplier-filter');
        const supplyBrandFilter = document.getElementById('supply-brand-filter');
        
        // Generate filename for supply export
        function generateSupplyExportFilename(ext) {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const dateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            return `ShoeVault_supply_record_${dateStr}.${ext}`;
        }

        // Load suppliers and brands for the filters
        async function loadSupplyFiltersData() {
            try {
                const response = await fetch('/owner/api/supply-filters', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                
                // Load suppliers
                const suppliersList = document.getElementById('suppliers-list');
                if (data && data.suppliers && data.suppliers.length > 0) {
                    suppliersList.innerHTML = data.suppliers.map(supplier => `
                        <label style="display:flex;align-items:center;gap:8px;padding:6px;cursor:pointer;">
                            <input type="checkbox" value="${supplier.name}" class="supplier-checkbox" style="width:16px;height:16px;">
                            <span style="color:#0f172a;">${supplier.name}</span>
                        </label>
                    `).join('');
                } else {
                    suppliersList.innerHTML = '<div style="text-align:center;color:#6b7280;padding:12px;">No suppliers found</div>';
                }
                
                // Load brands
                const brandsList = document.getElementById('brands-list');
                if (data && data.brands && data.brands.length > 0) {
                    brandsList.innerHTML = data.brands.map(brand => `
                        <label style="display:flex;align-items:center;gap:8px;padding:6px;cursor:pointer;">
                            <input type="checkbox" value="${brand.name}" class="brand-checkbox" style="width:16px;height:16px;">
                            <span style="color:#0f172a;">${brand.name}</span>
                        </label>
                    `).join('');
                } else {
                    brandsList.innerHTML = '<div style="text-align:center;color:#6b7280;padding:12px;">No brands found</div>';
                }
            
                // Handle select all suppliers checkbox
                const selectAllSuppliers = document.getElementById('select-all-suppliers');
                const supplierCheckboxes = document.querySelectorAll('.supplier-checkbox');
                
                selectAllSuppliers?.addEventListener('change', (e) => {
                    supplierCheckboxes.forEach(cb => cb.checked = e.target.checked);
                });
                
                supplierCheckboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        const allChecked = Array.from(supplierCheckboxes).every(checkbox => checkbox.checked);
                        const noneChecked = Array.from(supplierCheckboxes).every(checkbox => !checkbox.checked);
                        
                        if (allChecked) {
                            selectAllSuppliers.checked = true;
                            selectAllSuppliers.indeterminate = false;
                        } else if (noneChecked) {
                            selectAllSuppliers.checked = false;
                            selectAllSuppliers.indeterminate = false;
                        } else {
                            selectAllSuppliers.checked = false;
                            selectAllSuppliers.indeterminate = true;
                        }
                    });
                });
                
                // Handle select all brands checkbox
                const selectAllBrands = document.getElementById('select-all-brands');
                const brandCheckboxes = document.querySelectorAll('.brand-checkbox');
                
                selectAllBrands?.addEventListener('change', (e) => {
                    brandCheckboxes.forEach(cb => cb.checked = e.target.checked);
                });
                
                brandCheckboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        const allChecked = Array.from(brandCheckboxes).every(checkbox => checkbox.checked);
                        const noneChecked = Array.from(brandCheckboxes).every(checkbox => !checkbox.checked);
                        
                        if (allChecked) {
                            selectAllBrands.checked = true;
                            selectAllBrands.indeterminate = false;
                        } else if (noneChecked) {
                            selectAllBrands.checked = false;
                            selectAllBrands.indeterminate = false;
                        } else {
                            selectAllBrands.checked = false;
                            selectAllBrands.indeterminate = true;
                        }
                    });
                });
                
            } catch (error) {
                console.error('Failed to load supply filter data:', error);
                document.getElementById('suppliers-list').innerHTML = '<div style="text-align:center;color:#ef4444;padding:12px;">Failed to load suppliers</div>';
                document.getElementById('brands-list').innerHTML = '<div style="text-align:center;color:#ef4444;padding:12px;">Failed to load brands</div>';
            }
        }

        // Export supply logs with filters
        async function exportSupplyLogsWithFilters() {
            const isExportAll = document.getElementById('supply-export-all')?.checked;

            if (!isExportAll) {
                // Validate date range if not exporting all
                const startDate = document.getElementById('supply-export-start-date')?.value;
                const endDate = document.getElementById('supply-export-end-date')?.value;
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates.');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date cannot be later than end date.');
                    return;
                }
            }

            // Prepare API URL and parameters
            const url = new URL('/owner/api/export-supply', window.location.origin);
            
            if (isExportAll) {
                url.searchParams.set('export_all', 'true');
            } else {
                // Date range
                const startDate = document.getElementById('supply-export-start-date')?.value;
                const endDate = document.getElementById('supply-export-end-date')?.value;
                if (startDate) url.searchParams.set('start_date', startDate);
                if (endDate) url.searchParams.set('end_date', endDate);
                
                // Supplier filter
                const selectAllSuppliers = document.getElementById('select-all-suppliers').checked;
                if (!selectAllSuppliers) {
                    const selectedSuppliers = Array.from(document.querySelectorAll('.supplier-checkbox:checked')).map(cb => cb.value);
                    if (selectedSuppliers.length > 0) {
                        url.searchParams.set('suppliers', selectedSuppliers.join(','));
                    }
                }
                
                // Brand filter
                const selectAllBrands = document.getElementById('select-all-brands').checked;
                if (!selectAllBrands) {
                    const selectedBrands = Array.from(document.querySelectorAll('.brand-checkbox:checked')).map(cb => cb.value);
                    if (selectedBrands.length > 0) {
                        url.searchParams.set('brands', selectedBrands.join(','));
                    }
                }
            }

            // Show loading state
            const exportBtn = document.getElementById('supply-export-csv');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<div class="loader" style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top:2px solid #fff;border-radius:50%;animation:spin 1s linear infinite;"></div> Exporting...';
            exportBtn.disabled = true;

            try {
                // Fetch the data
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error(`Export failed: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Export failed');
                }

                if (!data.supply_logs || data.supply_logs.length === 0) {
                    alert('No supply logs found for the selected criteria.');
                    return;
                }

                console.log('Processing supply logs for export...');

                // Generate the file content matching the table structure
                const headers = ['Log ID', 'Supplier', 'Country', 'Brand', 'Size', 'Quantity', 'Received At'];
                
                const formatDateTime = (value) => {
                    if (!value) return 'N/A';
                    const d = new Date(value);
                    const date = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    return `${date} ${time}`;
                };
                
                const rows = data.supply_logs.map(log => [
                    log.id || 'N/A',
                    log.supplier_name || 'N/A',
                    log.country || 'N/A',
                    log.brand || 'N/A',
                    log.size || 'N/A',
                    log.quantity || '0',
                    formatDateTime(log.received_at)
                ]);

                // Generate and download CSV
                const csvContent = toCSV(headers, rows);
                const filename = generateSupplyExportFilename('csv');
                
                download(csvContent, 'text/csv', filename);
                
                // Close modal
                supplyExportModal.style.display = 'none';
                
                console.log('Supply export completed successfully');

            } catch (error) {
                console.error('Export error:', error);
                alert('Export failed: ' + error.message);
            } finally {
                // Reset button state
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            }
        }

        // Open supply export modal
        openSupplyModalBtn?.addEventListener('click', () => {
            console.log('Opening supply export modal');
            supplyExportModal.style.display = 'flex';
            loadSupplyFiltersData();
            
            // Set default dates (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            document.getElementById('supply-export-end-date').value = today.toISOString().split('T')[0];
            document.getElementById('supply-export-start-date').value = thirtyDaysAgo.toISOString().split('T')[0];
            
            // Trigger the export-all checkbox change event to apply the initial state
            const exportAllEvent = new Event('change');
            supplyExportAllCheckbox.dispatchEvent(exportAllEvent);
        });

        // Close supply export modal
        closeSupplyModalBtn?.addEventListener('click', () => {
            supplyExportModal.style.display = 'none';
        });

        // Close modal when clicking outside
        supplyExportModal?.addEventListener('click', (e) => {
            if (e.target === supplyExportModal) {
                supplyExportModal.style.display = 'none';
            }
        });

        // Handle supply export all checkbox
        supplyExportAllCheckbox?.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            console.log('Supply export all checkbox changed:', isChecked);
            
            supplyDateRangeFilter.style.opacity = isChecked ? '0.5' : '1';
            supplySupplierFilter.style.opacity = isChecked ? '0.5' : '1';
            supplyBrandFilter.style.opacity = isChecked ? '0.5' : '1';
            
            // Disable/enable form elements
            document.getElementById('supply-export-start-date').disabled = isChecked;
            document.getElementById('supply-export-end-date').disabled = isChecked;
            document.querySelectorAll('.supplier-checkbox').forEach(checkbox => checkbox.disabled = isChecked);
            document.querySelectorAll('.brand-checkbox').forEach(checkbox => checkbox.disabled = isChecked);
            document.getElementById('select-all-suppliers').disabled = isChecked;
            document.getElementById('select-all-brands').disabled = isChecked;
        });

        // Supply export button handler
        document.getElementById('supply-export-csv')?.addEventListener('click', () => exportSupplyLogsWithFilters());

        // Keep old export buttons for other sections (inventory)
        // Exclude the new export buttons (#export-csv, #open-reservation-export-modal, #open-supply-export-modal)
        document.querySelectorAll('.export-btn:not(#open-export-modal):not(#export-csv):not(#open-reservation-export-modal):not(#open-supply-export-modal)').forEach(btn => {
            btn.addEventListener('click', () => {
                exportActive();
            });
        });
    });
</script>
<script src="{{ asset('js/notifications.js') }}"></script>
<script>
// Initialize notifications for reports page
function initNotifications() {
    if (window.notificationManager) {
        console.log('notificationManager found, initializing...');
        try {
            window.notificationManager.init('{{ auth()->user()->role ?? "owner" }}');
            return true;
        } catch (e) {
            console.warn('notificationManager init failed:', e);
        }
    }
    
    // Fallback notification toggle
    console.log('Using fallback notification system');
    document.querySelectorAll('.notification-wrapper').forEach(wrapper => {
        const bell = wrapper.querySelector('.notification-bell');
        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                wrapper.classList.toggle('open');
            });
        }
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.notification-wrapper.open').forEach(w => w.classList.remove('open'));
    });
    return false;
}

// Try to initialize notifications with retries
document.addEventListener('DOMContentLoaded', function() {
    let attempts = 0;
    const maxAttempts = 10;
    const retryDelay = 100;
    
    function tryInit() {
        attempts++;
        if (initNotifications()) {
            console.log('Notifications initialized successfully');
            return;
        }
        
        if (attempts < maxAttempts) {
            setTimeout(tryInit, retryDelay);
        } else {
            console.log('Max attempts reached, using fallback');
        }
    }
    
    tryInit();
});
</script>
@include('partials.mobile-blocker')

</body>
</html>