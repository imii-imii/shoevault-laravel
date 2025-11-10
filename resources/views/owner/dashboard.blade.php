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
        /* Predictive UI */
        .odash-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:9999px; font-size:11px; font-weight:800; letter-spacing:.2px; }
        .odash-badge-predictive { background:linear-gradient(135deg,#ede9fe,#dbeafe); color:#6b21a8; border:1px solid #c4b5fd; box-shadow:0 4px 10px rgba(124,58,237,.12); }
        .odash-pred-card { position:relative; overflow:hidden; border:1px dashed #c7d2fe; background:linear-gradient(180deg,#ffffff, #f8fafc); }
        .odash-pred-card::before { content:""; position:absolute; inset:-40%; background:radial-gradient(600px 200px at 10% -20%, rgba(124,58,237,.08), transparent 60%); pointer-events:none; }

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
<body data-user-role="{{ auth()->user()->role ?? 'owner' }}">
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
        <li class="nav-item">
            <a href="{{ route('owner.reports') }}" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="nav-item">
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
                        <select id="dbf-range" class="odash-select" style="min-width:160px;">
                            <option value="day">Day</option>
                            <option value="weekly">Week</option>
                            <option value="monthly">Month</option>
                            <option value="quarterly">Quarter</option>
                            <option value="yearly">Year</option>
                        </select>
                        <!-- Manual pickers (toggle visibility based on Range) -->
                        <div class="dbf-pickers" style="display:inline-flex; gap:8px; align-items:center;">
                            <input id="dbf-date" type="date" class="odash-input" style="display:none;" />
                            <input id="dbf-week" type="week" class="odash-input" style="display:none;" />
                            <input id="dbf-month" type="month" class="odash-input" style="display:none;" />
                            <select id="dbf-quarter" class="odash-select" style="display:none; min-width:120px;">
                                <option value="1">Q1 (Jan–Mar)</option>
                                <option value="2">Q2 (Apr–Jun)</option>
                                <option value="3">Q3 (Jul–Sep)</option>
                                <option value="4">Q4 (Oct–Dec)</option>
                            </select>
                            <input id="dbf-year" type="number" class="odash-input" placeholder="Year" min="2000" max="2100" step="1" style="display:none; width:100px;" />
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

                <!-- Predictive Analytics Header -->
                <div class="odash-row" id="odash-prediction-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:12px;">
                    <div>
                        <div class="odash-title" style="font-size:16px; color:#1e3a8a;">Expected Sales (Predictions)</div>
                        <div class="sub" style="font-size:12px; color:#64748b;">Expected sales for the next periods based on historical averages.</div>
                    </div>
                    <span class="odash-badge odash-badge-predictive" title="These are predictive estimates based on past performance."><i class="fas fa-wand-magic-sparkles"></i> Predictive</span>
                </div>

                <!-- Predictive Analytics KPIs (Expected Sales) -->
                <div class="odash-row" id="odash-prediction-kpis" style="display:grid; grid-template-columns: repeat(5, 1fr); gap:12px; margin-top:12px;">
                    <div class="odash-card odash-pred-card" style="padding:12px;">
                        <div class="odash-title" style="font-size:12px; color:#64748b;">Next Day</div>
                        <div id="kpi-pred-day" class="odash-kpi-value" style="font-size:20px; font-weight:700; color:#1e3a8a;">—</div>
                        <div class="sub" style="font-size:11px; color:#64748b;">Expected sales for this prediction</div>
                    </div>
                    <div class="odash-card odash-pred-card" style="padding:12px;">
                        <div class="odash-title" style="font-size:12px; color:#64748b;">Next Week</div>
                        <div id="kpi-pred-week" class="odash-kpi-value" style="font-size:20px; font-weight:700; color:#1e3a8a;">—</div>
                        <div class="sub" style="font-size:11px; color:#64748b;">Expected sales for this prediction</div>
                    </div>
                    <div class="odash-card odash-pred-card" style="padding:12px;">
                        <div class="odash-title" style="font-size:12px; color:#64748b;">Next Month</div>
                        <div id="kpi-pred-month" class="odash-kpi-value" style="font-size:20px; font-weight:700; color:#1e3a8a;">—</div>
                        <div class="sub" style="font-size:11px; color:#64748b;">Expected sales for this prediction</div>
                    </div>
                    <div class="odash-card odash-pred-card" style="padding:12px;">
                        <div class="odash-title" style="font-size:12px; color:#64748b;">Next Quarter</div>
                        <div id="kpi-pred-quarter" class="odash-kpi-value" style="font-size:20px; font-weight:700; color:#1e3a8a;">—</div>
                        <div class="sub" style="font-size:11px; color:#64748b;">Expected sales for this prediction</div>
                    </div>
                    <div class="odash-card odash-pred-card" style="padding:12px;">
                        <div class="odash-title" style="font-size:12px; color:#64748b;">Next Year</div>
                        <div id="kpi-pred-year" class="odash-kpi-value" style="font-size:20px; font-weight:700; color:#1e3a8a;">—</div>
                        <div class="sub" style="font-size:11px; color:#64748b;">Expected sales for this prediction</div>
                    </div>
                </div>

                <!-- Popular Products and Stock Levels -->
                <div class="odash-row-products">
                    <!-- Popular Products List (Scrollable) -->
                    <div class="odash-card">
                        <div class="odash-card-header" style="justify-content:space-between; align-items:center;">
                            <div class="odash-title">Popular Products</div>
                            <select id="odash-popular-category" class="odash-select" style="min-width:180px;">
                                <option value="all" selected>All Categories</option>
                                <option value="men">Men</option>
                                <option value="women">Women</option>
                                <option value="accessories">Accessories</option>
                            </select>
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
                                <option value="all" selected>All Categories</option>
                                <option value="men">Men</option>
                                <option value="women">Women</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <!-- Stock Controls Row -->
                        <div style="display:flex; gap:8px; align-items:center; padding:8px 16px; border-bottom:1px solid #f3f4f6; background:#f9fafb;">
                            <div style="flex:1;">
                                <input type="text" id="odash-stock-search" placeholder="Search products..." 
                                       style="width:100%; padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:white;">
                            </div>
                            <select id="odash-stock-sort" style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:white; min-width:140px;">
                                <option value="name-asc">Name A-Z</option>
                                <option value="name-desc">Name Z-A</option>
                                <option value="stock-high">Stock High-Low</option>
                                <option value="stock-low" selected>Stock Low-High</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        <div class="odash-stock-list" id="odash-stock-list" style="height:360px; max-height:360px; overflow-y:auto; padding:8px;">
                            <!-- Stock items will be populated here -->
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
        apiDashboardData: '{{ route("owner.api.dashboard-data") }}',
        apiStockLevels: '{{ route("owner.api.stock-levels") }}',
        apiForecast: '{{ route("owner.api.forecast") }}',
        apiForecastPredictions: '{{ route("owner.api.forecast.predictions") }}',
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
        notificationManager.init('{{ auth()->user()->role ?? "owner" }}');
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
        const quarterSelect = document.getElementById('dbf-quarter');
        const yearInput = document.getElementById('dbf-year');
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
            quarterSelect.style.display = range === 'quarterly' ? '' : 'none';
            yearInput.style.display = (range === 'quarterly' || range === 'yearly') ? '' : 'none';
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
            } else if (range === 'quarterly') {
                const q = Math.floor(anchorDate.getMonth()/3) + 1;
                quarterSelect.value = String(q);
                yearInput.value = String(anchorDate.getFullYear());
            } else if (range === 'yearly') {
                yearInput.value = String(anchorDate.getFullYear());
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
            } else if (range === 'quarterly') {
                const q = Math.floor(d.getMonth()/3) + 1;
                const today = new Date();
                const tq = Math.floor(today.getMonth()/3) + 1;
                if (today.getFullYear()===d.getFullYear() && tq===q) text = `This Quarter`;
                else text = `Q${q} ${d.getFullYear()}`;
            } else if (range === 'yearly') {
                const today = new Date();
                if (today.getFullYear()===d.getFullYear()) text = 'This Year';
                else text = String(d.getFullYear());
            }
            windowPill.textContent = text;
        }

        function shiftAnchor(range, dir){
            if (range === 'day') anchorDate.setDate(anchorDate.getDate() + dir);
            else if (range === 'weekly') anchorDate.setDate(anchorDate.getDate() + dir*7);
            else if (range === 'monthly') anchorDate.setMonth(anchorDate.getMonth() + dir);
            else if (range === 'quarterly') anchorDate.setMonth(anchorDate.getMonth() + dir*3);
            else if (range === 'yearly') anchorDate.setFullYear(anchorDate.getFullYear() + dir);
            updateInputsFromAnchor(range);
            updateWindowText(range);
            // trigger forecast refresh (rangeSelect change already wired in forecast initializer)
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range, anchorDate }}));
        }

        rangeSelect.addEventListener('change', ()=>{
            const r = rangeSelect.value;
            setVisibility(r);
            // Set appropriate default dates for each range
            if (r === 'monthly') {
                // Default to October 2025 for better demo data
                anchorDate = new Date(2025, 9, 1); // October 1, 2025
            } else if (r === 'quarterly') {
                const today = new Date();
                const qStartMonth = Math.floor(today.getMonth()/3)*3; // 0,3,6,9
                anchorDate = new Date(today.getFullYear(), qStartMonth, 1);
            } else if (r === 'yearly') {
                const today = new Date();
                anchorDate = new Date(today.getFullYear(), 0, 1);
            } else {
                // For day and week, use today
                anchorDate = new Date(); anchorDate.setHours(0,0,0,0);
            }
            updateInputsFromAnchor(r);
            updateWindowText(r);
            document.dispatchEvent(new CustomEvent('odash:filter-range-changed',{ detail:{ range:r, anchorDate }}));
            // Refresh dashboard data with new range
            refreshDashboardData();
        });
        prevBtn.addEventListener('click', ()=> {
            shiftAnchor(rangeSelect.value, -1);
            refreshDashboardData();
        });
        nextBtn.addEventListener('click', ()=> {
            shiftAnchor(rangeSelect.value, 1);
            refreshDashboardData();
        });

        // Manual input listeners
        dateInput.addEventListener('change', ()=>{
            if (!dateInput.value) return; 
            anchorDate = new Date(dateInput.value+'T00:00:00'); 
            updateWindowText('day'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'day', anchorDate }}));
            refreshDashboardData();
        });
        weekInput.addEventListener('change', ()=>{
            if (!weekInput.value) return; // format YYYY-W##
            const [y, w] = weekInput.value.split('-W');
            // ISO week -> Thursday of week then back to Monday
            const simple = new Date(Number(y),0,4);
            const dayOfWeek = (simple.getDay()+6)%7; // 0..6 Monday=0
            simple.setDate(simple.getDate() - dayOfWeek + (Number(w)-1)*7);
            anchorDate = simple; 
            updateWindowText('weekly'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'weekly', anchorDate }}));
            refreshDashboardData();
        });
        monthInput.addEventListener('change', ()=>{
            if (!monthInput.value) return; // YYYY-MM
            const [y,m] = monthInput.value.split('-'); 
            anchorDate = new Date(Number(y), Number(m)-1, 1); 
            updateWindowText('monthly'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'monthly', anchorDate }}));
            refreshDashboardData();
        });

        // Quarter and Year inputs
        quarterSelect.addEventListener('change', ()=>{
            const q = parseInt(quarterSelect.value || '1', 10);
            const y = parseInt(yearInput.value || String(new Date().getFullYear()), 10);
            const startMonth = (q-1)*3; // 0,3,6,9
            anchorDate = new Date(y, startMonth, 1);
            updateWindowText('quarterly');
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'quarterly', anchorDate }}));
            refreshDashboardData();
        });
        yearInput.addEventListener('change', ()=>{
            const r = rangeSelect.value;
            const y = parseInt(yearInput.value || String(new Date().getFullYear()), 10);
            if (r === 'quarterly') {
                const q = parseInt(quarterSelect.value || '1', 10);
                const startMonth = (q-1)*3;
                anchorDate = new Date(y, startMonth, 1);
            } else if (r === 'yearly') {
                anchorDate = new Date(y, 0, 1);
            }
            updateWindowText(r);
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:r, anchorDate }}));
            refreshDashboardData();
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

// Function to refresh dashboard data based on current date range
function refreshDashboardData() {
    const rangeSelect = document.getElementById('dbf-range');
    const dateInput = document.getElementById('dbf-date');
    const weekInput = document.getElementById('dbf-week');
    const monthInput = document.getElementById('dbf-month');
    const quarterSelect = document.getElementById('dbf-quarter');
    const yearInput = document.getElementById('dbf-year');
    
    if (!rangeSelect) return;
    
    const range = rangeSelect.value;
    let startDate, endDate;
    
    // Calculate date range based on current selection
    const now = new Date();
    
    if (range === 'day') {
        const selectedDate = dateInput.value ? new Date(dateInput.value) : now;
        startDate = new Date(selectedDate);
        startDate.setHours(0, 0, 0, 0);
        endDate = new Date(selectedDate);
        endDate.setHours(23, 59, 59, 999);
    } else if (range === 'weekly') {
        const selectedWeek = weekInput.value;
        if (selectedWeek) {
            const [year, week] = selectedWeek.split('-W');
            startDate = getDateOfISOWeek(parseInt(week), parseInt(year));
            endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            endDate.setHours(23, 59, 59, 999);
        } else {
            // Current week
            const dayOfWeek = now.getDay();
            startDate = new Date(now);
            startDate.setDate(now.getDate() - dayOfWeek);
            startDate.setHours(0, 0, 0, 0);
            endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            endDate.setHours(23, 59, 59, 999);
        }
    } else if (range === 'monthly') {
        const selectedMonth = monthInput.value;
        if (selectedMonth) {
            const [year, month] = selectedMonth.split('-');
            startDate = new Date(parseInt(year), parseInt(month) - 1, 1);
            endDate = new Date(parseInt(year), parseInt(month), 0);
            endDate.setHours(23, 59, 59, 999);
        } else {
            // Default to October 2025 for better demo data (since November has very few reservations)
            startDate = new Date(2025, 9, 1); // October is month 9 (0-indexed)
            endDate = new Date(2025, 10, 0); // Last day of October
            endDate.setHours(23, 59, 59, 999);
        }
    } else if (range === 'quarterly') {
        const qVal = parseInt(quarterSelect.value || '1', 10);
        const yVal = parseInt(yearInput.value || String(now.getFullYear()), 10);
        const startMonth = (qVal - 1) * 3; // 0,3,6,9
        startDate = new Date(yVal, startMonth, 1);
        endDate = new Date(yVal, startMonth + 3, 0); // last day of quarter
        endDate.setHours(23, 59, 59, 999);
    } else if (range === 'yearly') {
        const yVal = parseInt(yearInput.value || String(now.getFullYear()), 10);
        startDate = new Date(yVal, 0, 1);
        endDate = new Date(yVal, 12, 0);
        endDate.setHours(23, 59, 59, 999);
    }
    
    // Format dates for API
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    // Show loading state
    showDashboardLoading();
    
    // Debug: Log the date range being requested
    console.log('Requesting dashboard data:', {
        start_date: formatDate(startDate),
        end_date: formatDate(endDate),
        range: range
    });

    // Make API call to fetch filtered data
    fetch(window.laravelData?.routes?.apiDashboardData || '/api/dashboard-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            start_date: formatDate(startDate),
            end_date: formatDate(endDate),
            range: range
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Dashboard API response:', data);
        if (data.success) {
            updateDashboardWithData(data.data);
        } else {
            console.error('Failed to fetch dashboard data:', data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching dashboard data:', error);
    })
    .finally(() => {
        hideDashboardLoading();
    });
}

// Helper function to get date of ISO week
function getDateOfISOWeek(w, y) {
    const simple = new Date(y, 0, 1 + (w - 1) * 7);
    const dow = simple.getDay();
    const ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
    return ISOweekStart;
}

// Show loading state for dashboard
function showDashboardLoading() {
    // Add loading state to KPI cards
    const kpiCards = document.querySelectorAll('.odash-kpi-value');
    kpiCards.forEach(card => {
        card.style.opacity = '0.5';
    });
    
    // Add loading overlay to chart
    const chartShell = document.querySelector('.odash-chart-shell');
    if (chartShell) {
        let scrim = chartShell.querySelector('.loading-scrim');
        if (!scrim) {
            scrim = document.createElement('div');
            scrim.className = 'loading-scrim';
            scrim.innerHTML = '<div class="loader"></div>';
            chartShell.style.position = 'relative';
            chartShell.appendChild(scrim);
        }
        scrim.style.display = 'flex';
    }
}

// Hide loading state for dashboard
function hideDashboardLoading() {
    // Remove loading state from KPI cards
    const kpiCards = document.querySelectorAll('.odash-kpi-value');
    kpiCards.forEach(card => {
        card.style.opacity = '1';
    });
    
    // Hide loading overlay from chart
    const scrim = document.querySelector('.loading-scrim');
    if (scrim) {
        scrim.style.display = 'none';
    }
}

// Update dashboard with new data
function updateDashboardWithData(data) {
    // Update KPI values
    if (data.kpis) {
        const kpiRevenue = document.getElementById('odash-kpi-revenue');
        const kpiSold = document.getElementById('odash-kpi-sold');
        const kpiCompleted = document.getElementById('odash-kpi-resv-completed');
        const kpiCancelled = document.getElementById('odash-kpi-resv-cancelled');
        
        if (kpiRevenue) kpiRevenue.textContent = '₱' + (data.kpis.revenue || 0).toLocaleString();
        if (kpiSold) kpiSold.textContent = data.kpis.products_sold || 0;
        if (kpiCompleted) kpiCompleted.textContent = data.kpis.completed_reservations || 0;
        if (kpiCancelled) kpiCancelled.textContent = data.kpis.cancelled_reservations || 0;
    }
    
    // Update forecast chart if data is available
    if (data.forecast && window.forecastChart) {
        window.forecastChart.data.labels = data.forecast.labels || [];
        window.forecastChart.data.datasets[0].data = data.forecast.pos || [];
        window.forecastChart.data.datasets[1].data = data.forecast.reservation || [];
        window.forecastChart.update();
    }
    
    // Update reservation gauge if data is available
    if (data.kpis) {
        const resvContainer = document.getElementById('odash-resv-gauge');
        if (resvContainer) {
            const completed = Number(data.kpis.completed_reservations || 0);
            const cancelled = Number(data.kpis.cancelled_reservations || 0);
            const pending = Number(data.kpis.pending_reservations || 0);
            const total = Math.max(0, completed + cancelled + pending);
            const pct = (n, d) => (d > 0 ? Math.round((n / d) * 100) : 0);
            
            // Debug: Log reservation data being applied
            console.log('Updating reservation gauge:', {
                completed, cancelled, pending, total,
                completedPct: pct(completed, total) + '%',
                cancelledPct: pct(cancelled, total) + '%',
                pendingPct: pct(pending, total) + '%'
            });
            
            // Update the text elements
            const totalElement = document.getElementById('odash-resv-total');
            const completedPctElement = document.getElementById('odash-resv-completed-pct');
            const cancelledPctElement = document.getElementById('odash-resv-cancelled-pct');
            const pendingPctElement = document.getElementById('odash-resv-pending-pct');
            
            if (totalElement) totalElement.textContent = total;
            if (completedPctElement) completedPctElement.textContent = pct(completed, total) + '%';
            if (cancelledPctElement) cancelledPctElement.textContent = pct(cancelled, total) + '%';
            if (pendingPctElement) pendingPctElement.textContent = pct(pending, total) + '%';
            
            // Re-render the gauge with new data
            renderNeonGauge(resvContainer, { completed, cancelled, pending, total });
        }
    }
    
    // Update popular products if data is available
    if (data.popular_products) {
        updatePopularProducts(data.popular_products);
    }
    
    console.log('Dashboard data updated for date range');
}

// Function to update popular products list
function updatePopularProducts(productsData) {
    const container = document.getElementById('odash-popular-products');
    if (!container || !Array.isArray(productsData)) return;
    
    console.log('Updating popular products:', productsData);
    
    let html = '';
    productsData.slice(0, 12).forEach((product, index) => {
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
    
    // Animate items
    Array.from(container.children || []).forEach((el, i) => {
        el.classList.add('reveal', 'in');
        el.style.transitionDelay = `${i * 30}ms`;
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

    async function fetchForecast(range, mode, anchor) {
        try {
            const base = (window.laravelData && window.laravelData.routes && window.laravelData.routes.apiForecast) ? window.laravelData.routes.apiForecast : '/owner/api/forecast';
            const url = new URL(base, window.location.origin);
            url.searchParams.set('type', mode);
            url.searchParams.set('range', range);
            if (anchor) {
                const y = anchor.getFullYear();
                const m = String(anchor.getMonth()+1).padStart(2,'0');
                const d = String(anchor.getDate()).padStart(2,'0');
                url.searchParams.set('anchor_date', `${y}-${m}-${d}`);
            }
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || json.success === false) throw new Error(json.message || 'Failed to fetch forecast');
            const payload = json.data || {};
            return payload; // { labels, datasets }
        } catch (e) {
            console.error('Forecast fetch error:', e);
            return { labels: [], datasets: {} };
        }
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
            return `${formatDateMDY(start)} – ${formatDateMDY(end)}`;
        }
        // day
        const d = new Date(base);
        d.setDate(d.getDate() + offset);
        if (offset === 0) return 'Today';
        return `${formatDateMDY(d)}`;
    }

    function updateWindowText(range, offset) {
        if (!windowText) return;
        // Derive label from anchorDate + offset
        const base = new Date(anchorDate.getTime());
        if (range === 'monthly') base.setMonth(base.getMonth()+offset);
        else if (range === 'weekly') base.setDate(base.getDate()+offset*7);
        else if (range === 'quarterly') base.setMonth(base.getMonth()+offset*3);
        else if (range === 'yearly') base.setFullYear(base.getFullYear()+offset);
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
        } else if (range === 'quarterly') {
            const tq = Math.floor(today.getMonth()/3)+1;
            const bq = Math.floor(base.getMonth()/3)+1;
            windowText.textContent = (today.getFullYear()===base.getFullYear() && tq===bq) ? 'This Quarter' : `Q${bq} ${base.getFullYear()}`;
        } else if (range === 'yearly') {
            windowText.textContent = (today.getFullYear()===base.getFullYear()) ? 'This Year' : String(base.getFullYear());
        } else {
            const diffDays = Math.round((base.getTime()-today.getTime())/86400000);
            windowText.textContent = diffDays===0 ? 'Today' : base.toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'});
        }
    }

    function updateNavButtons(range, offset) {
        if (!nextBtn) return;
        const today = new Date(); today.setHours(0,0,0,0);

        // Helper: determine if current window is the present period
        const isPresent = (() => {
            const base = new Date(anchorDate.getTime());
            if (range === 'monthly') return base.getFullYear() === today.getFullYear() && base.getMonth() === today.getMonth();
            if (range === 'weekly') {
                const isoWeek = (d)=>{ const c=new Date(d.getTime()); c.setDate(c.getDate()+3-((c.getDay()+6)%7)); const w1=new Date(c.getFullYear(),0,4); return 1+Math.round(((c-w1)/86400000-3+((w1.getDay()+6)%7))/7); };
                return isoWeek(base) === isoWeek(today) && base.getFullYear() === today.getFullYear();
            }
            if (range === 'quarterly') {
                const bq = Math.floor(base.getMonth()/3), tq = Math.floor(today.getMonth()/3);
                return base.getFullYear() === today.getFullYear() && bq === tq;
            }
            if (range === 'yearly') return base.getFullYear() === today.getFullYear();
            // day
            return base.getFullYear()===today.getFullYear() && base.getMonth()===today.getMonth() && base.getDate()===today.getDate();
        })();

        if (isPresent) {
            nextBtn.disabled = true;
            return;
        }

        // Else, prevent navigating into future period by checking the next window start
        const probeNext = new Date(anchorDate.getTime());
        if (range === 'monthly') probeNext.setMonth(probeNext.getMonth()+1);
        else if (range === 'weekly') probeNext.setDate(probeNext.getDate()+7);
        else if (range === 'quarterly') probeNext.setMonth(probeNext.getMonth()+3);
        else if (range === 'yearly') probeNext.setFullYear(probeNext.getFullYear()+1);
        else probeNext.setDate(probeNext.getDate()+1); // day

        let disable = false;
        if (range === 'monthly') {
            disable = (probeNext.getFullYear()>today.getFullYear()) || (probeNext.getFullYear()===today.getFullYear() && probeNext.getMonth()>today.getMonth());
        } else if (range === 'weekly') {
            // if the start day of next window is after today
            const startOfNext = new Date(probeNext.getTime()); startOfNext.setHours(0,0,0,0);
            disable = startOfNext.getTime() > today.getTime();
        } else if (range === 'quarterly') {
            const tq = Math.floor(today.getMonth()/3);
            const pq = Math.floor(probeNext.getMonth()/3);
            disable = (probeNext.getFullYear()>today.getFullYear()) || (probeNext.getFullYear()===today.getFullYear() && pq>tq);
        } else if (range === 'yearly') {
            disable = probeNext.getFullYear() > today.getFullYear();
        } else {
            disable = (probeNext.getTime() - today.getTime())/86400000 > 0; // day-level future
        }
        nextBtn.disabled = disable;
    }

    async function updateForecast(range, mode) {
        const payload = await fetchForecast(range, mode, anchorDate);
        const labels = payload.labels || [];
        
        // Destroy chart if switching modes to change dataset structure
        if (forecastChart && currentMode !== mode) {
            forecastChart.destroy();
            forecastChart = null;
        }

        // When viewing sales mode, also refresh predictive KPI cards (expected future revenue)
        if (mode === 'sales') {
            fetchAndRenderPredictions();
            const kpiRow = document.getElementById('odash-prediction-kpis');
            const kpiHeader = document.getElementById('odash-prediction-header');
            if (kpiRow) kpiRow.style.display = 'grid';
            if (kpiHeader) kpiHeader.style.display = 'flex';
        }
        else {
            const kpiRow = document.getElementById('odash-prediction-kpis');
            const kpiHeader = document.getElementById('odash-prediction-header');
            if (kpiRow) kpiRow.style.display = 'none';
            if (kpiHeader) kpiHeader.style.display = 'none';
        }

        if (mode === 'demand') {
            const menValues = (payload.datasets && Array.isArray(payload.datasets.men)) ? payload.datasets.men : [];
            const womenValues = (payload.datasets && Array.isArray(payload.datasets.women)) ? payload.datasets.women : [];
            const accValues = (payload.datasets && Array.isArray(payload.datasets.accessories)) ? payload.datasets.accessories : [];
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
        const posValues = (payload.datasets && Array.isArray(payload.datasets.pos)) ? payload.datasets.pos : [];
        const resvValues = (payload.datasets && Array.isArray(payload.datasets.reservation)) ? payload.datasets.reservation : [];
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

    // Fetch predictive analytics (next periods expected sales) and render KPI cards
    async function fetchAndRenderPredictions() {
        const route = window.laravelData?.routes?.apiForecastPredictions;
        if (!route) return;
        try {
            const res = await fetch(route, { headers: { 'Accept':'application/json' } });
            const json = await res.json();
            if (!res.ok || json.success === false) throw new Error(json.message || 'Predictions fetch failed');
            const data = json.data || {};
            const map = {
                day: document.getElementById('kpi-pred-day'),
                week: document.getElementById('kpi-pred-week'),
                month: document.getElementById('kpi-pred-month'),
                quarter: document.getElementById('kpi-pred-quarter'),
                year: document.getElementById('kpi-pred-year')
            };
            const fmt = (v)=> (v===null||v===undefined) ? '—' : '₱'+Number(v).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
            Object.entries(map).forEach(([k, el])=> { if (el) el.textContent = fmt(data[k]); });
            animatePredictionCards();
        } catch (e) {
            console.warn('Prediction KPI error:', e);
        }
    }

    function animatePredictionCards(){
        if (!window.anime) return;
        const cards = document.querySelectorAll('#odash-prediction-kpis .odash-card');
        anime({
            targets: cards,
            opacity: [0,1],
            translateY: [12,0],
            scale: [0.96,1],
            delay: anime.stagger(70),
            duration: 520,
            easing: 'easeOutQuad'
        });
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

    // Note: Prev/Next buttons are handled by the global filter bar which dispatches events.

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
    // Initialize current range from global filter if present
    const rangeEl = document.getElementById('dbf-range');
    let currentRange = rangeEl ? (rangeEl.value || 'monthly') : 'monthly';
    let currentStart = null; // YYYY-MM-DD
    let currentEnd = null;   // YYYY-MM-DD

    const iso = (d)=> new Date(d).toISOString().split('T')[0];
    function windowFromAnchor(anchorDate, range){
        const d = new Date(anchorDate);
        let s, e;
        if (range === 'day') {
            s = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0,0);
            e = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23,59,59,999);
        } else if (range === 'weekly') {
            // anchorDate is start of window per filter bar logic
            s = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0,0);
            e = new Date(s); e.setDate(s.getDate()+6); e.setHours(23,59,59,999);
        } else if (range === 'monthly') {
            s = new Date(d.getFullYear(), d.getMonth(), 1, 0,0,0,0);
            e = new Date(d.getFullYear(), d.getMonth()+1, 0, 23,59,59,999);
        } else if (range === 'quarterly') {
            const qStartMonth = Math.floor(d.getMonth()/3)*3;
            s = new Date(d.getFullYear(), qStartMonth, 1, 0,0,0,0);
            e = new Date(d.getFullYear(), qStartMonth+3, 0, 23,59,59,999);
        } else if (range === 'yearly') {
            s = new Date(d.getFullYear(), 0, 1, 0,0,0,0);
            e = new Date(d.getFullYear(), 12, 0, 23,59,59,999);
        } else {
            s = new Date(d.getFullYear(), d.getMonth(), 1, 0,0,0,0);
            e = new Date(d.getFullYear(), d.getMonth()+1, 0, 23,59,59,999);
        }
        return { start: iso(s), end: iso(e) };
    }

    function normalizeFromCategories(srcObj, categoryFilter) {
        const out = [];
        ['men','women','accessories'].forEach(cat => {
            const arr = srcObj?.[cat];
            if (Array.isArray(arr)) {
                arr.forEach(p => out.push({
                    name: p.name || p.product_name || 'Unknown',
                    category: cat,
                    sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0)
                }));
            }
        });
        const filtered = (!categoryFilter || categoryFilter==='all') ? out : out.filter(x => x.category === categoryFilter);
        return filtered.sort((a,b)=> b.sales - a.sales);
    }

    function adjustByRange(list, range) {
        const factor = range === 'yearly' ? 12 : (range === 'quarterly' ? 3 : 1);
        return list.map(it => ({ ...it, sales: Math.max(0, Math.round(it.sales * factor)) }));
    }

    async function fetchPopularFromApi(params) {
        try {
            const { range, month, year, category, start, end } = params || {};
            const base = window.laravelData?.routes?.popularProducts;
            if (!base) return null;
            const url = new URL(base, window.location.origin);
            if (range) url.searchParams.set('range', range);
            if (month) url.searchParams.set('month', String(month));
            if (year) url.searchParams.set('year', String(year));
            url.searchParams.set('limit', '24');
            if (category && category !== 'all') url.searchParams.set('category', category);
            if (start) url.searchParams.set('start', start);
            if (end) url.searchParams.set('end', end);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || data.success === false) throw new Error(data.message || 'Failed');
            const items = Array.isArray(data.items) ? data.items : [];
            return items.map(i => ({ name: i.name || i.product_name || 'Unknown', category: i.category || '', sales: Number(i.sold ?? i.sales ?? 0) }))
                        .sort((a,b)=> b.sales - a.sales);
        } catch (e) {
            return null;
        }
    }

    async function getPopularList(range, month, year, category, start, end) {
        // Prefer API; even an empty list means "no data" and should not fallback to mocks
        const apiList = await fetchPopularFromApi({ range, month, year, category, start, end });
        if (Array.isArray(apiList)) {
            // If backend didn't filter category, apply client-side filter when requested
            if (category && category !== 'all') {
                return apiList.filter(i => (i.category||'').toLowerCase() === category);
            }
            return apiList;
        }

        // Fallback only to exact-range preloaded dashboard data if available
        if (dd.popularProducts && typeof dd.popularProducts === 'object' && dd.popularProducts[range]) {
            const src = dd.popularProducts[range];
            if (Array.isArray(src)) {
                return src.map(p => ({ name: p.name || p.product_name || 'Unknown', sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0) }))
                          .sort((a,b)=> b.sales - a.sales);
            }
            if (typeof src === 'object') {
                return normalizeFromCategories(src, category);
            }
        }
        // No data for this timeline
        return [];
    }

    async function render(range, category) {
        const month = now.getMonth() + 1;
        const year = thisYear;
        const list = await getPopularList(range, month, year, category, currentStart, currentEnd);
        if (!list || list.length === 0) {
            container.innerHTML = '<div class="odash-empty" style="padding:12px; color:#6b7280; text-align:center;">No value for this timeline.</div>';
            return;
        }
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
    const catSelect = document.getElementById('odash-popular-category');
    let currentCategory = catSelect ? catSelect.value : 'all';
    // Initialize window from current filter inputs if available
    try {
        const anchorElDate = document.getElementById('dbf-date');
        const weekEl = document.getElementById('dbf-week');
        const monthEl = document.getElementById('dbf-month');
        const quarterEl = document.getElementById('dbf-quarter');
        const yearEl = document.getElementById('dbf-year');
        let anchor = new Date();
        if (currentRange === 'day' && anchorElDate?.value) anchor = new Date(anchorElDate.value);
        else if (currentRange === 'weekly' && weekEl?.value) {
            const [y,w] = weekEl.value.split('-W');
            // Simple ISO week to date start (Mon)
            const simple = new Date(parseInt(y,10),0,1 + (parseInt(w,10)-1)*7);
            const dow = simple.getDay();
            if (dow <= 4) simple.setDate(simple.getDate() - dow + 1); else simple.setDate(simple.getDate() + 8 - dow);
            anchor = simple;
        } else if (currentRange === 'monthly' && monthEl?.value) {
            const [y,m] = monthEl.value.split('-');
            anchor = new Date(parseInt(y,10), parseInt(m,10)-1, 1);
        } else if (currentRange === 'quarterly' && yearEl?.value && quarterEl?.value) {
            const y = parseInt(yearEl.value,10), q = parseInt(quarterEl.value,10);
            anchor = new Date(y, (q-1)*3, 1);
        } else if (currentRange === 'yearly' && yearEl?.value) {
            anchor = new Date(parseInt(yearEl.value,10), 0, 1);
        }
        const win = windowFromAnchor(anchor, currentRange);
        currentStart = win.start; currentEnd = win.end;
    } catch (e) { /* noop */ }
    render(currentRange, currentCategory);
    if (catSelect) {
        catSelect.addEventListener('change', () => {
            currentCategory = catSelect.value || 'all';
            // show skeleton while loading
            container.innerHTML = Array.from({length:8}).map(()=> '<div class="skeleton" style="height:36px; border-radius:8px; margin:8px 0;"></div>').join('');
            render(currentRange, currentCategory);
        });
    }
    // Re-render popular products when global range or window changes
    document.addEventListener('odash:filter-range-changed', (e) => {
        const detail = e.detail || {};
        if (!detail.range) return;
        // Update current range to match global selection
        currentRange = detail.range;
        if (detail.anchorDate) {
            const win = windowFromAnchor(detail.anchorDate, currentRange);
            currentStart = win.start; currentEnd = win.end;
        } else {
            currentStart = null; currentEnd = null;
        }
        // Show skeleton while recalculating
        container.innerHTML = Array.from({length:8}).map(()=> '<div class="skeleton" style="height:36px; border-radius:8px; margin:8px 0;"></div>').join('');
        render(currentRange, currentCategory);
    });
    document.addEventListener('odash:filter-window-updated', (e) => {
        // For day/week navigation we just re-render with same range; underlying month/year stays constant
        const detail = e.detail || {};
        if (detail.range && detail.range !== currentRange) {
            currentRange = detail.range;
        }
        if (detail.anchorDate) {
            const win = windowFromAnchor(detail.anchorDate, currentRange);
            currentStart = win.start; currentEnd = win.end;
        }
        // Show skeleton while recalculating
        container.innerHTML = Array.from({length:8}).map(()=> '<div class="skeleton" style="height:36px; border-radius:8px; margin:8px 0;"></div>').join('');
        render(currentRange, currentCategory);
    });
}

// ===== Stock Levels Scrollable List =====
function initStockLevels() {
    const stockList = document.getElementById('odash-stock-list');
    const categorySelect = document.getElementById('odash-stock-category');
    const modeToggle = document.getElementById('odash-stock-mode');
    const searchInput = document.getElementById('odash-stock-search');
    const sortSelect = document.getElementById('odash-stock-sort');
    let currentMode = 'pos';
    let currentCategory = 'all';
    let currentSort = 'stock-low';
    let currentSearch = '';
    let allItems = []; // Store all fetched items for client-side filtering

    if (!stockList) return;

    // Show initial loading
    stockList.innerHTML = Array.from({length: 8}).map(() => 
        '<div class="stock-item-skeleton" style="height:48px; border-radius:8px; margin:4px 0; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>'
    ).join('');

    async function fetchStockData(source, category) {
        try {
            const url = new URL(window.laravelData?.routes?.apiStockLevels, window.location.origin);
            url.searchParams.set('source', source);
            if (category) url.searchParams.set('category', category);
            
            const res = await fetch(url.toString(), { 
                headers: { 'Accept': 'application/json' } 
            });
            const data = await res.json();
            
            if (!res.ok || data.success === false) {
                throw new Error(data.message || 'Failed to fetch stock data');
            }
            
            return Array.isArray(data.items) ? data.items : [];
        } catch (e) {
            console.error('Error fetching stock data:', e);
            return [];
        }
    }

    function getStatusColor(status) {
        switch (status) {
            case 'critical': return '#ef4444'; // red
            case 'low': return '#f59e0b';      // orange
            case 'medium': return '#3b82f6';   // blue
            case 'good': return '#10b981';     // green
            default: return '#6b7280';         // gray
        }
    }

    function getStatusText(status) {
        switch (status) {
            case 'critical': return 'Critical';
            case 'low': return 'Low';
            case 'medium': return 'Medium';
            case 'good': return 'Good';
            default: return 'Unknown';
        }
    }

    async function updateStockList() {
        // Show loading only if we don't have cached data
        if (allItems.length === 0) {
            stockList.innerHTML = Array.from({length: 8}).map(() => 
                '<div class="stock-item-skeleton" style="height:48px; border-radius:8px; margin:4px 0; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"></div>'
            ).join('');

            const items = await fetchStockData(currentMode, currentCategory);
            allItems = items;
        }
        
        // Apply search filter
        let filteredItems = allItems.filter(item => {
            if (!currentSearch) return true;
            const searchTerm = currentSearch.toLowerCase();
            return item.name.toLowerCase().includes(searchTerm) || 
                   item.category.toLowerCase().includes(searchTerm) ||
                   (item.brand && item.brand.toLowerCase().includes(searchTerm)) ||
                   (item.color && item.color.toLowerCase().includes(searchTerm));
        });

        // Apply sorting
        filteredItems.sort((a, b) => {
            switch (currentSort) {
                case 'name-asc':
                    return a.name.localeCompare(b.name);
                case 'name-desc':
                    return b.name.localeCompare(a.name);
                case 'stock-high':
                    return (b.total_stock || 0) - (a.total_stock || 0);
                case 'stock-low':
                    return (a.total_stock || 0) - (b.total_stock || 0);
                case 'status':
                    const statusOrder = { 'critical': 0, 'low': 1, 'medium': 2, 'good': 3 };
                    return (statusOrder[a.status] || 4) - (statusOrder[b.status] || 4);
                default:
                    return 0;
            }
        });
        
        if (filteredItems.length === 0) {
            const message = currentSearch ? 
                `No items found matching "${currentSearch}"` : 
                'No items found';
            stockList.innerHTML = `<div style="text-align:center; padding:40px; color:#6b7280;">${message}</div>`;
            return;
        }

        let html = '';
        filteredItems.forEach(item => {
            const statusColor = getStatusColor(item.status);
            const statusText = getStatusText(item.status);
            
            // Build brand and color info
            const brandInfo = item.brand ? item.brand : '';
            const colorInfo = item.color ? item.color : '';
            const detailsText = [brandInfo, colorInfo].filter(Boolean).join(' • ');
            
            html += `
                <div class="stock-item" style="display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px solid #f3f4f6; transition:background-color 0.2s;">
                    <div style="flex:1;">
                        <div style="font-weight:500; color:#1f2937; margin-bottom:2px;">${item.name}</div>
                        ${detailsText ? `<div style="font-size:11px; color:#9ca3af; margin-bottom:2px;">${detailsText}</div>` : ''}
                        <div style="font-size:12px; color:#6b7280; text-transform:capitalize;">${item.category}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600; color:#1f2937; margin-bottom:2px;">${item.total_stock}</div>
                        <div style="font-size:11px; color:${statusColor}; font-weight:500; text-transform:uppercase;">${statusText}</div>
                    </div>
                </div>
            `;
        });
        
        stockList.innerHTML = html;

        // Add hover effects
        stockList.querySelectorAll('.stock-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f9fafb';
            });
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = 'transparent';
            });
        });
    }

    // Function to refresh data (clears cache and refetches)
    async function refreshStockData() {
        allItems = []; // Clear cache
        await updateStockList();
    }

    // Event listeners
    if (categorySelect) {
        currentCategory = categorySelect.value || 'all';
        categorySelect.addEventListener('change', () => {
            currentCategory = categorySelect.value;
            refreshStockData(); // Refresh data when category changes
        });
    }

    if (modeToggle) {
        modeToggle.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-mode]');
            if (!btn) return;
            
            currentMode = btn.getAttribute('data-mode') || 'pos';
            
            // Update button states
            Array.from(modeToggle.querySelectorAll('button[data-mode]')).forEach(b => {
                b.classList.toggle('active', b === btn);
            });
            
            refreshStockData(); // Refresh data when mode changes
        });
    }

    // Search functionality
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.trim();
            
            // Debounce search to avoid too many updates
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateStockList(); // Use cached data for search
            }, 300);
        });
        
        // Clear search on escape key
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                currentSearch = '';
                updateStockList();
            }
        });
    }

    // Sort functionality
    if (sortSelect) {
        currentSort = sortSelect.value || 'stock-low';
        sortSelect.addEventListener('change', () => {
            currentSort = sortSelect.value;
            updateStockList(); // Use cached data for sorting
        });
    }

    // Initial load
    updateStockList();
}
</script>
@include('partials.mobile-blocker')

</body>
</html>