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
        .notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
        .notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:200; }
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
        
        @media (max-width: 1024px) {
            .odash-row-forecast { grid-template-columns: 1fr; }
            .odash-chart-shell, .odash-gauge-shell { height:260px; max-height:260px; }
            .odash-row-products { grid-template-columns: 1fr; }
        }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                    <select id="odash-forecast-range" class="odash-select">
                                        <option value="day" selected>Day</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="odash-chart-shell">
                            <canvas id="odash-forecast"></canvas>
                        </div>
                    </div>

                    <!-- Reservation Gauge (Mock) -->
                    <div class="odash-card">
                        <div class="odash-card-header">
                            <div class="odash-title">Reservations Status</div>
                        </div>
                        <div class="odash-gauge-shell" style="position:relative;">
                            <canvas id="odash-resv-gauge" style="max-width:220px; width:100%; height:auto;"></canvas>
                            <div id="odash-gauge-center" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center; pointer-events:none; margin-top:-20px;">
                                <div style="color:#64748b; font-size:13px; font-weight:500; margin-bottom:4px;">Total</div>
                                <div style="color:#1e3a8a; font-size:28px; font-weight:700; line-height:1;" id="odash-resv-total">50</div>
                            </div>
                            <div class="odash-gauge-legend" style="margin-top:16px; justify-content:center; flex-wrap:wrap;">
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
                        <div class="odash-card-header">
                            <div class="odash-title">Popular Products</div>
                        </div>
                        <div class="odash-products-list" id="odash-popular-products">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Stock Levels Bar Chart -->
                    <div class="odash-card">
                        <div class="odash-card-header" style="justify-content:space-between;">
                            <div class="odash-title">Stock Levels</div>
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
        settings: '{{ route("owner.settings") }}'
    }
};

// Initialize dashboard when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    updateDateTime();
    setInterval(updateDateTime, 1000); // Update time every second
    // Simple notifications toggle
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

    // Initialize mock charts for forecast and reservations
    initOwnerForecastCharts();
    initPopularProducts();
    initStockLevels();
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
    const rangeSelect = document.getElementById('odash-forecast-range');
    const typeSelect = document.getElementById('odash-forecast-type');
    const legendBox = document.getElementById('odash-forecast-legend');
    let forecastChart;
    let currentMode = (typeSelect && typeSelect.value) ? typeSelect.value : 'sales';

    function getForecastData(range, mode) {
        if (mode === 'demand') {
            if (range === 'weekly') {
                return {
                    labels: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                    menValues:   [50, 62, 70, 58, 78, 95, 88],
                    womenValues: [44, 48, 52, 47, 60, 75, 68],
                    accValues:   [20, 25, 28, 24, 30, 35, 32]
                };
            } else if (range === 'monthly') {
                const labels = Array.from({length: 30}, (_, i) => `${i+1}`);
                return {
                    labels,
                    menValues:   [20,24,22,28,35,30,32,40,44,38,34,42,48,46,52,49,56,52,58,60,57,62,60,64,66,64,70,75,72,78],
                    womenValues: [18,20,19,23,27,24,26,30,34,31,29,33,36,36,40,38,42,40,44,45,44,48,45,48,50,50,52,55,53,55],
                    accValues:   [8,  9, 10, 11, 12, 11, 12, 14, 15, 14, 13, 14, 16, 16, 18, 17, 18, 18, 19, 20, 19, 21, 20, 21, 22, 22, 23, 24, 23, 24]
                };
            }
            // default day (hourly)
            return {
                labels: ['9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM'],
                menValues:   [6, 9, 11, 13, 18, 14, 12, 11, 15, 20, 14, 10],
                womenValues: [5, 7,  9, 11, 16, 12, 10,  9, 12, 16, 11,  8],
                accValues:   [2, 3,  4,  5,  6,  5,  5,  4,  6,  7,  5,  4]
            };
        }
        // sales mode
        if (range === 'weekly') {
            return {
                labels: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                posValues: [80, 95, 110, 90, 120, 150, 130],
                resvValues: [40, 55, 60, 50, 70, 90, 80]
            };
        } else if (range === 'monthly') {
            // 30 days mock
            const labels = Array.from({length: 30}, (_, i) => `${i+1}`);
            const posValues = [40,48,42,52,62,50,58,72,78,68,62,75,85,80,90,88,98,95,100,105,102,110,108,115,118,115,125,130,128,135];
            const resvValues = [20,24,23,28,33,28,30,38,42,37,36,40,45,45,50,47,52,50,55,57,56,60,57,60,62,63,65,70,67,70];
            return { labels, posValues, resvValues };
        }
        // default day (hourly)
        return {
            labels: ['9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM'],
            posValues: [8, 12, 16, 20, 32, 23, 21, 18, 26, 36, 25, 16],
            resvValues: [4, 6, 8, 10, 16, 12, 11, 10, 14, 19, 13, 9]
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

    function updateForecast(range, mode) {
        const data = getForecastData(range, mode);
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
    }

    // Initialize with current select value or 'day'
    const initialRange = rangeSelect && rangeSelect.value ? rangeSelect.value : 'day';
    setLegend(currentMode);
    updateForecast(initialRange, currentMode);

    // Handle range changes
    if (rangeSelect) {
        rangeSelect.addEventListener('change', () => updateForecast(rangeSelect.value, currentMode));
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            // Do NOT update currentMode here. Let updateForecast decide destruction based on previous mode.
            const newMode = typeSelect.value;
            setLegend(newMode);
            updateForecast(rangeSelect ? rangeSelect.value : 'day', newMode);
        });
    }

    // Reservation gauge (live values)
    const resvCanvas = document.getElementById('odash-resv-gauge');
    if (resvCanvas) {
        const ctx2 = resvCanvas.getContext('2d');
        const dd = (window.laravelData && window.laravelData.dashboardData) ? window.laravelData.dashboardData : {};
        const completed = Number(dd.completedReservations || 0);
        const cancelled = Number(dd.cancelledReservations || 0);
        const pending = Number(dd.activeReservations || 0); // active pending
        const total = completed + cancelled + pending;
        const safePct = (n, d) => (d > 0 ? Math.round((n / d) * 100) : 0);
        const completedPct = safePct(completed, total);
        const cancelledPct = safePct(cancelled, total);
        const pendingPct = safePct(pending, total);
        
        // Update center text and percentages
        document.getElementById('odash-resv-total').textContent = total;
        document.getElementById('odash-resv-completed-pct').textContent = completedPct + '%';
        document.getElementById('odash-resv-cancelled-pct').textContent = cancelledPct + '%';
        document.getElementById('odash-resv-pending-pct').textContent = pendingPct + '%';
        
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Cancelled', 'Pending'],
                datasets: [{
                    data: [completed, cancelled, pending],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                    borderWidth: 0,
                    borderRadius: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '75%',
                rotation: -90,
                circumference: 180,
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        enabled: true,
                        backgroundColor: '#1e3a8a',
                        titleColor: '#fff',
                        bodyColor: '#bfdbfe',
                        borderColor: '#3b82f6',
                        borderWidth: 1
                    }
                }
            }
        });
    }
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
    if (!container) return;

    // Prefer live dashboard data if available
    const dd = (window.laravelData && window.laravelData.dashboardData) ? window.laravelData.dashboardData : {};
    let list = [];
    if (dd.popularProducts && typeof dd.popularProducts === 'object') {
        ['men','women','accessories'].forEach(cat => {
            const arr = dd.popularProducts[cat];
            if (Array.isArray(arr)) {
                arr.forEach(p => list.push({ name: p.name || 'Unknown', sales: Number(p.sold ?? p.total_sold ?? p.sales ?? 0) }));
            }
        });
        list.sort((a,b)=> b.sales - a.sales);
    }
    // Fallback to mock if backend didn't provide
    if (!list.length) {
        list = [
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
}

// ===== Stock Levels Horizontal Bar Chart (Mock Data) =====
function initStockLevels() {
    const stockCanvas = document.getElementById('odash-stock-chart');
    const categorySelect = document.getElementById('odash-stock-category');
    let stockChart;

    async function fetchStockData(category) {
        try {
            const url = new URL(window.laravelData?.routes?.inventoryOverview, window.location.origin);
            url.searchParams.set('source', 'pos');
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
    }

    const initialCategory = categorySelect && categorySelect.value ? categorySelect.value : 'men';
    updateStockChart(initialCategory);
    if (categorySelect) {
        categorySelect.addEventListener('change', () => updateStockChart(categorySelect.value));
    }
}
</script>

</body>
</html>
