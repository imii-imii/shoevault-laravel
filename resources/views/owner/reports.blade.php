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
    /* Reservation cards */
    .resv-card-list { display:flex; flex-direction:column; gap:12px; margin-top:12px; }
    .resv-card { background:#fff; border-radius:14px; padding:16px 18px; border:1px solid #eef2f7; box-shadow: 0 8px 24px -18px rgba(2,6,23,.18); }
    .resv-grid { display:grid; grid-template-columns: repeat(5, 1fr); gap:8px 18px; align-items:center; }
    .resv-field { display:flex; flex-direction:column; gap:2px; }
    .resv-label { font-size:11px; color:#64748b; }
    .resv-value { font-size:13px; font-weight:700; color:#0f172a; }
    .resv-badge { display:inline-block; padding:6px 10px; font-size:11px; border-radius:999px; }
    .resv-badge.completed { background:#dcfce7; color:#166534; }
    .resv-badge.cancelled { background:#fee2e2; color:#991b1b; }
    @media (max-width: 1100px) { .resv-grid { grid-template-columns: repeat(2, 1fr); } }
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
            <a href="{{ route('owner.dashboard') }}" class="nav-link">
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
            </div>
            <div class="table-container">
                <table id="sales-history-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Products</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
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
                <input type="text" id="supply-search" placeholder="Search supply logs..." class="search-input" style="flex:1; min-width:180px;">
                <select id="supply-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date Modified (Newest)</option>
                    <option value="date-asc">Date Modified (Oldest)</option>
                    <option value="id-desc">Log ID (Descending)</option>
                    <option value="id-asc">Log ID (Ascending)</option>
                </select>
            </div>
            <div class="table-container">
                <table id="supply-logs-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Supplier Name</th>
                            <th>Contact</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Country</th>
                            <th>Last Update</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
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
                <select id="inventory-brand-filter" class="filter-select">
                    <option value="">All Brands</option>
                    <option value="Nike">Nike</option>
                    <option value="Adidas">Adidas</option>
                    <option value="Puma">Puma</option>
                    <option value="Converse">Converse</option>
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
            <div class="table-container">
                <table id="inventory-overview-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Colors</th>
                            <th>Sizes</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-overview-tbody"></tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{ asset('js/owner.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>

</body>
</html>