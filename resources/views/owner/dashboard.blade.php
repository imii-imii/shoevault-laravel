<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Owner Dashboard - ShoeVault Batangas</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Owner CSS -->
    <link href="{{ asset('css/owner.css') }}" rel="stylesheet">
    <style>
        .logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
		.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
		.logout-btn i{font-size:1rem}
        .notification-wrapper { position: relative; }
        .notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
        .notification-bell:hover { background:#f3f4f6; color:#1f2937; }
        .notification-count { position: absolute; top: 2px; right: 2px; background: rgb(239, 68, 68); color: rgb(255, 255, 255); border-radius: 999px; padding: 2px 6px; font-size: 11px; display: inline-block; }
        .notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:9999; }
        .notification-wrapper.open .notification-dropdown { display:block; }
        .notification-list { max-height:300px; overflow-y:auto; }
        .notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }

        /* Forecast row layout: chart spans width of ~3 KPI cards, gauge takes remaining ~1 card */
        .odash-row-forecast { display:grid; grid-template-columns: 3fr 1fr; gap:20px; margin-top:20px; }
        .odash-chart-shell { position:relative; height:280px; max-height:280px; overflow:hidden; }
        .odash-gauge-shell { position:relative; height:280px; max-height:280px; display:flex; flex-direction:column; align-items:center; justify-content:center; overflow:hidden; }
        .odash-gauge-legend { display:flex; gap:10px; margin-top:10px; font-size:12px; color:#6b7280; }
        .dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
        
        /* Products and Stock Levels row layout */
        .odash-row-products { display:grid; grid-template-columns: 1fr 3fr; gap:20px; margin-top:20px; }
        .odash-products-list { max-height: 360px; overflow-y: auto; padding-right: 4px; }
        .odash-products-list::-webkit-scrollbar { width: 6px; }
        .odash-products-list::-webkit-scrollbar-track { background: #dbeafe; border-radius: 3px; }
        .odash-products-list::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 3px; }
        .odash-products-list::-webkit-scrollbar-thumb:hover { background: #2563eb; }
        .odash-product-item { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; margin-bottom:8px; background:#f0f9ff; border-radius:8px; transition: all .2s ease; border:1px solid #e0f2fe; }
        .odash-product-item:hover { background:#e0f2fe; transform: translateX(2px); }
        .odash-product-item.top-product { background:linear-gradient(135deg, #dbeafe, #bfdbfe); border-left:4px solid #3b82f6; font-weight:600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1); }
        .odash-product-name { font-size:13px; color:#1e3a8a; flex:1; }
        .odash-product-sales { font-size:12px; color:#60a5fa; font-weight:600; }
        .odash-product-rank { display:inline-block; width:20px; height:20px; line-height:20px; text-align:center; background:linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; border-radius:50%; font-size:10px; font-weight:700; margin-right:8px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3); }
    /* Compact, trendy forecast controls */
    .odash-btn { padding:14px; width:28px; height:28px; border-radius:9999px; border:1px solid #e5e7eb; background:#ffffff; color:#1e3a8a; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s ease; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .odash-btn:hover { background:linear-gradient(135deg,#eef2ff,#dbeafe); color:#0f172a; border-color:#bfdbfe; box-shadow:0 2px 6px rgba(59,130,246,.15); transform: translateY(-1px); }
    .odash-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
    /* Removed internal forecast window pill (#odash-forecast-window) in favor of global filter bar (#dbf-window-text) */

        /* Trendy global filter bar */
        .odash-filter-bar { 
            display:flex; flex-wrap:wrap; gap:10px; align-items:center;
            background: linear-gradient(180deg, rgba(255,255,255,0.85), rgba(255,255,255,0.7));
            border:1px solid rgba(59,130,246,0.18);
            border-radius:16px; padding:10px 12px;
            box-shadow: 0 10px 30px -12px rgba(2,6,23,.25), inset 0 1px 0 rgba(255,255,255,.6);
            backdrop-filter: blur(6px);
        }
        .odash-chip {
            padding:6px 12px; border-radius:9999px; background:#eef2ff; color:#1e3a8a; font-weight:700; border:1px solid #dbeafe; font-size:12px;
        }
        .odash-select, .odash-input { 
            padding:6px 10px; border-radius:10px; border:1px solid #e5e7eb; background:#fff; color:#0f172a; font-size:12px; outline:none;
        }
        .odash-select:focus, .odash-input:focus { border-color:#93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .odash-toggle { display:inline-flex; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
        .odash-toggle button { padding:6px 10px; border:none; background:transparent; cursor:pointer; font-size:12px; color:#1e3a8a; font-weight:700; }
        .odash-toggle button.active { background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#0f172a; }
        .odash-toggle button + button { border-left:1px solid #e5e7eb; }

        /* Futuristic gauge styles (refined for semicircular segmented look) */
        .f-gauge { position: relative; display:flex; align-items:center; justify-content:center; }
        .f-gauge svg { overflow: visible; }
        .f-gauge .g-glow { filter: drop-shadow(0 8px 18px rgba(14, 165, 233, 0.08)); }
        .f-gauge .g-segment { transition: stroke-opacity .25s ease, transform .3s ease; }
        .f-gauge .g-center { transition: transform .25s ease; }

    /* Opening animations for cards */
        .reveal { opacity: 0; transform: translateY(12px) scale(.98); }
        .reveal.in { opacity: 1; transform: translateY(0) scale(1); transition: opacity .6s ease, transform .6s cubic-bezier(.2,.7,.2,1); }
    /* Loading + animations */
    @keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
    .page-loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.85); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 20000; }
    .loader { width: 48px; height: 48px; border-radius: 50%; border: 3px solid #bfdbfe; border-top-color: #3b82f6; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .skeleton { position: relative; overflow: hidden; background: #eef2f7; border-radius: 10px; }
    .skeleton::after { content: ""; position: absolute; inset: 0; background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.6) 40%, rgba(255,255,255,0) 80%); background-size: 1000px 100%; animation: shimmer 1.2s infinite; opacity: .7; }
    .skeleton.line { height: 12px; margin: 6px 0; }
    .loading-scrim { position: absolute; inset: 0; background: rgba(255,255,255,.7); display:flex; align-items:center; justify-content:center; z-index:10; }
        
        @media (max-width: 1024px) {
            .odash-row-forecast { grid-template-columns: 1fr; }
            .odash-chart-shell, .odash-gauge-shell { height:260px; max-height:260px; }
            .odash-row-products { grid-template-columns: 1fr; }
        }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Anime.js for richer motion -->
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
</head>
<body>
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text">
            <h2>ShoeVault Batangas</h2>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item active" data-section="inventory-dashboard">
            <a href="#" class="nav-link" onclick="showSection('inventory-dashboard'); return false;">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item" data-section="reports">
            <a href="{{ route('owner.reports') }}" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="nav-item" data-section="settings">
            <a href="{{ route('owner.settings') }}" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Master Controls</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <img src="{{ asset('images/profile.png') }}" alt="Owner">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name ?? 'Owner' }}</h4>
            </div>
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

<!-- Main Content Area -->
<main class="main-content">
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <h1 class="main-title" id="page-title">Dashboard</h1>
        </div>
        <div class="header-right">
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span id="current-time">Loading...</span>
            </div>
            <div class="date-display">
                <i class="fas fa-calendar"></i>
                <span id="current-date">Loading...</span>
            </div>
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

    <!-- Content Grid -->
    <div class="content-grid">
        

        <!-- Dashboard Section -->
        <section id="inventory-dashboard" class="content-section active">
            <div class="odash-wrap">
                <!-- Top 4 KPI Cards -->
                <div class="odash-row-top">
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon"><i class="fas fa-peso-sign"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-revenue">₱0.00</div>
                                <div class="odash-kpi-label">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#2563eb,#60a5fa)"><i class="fas fa-cube"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-sold">0</div>
                                <div class="odash-kpi-label">Total Products Sold</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#16a34a,#34d399)"><i class="fas fa-check-circle"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-resv-completed">0</div>
                                <div class="odash-kpi-label">Completed Reservations</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#ef4444,#f97316)"><i class="fas fa-ban"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-resv-cancelled">0</div>
                                <div class="odash-kpi-label">Cancelled Reservations</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Dashboard Filter Bar (below KPI cards) -->
                <div class="odash-filter-row" style="margin-top:12px;">
                    <div class="odash-filter-bar">
                        <label for="dbf-range" style="font-size:12px;color:#64748b;font-weight:700;">Range</label>
                        <select id="dbf-range" class="odash-select" style="min-width:130px;">
                            <option value="day">Day</option>
                            <option value="weekly">Week</option>
                            <option value="monthly">Month</option>
                        </select>
                        <!-- Manual pickers (toggle visibility based on Range) -->
                        <div class="dbf-pickers" style="display:inline-flex; gap:8px; align-items:center;">
                            <input id="dbf-date" type="date" class="odash-input" style="display:none;" />
                            <input id="dbf-week" type="week" class="odash-input" style="display:none;" />
                            <input id="dbf-month" type="month" class="odash-input" style="display:none;" />
                        </div>
                        <div class="odash-filter-controls" style="display:flex;align-items:center;gap:8px;margin-left:12px;margin-right:20px;flex:1;justify-content:center;">
                            <button id="dbf-prev" class="odash-btn" type="button" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                            <div id="dbf-window-text" class="odash-chip" style="min-width:95%; text-align:center; ">Today</div>
                            <button id="dbf-next" class="odash-btn" type="button" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Sales Forecast and Reservation Gauge -->
                <div class="odash-row-forecast">
                    <!-- Sales Forecast (Mock) -->
                    <div class="odash-card odash-line-card">
                        <div class="odash-card-header" style="align-items:center; justify-content:space-between;">
                            <div class="odash-title">Forecast</div>
                            <div style="display:flex; gap:12px; align-items:center; color:#64748b; flex-wrap:wrap;">
                                <div id="odash-forecast-legend" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;"></div>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <select id="odash-forecast-type" class="odash-select">
                                        <option value="sales" selected>Sales</option>
                                        <option value="demand">Demand</option>
                                    </select>
                                    <!-- Removed internal range select (#odash-forecast-range); global #dbf-range now controls forecast -->
                                </div>
                                <!-- Removed internal prev/window/next controls (#odash-forecast-prev/#odash-forecast-window/#odash-forecast-next); global filter bar provides navigation -->
                            </div>
                        </div>
                        <div class="odash-chart-shell">
                            <canvas id="odash-forecast"></canvas>
                        </div>
                    </div>

                    <!-- Reservation Gauge (Futuristic) -->
                    <div class="odash-card">
                        <div class="odash-card-header">
                            <div class="odash-title">Reservations Status</div>
                        </div>
                        <div class="odash-gauge-shell" style="position:relative;">
                            <div id="odash-resv-gauge" class="f-gauge" style="width:260px; height:180px;"></div>
                            <div id="odash-gauge-center" style="position:absolute; top:40%; left:50%; transform:translate(-50%, -50%); text-align:center; pointer-events:none; margin-top:-20px;">
                                <div style="color:#64748b; font-size:13px; font-weight:500; margin-bottom:4px;">Total</div>
                                <div style="color:#1e3a8a; font-size:28px; font-weight:700; line-height:1;" id="odash-resv-total">50</div>
                            </div>
                            <div class="odash-gauge-legend" style="margin-top:5px; justify-content:center; flex-wrap:wrap;">
                                <div style="text-align:center; margin:0 8px;">
                                    <div style="display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                        <span class="dot" style="background:#10b981"></span><span style="font-size:12px;">Completed</span>
                                    </div>
                                    <div style="color:#1e3a8a; font-size:16px; font-weight:700;" id="odash-resv-completed-pct">60%</div>
                                </div>
                                <div style="text-align:center; margin:0 8px;">
                                    <div style="display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                        <span class="dot" style="background:#ef4444; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);"></span><span style="font-size:12px; color:#991b1b; font-weight:500;">Cancelled</span>
                                    </div>
                                    <div style="color:#1e3a8a; font-size:16px; font-weight:700;" id="odash-resv-cancelled-pct">20%</div>
                                </div>
                                <div style="text-align:center; margin:0 8px;">
                                    <div style="display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                        <span class="dot" style="background:#f59e0b; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);"></span><span style="font-size:12px; color:#92400e; font-weight:500;">Pending</span>
                                    </div>
                                    <div style="color:#1e3a8a; font-size:16px; font-weight:700;" id="odash-resv-pending-pct">20%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Products and Stock Levels -->
                <div class="odash-row-products">
                    <!-- Popular Products List (Scrollable) -->
                    <div class="odash-card">
                        <div class="odash-card-header" style="justify-content:space-between; align-items:center;">
                            <div class="odash-title">Popular Products</div>
                            <!-- Removed popular products month/year selectors (#odash-popular-month, #odash-popular-year) -->
                        </div>
                        <div class="odash-products-list" id="odash-popular-products">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Stock Levels Bar Chart -->
                    <div class="odash-card">
                        <div class="odash-card-header" style="justify-content:space-between; align-items:center; gap:10px;">
                            <div style="display:flex; gap:10px; align-items:center;">
                                <div class="odash-title">Stock Levels</div>
                                <div class="odash-toggle" id="odash-stock-mode" role="group" aria-label="Stock Source">
                                    <button type="button" data-mode="pos" class="active">POS</button>
                                    <button type="button" data-mode="reservation">Reservation</button>
                                </div>
                            </div>
                            <select id="odash-stock-category" class="odash-select">
                                <option value="men" selected>Men</option>
                                <option value="women">Women</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="odash-chart-shell" style="height:360px; max-height:360px;">
                            <canvas id="odash-stock-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Add User Modal -->
<div id="add-user-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-add-user-modal">&times;</span>
        <h3>Add New User</h3>
        <form id="add-user-form">
            @csrf
            <input type="text" id="add-user-fullname" placeholder="Full Name" required>
            <input type="text" id="add-user-username" placeholder="Username" required>
            <input type="email" id="add-user-email" placeholder="Email" required>
            <select id="add-user-role" required>
                <option value="">Select Role</option>
                <option value="manager">Manager</option>
                <option value="cashier">Cashier</option>
                <option value="owner">Owner</option>
            </select>
            <input type="password" id="add-user-password" placeholder="Password" required>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Include Laravel's CSRF token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="{{ asset('js/notifications.js') }}"></script>
<script src="{{ asset('js/owner.js') }}"></script>
<script>
// Pass Laravel data to JavaScript
window.laravelData = {
    dashboardData: @json($dashboardData ?? []),
    user: @json(auth()->user()),
    routes: {
        salesHistory: '{{ route("owner.sales-history") }}',
        reservationLogs: '{{ route("owner.reservation-logs") }}',
        supplyLogs: '{{ route("owner.supply-logs") }}',
        inventoryOverview: '{{ route("owner.inventory-overview") }}',
        popularProducts: '{{ route("owner.popular-products") }}',
        settings: '{{ route("owner.settings") }}'
    }
};

// Initialize dashboard when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Section-scoped loader helpers (create a loader inside the active .content-section)
    function getActiveSection() {
        return document.querySelector('.content-section.active') || document.querySelector('.content-section');
    }
    function ensureSectionPosition(sec) {
        if (!sec) return;
        const pos = window.getComputedStyle(sec).position;
        if (!pos || pos === 'static') sec.style.position = 'relative';
    }
    function showSectionLoader() {
        const sec = getActiveSection();
        if (!sec) return;
        ensureSectionPosition(sec);
        if (sec.querySelector('.section-loader')) return;
        const loader = document.createElement('div');
        loader.className = 'section-loader';
        loader.style.position = 'absolute';
        loader.style.inset = '0';
        loader.style.display = 'flex';
        loader.style.alignItems = 'center';
        loader.style.justifyContent = 'center';
        loader.style.background = 'rgba(255,255,255,0.78)';
        loader.style.backdropFilter = 'blur(2px)';
        loader.style.zIndex = 1200;
        loader.innerHTML = '<div class="loader" aria-label="Loading"></div>';
        sec.appendChild(loader);
    }
    function hideSectionLoader() {
        const sec = getActiveSection();
        if (!sec) return;
        const loader = sec.querySelector('.section-loader');
        if (loader) loader.remove();
    }

    showSectionLoader();
    initializeDashboard();
    updateDateTime();
    setInterval(updateDateTime, 1000); // Update time every second
    
    // Initialize notification system
    if (typeof NotificationManager !== 'undefined') {
        const notificationManager = new NotificationManager();
        notificationManager.init();
        window.notificationManager = notificationManager; // Make it globally accessible
    }

    // Initialize mock charts for forecast and reservations
    initOwnerForecastCharts();
    initPopularProducts();
    initStockLevels();
    initOpeningAnimations();
    // Hide the section loader after initial render (short delay to allow charts to initialize)
    setTimeout(hideSectionLoader, 600);
    // Light KPI count-up if values are present
    setTimeout(()=>{ try { animateKpiCounts(); } catch(e){} }, 700);

    // Global filter bar now directly controls forecast (no internal duplicate controls)
    (function initDynamicFilterPickers(){
        const rangeSelect = document.getElementById('dbf-range');
        const dateInput = document.getElementById('dbf-date');
        const weekInput = document.getElementById('dbf-week');
        const monthInput = document.getElementById('dbf-month');
        const prevBtn = document.getElementById('dbf-prev');
        const nextBtn = document.getElementById('dbf-next');
        const windowPill = document.getElementById('dbf-window-text');
        if (!rangeSelect || !prevBtn || !nextBtn || !windowPill) return;

        // Anchor date represents the current window start reference
        let anchorDate = new Date();

        function setVisibility(range){
            dateInput.style.display = range === 'day' ? '' : 'none';
            weekInput.style.display = range === 'weekly' ? '' : 'none';
            monthInput.style.display = range === 'monthly' ? '' : 'none';
        }

        function normalizeWeekValue(dt){
            // ISO week string YYYY-W##
            const target = new Date(dt.getTime());
            // Monday based week (ISO) -> find Thursday for ISO algorithm
            target.setHours(0,0,0,0);
            // Thursday of current week
            target.setDate(target.getDate() + 3 - (target.getDay() + 6) % 7);
            const week1 = new Date(target.getFullYear(),0,4);
            const week = 1 + Math.round(((target.getTime() - week1.getTime())/86400000 - 3 + (week1.getDay()+6)%7)/7);
            return `${target.getFullYear()}-W${String(week).padStart(2,'0')}`;
        }

        function updateInputsFromAnchor(range){
            if (range === 'day') {
                dateInput.value = anchorDate.toISOString().slice(0,10);
            } else if (range === 'weekly') {
                weekInput.value = normalizeWeekValue(anchorDate);
            } else if (range === 'monthly') {
                monthInput.value = `${anchorDate.getFullYear()}-${String(anchorDate.getMonth()+1).padStart(2,'0')}`;
            }
        }

        function updateWindowText(range){
            const d = new Date(anchorDate.getTime());
            let text = '';
            if (range === 'day') {
                const today = new Date(); today.setHours(0,0,0,0);
                const cmp = d.getTime() - today.getTime();
                if (cmp === 0) text = 'Today';
                else if (cmp === 86400000) text = 'Tomorrow';
                else if (cmp === -86400000) text = 'Yesterday';
                else text = d.toLocaleDateString('en-US',{month:'short', day:'2-digit', year:'numeric'});
            } else if (range === 'weekly') {
                // week start (Sunday) and end (Saturday)
                const wd = d.getDay();
                const start = new Date(d.getTime()); start.setDate(d.getDate()-wd);
                const end = new Date(start.getTime()); end.setDate(start.getDate()+6);
                const today = new Date();
                const sameWeek = (normalizeWeekValue(today) === normalizeWeekValue(d));
                if (sameWeek) text = 'This Week';
                else text = `${start.toLocaleDateString('en-US',{month:'short',day:'2-digit'})} – ${end.toLocaleDateString('en-US',{month:'short',day:'2-digit'})}`;
            } else if (range === 'monthly') {
                const today = new Date();
                const sameMonth = today.getFullYear()===d.getFullYear() && today.getMonth()===d.getMonth();
                if (sameMonth) text = 'This Month';
                else text = d.toLocaleDateString('en-US',{month:'long', year:'numeric'});
            }
            windowPill.textContent = text;
        }

        function shiftAnchor(range, dir){
            if (range === 'day') anchorDate.setDate(anchorDate.getDate() + dir);
            else if (range === 'weekly') anchorDate.setDate(anchorDate.getDate() + dir*7);
            else if (range === 'monthly') anchorDate.setMonth(anchorDate.getMonth() + dir);
            updateInputsFromAnchor(range);
            updateWindowText(range);
            // trigger forecast refresh (rangeSelect change already wired in forecast initializer)
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range, anchorDate }}));
        }

        rangeSelect.addEventListener('change', ()=>{
            const r = rangeSelect.value;
            setVisibility(r);
            // reset anchorDate to today when switching range
            anchorDate = new Date(); anchorDate.setHours(0,0,0,0);
            updateInputsFromAnchor(r);
            updateWindowText(r);
            document.dispatchEvent(new CustomEvent('odash:filter-range-changed',{ detail:{ range:r, anchorDate }}));
        });
        prevBtn.addEventListener('click', ()=> shiftAnchor(rangeSelect.value, -1));
        nextBtn.addEventListener('click', ()=> shiftAnchor(rangeSelect.value, 1));

        // Manual input listeners
        dateInput.addEventListener('change', ()=>{
            if (!dateInput.value) return; anchorDate = new Date(dateInput.value+'T00:00:00'); updateWindowText('day'); document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'day', anchorDate }}));
        });
        weekInput.addEventListener('change', ()=>{
            if (!weekInput.value) return; // format YYYY-W##
            const [y, w] = weekInput.value.split('-W');
            // ISO week -> Thursday of week then back to Monday
            const simple = new Date(Number(y),0,4);
            const dayOfWeek = (simple.getDay()+6)%7; // 0..6 Monday=0
            simple.setDate(simple.getDate() - dayOfWeek + (Number(w)-1)*7);
            anchorDate = simple; updateWindowText('weekly'); document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'weekly', anchorDate }}));
        });
        monthInput.addEventListener('change', ()=>{
            if (!monthInput.value) return; // YYYY-MM
            const [y,m] = monthInput.value.split('-'); anchorDate = new Date(Number(y), Number(m)-1, 1); updateWindowText('monthly'); document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'monthly', anchorDate }}));
        });

        // Initialize default
        setVisibility(rangeSelect.value);
        updateInputsFromAnchor(rangeSelect.value);
        updateWindowText(rangeSelect.value);
    })();
});

function updateDateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString();
    document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<script>
// ===== Owner Forecast and Reservation Gauge (Mock Data) =====
function initOwnerForecastCharts() {
    const forecastCanvas = document.getElementById('odash-forecast');
    const forecastShell = forecastCanvas ? forecastCanvas.closest('.odash-chart-shell') : null;
    if (forecastShell) {
        // show skeleton overlay
        let scrim = forecastShell.querySelector('.loading-scrim');
        if (!scrim) { scrim = document.createElement('div'); scrim.className = 'loading-scrim'; scrim.innerHTML = '<div class="loader"></div>'; forecastShell.style.position='relative'; forecastShell.appendChild(scrim); }
    }
    // Use global range select & navigation + manual pickers
    const rangeSelect = document.getElementById('dbf-range');
    const dateInput = document.getElementById('dbf-date');
    const weekInput = document.getElementById('dbf-week');
    const monthInput = document.getElementById('dbf-month');
    const typeSelect = document.getElementById('odash-forecast-type');
    const legendBox = document.getElementById('odash-forecast-legend');
    const prevBtn = document.getElementById('dbf-prev');
    const nextBtn = document.getElementById('dbf-next');
    const windowText = document.getElementById('dbf-window-text');
    let forecastChart;
    let currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : 'sales';
    let offset = 0; // relative adjustment from anchor
    let anchorDate = new Date(); // set by filter pickers

    function getForecastData(range, mode, offset = 0) {
        if (mode === 'demand') {
            if (range === 'weekly') {
                return {
                        labels: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                        menValues:   [50, 62, 70, 58, 78, 95, 88].map(v => Math.max(5, Math.round(v * (1 + 0.05*offset)))),
                        womenValues: [44, 48, 52, 47, 60, 75, 68].map(v => Math.max(4, Math.round(v * (1 + 0.05*offset)))),
                        accValues:   [20, 25, 28, 24, 30, 35, 32].map(v => Math.max(2, Math.round(v * (1 + 0.05*offset))))
                };
            } else if (range === 'monthly') {
                const labels = Array.from({length: 30}, (_, i) => `${i+1}`);
                return {
                    labels,
                        menValues:   [20,24,22,28,35,30,32,40,44,38,34,42,48,46,52,49,56,52,58,60,57,62,60,64,66,64,70,75,72,78].map(v => Math.max(5, Math.round(v * (1 + 0.04*offset)))),
                        womenValues: [18,20,19,23,27,24,26,30,34,31,29,33,36,36,40,38,42,40,44,45,44,48,45,48,50,50,52,55,53,55].map(v => Math.max(4, Math.round(v * (1 + 0.04*offset)))),
                        accValues:   [8,  9, 10, 11, 12, 11, 12, 14, 15, 14, 13, 14, 16, 16, 18, 17, 18, 18, 19, 20, 19, 21, 20, 21, 22, 22, 23, 24, 23, 24].map(v => Math.max(1, Math.round(v * (1 + 0.04*offset))))
                };
            }
            // default day (hourly)
            return {
                labels: ['9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM'],
                    menValues:   [6, 9, 11, 13, 18, 14, 12, 11, 15, 20, 14, 10].map(v => Math.max(2, Math.round(v * (1 + 0.06*offset)))),
                    womenValues: [5, 7,  9, 11, 16, 12, 10,  9, 12, 16, 11,  8].map(v => Math.max(2, Math.round(v * (1 + 0.06*offset)))),
                    accValues:   [2, 3,  4,  5,  6,  5,  5,  4,  6,  7,  5,  4].map(v => Math.max(1, Math.round(v * (1 + 0.06*offset))))
            };
        }
        // sales mode
        if (range === 'weekly') {
            return {
                labels: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                    posValues: [80, 95, 110, 90, 120, 150, 130].map(v => Math.max(10, Math.round(v * (1 + 0.05*offset)))),
                    resvValues: [40, 55, 60, 50, 70, 90, 80].map(v => Math.max(8, Math.round(v * (1 + 0.05*offset))))
            };
        } else if (range === 'monthly') {
            // 30 days mock
            const labels = Array.from({length: 30}, (_, i) => `${i+1}`);
                const posValues = [40,48,42,52,62,50,58,72,78,68,62,75,85,80,90,88,98,95,100,105,102,110,108,115,118,115,125,130,128,135].map(v => Math.max(10, Math.round(v * (1 + 0.04*offset))));
                const resvValues = [20,24,23,28,33,28,30,38,42,37,36,40,45,45,50,47,52,50,55,57,56,60,57,60,62,63,65,70,67,70].map(v => Math.max(6, Math.round(v * (1 + 0.04*offset))));
            return { labels, posValues, resvValues };
        }
        // default day (hourly)
        return {
            labels: ['9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM'],
                posValues: [8, 12, 16, 20, 32, 23, 21, 18, 26, 36, 25, 16].map(v => Math.max(3, Math.round(v * (1 + 0.06*offset)))),
                resvValues: [4, 6, 8, 10, 16, 12, 11, 10, 14, 19, 13, 9].map(v => Math.max(2, Math.round(v * (1 + 0.06*offset))))
        };
    }

    function buildGradient(ctx, canvas) {
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.clientHeight || canvas.height);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.02)');
        return gradient;
    }

    function setLegend(mode) {
        if (!legendBox) return;
        if (mode === 'demand') {
            legendBox.innerHTML = `
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#3b82f6; box-shadow:0 2px 4px rgba(59,130,246,.3)"></span>
                    <span class="sub" style="color:#1e40af; font-weight:500;">Men</span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#8b5cf6; box-shadow:0 2px 4px rgba(139,92,246,.3)"></span>
                    <span class="sub" style="color:#5b21b6; font-weight:500;">Women</span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#06b6d4; box-shadow:0 2px 4px rgba(6,182,212,.3)"></span>
                    <span class="sub" style="color:#0e7490; font-weight:500;">Accessories</span>
                </div>`;
        } else {
            legendBox.innerHTML = `
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#3b82f6; box-shadow:0 2px 4px rgba(59,130,246,.3)"></span>
                    <span class="sub" style="color:#1e40af; font-weight:500;">POS</span>
                    <span style="display:inline-block; width:24px; height:2px; background:#f59e0b; border-top:2.5px dashed #f59e0b; margin:0 4px;"></span>
                    <span class="sub" style="font-size:11px; color:#92400e;">Trend</span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#06b6d4; box-shadow:0 2px 4px rgba(6,182,212,.3)"></span>
                    <span class="sub" style="color:#0e7490; font-weight:500;">Reservation</span>
                    <span style="display:inline-block; width:24px; height:2px; background:#8b5cf6; border-top:2.5px dotted #8b5cf6; margin:0 4px;"></span>
                    <span class="sub" style="font-size:11px; color:#5b21b6;">Trend</span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="dot" style="background:#ef4444; box-shadow:0 2px 4px rgba(239,68,68,.3)"></span>
                    <span class="sub" style="color:#991b1b; font-weight:500;">Peak</span>
                </div>`;
        }
    }

    function formatDateMDY(d) {
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }

    function getWindowRange(range, offset) {
        const base = new Date();
        let start, end;
        if (range === 'monthly') {
            const m = base.getMonth() + offset;
            const y = base.getFullYear();
            const dt = new Date(y, base.getMonth(), 1);
            dt.setMonth(dt.getMonth() + offset);
            start = new Date(dt.getFullYear(), dt.getMonth(), 1);
            end = new Date(dt.getFullYear(), dt.getMonth() + 1, 0);
            if (offset === 0) return 'This Month';
            if (offset === 1) return 'Next Month';
            return `${start.toLocaleString('en-US', { month: 'long', year: 'numeric' })}`;
        } else if (range === 'weekly') {
            const dt = new Date(base);
            dt.setDate(dt.getDate() + offset * 7);
            // get Sunday as start of week
            const day = dt.getDay();
            start = new Date(dt);
            start.setDate(dt.getDate() - day);
            end = new Date(start);
            end.setDate(start.getDate() + 6);
            if (offset === 0) return 'This Week';
            if (offset === 1) return 'Next Week';
            return `${formatDateMDY(start)} – ${formatDateMDY(end)}`;
        }
        // day
        const d = new Date(base);
        d.setDate(d.getDate() + offset);
        if (offset === 0) return 'Today';
        if (offset === 1) return 'Tomorrow';
        return `${formatDateMDY(d)}`;
    }

    function updateWindowText(range, offset) {
        if (!windowText) return;
        // Derive label from anchorDate + offset
        const base = new Date(anchorDate.getTime());
        if (range === 'monthly') base.setMonth(base.getMonth()+offset);
        else if (range === 'weekly') base.setDate(base.getDate()+offset*7);
        else base.setDate(base.getDate()+offset);
        // Build text similar to previous behavior
        const today = new Date(); today.setHours(0,0,0,0);
        if (range === 'monthly') {
            const t = (today.getFullYear()===base.getFullYear() && today.getMonth()===base.getMonth());
            windowText.textContent = t ? 'This Month' : base.toLocaleDateString('en-US',{month:'long',year:'numeric'});
        } else if (range === 'weekly') {
            const iso = (d)=>{ const c=new Date(d.getTime()); c.setDate(c.getDate()+3-((c.getDay()+6)%7)); const w1=new Date(c.getFullYear(),0,4); return 1+Math.round(((c-w1)/86400000-3+((w1.getDay()+6)%7))/7); };
            const sameWeek = iso(today)===iso(base) && today.getFullYear()===base.getFullYear();
            if (sameWeek) { windowText.textContent = 'This Week'; }
            else {
                const wd = base.getDay(); const start=new Date(base.getTime()); start.setDate(base.getDate()-wd); const end=new Date(start.getTime()); end.setDate(start.getDate()+6);
                windowText.textContent = `${start.toLocaleDateString('en-US',{month:'short',day:'2-digit'})} – ${end.toLocaleDateString('en-US',{month:'short',day:'2-digit'})}`;
            }
        } else {
            const diffDays = Math.round((base.getTime()-today.getTime())/86400000);
            windowText.textContent = diffDays===0 ? 'Today' : (diffDays===1 ? 'Tomorrow' : (diffDays===-1 ? 'Yesterday' : base.toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'})));
        }
    }

    function updateNavButtons(range, offset) {
        if (!nextBtn) return;
        const probe = new Date(anchorDate.getTime());
        if (range === 'monthly') probe.setMonth(probe.getMonth()+offset);
        else if (range === 'weekly') probe.setDate(probe.getDate()+offset*7);
        else probe.setDate(probe.getDate()+offset);
        const today = new Date(); today.setHours(0,0,0,0);
        let disable = false;
        if (range === 'monthly') disable = (probe.getFullYear()>today.getFullYear()) || (probe.getFullYear()===today.getFullYear() && probe.getMonth()>=today.getMonth()+1);
        else if (range === 'weekly') disable = (probe.getTime() - today.getTime())/86400000 >= 7; // next whole week ahead
        else disable = (probe.getTime() - today.getTime())/86400000 >= 1; // next day ahead
        nextBtn.disabled = disable;
    }

    function updateForecast(range, mode) {
        const data = getForecastData(range, mode, offset);
        const labels = data.labels;
        
        // Destroy chart if switching modes to change dataset structure
        if (forecastChart && currentMode !== mode) {
            forecastChart.destroy();
            forecastChart = null;
        }

        if (mode === 'demand') {
            const { menValues, womenValues, accValues } = data;
            const menPeaks = getTopIndices(menValues, 2);
            const womenPeaks = getTopIndices(womenValues, 2);
            const accPeaks = getTopIndices(accValues, 2);

            const menPointRadius = menValues.map((_, i) => menPeaks.includes(i) ? 6 : 3);
            const menPointBg = menValues.map((_, i) => menPeaks.includes(i) ? '#ef4444' : '#3b82f6');
            const womenPointRadius = womenValues.map((_, i) => womenPeaks.includes(i) ? 6 : 3);
            const womenPointBg = womenValues.map((_, i) => womenPeaks.includes(i) ? '#ef4444' : '#8b5cf6');
            const accPointRadius = accValues.map((_, i) => accPeaks.includes(i) ? 6 : 3);
            const accPointBg = accValues.map((_, i) => accPeaks.includes(i) ? '#ef4444' : '#06b6d4');

            if (!forecastChart) {
                if (!forecastCanvas) return;
                const ctx = forecastCanvas.getContext('2d');
                forecastChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label:'Men', data: menValues, borderColor:'#3b82f6', fill:false, tension:0.35, pointRadius:menPointRadius, pointBackgroundColor:menPointBg, borderWidth:2.5 },
                            { label:'Women', data: womenValues, borderColor:'#8b5cf6', fill:false, tension:0.35, pointRadius:womenPointRadius, pointBackgroundColor:womenPointBg, borderWidth:2.5 },
                            { label:'Accessories', data: accValues, borderColor:'#06b6d4', fill:false, tension:0.35, pointRadius:accPointRadius, pointBackgroundColor:accPointBg, borderWidth:2.5 }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                intersect:false, mode:'index',
                                backgroundColor:'#1e3a8a', titleColor:'#fff', bodyColor:'#bfdbfe', borderColor:'#3b82f6', borderWidth:1
                            }
                        },
                        scales: {
                            x: { grid:{display:false}, ticks:{ color:'#64748b', font:{ size:11 } } },
                            y: { beginAtZero:true, grid:{ color:'rgba(59, 130, 246, 0.08)' }, ticks:{ color:'#64748b' } }
                        }
                    }
                });
            } else {
                forecastChart.data.labels = labels;
                forecastChart.data.datasets[0].data = menValues;
                forecastChart.data.datasets[0].pointRadius = menPointRadius;
                forecastChart.data.datasets[0].pointBackgroundColor = menPointBg;
                forecastChart.data.datasets[1].data = womenValues;
                forecastChart.data.datasets[1].pointRadius = womenPointRadius;
                forecastChart.data.datasets[1].pointBackgroundColor = womenPointBg;
                forecastChart.data.datasets[2].data = accValues;
                forecastChart.data.datasets[2].pointRadius = accPointRadius;
                forecastChart.data.datasets[2].pointBackgroundColor = accPointBg;
                forecastChart.update();
            }
            currentMode = mode;
            if (forecastShell) {
                const scrim = forecastShell.querySelector('.loading-scrim');
                if (scrim) scrim.remove();
            }
            return;
        }

        // sales mode
    const { posValues, resvValues } = data;
        const posPeaks = getTopIndices(posValues, 2);
        const resvPeaks = getTopIndices(resvValues, 2);
        const posPointRadius = posValues.map((_, i) => posPeaks.includes(i) ? 6 : 3);
        const posPointBackground = posValues.map((_, i) => posPeaks.includes(i) ? '#ef4444' : '#3b82f6');
        const resvPointRadius = resvValues.map((_, i) => resvPeaks.includes(i) ? 6 : 3);
        const resvPointBackground = resvValues.map((_, i) => resvPeaks.includes(i) ? '#ef4444' : '#06b6d4');
        const posTrend = linearRegressionSeries(posValues);
        const resvTrend = linearRegressionSeries(resvValues);

        if (!forecastChart) {
            if (!forecastCanvas) return;
            const ctx = forecastCanvas.getContext('2d');
            
            // Gradient for POS
            const posGradient = ctx.createLinearGradient(0, 0, 0, forecastCanvas.clientHeight || forecastCanvas.height);
            posGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
            posGradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');
            
            // Gradient for Reservation
            const resvGradient = ctx.createLinearGradient(0, 0, 0, forecastCanvas.clientHeight || forecastCanvas.height);
            resvGradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
            resvGradient.addColorStop(1, 'rgba(6, 182, 212, 0.01)');
            
            forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'POS',
                            data: posValues,
                            borderColor: '#3b82f6',
                            backgroundColor: posGradient,
                            fill: true,
                            tension: 0.35,
                            pointRadius: posPointRadius,
                            pointBackgroundColor: posPointBackground,
                            borderWidth: 2.5
                        },
                        {
                            label: 'Reservation',
                            data: resvValues,
                            borderColor: '#06b6d4',
                            backgroundColor: resvGradient,
                            fill: true,
                            tension: 0.35,
                            pointRadius: resvPointRadius,
                            pointBackgroundColor: resvPointBackground,
                            borderWidth: 2.5
                        },
                        {
                            label: 'POS Trend',
                            data: posTrend,
                            borderColor: '#f59e0b',
                            borderDash: [8,4],
                            pointRadius: 0,
                            tension: 0.2,
                            borderWidth: 2.5,
                            fill: false
                        },
                        {
                            label: 'Reservation Trend',
                            data: resvTrend,
                            borderColor: '#8b5cf6',
                            borderDash: [2,3],
                            pointRadius: 0,
                            tension: 0.2,
                            borderWidth: 2.5,
                            fill: false
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { 
                            intersect: false, 
                            mode: 'index',
                            backgroundColor: '#1e3a8a',
                            titleColor: '#fff',
                            bodyColor: '#bfdbfe',
                            borderColor: '#3b82f6',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        },
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(59, 130, 246, 0.08)' },
                            ticks: { color: '#64748b' }
                        }
                    }
                }
            });
        } else {
            // update existing chart
            forecastChart.data.labels = labels;
            forecastChart.data.datasets[0].data = posValues;
            forecastChart.data.datasets[0].pointRadius = posPointRadius;
            forecastChart.data.datasets[0].pointBackgroundColor = posPointBackground;
            forecastChart.data.datasets[1].data = resvValues;
            forecastChart.data.datasets[1].pointRadius = resvPointRadius;
            forecastChart.data.datasets[1].pointBackgroundColor = resvPointBackground;
            forecastChart.data.datasets[2].data = posTrend;
            forecastChart.data.datasets[3].data = resvTrend;
            forecastChart.update();
        }
        currentMode = mode;
        if (forecastShell) {
            const scrim = forecastShell.querySelector('.loading-scrim');
            if (scrim) scrim.remove();
        }
    }

    // Initialize with current select value or 'day'; also derive anchor from pickers if set
    const initialRange = rangeSelect && rangeSelect.value ? rangeSelect.value : 'day';
    // Read anchor from pickers
    try {
        if (initialRange==='day' && dateInput && dateInput.value) anchorDate = new Date(dateInput.value+'T00:00:00');
        else if (initialRange==='weekly' && weekInput && weekInput.value) {
            const [y,w] = weekInput.value.split('-W'); const d=new Date(Number(y),0,4); const dow=(d.getDay()+6)%7; d.setDate(d.getDate()-dow+(Number(w)-1)*7); anchorDate = d;
        } else if (initialRange==='monthly' && monthInput && monthInput.value) {
            const [y,m]=monthInput.value.split('-'); anchorDate = new Date(Number(y), Number(m)-1, 1);
        }
    } catch(e){}
    setLegend(currentMode);
    updateForecast(initialRange, currentMode);
    updateWindowText(initialRange, offset);
    updateNavButtons(initialRange, offset);

    // Handle range changes
    if (rangeSelect) {
        rangeSelect.addEventListener('change', () => {
            offset = 0; // reset window when range changes
            updateForecast(rangeSelect.value, currentMode);
            updateWindowText(rangeSelect.value, offset);
            updateNavButtons(rangeSelect.value, offset);
        });
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            // Do NOT update currentMode here. Let updateForecast decide destruction based on previous mode.
            const newMode = typeSelect.value;
            setLegend(newMode);
            offset = 0; // reset window when type changes
            updateForecast(rangeSelect ? rangeSelect.value : 'day', newMode);
            updateWindowText(rangeSelect ? rangeSelect.value : 'day', offset);
            updateNavButtons(rangeSelect ? rangeSelect.value : 'day', offset);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            offset -= 1;
            const r = rangeSelect ? rangeSelect.value : 'day';
            updateForecast(r, currentMode);
            updateWindowText(r, offset);
            updateNavButtons(r, offset);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (offset >= 1) return; // guard: only allow up to +1 into the future
            offset += 1;
            const r = rangeSelect ? rangeSelect.value : 'day';
            updateForecast(r, currentMode);
            updateWindowText(r, offset);
            updateNavButtons(r, offset);
        });
    }

    // React to manual picker changes from the global filter bar
    document.addEventListener('odash:filter-window-updated', (ev)=>{
        const detail = ev.detail||{}; if (!detail.range) return;
        anchorDate = detail.anchorDate ? new Date(detail.anchorDate) : new Date();
        if (rangeSelect && rangeSelect.value !== detail.range) rangeSelect.value = detail.range;
        offset = 0; // reset
        updateForecast(rangeSelect ? rangeSelect.value : 'day', currentMode);
        updateWindowText(rangeSelect ? rangeSelect.value : 'day', offset);
        updateNavButtons(rangeSelect ? rangeSelect.value : 'day', offset);
            // Animate window pill feedback
            if (window.anime) {
                anime({
                    targets: '#dbf-window-text',
                    scale: [1,1.085,1],
                    backgroundColor: ['#eef2ff','#dbeafe','#eef2ff'],
                    duration: 520,
                    easing: 'easeOutQuad'
                });
            }
    });

    document.addEventListener('odash:filter-range-changed', (ev)=>{
        const detail = ev.detail||{}; if (!detail.range) return;
        anchorDate = detail.anchorDate ? new Date(detail.anchorDate) : new Date();
        offset = 0;
        updateForecast(detail.range, currentMode);
        updateWindowText(detail.range, offset);
        updateNavButtons(detail.range, offset);
            if (window.anime) {
                anime({
                    targets: '#dbf-window-text',
                    scale: [1,1.095,1],
                    backgroundColor: ['#eef2ff','#bfdbfe','#eef2ff'],
                    duration: 560,
                    easing: 'easeOutQuad'
                });
            }
    });

    // Reservation gauge (live values) - Futuristic SVG
    const resvContainer = document.getElementById('odash-resv-gauge');
    if (resvContainer) {
        const dd = (window.laravelData && window.laravelData.dashboardData) ? window.laravelData.dashboardData : {};
        const completed = Number(dd.completedReservations || 0);
        const cancelled = Number(dd.cancelledReservations || 0);
        const pending = Number(dd.activeReservations || 0);
        const total = Math.max(0, completed + cancelled + pending);
        const pct = (n, d) => (d > 0 ? Math.round((n / d) * 100) : 0);
        document.getElementById('odash-resv-total').textContent = total;
        document.getElementById('odash-resv-completed-pct').textContent = pct(completed, total) + '%';
        document.getElementById('odash-resv-cancelled-pct').textContent = pct(cancelled, total) + '%';
        document.getElementById('odash-resv-pending-pct').textContent = pct(pending, total) + '%';

        renderNeonGauge(resvContainer, { completed, cancelled, pending, total });
    }
}

// ===== Futuristic Neon Gauge (SVG) =====
function renderNeonGauge(container, { completed, cancelled, pending, total }) {
    // Clear
    container.innerHTML = '';
    const width = Math.max(container.clientWidth || 220, 220);
    const height = Math.max(container.clientHeight || 150, 150);
    const vbw = 520, vbh = 300; // wider viewbox to make the semicircle feel more open
    const cx = vbw/2, cy = vbh/2 + 10; // center
    const r = 140; // larger radius for a bigger diameter
    const stroke = 26; // thicker stroke for a bolder ring
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', `0 0 ${vbw} ${vbh}`);
    svg.setAttribute('width', width);
    svg.setAttribute('height', height);

    // defs: gradients & glow
    const defs = document.createElementNS(svg.namespaceURI, 'defs');
    defs.innerHTML = `
        <linearGradient id="gradCompleted" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#10b981"/>
            <stop offset="100%" stop-color="#34d399"/>
        </linearGradient>
        <linearGradient id="gradCancelled" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ef4444"/>
            <stop offset="100%" stop-color="#f97316"/>
        </linearGradient>
        <linearGradient id="gradPending" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#f59e0b"/>
            <stop offset="100%" stop-color="#fbbf24"/>
        </linearGradient>
        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
            <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
            <feMerge>
              <feMergeNode in="coloredBlur"/>
              <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>`;
    svg.appendChild(defs);

    // We'll group the visual elements so we can rotate the whole semicircle by 90deg
    const g = document.createElementNS(svg.namespaceURI, 'g');
    // rotate by 90 degrees around center to shift start point
    g.setAttribute('transform', `rotate(90 ${cx} ${cy})`);

    // Background faint track (full semicircle)
    const track = pathArc(cx, cy, r, -180, 0);
    track.setAttribute('stroke', '#eef2f7');
    track.setAttribute('stroke-width', String(stroke));
    track.setAttribute('fill', 'none');
    track.setAttribute('stroke-linecap', 'round');
    track.setAttribute('opacity', '.95');
    track.classList.add('g-glow');
    g.appendChild(track);

    // Segmented arcs with tiny gaps
    const segments = [];
    const values = [completed, cancelled, pending];
    const colors = ['url(#gradCompleted)', 'url(#gradCancelled)', 'url(#gradPending)'];
    const totalSafe = total > 0 ? total : 1;
    const gapDeg = 4; // small gap between segments
    const usableArc = 180 - gapDeg * (values.length - 1);
    const rawSpans = values.map(v => (v / totalSafe) * usableArc);
    let startAng = -180;
    for (let i = 0; i < values.length; i++) {
        const span = rawSpans[i];
        const seg = pathArc(cx, cy, r, startAng, startAng + span);
        seg.setAttribute('stroke', colors[i]);
        seg.setAttribute('stroke-width', String(stroke));
        seg.setAttribute('stroke-linecap', 'round');
        seg.setAttribute('fill', 'none');
        seg.setAttribute('opacity', '0.98');
        seg.classList.add('g-segment');
        g.appendChild(seg);
        segments.push(seg);
        startAng += span + gapDeg;
    }

    svg.appendChild(g);
    container.appendChild(svg);

    // Reveal animation: stroke-dash technique per segment
    const duration = 850;
    segments.forEach((seg, idx) => {
        const len = seg.getTotalLength();
        seg.style.strokeDasharray = `${len}`;
        seg.style.strokeDashoffset = `${len}`;
        const delay = 90 * idx;
        const start = performance.now() + delay;
        const tick = (now) => {
            const t = Math.max(0, Math.min(1, (now - start) / duration));
            const eased = 1 - Math.pow(1 - t, 3);
            seg.style.strokeDashoffset = `${Math.round((1 - eased) * len)}`;
            if (t < 1) requestAnimationFrame(tick);
            else seg.style.strokeDashoffset = '0';
        };
        requestAnimationFrame(tick);
    });
}

// Helpers for SVG arc
function pathArc(cx, cy, r, startAngle, endAngle) {
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', describeArc(cx, cy, r, startAngle, endAngle));
    return path;
}
function describeArc(cx, cy, r, startAngle, endAngle) {
    const start = polarToCartesian(cx, cy, r, endAngle);
    const end = polarToCartesian(cx, cy, r, startAngle);
    const largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;
    return [
        'M', start.x, start.y,
        'A', r, r, 0, largeArcFlag, 0, end.x, end.y
    ].join(' ');
}
function polarToCartesian(cx, cy, r, angleInDegrees) {
    const angleInRadians = (angleInDegrees - 90) * Math.PI / 180.0;
    return {
        x: cx + (r * Math.cos(angleInRadians)),
        y: cy + (r * Math.sin(angleInRadians))
    };
}

// ===== Opening animations for dashboard cards =====
function initOpeningAnimations() {
    const cards = document.querySelectorAll('#inventory-dashboard .odash-card');
    cards.forEach(c=> c.classList.add('reveal'));
    if (window.anime) {
        anime({
            targets: '#inventory-dashboard .odash-card.reveal',
            opacity: [0,1],
            translateY: [14,0],
            scale: [0.97,1],
            easing: 'easeOutQuad',
            duration: 640,
            delay: anime.stagger(95, { start: 100 }),
            complete: () => cards.forEach(c=> c.classList.add('in'))
        });
    } else {
        // Fallback to simple stagger if anime.js failed to load
        cards.forEach((card,i)=>{
            const delay = 90*i;
            setTimeout(()=> card.classList.add('in'), delay);
        });
    }
}

// Subtle KPI count-up using anime.js (only if target values > 0)
function animateKpiCounts(){
    if (!window.anime) return;
    const fmtCurrency = (n)=> `₱${n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const fmtInt = (n)=> `${Math.round(n).toLocaleString('en-PH')}`;
    const items = [
        { id: 'odash-kpi-revenue', type: 'currency' },
        { id: 'odash-kpi-sold', type: 'int' },
        { id: 'odash-kpi-resv-completed', type: 'int' },
        { id: 'odash-kpi-resv-cancelled', type: 'int' }
    ];
    items.forEach(({id,type})=>{
        const el = document.getElementById(id); if (!el) return;
        const raw = el.textContent || '';
        const num = type==='currency' ? parseFloat(raw.replace(/[^0-9.]/g,''))||0 : parseInt(raw.replace(/[^0-9]/g,''))||0;
        if (num <= 0) return;
        anime({
            targets: { val: 0 },
            val: num,
            duration: 900,
            easing: 'easeOutCubic',
            update: (a)=>{
                const v = a.animations[0].currentValue;
                el.textContent = type==='currency' ? fmtCurrency(v) : fmtInt(v);
            }
        });
    });
}

// Utilities
function linearRegressionSeries(y) {
    // Simple linear regression y = a + b*x for x = 0..n-1; returns predicted y for each x
    const n = y.length;
    const x = Array.from({length:n}, (_, i) => i);
    const sum = (arr) => arr.reduce((a,b) => a+b, 0);
    const sumX = sum(x);
    const sumY = sum(y);
    const sumXY = sum(x.map((xi, i) => xi * y[i]));
    const sumXX = sum(x.map((xi) => xi * xi));
    const denom = n * sumXX - sumX * sumX || 1;
    const b = (n * sumXY - sumX * sumY) / denom;
    const a = (sumY - b * sumX) / n;
    return x.map((xi) => a + b * xi);
}

function getTopIndices(arr, k) {
    return arr
        .map((v, i) => ({ v, i }))
        .sort((a, b) => b.v - a.v)
        .slice(0, k)
        .map(o => o.i)
        .sort((a,b) => a - b);
}

// ===== Popular Products List (Mock Data) =====
function initPopularProducts() {
    const container = document.getElementById('odash-popular-products');
    // rangeSelect removed; default to monthly behavior
    const monthSelect = null; // removed
    const yearSelect = null; // removed
    if (!container) return;
    // initial skeleton items
    container.innerHTML = Array.from({length:8}).map(()=> '<div class="skeleton" style="height:36px; border-radius:8px; margin:8px 0;"></div>').join('');

    // Populate year select with a sensible range (current year +/- 4)
    const now = new Date();
    const thisYear = now.getFullYear();
    // Month/year selects removed; default to current month/year values (not user-adjustable)

    const dd = (window.laravelData && window.laravelData.dashboardData) ? window.laravelData.dashboardData : {};
    let currentRange = 'monthly';

    function normalizeFromCategories(srcObj) {
        const out = [];
        ['men','women','accessories'].forEach(cat => {
            const arr = srcObj?.[cat];
            if (Array.isArray(arr)) {
                arr.forEach(p => out.push({
                    name: p.name || p.product_name || 'Unknown',
                    sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0)
                }));
            }
        });
        return out.sort((a,b)=> b.sales - a.sales);
    }

    function adjustByRange(list, range) {
        const factor = range === 'yearly' ? 12 : (range === 'quarterly' ? 3 : 1);
        return list.map(it => ({ ...it, sales: Math.max(0, Math.round(it.sales * factor)) }));
    }

    async function fetchPopularFromApi(range, month, year) {
        try {
            const base = window.laravelData?.routes?.popularProducts;
            if (!base) return null;
            const url = new URL(base, window.location.origin);
            if (range) url.searchParams.set('range', range);
            if (month) url.searchParams.set('month', String(month));
            if (year) url.searchParams.set('year', String(year));
            url.searchParams.set('limit', '24');
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || data.success === false) throw new Error(data.message || 'Failed');
            const items = Array.isArray(data.items) ? data.items : [];
            return items.map(i => ({ name: i.name || i.product_name || 'Unknown', sales: Number(i.sold ?? i.sales ?? 0) }))
                        .sort((a,b)=> b.sales - a.sales);
        } catch (e) {
            return null;
        }
    }

    async function getPopularList(range, month, year) {
        // Prefer API if available
        const apiList = await fetchPopularFromApi(range, month, year);
        if (apiList && apiList.length) return apiList;

        // Try explicit keyed objects first: popularProducts[range]
        if (dd.popularProducts && typeof dd.popularProducts === 'object' && dd.popularProducts[range]) {
            const src = dd.popularProducts[range];
            if (Array.isArray(src)) {
                return src.map(p => ({ name: p.name || p.product_name || 'Unknown', sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0) }))
                          .sort((a,b)=> b.sales - a.sales);
            }
            if (typeof src === 'object') {
                return normalizeFromCategories(src);
            }
        }
        // Try alternative flat keys
        const altKey = range === 'monthly' ? 'popularProductsMonthly' : (range === 'quarterly' ? 'popularProductsQuarterly' : 'popularProductsYearly');
        if (dd[altKey]) {
            const src = dd[altKey];
            if (Array.isArray(src)) {
                return src.map(p => ({ name: p.name || p.product_name || 'Unknown', sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0) }))
                          .sort((a,b)=> b.sales - a.sales);
            }
            if (typeof src === 'object') {
                return normalizeFromCategories(src);
            }
        }
        // Baseline categories or mock
        let base = [];
        if (dd.popularProducts && typeof dd.popularProducts === 'object') {
            base = normalizeFromCategories(dd.popularProducts);
        }
        if (!base.length) {
            base = [
                { name: 'Nike Air Max 270', sales: 245 },
                { name: 'Adidas Ultraboost 22', sales: 198 },
                { name: 'Puma RS-X', sales: 176 },
                { name: 'New Balance 574', sales: 142 },
                { name: 'Converse Chuck Taylor', sales: 128 },
                { name: 'Vans Old Skool', sales: 115 },
                { name: 'Reebok Classic Leather', sales: 98 },
                { name: 'Asics Gel-Kayano', sales: 87 },
                { name: 'Skechers D\'Lites', sales: 76 },
                { name: 'Fila Disruptor II', sales: 65 },
                { name: 'Under Armour HOVR', sales: 54 },
                { name: 'Brooks Ghost 14', sales: 43 }
            ];
        }
        return adjustByRange(base, range);
    }

    async function render(range) {
    const month = now.getMonth() + 1;
    const year = thisYear;
        const list = await getPopularList(range, month, year);
        let html = '';
        list.slice(0, 12).forEach((product, index) => {
            const isTop3 = index < 3;
            const rankBadge = isTop3 ? `<span class="odash-product-rank">${index + 1}</span>` : '';
            const itemClass = isTop3 ? 'odash-product-item top-product' : 'odash-product-item';
            html += `
                <div class="${itemClass}">
                    <span class="odash-product-name">${rankBadge}${product.name}</span>
                    <span class="odash-product-sales">${product.sales} sold</span>
                </div>
            `;
        });
        container.innerHTML = html;
        // animate items
        Array.from(container.children||[]).forEach((el,i)=> { el.classList.add('reveal','in'); el.style.transitionDelay = `${i*30}ms`; });
    }

    // Initial render
    render(currentRange);
    // Re-render on changes
    // No listeners: filters removed
}

// ===== Stock Levels Horizontal Bar Chart (Mock Data) =====
function initStockLevels() {
    const stockCanvas = document.getElementById('odash-stock-chart');
    const shell = stockCanvas ? stockCanvas.closest('.odash-chart-shell') : null;
    const categorySelect = document.getElementById('odash-stock-category');
    const modeToggle = document.getElementById('odash-stock-mode');
    let stockChart;
    let currentMode = 'pos';

    async function fetchStockData(category) {
        try {
            const url = new URL(window.laravelData?.routes?.inventoryOverview, window.location.origin);
            url.searchParams.set('source', currentMode);
            if (category) url.searchParams.set('category', category);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || data.success === false) throw new Error(data.message || 'Failed');
            const items = Array.isArray(data.items) ? data.items : [];
            const labels = items.map(i => i.name || i.product_name || 'Item');
            const stocks = items.map(i => Number(i.total_stock || 0));
            const maxVal = stocks.length ? Math.max(...stocks) || 1 : 1;
            const maxStock = stocks.map(() => maxVal);
            return { labels, stocks, maxStock };
        } catch (e) {
            return { labels: [], stocks: [], maxStock: [] };
        }
    }

    async function updateStockChart(category) {
        if (shell) {
            let scrim = shell.querySelector('.loading-scrim');
            if (!scrim) { scrim = document.createElement('div'); scrim.className = 'loading-scrim'; scrim.innerHTML = '<div class="loader"></div>'; shell.style.position='relative'; shell.appendChild(scrim); }
        }
        const { labels, stocks, maxStock } = await fetchStockData(category);
        const backgroundColors = stocks.map((stock, i) => {
            const pct = maxStock[i] ? (stock / maxStock[i]) * 100 : 0;
            if (pct < 30) return '#ef4444';
            if (pct < 60) return '#f59e0b';
            return '#3b82f6';
        });

        if (!stockChart) {
            if (!stockCanvas) return;
            const ctx = stockCanvas.getContext('2d');
            stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Current Stock',
                        data: stocks,
                        backgroundColor: backgroundColors,
                        borderRadius: 6,
                        barThickness: 28
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e3a8a',
                            titleColor: '#fff',
                            bodyColor: '#bfdbfe',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            callbacks: {
                                afterLabel: function(context) {
                                    const index = context.dataIndex;
                                    const max = maxStock[index] || 1;
                                    const pct = Math.round((context.parsed.x / max) * 100);
                                    return `Max (set): ${max} (${pct}% stocked)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(59, 130, 246, 0.08)' },
                            ticks: { color: '#64748b' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#1e3a8a', font: { weight: 500 } }
                        }
                    }
                }
            });
        } else {
            stockChart.data.labels = labels;
            stockChart.data.datasets[0].data = stocks;
            stockChart.data.datasets[0].backgroundColor = backgroundColors;
            stockChart.update();
        }
        if (shell) { const scrim = shell.querySelector('.loading-scrim'); if (scrim) scrim.remove(); }
    }

    const initialCategory = categorySelect && categorySelect.value ? categorySelect.value : 'men';
    updateStockChart(initialCategory);
    if (categorySelect) {
        categorySelect.addEventListener('change', () => updateStockChart(categorySelect.value));
    }
    if (modeToggle) {
        modeToggle.addEventListener('click', (e)=>{
            const btn = e.target.closest('button[data-mode]');
            if (!btn) return;
            currentMode = btn.getAttribute('data-mode') || 'pos';
            Array.from(modeToggle.querySelectorAll('button[data-mode]')).forEach(b=> b.classList.toggle('active', b===btn));
            updateStockChart(categorySelect ? categorySelect.value : undefined);
        });
    }
}
</script>

</body>
</html>