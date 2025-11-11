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
        /* Fix for date picker inputs to display as native controls */
        input[type="date"], 
        input[type="week"], 
        input[type="month"] {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background: white !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-family: inherit !important;
            font-size: 14px !important;
            color: #111827 !important;
            cursor: pointer !important;
            min-width: 140px !important;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="week"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            cursor: pointer !important;
            border-radius: 4px;
            margin-left: 8px;
            opacity: 0.6;
            filter: invert(0.5);
            background: none !important;
            color: inherit !important;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="week"]::-webkit-calendar-picker-indicator:hover,
        input[type="month"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            filter: invert(0.2);
        }
        
        input[type="date"]:focus,
        input[type="week"]:focus,
        input[type="month"]:focus {
            outline: none !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        /* Flatpickr custom styles */
        .flatpickr-calendar {
            box-shadow: 0 10px 25px rgba(0,0,0,.1) !important;
            border: 1px solid #e5e7eb !important;
        }
        
        .flatpickr-day.week-hover {
            background: #dbeafe !important;
            border-color: #3b82f6 !important;
        }
        
        .flatpickr-day.selected {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
        }
        
        .flatpickr-input {
            background: white !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-size: 14px !important;
            color: #111827 !important;
        }
        
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
        .odash-chart-shell.scrollable { overflow-y:auto; }
        .odash-chart-shell.scrollable canvas { min-height:280px; }
        .odash-chart-shell.scrollable::-webkit-scrollbar { width: 6px; }
        .odash-chart-shell.scrollable::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .odash-chart-shell.scrollable::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .odash-chart-shell.scrollable::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .odash-gauge-shell { position:relative; height:280px; max-height:280px; display:flex; flex-direction:column; align-items:center; justify-content:center; overflow:hidden; }
        .odash-gauge-legend { display:flex; gap:10px; margin-top:10px; font-size:12px; color:#6b7280; }
        .dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
        
        /* Products and Stock Levels row layout */
        .odash-row-products { display:grid; grid-template-columns: 1fr 3fr; gap:20px; margin-top:20px; }
        .odash-products-list { max-height: 400px; overflow-y: auto; padding: 8px 4px 8px 0; }
        .odash-products-list::-webkit-scrollbar { width: 6px; }
        .odash-products-list::-webkit-scrollbar-track { background: #dbeafe; border-radius: 3px; }
        .odash-products-list::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 3px; }
        .odash-products-list::-webkit-scrollbar-thumb:hover { background: #2563eb; }
        .odash-product-item { display:flex; justify-content:space-between; align-items:flex-start; padding:12px; margin-bottom:6px; background:#f0f9ff; border-radius:8px; transition: all .2s ease; border:1px solid #e0f2fe; min-height: 60px; }
        .odash-product-item:hover { background:#e0f2fe; transform: translateX(2px); }
        .odash-product-item.top-product { background:linear-gradient(135deg, #dbeafe, #bfdbfe); border-left:4px solid #3b82f6; font-weight:600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1); }
        .odash-product-item:last-child { margin-bottom: 0; }
        .odash-product-main { flex: 1; display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .odash-product-name { font-size:13px; color:#1e3a8a; font-weight: 600; line-height: 1.3; }
        .odash-product-details { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .odash-product-brand { font-size:11px; color:#64748b; font-weight: 500; }
        .odash-product-color { font-size:11px; color:#64748b; font-style: italic; }
        .odash-category-badge { font-size:10px; padding:2px 6px; border-radius:10px; font-weight:500; text-transform:uppercase; letter-spacing:0.5px; }
        .odash-category-badge.men { background:#dbeafe; color:#1d4ed8; }
        .odash-category-badge.women { background:#fce7f3; color:#be185d; }
        .odash-category-badge.accessories { background:#dcfce7; color:#15803d; }
        .odash-product-sales { font-size:12px; color:#60a5fa; font-weight:600; white-space: nowrap; align-self: flex-start; margin-top: 2px; }
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
    
    <!-- Flatpickr for cross-browser date picker support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                            <input id="dbf-date" type="date" class="odash-input" style="display:none;" min="" max="" />
                            <input id="dbf-week" type="week" class="odash-input" style="display:none;" min="" max="" />
                            <input id="dbf-month" type="month" class="odash-input" style="display:none;" min="" max="" />
                            <select id="dbf-quarter" class="odash-select" style="display:none; min-width:120px;">
                                <option value="1">Q1 (Jan–Mar)</option>
                                <option value="2">Q2 (Apr–Jun)</option>
                                <option value="3">Q3 (Jul–Sep)</option>
                                <option value="4">Q4 (Oct–Dec)</option>
                            </select>
                            <select id="dbf-year" class="odash-input" style="display:none; width:100px;">
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="odash-filter-controls" style="display:flex;align-items:center;gap:8px;margin-left:12px;margin-right:20px;flex:1;justify-content:center;">
                            <button id="dbf-prev" class="odash-btn" type="button" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                            <div id="dbf-window-text" class="odash-chip" style="min-width:95%; text-align:center; ">Loading...</div>
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
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer; user-select:none;">
                                        <input type="checkbox" id="odash-predictive-mode" style="accent-color:#3b82f6; transform:scale(0.9);">
                                        <span>Predictive Mode</span>
                                    </label>
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
// Global variables for debouncing and request cancellation (accessible to all functions)
window.forecastDebounceTimer = null;
window.currentForecastRequest = null;
window.refreshDebounceTimer = null;
window.currentDashboardRequest = null;

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
        settings: '{{ route("owner.settings") }}'
    }
};

// Initialize dashboard when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Ensure we always use the current local date for initialization
    const today = new Date();
    const currentDateString = formatDate(today);
    console.log('Dashboard initializing on date:', currentDateString, 'Local time:', today.toString());
    
    // Set the date picker to today's date
    const dateInput = document.getElementById('dbf-date');
    if (dateInput) {
        dateInput.value = currentDateString;
    }
    
    // Set the window text to show today's date
    const windowText = document.getElementById('dbf-window-text');
    if (windowText) {
        const formattedDate = today.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        windowText.textContent = formattedDate;
    }
    
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

    // Initialize dashboard with Laravel data
    initializeDashboardKPIs();
    
    // Initialize mock charts for forecast and reservations
    initOwnerForecastCharts();
    initPopularProducts();
    initStockLevels();
    initOpeningAnimations();
    // Hide the section loader after initial render (short delay to allow charts to initialize)
    setTimeout(hideSectionLoader, 600);
    // Light KPI count-up if values are present
    setTimeout(()=>{ try { animateKpiCounts(); } catch(e){} }, 700);

    // Global variables for date limits (will be set dynamically from transaction data)
    let globalMinDate = new Date('2022-01-01T00:00:00'); // Default fallback
    let globalMaxDate = new Date();
    globalMaxDate.setHours(23, 59, 59, 999);

    // Dashboard debouncing variables are now global (window object)

    // Global forecast state variables (accessible to moved functions)
    window.forecastChart = null;
    window.currentForecastMode = 'sales';
    window.forecastAnchorDate = new Date();

    // Global filter bar now directly controls forecast (no internal duplicate controls)
    (async function initDynamicFilterPickers(){
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
            // ISO 8601 week calculation
            const date = new Date(dt.getTime());
            date.setHours(0, 0, 0, 0);
            
            // Get Monday of the current week
            const dayOfWeek = date.getDay();
            const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Sunday = 0, so offset is -6
            const monday = new Date(date.getTime() + mondayOffset * 24 * 60 * 60 * 1000);
            
            // Get Thursday of the current week (used for year determination in ISO)
            const thursday = new Date(monday.getTime() + 3 * 24 * 60 * 60 * 1000);
            
            // Get January 4th of Thursday's year (always in week 1)
            const jan4 = new Date(thursday.getFullYear(), 0, 4);
            
            // Get Monday of the week containing January 4th
            const jan4DayOfWeek = jan4.getDay();
            const jan4MondayOffset = jan4DayOfWeek === 0 ? -6 : 1 - jan4DayOfWeek;
            const jan4Monday = new Date(jan4.getTime() + jan4MondayOffset * 24 * 60 * 60 * 1000);
            
            // Calculate week number
            const weekNumber = Math.floor((monday.getTime() - jan4Monday.getTime()) / (7 * 24 * 60 * 60 * 1000)) + 1;
            
            return `${thursday.getFullYear()}-W${String(weekNumber).padStart(2,'0')}`;
        }

        async function setDatePickerLimits() {
            try {
                const response = await fetch('/owner/api/transaction-date-range', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const dateRange = await response.json();
                
                // Update global date limits
                globalMinDate = new Date(dateRange.earliest + 'T00:00:00');
                globalMaxDate = new Date();
                globalMaxDate.setHours(23, 59, 59, 999);
                
                // Set minimum date restrictions based on actual transaction data
                dateInput.setAttribute('min', dateRange.earliest);
                weekInput.setAttribute('min', dateRange.earliest_week);
                monthInput.setAttribute('min', dateRange.earliest_month);
                
                // Populate year dropdown with available years
                populateYearDropdown(dateRange.earliest, dateRange.latest);
                
                console.log('Date picker limits set:', {
                    earliest: dateRange.earliest,
                    latest: dateRange.latest,
                    earliest_week: dateRange.earliest_week,
                    earliest_month: dateRange.earliest_month
                });
                
            } catch (error) {
                console.error('Error fetching transaction date range:', error);
                
                // Fallback to safe defaults if API fails
                globalMinDate = new Date('2022-01-01T00:00:00');
                dateInput.setAttribute('min', '2022-01-01');
                weekInput.setAttribute('min', '2022-W01');
                monthInput.setAttribute('min', '2022-01');
                
                // Populate year dropdown with fallback range
                populateYearDropdown('2022-01-01', new Date().toISOString().split('T')[0]);
            }
        }

        // Initialize Flatpickr for cross-browser compatibility
        function initializeFlatpickr() {
            // Check if browser supports the input types natively
            const supportsWeek = (function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'week');
                return input.type === 'week';
            })();
            
            const supportsMonth = (function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'month');
                return input.type === 'month';
            })();
            
            // Only initialize Flatpickr if browser doesn't support native inputs
            if (!supportsWeek && weekInput) {
                // Convert to regular date picker with week selection
                weekInput.type = 'text';
                weekInput.placeholder = 'Select week...';
                flatpickr(weekInput, {
                    mode: "single",
                    dateFormat: "Y-\\WW",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates[0]) {
                            // Calculate week format
                            const date = selectedDates[0];
                            const weekStr = normalizeWeekValue(date);
                            instance.input.value = weekStr;
                        }
                        // Trigger change event for existing logic
                        weekInput.dispatchEvent(new Event('change'));
                    },
                    onOpen: function(selectedDates, dateStr, instance) {
                        // Add week selection behavior
                        const calendar = instance.calendarContainer;
                        const days = calendar.querySelectorAll('.flatpickr-day');
                        days.forEach(day => {
                            day.addEventListener('mouseover', function() {
                                // Highlight the week
                                const weekDays = getWeekDays(this);
                                weekDays.forEach(d => d.classList.add('week-hover'));
                            });
                            day.addEventListener('mouseout', function() {
                                days.forEach(d => d.classList.remove('week-hover'));
                            });
                        });
                    }
                });
            }
            
            if (!supportsMonth && monthInput) {
                // Convert to regular date picker with month selection
                monthInput.type = 'text';
                monthInput.placeholder = 'Select month...';
                flatpickr(monthInput, {
                    mode: "single",
                    dateFormat: "Y-m",
                    defaultDate: new Date(),
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates[0]) {
                            // Format as YYYY-MM
                            const date = selectedDates[0];
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            instance.input.value = `${year}-${month}`;
                        }
                        // Trigger change event for existing logic
                        monthInput.dispatchEvent(new Event('change'));
                    },
                    onMonthChange: function(selectedDates, dateStr, instance) {
                        // When month changes, update the input value
                        const date = instance.currentMonth !== undefined ? 
                            new Date(instance.currentYear, instance.currentMonth, 1) : 
                            new Date();
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        instance.input.value = `${year}-${month}`;
                        instance.close();
                        monthInput.dispatchEvent(new Event('change'));
                    }
                });
            }
        }
        
        function getWeekDays(dayElement) {
            const allDays = Array.from(dayElement.parentNode.querySelectorAll('.flatpickr-day'));
            const dayIndex = allDays.indexOf(dayElement);
            const weekStart = dayIndex - (dayIndex % 7);
            return allDays.slice(weekStart, weekStart + 7);
        }

        function populateYearDropdown(earliestDate, latestDate) {
            const yearSelect = document.getElementById('dbf-year');
            if (!yearSelect) return;
            
            // Clear existing options
            yearSelect.innerHTML = '';
            
            // Get year range
            const startYear = new Date(earliestDate).getFullYear();
            const endYear = new Date(latestDate).getFullYear();
            
            // Populate dropdown with years in descending order (newest first)
            for (let year = endYear; year >= startYear; year--) {
                const option = document.createElement('option');
                option.value = year.toString();
                option.textContent = year.toString();
                yearSelect.appendChild(option);
            }
            
            // Set current year as default
            const currentYear = new Date().getFullYear();
            if (currentYear >= startYear && currentYear <= endYear) {
                yearSelect.value = currentYear.toString();
            } else {
                yearSelect.value = endYear.toString();
            }
        }

        function updateInputsFromAnchor(range){
            if (range === 'day') {
                // Use same date formatting as updateWindowText to avoid timezone issues
                const dateString = anchorDate.getFullYear() + '-' + 
                                 String(anchorDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                 String(anchorDate.getDate()).padStart(2, '0');
                dateInput.value = dateString;
                
                // Debug logging
                console.log('updateInputsFromAnchor Debug:', {
                    anchorDate: dateString,
                    inputValue: dateInput.value,
                    range: range
                });
            } else if (range === 'weekly') {
                const weekValue = normalizeWeekValue(anchorDate);
                weekInput.value = weekValue;
                
                // Debug logging for weekly
                console.log('updateInputsFromAnchor Weekly Debug:', {
                    anchorDate: anchorDate.toDateString(),
                    weekValue: weekValue,
                    inputValue: weekInput.value
                });
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
                // Get current system date
                const today = new Date();
                
                // Normalize both dates to compare just the date part (ignore time)
                const todayYear = today.getFullYear();
                const todayMonth = today.getMonth();
                const todayDate = today.getDate();
                
                const anchorYear = d.getFullYear();
                const anchorMonth = d.getMonth();
                const anchorDate = d.getDate();
                
                // Check if anchor date matches today
                if (anchorYear === todayYear && anchorMonth === todayMonth && anchorDate === todayDate) {
                    text = 'Today';
                } else {
                    // Check if anchor date matches yesterday
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    const yesterdayYear = yesterday.getFullYear();
                    const yesterdayMonth = yesterday.getMonth();
                    const yesterdayDate = yesterday.getDate();
                    
                    if (anchorYear === yesterdayYear && anchorMonth === yesterdayMonth && anchorDate === yesterdayDate) {
                        text = 'Yesterday';
                    } else {
                        // Show the actual date for anything else
                        text = d.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                    }
                }
            } else if (range === 'weekly') {
                // ISO week: Monday to Sunday
                const dayOfWeek = d.getDay();
                const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Sunday = 0, so offset is -6
                const monday = new Date(d.getTime() + mondayOffset * 24 * 60 * 60 * 1000);
                const sunday = new Date(monday.getTime() + 6 * 24 * 60 * 60 * 1000);
                
                const today = new Date();
                const sameWeek = (normalizeWeekValue(today) === normalizeWeekValue(d));
                if (sameWeek) text = 'This Week';
                else text = `${monday.toLocaleDateString('en-US',{month:'short',day:'2-digit'})} – ${sunday.toLocaleDateString('en-US',{month:'short',day:'2-digit'})}`;
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
            
            // Also update browser focus on the corresponding date input to ensure visual sync
            const activeInput = getActiveInput(range);
            if (activeInput && document.activeElement !== activeInput) {
                // Briefly highlight the active input to show which date picker is active
                activeInput.style.borderColor = '#3b82f6';
                setTimeout(() => {
                    activeInput.style.borderColor = '';
                }, 200);
            }
        }
        
        function getActiveInput(range) {
            switch(range) {
                case 'day': return dateInput;
                case 'weekly': return weekInput;
                case 'monthly': return monthInput;
                case 'quarterly': return quarterSelect;
                case 'yearly': return yearInput;
                default: return null;
            }
        }
        
        function updateNavigationButtons(range) {
            // Check if we can navigate forward/backward using global date limits
            const minDate = globalMinDate;
            const maxDate = globalMaxDate;
            
            // Calculate what the previous and next dates would be
            const prevDate = new Date(anchorDate.getTime());
            const nextDate = new Date(anchorDate.getTime());
            
            if (range === 'day') {
                prevDate.setDate(prevDate.getDate() - 1);
                nextDate.setDate(nextDate.getDate() + 1);
            } else if (range === 'weekly') {
                prevDate.setDate(prevDate.getDate() - 7);
                nextDate.setDate(nextDate.getDate() + 7);
            } else if (range === 'monthly') {
                prevDate.setMonth(prevDate.getMonth() - 1);
                nextDate.setMonth(nextDate.getMonth() + 1);
            } else if (range === 'quarterly') {
                prevDate.setMonth(prevDate.getMonth() - 3);
                nextDate.setMonth(nextDate.getMonth() + 3);
            } else if (range === 'yearly') {
                prevDate.setFullYear(prevDate.getFullYear() - 1);
                nextDate.setFullYear(nextDate.getFullYear() + 1);
            }
            
            // Enable/disable buttons based on date boundaries
            prevBtn.disabled = prevDate < minDate;
            nextBtn.disabled = nextDate > maxDate;
            
            // Update button styling
            if (prevBtn.disabled) {
                prevBtn.style.opacity = '0.3';
                prevBtn.style.cursor = 'not-allowed';
            } else {
                prevBtn.style.opacity = '';
                prevBtn.style.cursor = 'pointer';
            }
            
            if (nextBtn.disabled) {
                nextBtn.style.opacity = '0.3';
                nextBtn.style.cursor = 'not-allowed';
            } else {
                nextBtn.style.opacity = '';
                nextBtn.style.cursor = 'pointer';
            }
        }

        function shiftAnchor(range, dir){
            // Create a new date object to avoid mutation issues
            const oldDate = new Date(anchorDate.getTime());
            const newDate = new Date(anchorDate.getTime());
            
            if (range === 'day') {
                newDate.setDate(newDate.getDate() + dir);
            } else if (range === 'weekly') {
                newDate.setDate(newDate.getDate() + dir*7);
            } else if (range === 'monthly') {
                newDate.setMonth(newDate.getMonth() + dir);
            } else if (range === 'quarterly') {
                newDate.setMonth(newDate.getMonth() + dir*3);
            } else if (range === 'yearly') {
                newDate.setFullYear(newDate.getFullYear() + dir);
            }
            
            // Check date boundaries using global date limits
            if (newDate < globalMinDate || newDate > globalMaxDate) {
                // Don't update if the new date is outside allowed range
                return;
            }
            
            // Debug logging
            console.log('shiftAnchor Debug:', {
                direction: dir > 0 ? 'next' : 'prev',
                oldDate: oldDate.getFullYear() + '-' + String(oldDate.getMonth() + 1).padStart(2, '0') + '-' + String(oldDate.getDate()).padStart(2, '0'),
                newDate: newDate.getFullYear() + '-' + String(newDate.getMonth() + 1).padStart(2, '0') + '-' + String(newDate.getDate()).padStart(2, '0'),
                range: range
            });
            
            // Update anchorDate with the new date
            anchorDate = newDate;
            window.forecastAnchorDate = anchorDate; // Sync global state
            
            updateInputsFromAnchor(range);
            updateWindowText(range);
            updateNavigationButtons(range);
            // trigger forecast refresh (rangeSelect change already wired in forecast initializer)
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range, anchorDate }}));
        }

        rangeSelect.addEventListener('change', ()=>{
            const r = rangeSelect.value;
            setVisibility(r);
            
            // Preserve the current anchor date when changing ranges, only adjust if needed
            // Use the existing anchorDate instead of defaulting to current date
            const preservedDate = new Date(anchorDate.getTime());
            
            if (r === 'monthly') {
                // Keep the same month but set to first day
                anchorDate = new Date(preservedDate.getFullYear(), preservedDate.getMonth(), 1);
            } else if (r === 'quarterly') {
                // Keep the same quarter start
                const qStartMonth = Math.floor(preservedDate.getMonth()/3)*3; // 0,3,6,9
                anchorDate = new Date(preservedDate.getFullYear(), qStartMonth, 1);
            } else if (r === 'yearly') {
                // Keep the same year
                anchorDate = new Date(preservedDate.getFullYear(), 0, 1);
            } else {
                // For day and week, keep the exact same date
                anchorDate = new Date(preservedDate.getFullYear(), preservedDate.getMonth(), preservedDate.getDate());
            }
            
            // Sync with global forecast anchor date
            window.forecastAnchorDate = anchorDate;
            
            updateInputsFromAnchor(r);
            updateWindowText(r);
            updateNavigationButtons(r);
            document.dispatchEvent(new CustomEvent('odash:filter-range-changed',{ detail:{ range:r, anchorDate }}));
            // Note: Dashboard data refresh is handled by the odash:filter-range-changed event listener
            // which preserves the current forecast mode
        });
        prevBtn.addEventListener('click', ()=> {
            if (!prevBtn.disabled) {
                shiftAnchor(rangeSelect.value, -1);
                // Update both KPIs and forecast, but preserve forecast mode
                refreshDashboardKPIs(); // Update KPIs only
                const typeSelect = document.getElementById('odash-forecast-type');
                const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
                updateForecast(rangeSelect.value, currentMode); // Update forecast with correct mode
            }
        });
        nextBtn.addEventListener('click', ()=> {
            if (!nextBtn.disabled) {
                shiftAnchor(rangeSelect.value, 1);
                // Update both KPIs and forecast, but preserve forecast mode
                refreshDashboardKPIs(); // Update KPIs only
                const typeSelect = document.getElementById('odash-forecast-type');
                const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
                updateForecast(rangeSelect.value, currentMode); // Update forecast with correct mode
            }
        });

        // Manual input listeners
        dateInput.addEventListener('change', ()=>{
            if (!dateInput.value) return; 
            anchorDate = new Date(dateInput.value+'T00:00:00'); 
            window.forecastAnchorDate = anchorDate; // Sync global state
            updateWindowText('day'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'day', anchorDate }}));
            // Don't call refreshDashboardData() - it uses wrong API and loses forecast mode
            // Instead update forecast with current mode preserved
            const typeSelect = document.getElementById('odash-forecast-type');
            const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast('day', currentMode);
        });
        weekInput.addEventListener('change', ()=>{
            if (!weekInput.value) return; // format YYYY-W##
            const [y, w] = weekInput.value.split('-W');
            const year = Number(y);
            const week = Number(w);
            
            // Get January 4th of the year (always in week 1)
            const jan4 = new Date(year, 0, 4);
            
            // Get Monday of the week containing January 4th
            const jan4DayOfWeek = jan4.getDay();
            const jan4MondayOffset = jan4DayOfWeek === 0 ? -6 : 1 - jan4DayOfWeek;
            const jan4Monday = new Date(jan4.getTime() + jan4MondayOffset * 24 * 60 * 60 * 1000);
            
            // Calculate the Monday of the target week
            const targetMonday = new Date(jan4Monday.getTime() + (week - 1) * 7 * 24 * 60 * 60 * 1000);
            
            anchorDate = targetMonday;
            window.forecastAnchorDate = anchorDate; // Sync global state
            updateWindowText('weekly'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'weekly', anchorDate }}));
            // Preserve forecast mode instead of calling refreshDashboardData()
            const typeSelect = document.getElementById('odash-forecast-type');
            const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast('weekly', currentMode);
        });
        monthInput.addEventListener('change', ()=>{
            if (!monthInput.value) return; // YYYY-MM
            const [y,m] = monthInput.value.split('-'); 
            anchorDate = new Date(Number(y), Number(m)-1, 1); 
            window.forecastAnchorDate = anchorDate; // Sync global state
            updateWindowText('monthly'); 
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'monthly', anchorDate }}));
            // Preserve forecast mode instead of calling refreshDashboardData()
            const typeSelect = document.getElementById('odash-forecast-type');
            const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast('monthly', currentMode);
        });

        // Quarter and Year inputs
        quarterSelect.addEventListener('change', ()=>{
            const q = parseInt(quarterSelect.value || '1', 10);
            const y = parseInt(yearInput.value || String(new Date().getFullYear()), 10);
            const startMonth = (q-1)*3; // 0,3,6,9
            anchorDate = new Date(y, startMonth, 1);
            window.forecastAnchorDate = anchorDate; // Sync global state
            updateWindowText('quarterly');
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:'quarterly', anchorDate }}));
            // Preserve forecast mode instead of calling refreshDashboardData()
            const typeSelect = document.getElementById('odash-forecast-type');
            const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast('quarterly', currentMode);
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
            window.forecastAnchorDate = anchorDate; // Sync global state
            updateWindowText(r);
            document.dispatchEvent(new CustomEvent('odash:filter-window-updated',{ detail:{ range:r, anchorDate }}));
            // Preserve forecast mode instead of calling refreshDashboardData()
            const typeSelect = document.getElementById('odash-forecast-type');
            const currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast(r, currentMode);
        });

        // Initialize default - ensure anchorDate is properly set first
        const initialRange = rangeSelect.value || 'daily';
        
        // Always start with a fresh date object to avoid reference issues
        const currentDay = new Date();
        
        if (initialRange === 'monthly') {
            // First day of current month
            anchorDate = new Date(currentDay.getFullYear(), currentDay.getMonth(), 1);
        } else if (initialRange === 'quarterly') {
            const qStartMonth = Math.floor(currentDay.getMonth()/3)*3; // 0,3,6,9
            anchorDate = new Date(currentDay.getFullYear(), qStartMonth, 1);
        } else if (initialRange === 'yearly') {
            anchorDate = new Date(currentDay.getFullYear(), 0, 1);
        } else {
            // For day and week, use today - create exact copy with same date
            anchorDate = new Date(currentDay.getFullYear(), currentDay.getMonth(), currentDay.getDate());
        }
        
        // Sync initial anchor date with global state
        window.forecastAnchorDate = anchorDate;
        
        // Set date range restrictions
        const currentDate = new Date();
        const todayStr = currentDate.getFullYear() + '-' + 
                        String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(currentDate.getDate()).padStart(2, '0'); // YYYY-MM-DD format
        const currentWeek = normalizeWeekValue(currentDate); // YYYY-W## format
        const currentMonth = `${currentDate.getFullYear()}-${String(currentDate.getMonth()+1).padStart(2,'0')}`; // YYYY-MM format
        
        // Set max dates to today
        dateInput.setAttribute('max', todayStr);
        weekInput.setAttribute('max', currentWeek);
        monthInput.setAttribute('max', currentMonth);
        
        // Fetch dynamic date range from transactions and set min date restrictions
        await setDatePickerLimits();
        
        // Initialize Flatpickr for browsers that don't support week/month inputs
        initializeFlatpickr();
        
        setVisibility(initialRange);
        updateInputsFromAnchor(initialRange);
        updateWindowText(initialRange);
        updateNavigationButtons(initialRange);
        
        // Add periodic sync check to ensure date picker and window text stay synchronized
        setInterval(() => {
            const currentRange = rangeSelect.value;
            const activeInput = getActiveInput(currentRange);
            
            if (activeInput && activeInput.value) {
                // Verify that the anchorDate matches the input value
                let inputDate = null;
                
                try {
                    if (currentRange === 'day') {
                        inputDate = new Date(activeInput.value + 'T00:00:00');
                    } else if (currentRange === 'weekly') {
                        const [y, w] = activeInput.value.split('-W');
                        const simple = new Date(Number(y), 0, 4);
                        const dayOfWeek = (simple.getDay() + 6) % 7;
                        simple.setDate(simple.getDate() - dayOfWeek + (Number(w) - 1) * 7);
                        inputDate = simple;
                    } else if (currentRange === 'monthly') {
                        const [y, m] = activeInput.value.split('-');
                        inputDate = new Date(Number(y), Number(m) - 1, 1);
                    } else if (currentRange === 'quarterly') {
                        const q = parseInt(quarterSelect.value || '1', 10);
                        const y = parseInt(yearInput.value || String(new Date().getFullYear()), 10);
                        const startMonth = (q - 1) * 3;
                        inputDate = new Date(y, startMonth, 1);
                    } else if (currentRange === 'yearly') {
                        const y = parseInt(activeInput.value || String(new Date().getFullYear()), 10);
                        inputDate = new Date(y, 0, 1);
                    }
                    
                    // If dates don't match, update window text
                    if (inputDate && Math.abs(inputDate.getTime() - anchorDate.getTime()) > 86400000) {
                        anchorDate = inputDate;
                        updateWindowText(currentRange);
                    }
                } catch (e) {
                    // Ignore parsing errors
                }
            }
        }, 1000); // Check every second
    })();
});

// Forecast-related functions (moved outside IIFE for global access)
async function fetchForecast(range, mode, anchor, abortSignal = null) {
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
        
        const fetchOptions = { 
            headers: { 'Accept': 'application/json' }
        };
        
        if (abortSignal) {
            fetchOptions.signal = abortSignal;
        }
        
        const res = await fetch(url.toString(), fetchOptions);
        const json = await res.json();
        if (!res.ok || json.success === false) throw new Error(json.message || 'Failed to fetch forecast');
        const payload = json.data || {};
        return payload; // { labels, datasets }
    } catch (e) {
        // Don't log errors for aborted requests
        if (e.name !== 'AbortError') {
            console.error('Forecast fetch error:', e);
        }
        return { labels: [], datasets: {} };
    }
}

// Debounced function to update forecast data (prevents multiple rapid requests)
function updateForecast(range, mode) {
    // Clear any existing debounce timer
    if (window.forecastDebounceTimer) {
        clearTimeout(window.forecastDebounceTimer);
    }
    
    // Cancel any ongoing forecast request
    if (window.currentForecastRequest) {
        window.currentForecastRequest.abort();
        window.currentForecastRequest = null;
    }
    
    // Show subtle visual feedback during debounce
    showForecastDebounceIndicator();
    
    // Debounce the actual forecast update by 250ms (slightly faster than dashboard)
    window.forecastDebounceTimer = setTimeout(() => {
        hideForecastDebounceIndicator();
        performForecastUpdate(range, mode);
    }, 250);
}

// Simplified function - no longer needs complex transformations since backend provides proper labels
function transformForecastData(payload, range) {
    // Backend now provides proper labels, so just return the data as-is
    return { 
        labels: payload.labels || [], 
        payload: payload 
    };
}

// Actual function that performs the forecast update
async function performForecastUpdate(range, mode) {
    // Get the necessary global variables that exist inside the forecast initialization
    const forecastCanvas = document.getElementById('odash-forecast');
    const forecastShell = forecastCanvas ? forecastCanvas.closest('.odash-chart-shell') : null;
    const legendBox = document.querySelector('.odash-legend');
    let forecastChart = window.forecastChart; // Access global chart instance
    let currentMode = window.currentForecastMode || 'sales';
    let anchorDate = window.forecastAnchorDate || new Date();
    
    // Create AbortController for request cancellation
    const abortController = new AbortController();
    window.currentForecastRequest = abortController;
    
    const payload = await fetchForecast(range, mode, anchorDate, abortController.signal);
    
    // Check if request was cancelled
    if (abortController.signal.aborted) {
        return;
    }
    
    // Transform labels and data based on range
    const transformed = transformForecastData(payload, range);
    const labels = transformed.labels;
    const aggregatedPayload = transformed.payload;
    
    // Destroy chart if switching modes to change dataset structure
    if (forecastChart && currentMode !== mode) {
        forecastChart.destroy();
        forecastChart = null;
        window.forecastChart = null;
    }



    if (mode === 'demand') {
        // Demand mode: horizontal bar chart showing quantities by brand
        const brands = (aggregatedPayload.datasets && Array.isArray(aggregatedPayload.datasets.brands)) ? aggregatedPayload.datasets.brands : [];
        const quantities = (aggregatedPayload.datasets && Array.isArray(aggregatedPayload.datasets.quantities)) ? aggregatedPayload.datasets.quantities : [];
        
        // Find the brand with highest sales for highlighting
        const maxIndex = findMaxIndex(quantities);
        const backgroundColors = quantities.map((_, i) => i === maxIndex ? '#ef4444' : '#3b82f6');
        const borderColors = quantities.map((_, i) => i === maxIndex ? '#dc2626' : '#2563eb');

        // Handle scrollable container for 6+ brands
        const isScrollable = brands.length >= 6;
        const chartHeight = isScrollable ? Math.max(brands.length * 45, 280) : 280;
        
        if (forecastShell) {
            if (isScrollable) {
                forecastShell.classList.add('scrollable');
                forecastCanvas.style.height = chartHeight + 'px';
            } else {
                forecastShell.classList.remove('scrollable');
                forecastCanvas.style.height = '280px';
            }
        }

        if (!forecastChart) {
            if (!forecastCanvas) return;
            const ctx = forecastCanvas.getContext('2d');
            forecastChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: brands,
                    datasets: [{
                        label: 'Items Sold',
                        data: quantities,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y', // This makes it horizontal
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { 
                            backgroundColor:'#1e3a8a', 
                            titleColor:'#fff', 
                            bodyColor:'#bfdbfe', 
                            borderColor:'#3b82f6', 
                            borderWidth:1,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.x;
                                    return `${context.label}: ${value} items sold`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true, 
                            grid: { color:'rgba(59, 130, 246, 0.08)' }, 
                            ticks: { color:'#64748b', font:{ size:11 } },
                            title: {
                                display: true,
                                text: 'Items Sold',
                                color: '#64748b',
                                font: { size: 12, weight: 'bold' }
                            }
                        },
                        y: { 
                            grid: { display: false }, 
                            ticks: { color:'#64748b', font:{ size:11 } },
                            title: {
                                display: true,
                                text: 'Brands',
                                color: '#64748b',
                                font: { size: 12, weight: 'bold' }
                            }
                        }
                    },
                    layout: {
                        padding: isScrollable ? { top: 10, bottom: 10, left: 10, right: 10 } : undefined
                    }
                }
            });
            window.forecastChart = forecastChart;
        } else {
            // Update existing chart
            forecastChart.data.labels = brands;
            forecastChart.data.datasets[0].data = quantities;
            forecastChart.data.datasets[0].backgroundColor = backgroundColors;
            forecastChart.data.datasets[0].borderColor = borderColors;
            
            // Update chart height and container if needed
            if (forecastShell) {
                const newScrollable = brands.length >= 6;
                const newHeight = newScrollable ? Math.max(brands.length * 45, 280) : 280;
                
                if (newScrollable) {
                    forecastShell.classList.add('scrollable');
                    forecastCanvas.style.height = newHeight + 'px';
                } else {
                    forecastShell.classList.remove('scrollable');
                    forecastCanvas.style.height = '280px';
                }
            }
            
            forecastChart.update();
        }
        currentMode = mode;
        window.currentForecastMode = currentMode;
        if (forecastShell) {
            const scrim = forecastShell.querySelector('.loading-scrim');
            if (scrim) scrim.remove();
        }
        
        // Clear the current forecast request reference
        if (window.currentForecastRequest === abortController) {
            window.currentForecastRequest = null;
        }
        return;
    }

    // sales mode - reset container to normal height
    if (forecastShell) {
        forecastShell.classList.remove('scrollable');
        if (forecastCanvas) {
            forecastCanvas.style.height = '280px';
        }
    }
    
    const posValues = (aggregatedPayload.datasets && Array.isArray(aggregatedPayload.datasets.pos)) ? aggregatedPayload.datasets.pos : [];
    const resvValues = (aggregatedPayload.datasets && Array.isArray(aggregatedPayload.datasets.reservation)) ? aggregatedPayload.datasets.reservation : [];
    // Find the single highest value in each array
    const posPeakIndex = findMaxIndex(posValues);
    const resvPeakIndex = findMaxIndex(resvValues);
    
    const posPeaks = posPeakIndex >= 0 ? [posPeakIndex] : [];
    const resvPeaks = resvPeakIndex >= 0 ? [resvPeakIndex] : [];
    
    console.log('POS Peak:', posPeakIndex >= 0 ? `${posValues[posPeakIndex]} at index ${posPeakIndex}` : 'No peak found');
    console.log('Reservation Peak:', resvPeakIndex >= 0 ? `${resvValues[resvPeakIndex]} at index ${resvPeakIndex}` : 'No peak found');
    const posPointRadius = posValues.map((_, i) => posPeaks.includes(i) ? 6 : 3);
    const posPointBackground = posValues.map((_, i) => posPeaks.includes(i) ? '#ef4444' : '#3b82f6');
    const resvPointRadius = resvValues.map((_, i) => resvPeaks.includes(i) ? 6 : 3);
    const resvPointBackground = resvValues.map((_, i) => resvPeaks.includes(i) ? '#ef4444' : '#06b6d4');
    const posTrend = linearRegressionSeries(posValues);
    const resvTrend = linearRegressionSeries(resvValues);

    if (!forecastChart) {
        if (!forecastCanvas) {
            return;
        }
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
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return `${context.dataset.label}: ₱${value.toLocaleString()}`;
                            }
                        }
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
        window.forecastChart = forecastChart;
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
    window.currentForecastMode = currentMode;
    if (forecastShell) {
        const scrim = forecastShell.querySelector('.loading-scrim');
        if (scrim) scrim.remove();
    }
    
    // Clear the current forecast request reference
    if (window.currentForecastRequest === abortController) {
        window.currentForecastRequest = null;
    }
}

// Cleanup function to cancel all pending requests
function cancelAllRequests() {
    // Cancel dashboard request
    if (window.currentDashboardRequest) {
        window.currentDashboardRequest.abort();
        window.currentDashboardRequest = null;
    }
    
    // Cancel forecast request
    if (window.currentForecastRequest) {
        window.currentForecastRequest.abort();
        window.currentForecastRequest = null;
    }
    
    // Clear timers
    if (window.refreshDebounceTimer) {
        clearTimeout(window.refreshDebounceTimer);
        window.refreshDebounceTimer = null;
    }
    
    if (window.forecastDebounceTimer) {
        clearTimeout(window.forecastDebounceTimer);
        window.forecastDebounceTimer = null;
    }
}

// Cancel all requests when page is unloaded
window.addEventListener('beforeunload', cancelAllRequests);

// Initialize dashboard KPIs with Laravel data
function initializeDashboardKPIs() {
    console.log('Initializing KPIs - will fetch fresh data for today');
    
    // Set loading state for KPIs
    const kpiRevenue = document.getElementById('odash-kpi-revenue');
    const kpiSold = document.getElementById('odash-kpi-sold');
    const kpiCompleted = document.getElementById('odash-kpi-resv-completed');
    const kpiCancelled = document.getElementById('odash-kpi-resv-cancelled');
    
    if (kpiRevenue) kpiRevenue.textContent = 'Loading...';
    if (kpiSold) kpiSold.textContent = 'Loading...';
    if (kpiCompleted) kpiCompleted.textContent = 'Loading...';
    if (kpiCancelled) kpiCancelled.textContent = 'Loading...';
    
    // Use the existing dashboard refresh function to fetch today's data
    performDashboardRefresh();
}





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

// Debounced function to refresh dashboard data (prevents multiple rapid requests)
function refreshDashboardData() {
    // Clear any existing debounce timer
    if (window.refreshDebounceTimer) {
        clearTimeout(window.refreshDebounceTimer);
    }
    
    // Cancel any ongoing request
    if (window.currentDashboardRequest) {
        window.currentDashboardRequest.abort();
        window.currentDashboardRequest = null;
    }
    
    // Show subtle visual feedback during debounce
    showDebounceIndicator();
    
    // Debounce the actual refresh call by 300ms
    window.refreshDebounceTimer = setTimeout(() => {
        hideDebounceIndicator();
        performDashboardRefresh();
    }, 300);
}

// Actual function that performs the dashboard data refresh
function performDashboardRefresh() {
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
            // Default to current month for real-time data
            const now = new Date();
            startDate = new Date(now.getFullYear(), now.getMonth(), 1); // First day of current month
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0); // Last day of current month
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
    
    // Format dates for API (timezone-safe)
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    // Show loading state
    showDashboardLoading();
    
    // Debug: Log the date range being requested
    console.log('Requesting dashboard data:', {
        start_date: formatDate(startDate),
        end_date: formatDate(endDate),
        range: range
    });
    console.log('Raw dates before formatting:', {
        startDate: startDate,
        endDate: endDate
    });

    // Create AbortController for request cancellation
    const abortController = new AbortController();
    window.currentDashboardRequest = abortController;

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
        }),
        signal: abortController.signal
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Only process the response if this request wasn't cancelled
        if (!abortController.signal.aborted) {
            console.log('Dashboard API response:', data);
            if (data.success) {
                updateDashboardWithData(data.data);
            } else {
                console.error('Failed to fetch dashboard data:', data.message);
            }
        }
    })
    .catch(error => {
        // Don't log errors for aborted requests
        if (error.name !== 'AbortError' && !abortController.signal.aborted) {
            console.error('Error fetching dashboard data:', error);
        }
    })
    .finally(() => {
        // Only hide loading if this request wasn't cancelled
        if (!abortController.signal.aborted) {
            hideDashboardLoading();
        }
        // Clear the current request reference
        if (window.currentDashboardRequest === abortController) {
            window.currentDashboardRequest = null;
        }
    });
}

// Refresh only KPIs without affecting forecast chart
function refreshDashboardKPIs() {
    // Clear any existing debounce timer
    if (window.refreshKPIDebounceTimer) {
        clearTimeout(window.refreshKPIDebounceTimer);
    }
    
    // Cancel any ongoing KPI request
    if (window.currentKPIRequest) {
        window.currentKPIRequest.abort();
        window.currentKPIRequest = null;
    }
    
    // Show subtle visual feedback during debounce
    showDebounceIndicator();
    
    // Debounce the actual refresh call by 300ms
    window.refreshKPIDebounceTimer = setTimeout(() => {
        hideDebounceIndicator();
        performKPIRefresh();
    }, 300);
}

// Actual function that performs only KPI data refresh (not forecast chart)
function performKPIRefresh() {
    const rangeSelect = document.getElementById('dbf-range');
    const dateInput = document.getElementById('dbf-date');
    const weekInput = document.getElementById('dbf-week');
    const monthInput = document.getElementById('dbf-month');
    const quarterSelect = document.getElementById('dbf-quarter');
    const yearInput = document.getElementById('dbf-year');
    
    if (!rangeSelect) return;
    
    const range = rangeSelect.value;
    let startDate, endDate;
    
    // Calculate date range based on current selection (same logic as refreshDashboardData)
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
            const now = new Date();
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            endDate.setHours(23, 59, 59, 999);
        }
    } else if (range === 'quarterly') {
        const qVal = parseInt(quarterSelect.value || '1', 10);
        const yVal = parseInt(yearInput.value || String(now.getFullYear()), 10);
        const startMonth = (qVal - 1) * 3;
        startDate = new Date(yVal, startMonth, 1);
        endDate = new Date(yVal, startMonth + 3, 0);
        endDate.setHours(23, 59, 59, 999);
    } else if (range === 'yearly') {
        const yVal = parseInt(yearInput.value || String(now.getFullYear()), 10);
        startDate = new Date(yVal, 0, 1);
        endDate = new Date(yVal, 12, 0);
        endDate.setHours(23, 59, 59, 999);
    }
    
    // Format dates for API
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    // Show loading state
    showDashboardLoading();
    
    // Create AbortController for request cancellation
    const abortController = new AbortController();
    window.currentKPIRequest = abortController;

    // Make API call to fetch KPI data only
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
        }),
        signal: abortController.signal
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Only process the response if this request wasn't cancelled
        if (!abortController.signal.aborted) {
            console.log('KPI-only API response:', data);
            if (data.success) {
                // Update only KPIs and reservation gauge, skip forecast chart
                updateKPIsOnly(data.data);
            } else {
                console.error('Failed to fetch KPI data:', data.message);
            }
        }
    })
    .catch(error => {
        // Don't log errors for aborted requests
        if (error.name !== 'AbortError' && !abortController.signal.aborted) {
            console.error('Error fetching KPI data:', error);
        }
    })
    .finally(() => {
        // Only hide loading if this request wasn't cancelled
        if (!abortController.signal.aborted) {
            hideDashboardLoading();
        }
        // Clear the current request reference
        if (window.currentKPIRequest === abortController) {
            window.currentKPIRequest = null;
        }
    });
}

// Update only KPIs and reservation gauge (not forecast chart)
function updateKPIsOnly(data) {
    console.log('updateKPIsOnly received:', data);
    
    // Update KPI values
    if (data.kpis) {
        console.log('KPI data found:', data.kpis);
        const kpiRevenue = document.getElementById('odash-kpi-revenue');
        const kpiSold = document.getElementById('odash-kpi-sold');
        const kpiCompleted = document.getElementById('odash-kpi-resv-completed');
        const kpiCancelled = document.getElementById('odash-kpi-resv-cancelled');
        
        if (kpiRevenue) kpiRevenue.textContent = '₱' + (data.kpis.revenue || 0).toLocaleString();
        if (kpiSold) kpiSold.textContent = data.kpis.products_sold || 0;
        if (kpiCompleted) kpiCompleted.textContent = data.kpis.completed_reservations || 0;
        if (kpiCancelled) kpiCancelled.textContent = data.kpis.cancelled_reservations || 0;
        
        console.log('KPIs updated:', {
            revenue: data.kpis.revenue,
            products_sold: data.kpis.products_sold,
            completed_reservations: data.kpis.completed_reservations,
            cancelled_reservations: data.kpis.cancelled_reservations
        });
    } else {
        console.log('No KPI data found in response');
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
            
            // Update reservation gauge
            console.log('Updating reservation gauge (KPI-only):', {
                completed, cancelled, pending, total,
                completedPct: pct(completed, total) + '%',
                cancelledPct: pct(cancelled, total) + '%',
                pendingPct: pct(pending, total) + '%'
            });
            
            // Update the percentage text elements
            document.getElementById('odash-resv-total').textContent = total;
            document.getElementById('odash-resv-completed-pct').textContent = pct(completed, total) + '%';
            document.getElementById('odash-resv-cancelled-pct').textContent = pct(cancelled, total) + '%';
            document.getElementById('odash-resv-pending-pct').textContent = pct(pending, total) + '%';
            
            renderNeonGauge(resvContainer, {
                completed: completed,
                cancelled: cancelled,
                pending: pending,
                total: total
            });
        }
    }
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

// Show subtle indicator during debounce period
function showDebounceIndicator() {
    const windowText = document.getElementById('dbf-window-text');
    if (windowText) {
        windowText.style.opacity = '0.7';
        windowText.style.transition = 'opacity 0.2s ease';
    }
}

// Hide debounce indicator
function hideDebounceIndicator() {
    const windowText = document.getElementById('dbf-window-text');
    if (windowText) {
        windowText.style.opacity = '1';
    }
}

// Show subtle indicator during forecast debounce period
function showForecastDebounceIndicator() {
    const forecastShell = document.querySelector('.odash-chart-shell');
    if (forecastShell) {
        forecastShell.style.opacity = '0.8';
        forecastShell.style.transition = 'opacity 0.2s ease';
    }
}

// Hide forecast debounce indicator
function hideForecastDebounceIndicator() {
    const forecastShell = document.querySelector('.odash-chart-shell');
    if (forecastShell) {
        forecastShell.style.opacity = '1';
    }
}

// Update dashboard with new data
function updateDashboardWithData(data) {
    console.log('updateDashboardWithData received:', data);
    
    // Update KPI values
    if (data.kpis) {
        console.log('KPI data found:', data.kpis);
        const kpiRevenue = document.getElementById('odash-kpi-revenue');
        const kpiSold = document.getElementById('odash-kpi-sold');
        const kpiCompleted = document.getElementById('odash-kpi-resv-completed');
        const kpiCancelled = document.getElementById('odash-kpi-resv-cancelled');
        
        if (kpiRevenue) kpiRevenue.textContent = '₱' + (data.kpis.revenue || 0).toLocaleString();
        if (kpiSold) kpiSold.textContent = data.kpis.products_sold || 0;
        if (kpiCompleted) kpiCompleted.textContent = data.kpis.completed_reservations || 0;
        if (kpiCancelled) kpiCancelled.textContent = data.kpis.cancelled_reservations || 0;
        
        console.log('KPIs updated:', {
            revenue: data.kpis.revenue,
            products_sold: data.kpis.products_sold,
            completed_reservations: data.kpis.completed_reservations,
            cancelled_reservations: data.kpis.cancelled_reservations
        });
    } else {
        console.log('No KPI data found in response');
    }
    
    // Don't update forecast chart here - it should be handled by the forecast API
    // to preserve the current mode (sales vs demand). The dashboard API only provides
    // sales data and would override demand mode if we updated the chart here.
    // Forecast updates are handled by updateForecast() calls that use the proper API.
    
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



    function buildGradient(ctx, canvas) {
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.clientHeight || canvas.height);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.02)');
        return gradient;
    }

    function setLegend(mode) {
        if (!legendBox) return;
        if (mode === 'demand') {
            // No legends for demand mode - show brands in horizontal bar chart
            legendBox.innerHTML = '';
        } else {
            // Keep legends for sales mode
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
                // Calculate Monday-based week start (ISO 8601)
                const wd = (base.getDay() + 6) % 7; // Convert Sunday=0 to Monday=0 system
                const start=new Date(base.getTime()); 
                start.setDate(base.getDate()-wd); 
                const end=new Date(start.getTime()); 
                end.setDate(start.getDate()+6);
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
            // Get current mode from dropdown or global state to ensure consistency
            const activeMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
            updateForecast(rangeSelect.value, activeMode);
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
    
    // Predictive Mode toggle (frontend-only for now)
    const predictiveToggle = document.getElementById('odash-predictive-mode');
    if (predictiveToggle) {
        predictiveToggle.addEventListener('change', () => {
            // For now, just log the state - backend integration to be added later
            console.log('Predictive Mode:', predictiveToggle.checked ? 'ON' : 'OFF');
            // TODO: Integrate with backend prediction system
        });
    }

    // Note: Prev/Next buttons are handled by the global filter bar which dispatches events.

    // React to manual picker changes from the global filter bar
    document.addEventListener('odash:filter-window-updated', (ev)=>{
        const detail = ev.detail||{}; if (!detail.range) return;
        anchorDate = detail.anchorDate ? new Date(detail.anchorDate) : new Date();
        if (rangeSelect && rangeSelect.value !== detail.range) rangeSelect.value = detail.range;
        offset = 0; // reset
        // Get current mode from dropdown or global state to ensure consistency
        const activeMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
        updateForecast(rangeSelect ? rangeSelect.value : 'day', activeMode);
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
        // Get current mode from dropdown or global state to ensure consistency
        const activeMode = (typeSelect && typeSelect.value) ? typeSelect.value : (window.currentForecastMode || 'sales');
        updateForecast(detail.range, activeMode);
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

// Simple function to find the index of the maximum value in an array
function findMaxIndex(arr) {
    if (!Array.isArray(arr) || arr.length === 0) {
        return -1;
    }
    
    let maxIndex = 0;
    let maxValue = parseFloat(arr[0]) || 0;
    
    for (let i = 1; i < arr.length; i++) {
        const value = parseFloat(arr[i]) || 0;
        if (value > maxValue) {
            maxValue = value;
            maxIndex = i;
        }
    }
    
    return maxIndex;
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
            console.log('🔍 Fetching Popular Products from API:', {
                range, month, year, category, start, end
            });
            
            const base = window.laravelData?.routes?.popularProducts;
            if (!base) {
                console.error('❌ Popular Products API base URL not found');
                return null;
            }
            
            const url = new URL(base, window.location.origin);
            if (range) url.searchParams.set('range', range);
            if (month) url.searchParams.set('month', String(month));
            if (year) url.searchParams.set('year', String(year));
            url.searchParams.set('limit', '100'); // Increase limit to see more products
            if (category && category !== 'all') url.searchParams.set('category', category);
            if (start) url.searchParams.set('start', start);
            if (end) url.searchParams.set('end', end);
            
            console.log('📡 Popular Products API URL:', url.toString());
            
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            
            console.log('📨 Popular Products API Response:', {
                status: res.status,
                ok: res.ok,
                data: data,
                sampleItem: data.items?.[0] || 'No items'
            });
            
            if (!res.ok || data.success === false) throw new Error(data.message || 'Failed');
            const items = Array.isArray(data.items) ? data.items : [];
            
            const processedItems = items.map(i => ({ 
                name: i.name || i.product_name || 'Unknown', 
                category: i.category || '', 
                brand: i.brand || 'Unknown',
                color: i.color || 'Unknown',
                sales: Number(i.sold ?? i.sales ?? 0) 
            })).sort((a,b)=> b.sales - a.sales);
            
            console.log('✅ Popular Products Processed Items:', processedItems);
            
            return processedItems;
        } catch (e) {
            console.error('❌ Popular Products API Error:', e);
            return null;
        }
    }

    async function getPopularList(range, month, year, category, start, end) {
        console.log('📊 Getting Popular Products List:', {
            range, month, year, category, start, end
        });
        
        // Only use API data for the specific filtered date range - no fallback to all-time data
        const apiList = await fetchPopularFromApi({ range, month, year, category, start, end });
        
        if (Array.isArray(apiList)) {
            console.log('✅ API returned data:', apiList.length, 'items');
            
            // Transform the API data to include brand and color information
            const transformedList = apiList.map(item => {
                console.log('🔍 Processing item:', {
                    raw: item,
                    extractedName: item.name || item.product_name,
                    extractedBrand: item.brand || item.product_brand,
                    extractedColor: item.color || item.product_color
                });
                return {
                    name: item.name || item.product_name || 'Unknown',
                    category: item.category || '',
                    brand: item.brand || item.product_brand || 'Unknown',
                    color: item.color || item.product_color || 'Unknown',
                    sales: Number(item.sold ?? item.sales ?? 0)
                };
            });
            
            // If backend didn't filter category, apply client-side filter when requested
            if (category && category !== 'all') {
                const filtered = transformedList.filter(i => (i.category||'').toLowerCase() === category);
                console.log('🔧 Client-side category filter applied:', {
                    originalCount: transformedList.length,
                    filteredCount: filtered.length,
                    category: category
                });
                return filtered;
            }
            return transformedList;
        }

        console.log('⚠️ No data returned from API, showing empty state');
        // No fallback to avoid showing misleading all-time data when filtering by specific dates
        // If there's no data for the filtered period, show empty state instead
        return [];
    }

    async function render(range, category) {
        console.log('🎨 Rendering Popular Products:', { range, category, currentRange });
        
        // We'll extract month/year from the anchor date after we determine it
        let month, year;
        // For Popular Products, always use only the anchor date (not the full range)
        // Get anchor date from global dashboard state instead of trying to extract from range
        let singleDayStart, singleDayEnd;
        let anchorDate;
        
        // Try to get anchor date from global state
        if (window.forecastAnchorDate) {
            anchorDate = new Date(window.forecastAnchorDate);
        } else {
            // Fallback: try to extract from form inputs based on current range
            try {
                const anchorElDate = document.getElementById('dbf-date');
                const weekEl = document.getElementById('dbf-week');
                const monthEl = document.getElementById('dbf-month');
                const quarterEl = document.getElementById('dbf-quarter');
                const yearEl = document.getElementById('dbf-year');
                
                if (currentRange === 'day' && anchorElDate?.value) {
                    anchorDate = new Date(anchorElDate.value);
                } else if (currentRange === 'weekly' && weekEl?.value) {
                    const [y,w] = weekEl.value.split('-W');
                    const simple = new Date(parseInt(y,10),0,1 + (parseInt(w,10)-1)*7);
                    const dow = simple.getDay();
                    if (dow <= 4) simple.setDate(simple.getDate() - dow + 1); 
                    else simple.setDate(simple.getDate() + 8 - dow);
                    anchorDate = simple;
                } else if (currentRange === 'monthly' && monthEl?.value) {
                    const [y,m] = monthEl.value.split('-');
                    anchorDate = new Date(parseInt(y,10), parseInt(m,10)-1, 1);
                } else if (currentRange === 'quarterly' && yearEl?.value && quarterEl?.value) {
                    const y = parseInt(yearEl.value,10), q = parseInt(quarterEl.value,10);
                    anchorDate = new Date(y, (q-1)*3, 1);
                } else if (currentRange === 'yearly' && yearEl?.value) {
                    anchorDate = new Date(parseInt(yearEl.value,10), 0, 1);
                } else {
                    anchorDate = new Date(); // Current date fallback
                }
            } catch (e) {
                anchorDate = new Date(); // Current date fallback
            }
        }
        
        // Extract month/year from the actual anchor date, not from the full range
        month = anchorDate.getMonth() + 1;
        year = anchorDate.getFullYear();
        
        // Calculate appropriate date range based on the current range type
        let rangeStart, rangeEnd;
        if (currentRange === 'day') {
            // For daily view, use only the anchor date
            rangeStart = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), anchorDate.getDate(), 0, 0, 0, 0);
            rangeEnd = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), anchorDate.getDate(), 23, 59, 59, 999);
        } else if (currentRange === 'weekly') {
            // For weekly view, find the ISO week (Monday to Sunday) containing the anchor date
            const anchorDay = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), anchorDate.getDate());
            const dayOfWeek = anchorDay.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            
            // Calculate Monday of the week (ISO week starts on Monday)
            const daysFromMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Sunday = 6 days from Monday
            rangeStart = new Date(anchorDay);
            rangeStart.setDate(anchorDay.getDate() - daysFromMonday);
            rangeStart.setHours(0, 0, 0, 0);
            
            // Calculate Sunday of the week (end of ISO week)
            rangeEnd = new Date(rangeStart);
            rangeEnd.setDate(rangeStart.getDate() + 6);
            rangeEnd.setHours(23, 59, 59, 999);
        } else if (currentRange === 'monthly') {
            // For monthly view, use the full month containing the anchor date
            rangeStart = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), 1, 0, 0, 0, 0);
            rangeEnd = new Date(anchorDate.getFullYear(), anchorDate.getMonth() + 1, 0, 23, 59, 59, 999);
        } else if (currentRange === 'quarterly') {
            // For quarterly view, use the full quarter containing the anchor date
            const qStartMonth = Math.floor(anchorDate.getMonth() / 3) * 3;
            rangeStart = new Date(anchorDate.getFullYear(), qStartMonth, 1, 0, 0, 0, 0);
            rangeEnd = new Date(anchorDate.getFullYear(), qStartMonth + 3, 0, 23, 59, 59, 999);
        } else if (currentRange === 'yearly') {
            // For yearly view, use the full year containing the anchor date
            rangeStart = new Date(anchorDate.getFullYear(), 0, 1, 0, 0, 0, 0);
            rangeEnd = new Date(anchorDate.getFullYear(), 12, 0, 23, 59, 59, 999);
        } else {
            // Default fallback to single day
            rangeStart = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), anchorDate.getDate(), 0, 0, 0, 0);
            rangeEnd = new Date(anchorDate.getFullYear(), anchorDate.getMonth(), anchorDate.getDate(), 23, 59, 59, 999);
        }
        

        
        // Format dates as local date strings to avoid timezone conversion issues
        const formatLocalDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        };
        
        const rangeStartStr = formatLocalDate(rangeStart);
        const rangeEndStr = formatLocalDate(rangeEnd);
        
        console.log('📅 Popular Products Date Range Calculated:', {
            anchorDate: anchorDate.toISOString(),
            currentRange,
            rangeStart: rangeStartStr,
            rangeEnd: rangeEndStr,
            month,
            year,
            category: category,
            isQ3_2025: rangeStartStr.includes('2025-07') && rangeEndStr.includes('2025-09')
        });

        const list = await getPopularList(range, month, year, category, rangeStartStr, rangeEndStr);

        if (!list || list.length === 0) {
            console.log('📭 No Popular Products data to display');
            container.innerHTML = '<div class="odash-empty" style="padding:12px; color:#6b7280; text-align:center;">No value for this timeline.</div>';
            return;
        }
        
        // Calculate total sales from the list
        const totalSalesFromList = list.reduce((sum, product) => sum + (product.sales || 0), 0);
        
        console.log('🎯 Rendering Popular Products UI:', {
            totalItems: list.length,
            displayedItems: list.length, // Show all items instead of limiting to 12
            totalSalesFromAllItems: totalSalesFromList,
            topProducts: list.slice(0, 3).map(p => ({ 
                name: p.name, 
                brand: p.brand, 
                color: p.color, 
                category: p.category,
                sales: p.sales 
            }))
        });
        
        let html = '';
        list.forEach((product, index) => { // Remove slice(0, 12) to show all products
            const isTop3 = index < 3;
            const rankBadge = isTop3 ? `<span class="odash-product-rank">${index + 1}</span>` : '';
            const itemClass = isTop3 ? 'odash-product-item top-product' : 'odash-product-item';
            
            // Format category display with color coding
            const categoryDisplay = product.category ? product.category.charAt(0).toUpperCase() + product.category.slice(1) : '';
            let categoryBadge = '';
            if (categoryDisplay) {
                const categoryClass = product.category.toLowerCase();
                categoryBadge = `<span class="odash-category-badge ${categoryClass}">${categoryDisplay}</span>`;
            }
            
            html += `
                <div class="${itemClass}">
                    <div class="odash-product-main">
                        <span class="odash-product-name">${rankBadge}${product.name}</span>
                        <div class="odash-product-details">
                            <span class="odash-product-brand">${product.brand}</span>
                            <span class="odash-product-color">${product.color}</span>
                            ${categoryBadge}
                        </div>
                    </div>
                    <span class="odash-product-sales">${product.sales} sold</span>
                </div>
            `;
        });
        container.innerHTML = html;
        
        console.log('✨ Popular Products rendered successfully');
        
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
            
            // Get current global filter state to ensure we use the same date range
            const rangeEl = document.getElementById('dbf-range');
            const currentGlobalRange = rangeEl ? rangeEl.value : 'monthly';
            
            // Update our local state to match global state
            currentRange = currentGlobalRange;
            
            console.log('🔄 Category changed, using current filter state:', {
                category: currentCategory,
                range: currentRange,
                globalRange: currentGlobalRange
            });
            
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