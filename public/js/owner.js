// Owner Dashboard JavaScript - Laravel Integration
// CSRF Token setup
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Set up AJAX defaults (wrap fetch to include CSRF and AJAX header)
if (csrfToken) {
    const originalFetch = window.fetch.bind(window);
    window.fetch = function(url, options = {}) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = csrfToken;
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        return originalFetch(url, options);
    };
}

// Global variables
let salesChart, reservationChart, inventoryChart;
let odashLineChart, odashBarChart;
const laravelRoutes = window.laravelData?.routes || {};

// --- Main Section Navigation ---
document.addEventListener('DOMContentLoaded', function() {
    const mainDropdown = document.getElementById('section-dropdown');
    const dashFilter = document.getElementById('dashboard-scope');
    
    // Dashboard and report section IDs
    const dashboardSections = ['inventory-dashboard', 'pos-inventory', 'reservation-inventory'];
    const reportSections = [
        'reports-sales-history',
        'reports-reservation-logs', 
        'reports-supply-logs',
        'reports-inventory-overview'
    ];

    // Show section function
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });

        // Show selected section
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = '';
            section.classList.add('active');
        }

        // Update page title
        updatePageTitle(sectionId);

        // Sync dashboard scope dropdowns
        syncDashboardScopes(sectionId);

    // Sync report navigation dropdowns (legacy) and switch tabs (new)
    syncReportDropdowns(sectionId);
    syncSwitchTabs(sectionId);

        // Load section data
        loadSectionData(sectionId);

        // Update sidebar active state
        updateSidebarActive(sectionId);
    }

    // Make showSection globally available
    window.showSection = showSection;

    // Update page title based on section
    function updatePageTitle(sectionId) {
        const titleElement = document.getElementById('page-title');
        const titles = {
            'inventory-dashboard': 'Dashboard',
            'reports-sales-history': 'Sales History',
            'reports-reservation-logs': 'Reservation Logs', 
            'reports-supply-logs': 'Supply Logs',
            'reports-inventory-overview': 'Inventory Overview',
            'settings': 'Master Controls'
        };
        
        if (titleElement && titles[sectionId]) {
            titleElement.textContent = titles[sectionId];
        }
    }

    // Sync dashboard scope dropdowns
    function syncDashboardScopes(sectionId) {
        document.querySelectorAll('#dashboard-scope').forEach(filter => {
            if (sectionId === 'inventory-dashboard') filter.value = 'inventory';
            else if (sectionId === 'pos-inventory') filter.value = 'pos';
            else if (sectionId === 'reservation-inventory') filter.value = 'reservation';
        });
    }

    // Sync report navigation dropdowns
    function syncReportDropdowns(sectionId) {
        document.querySelectorAll('[id^="reports-nav-dropdown-"]').forEach(dropdown => {
            if (reportSections.includes(sectionId)) {
                dropdown.value = sectionId;
            }
        });
    }

    // Sync switch-style tabs across report sections
    function syncSwitchTabs(sectionId) {
        document.querySelectorAll('.switch-tabs').forEach(group => {
            const buttons = group.querySelectorAll('.switch-tab');
            buttons.forEach(btn => {
                // Only handle tabs that are meant to switch sections (they have data-target).
                // Do not touch switch-tabs that are filters (e.g. data-status) so page-level
                // scripts can manage their active state independently.
                const target = btn.getAttribute('data-target');
                if (target === null || typeof target === 'undefined') return;
                const isActive = target === sectionId;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        });
    }

    // Update sidebar active state
    function updateSidebarActive(sectionId) {
        document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
            item.classList.remove('active');
        });

        if (reportSections.includes(sectionId)) {
            const reportsItem = document.querySelector('[data-section="reports"]');
            if (reportsItem) reportsItem.classList.add('active');
        } else if (sectionId === 'settings') {
            const settingsItem = document.querySelector('[data-section="settings"]');
            if (settingsItem) settingsItem.classList.add('active');
        } else {
            const dashboardItem = document.querySelector('[data-section="inventory-dashboard"]');
            if (dashboardItem) dashboardItem.classList.add('active');
        }
    }

    // Load section data via AJAX
    function loadSectionData(sectionId) {
        switch(sectionId) {
            case 'reports-sales-history':
                loadSalesHistory();
                break;
            case 'reports-reservation-logs':
                // Reservation logs are handled by the reports page script itself
                break;
            case 'reports-supply-logs':
                loadSupplyLogs();
                break;
            case 'reports-inventory-overview':
                loadInventoryOverview();
                break;
            case 'inventory-dashboard':
                loadDashboardData();
                break;
            case 'settings':
                loadSettings();
                break;
        }
    }

    // Event listeners for navigation
    if (mainDropdown) {
        mainDropdown.addEventListener('change', function() {
            showSection(mainDropdown.value);
        });
    }

    // Dashboard filter navigation
    document.querySelectorAll('#dashboard-scope').forEach(filter => {
        filter.addEventListener('change', function() {
            if (filter.value === 'inventory') showSection('inventory-dashboard');
            else if (filter.value === 'pos') showSection('pos-inventory');
            else if (filter.value === 'reservation') showSection('reservation-inventory');
        });
    });

    // Sidebar navigation: handled by anchor hrefs now (reports navigates to separate page)

    // Report dropdown navigation removed (reports are on a separate blade)

    // Settings tabs
    setupSettingsTabs();

    // Initialize with dashboard or a page-provided initial section
    const init = window.initialOwnerSection || 'inventory-dashboard';
    showSection(init);
});

// --- Data Loading Functions ---
async function loadSalesHistory(period = 'weekly') {
    try {
        const response = await fetch(`${laravelRoutes.salesHistory}?period=${period}`);
        const data = await response.json();
        
        if (response.ok) {
            // Cache transactions for client-side filtering on reports page
            window.__salesTransactions = Array.isArray(data.transactions) ? data.transactions : [];
            updateSalesKPIs(data);
            renderSalesChart(data.salesData, period);
            renderTopSellingProducts(data.topProducts);
            // Prefer transactional rows if provided; fallback to aggregated structure
            if (Array.isArray(data.transactions)) {
                renderSalesTable(data.transactions);
            } else {
                renderSalesTable([]);
            }
        } else {
            console.error('Failed to load sales history:', data.message);
        }
    } catch (error) {
        console.error('Error loading sales history:', error);
    }
}

// Apply filters on cached sales transactions and re-render the table
function applySalesFilters({ search = '', sort = 'date-desc', periodUi = '' } = {}) {
    let rows = Array.isArray(window.__salesTransactions) ? [...window.__salesTransactions] : [];
    const todayStr = new Date().toISOString().slice(0,10);
    // periodUi can be: '', 'today', 'week', 'month'
    if (periodUi === 'today') {
        rows = rows.filter(r => (r.sale_datetime || '').slice(0,10) === todayStr);
    }
    // Search across transaction_id, cashier_name, products
    const q = (search || '').toLowerCase();
    if (q) {
        rows = rows.filter(r => (
            String(r.transaction_id || '').toLowerCase().includes(q) ||
            String(r.cashier_name || '').toLowerCase().includes(q) ||
            String(r.products || '').toLowerCase().includes(q)
        ));
    }
    // Sorting
    const num = (v) => Number(v ?? 0);
    if (sort === 'date-asc') rows.sort((a,b)=> new Date(a.sale_datetime) - new Date(b.sale_datetime));
    else if (sort === 'date-desc') rows.sort((a,b)=> new Date(b.sale_datetime) - new Date(a.sale_datetime));
    else if (sort === 'amount-asc') rows.sort((a,b)=> num(a.total_amount) - num(b.total_amount));
    else if (sort === 'amount-desc') rows.sort((a,b)=> num(b.total_amount) - num(a.total_amount));
    renderSalesTable(rows);
}

async function loadReservationLogs(period = 'weekly') {
    try {
        const response = await fetch(`${laravelRoutes.reservationLogs}?period=${period}`);
        const data = await response.json();
        if (response.ok) {
            // Update KPIs using API data if needed
            if (typeof updateReservationKPIs === 'function') {
                updateReservationKPIs(data);
            }
            // Normalize into card items with mock customer/product details
            const apiItems = Array.isArray(data.reservations) ? data.reservations : (Array.isArray(data.reservationData) ? data.reservationData : []);
            // Map API reservation objects into the shape expected by the renderer
            const normalized = apiItems.map(item => ({
                id: item.id || item.reservation_id || '',
                reservation_id: item.reservation_id || item.id || '',
                customer: item.customer_name || item.customer || '',
                email: item.customer_email || '',
                product: item.product_name || item.product || '',
                reservationDate: item.created_at || item.date || '',
                pickupDate: item.pickup_date || item.pickupDate || '',
                status: item.status || 'completed'
            }));

            renderReservationCards(normalized);
            setupReservationStatusSwitch(normalized);
        } else {
            console.error('Failed to load reservation logs:', data.message);
            // Render empty state
            renderReservationCards([]);
            setupReservationStatusSwitch([]);
        }
    } catch (error) {
        console.error('Error loading reservation logs:', error);
        // On error, render empty state (avoid seeding mock data)
        renderReservationCards([]);
        setupReservationStatusSwitch([]);
    }
}

async function loadSupplyLogs() {
    try {
        const response = await fetch(laravelRoutes.supplyLogs);
        const data = await response.json();
        
        if (response.ok) {
            // Cache supply data for client-side filters
            window.__supplyData = Array.isArray(data.supplyData) ? data.supplyData : [];
            renderSupplyTable(data.supplyData);
        } else {
            console.error('Failed to load supply logs:', data.message);
        }
    } catch (error) {
        console.error('Error loading supply logs:', error);
    }
}

function applySupplyFilters({ search = '', sort = 'date-desc' } = {}) {
    let rows = Array.isArray(window.__supplyData) ? [...window.__supplyData] : [];
    const q = (search || '').toLowerCase();
    if (q) {
        rows = rows.filter(r => String(r.date || '').toLowerCase().includes(q) || String(r.supplies || '').toLowerCase().includes(q));
    }
    if (sort === 'date-asc') rows.sort((a,b)=> new Date(a.date) - new Date(b.date));
    else if (sort === 'date-desc') rows.sort((a,b)=> new Date(b.date) - new Date(a.date));
    else if (sort === 'id-asc') rows = rows; // placeholder; IDs are mock in renderer
    else if (sort === 'id-desc') rows = rows; // keep as-is
    renderSupplyTable(rows);
}

async function loadInventoryOverview(source = 'pos', opts = {}) {
    try {
        const url = new URL(laravelRoutes.inventoryOverview, window.location.origin);
        if (source) url.searchParams.set('source', source);
        if (opts.category) url.searchParams.set('category', opts.category);
        if (opts.search) url.searchParams.set('search', opts.search);
        const response = await fetch(url.toString());
        const data = await response.json();
        
        if (response.ok) {
            // Optionally sort client-side
            const sort = opts.sort || '';
            if (Array.isArray(data.items) && sort) {
                const items = [...data.items];
                if (sort === 'name-asc') items.sort((a,b)=> String(a.name||'').localeCompare(String(b.name||'')));
                else if (sort === 'name-desc') items.sort((a,b)=> String(b.name||'').localeCompare(String(a.name||'')));
                else if (sort === 'brand-asc') items.sort((a,b)=> String(a.brand||'').localeCompare(String(b.brand||'')));
                else if (sort === 'brand-desc') items.sort((a,b)=> String(b.brand||'').localeCompare(String(a.brand||'')));
                else if (sort === 'stock-asc') items.sort((a,b)=> Number(a.total_stock||0) - Number(b.total_stock||0));
                else if (sort === 'stock-desc') items.sort((a,b)=> Number(b.total_stock||0) - Number(a.total_stock||0));
                data.items = items;
            }
            renderInventoryOverviewCards(data);
        } else {
            console.error('Failed to load inventory overview:', data.message);
        }
    } catch (error) {
        console.error('Error loading inventory overview:', error);
    }
}

async function loadDashboardData() {
    try {
        // Load dashboard KPIs and charts
        const dashboardData = window.laravelData?.dashboardData || {};
        updateDashboardKPIs(dashboardData);
        renderDashboardCharts(dashboardData);
        renderOwnerCompactDashboard(dashboardData);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadSettings() {
    try {
        // Prefer a dedicated JSON API if provided; otherwise, skip to avoid parsing HTML views as JSON
        const apiUrl = laravelRoutes.settingsApi || laravelRoutes.ownerSettingsApi;
        if (!apiUrl) {
            return; // No API endpoint configured for settings; nothing to fetch
        }

        const response = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        if (!isJson) {
            return; // Avoid JSON parse on non-JSON responses
        }
        const data = await response.json();
        if (response.ok) {
            updateSystemStats(data);
        } else {
            console.error('Failed to load settings:', data.message || response.statusText);
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

// --- Chart Rendering Functions ---
function renderSalesChart(salesData, period) {
    const ctx = document.getElementById('sales-report-chart');
    if (!ctx) return;

    if (salesChart) salesChart.destroy();

    const labels = salesData.map(item => formatDateLabel(item.date, period));
    const data = salesData.map(item => parseFloat(item.total));

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: data,
                borderColor: '#ffffff',
                backgroundColor: 'rgba(255, 255, 255, 0.2)',
                fill: true,
                tension: 0.3,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#ffffff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.3)' },
                    ticks: { color: '#ffffff' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.3)' },
                    ticks: { 
                        color: '#ffffff',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function renderReservationChart(reservationData, period) {
    const ctx = document.getElementById('reservation-report-chart');
    if (!ctx) return;

    if (reservationChart) reservationChart.destroy();

    const labels = reservationData.map(item => formatDateLabel(item.date, period));
    const data = reservationData.map(item => parseInt(item.count));

    reservationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Reservations',
                data: data,
                borderColor: '#ffffff',
                backgroundColor: 'rgba(255, 255, 255, 0.2)',
                fill: true,
                tension: 0.3,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#ffffff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.3)' },
                    ticks: { color: '#ffffff' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.3)' },
                    ticks: { color: '#ffffff' }
                }
            }
        }
    });
}

function renderDashboardCharts(dashboardData) {
    // Render stocks by brand chart
    renderStocksByBrandChart();
    
    // Render products sold by category chart  
    renderProductsSoldChart();
}

// ===== Owner Compact Dashboard Rendering =====
function renderOwnerCompactDashboard(d) {
    // KPIs
    const revenue = Number(d.todaySales || 0);
    const sold = Number(d.totalQuantitySold || 0);
    const resvCompleted = Number(d.completedReservations || 0);
    const resvCancelled = Number(d.cancelledReservations || 0);
    updateElement('odash-kpi-revenue', '₱' + revenue.toLocaleString(undefined, { minimumFractionDigits: 2 }));
    updateElement('odash-kpi-sold', sold);
    updateElement('odash-kpi-resv-completed', resvCompleted);
    updateElement('odash-kpi-resv-cancelled', resvCancelled);

    // Line chart data (mock fallback if backend not provided)
    const lineRangeSel = document.getElementById('odash-line-range');
    if (lineRangeSel) {
        lineRangeSel.onchange = () => buildLineChart(lineRangeSel.value, d);
        buildLineChart(lineRangeSel.value, d);
    }

    // Latest POS (limit 5)
    const latestPosEl = document.getElementById('odash-latest-pos');
    if (latestPosEl) {
        const tx = (d.latestPos || []).slice(0,5);
        if (tx.length === 0) {
            latestPosEl.innerHTML = `<div class="odash-empty-hint">No recent transactions.</div>`;
        } else {
            latestPosEl.innerHTML = tx.map(t => `
                <div class="odash-list-item">
                    <div>
                        <div class="name">TXN-${String(t.id || 0).toString().padStart(4,'0')}</div>
                        <div class="sub">${formatDate(t.date || new Date())}</div>
                    </div>
                    <div class="odash-badge"><i class="fas fa-peso-sign"></i> ${Number(t.total || 0).toLocaleString(undefined,{minimumFractionDigits:2})}</div>
                </div>
            `).join('');
        }
    }

    // Bar chart + stock levels
    const barFilter = document.getElementById('odash-bar-filter');
    if (barFilter) {
        barFilter.onchange = () => buildBarChartAndStock(barFilter.value, d);
        buildBarChartAndStock(barFilter.value, d);
    }

    // Lowest stock list (percentage ascending)
    const lowList = document.getElementById('odash-lowest-stock');
    if (lowList) {
        let items = Array.isArray(d.lowestStock) ? d.lowestStock : [];
        if (!items.length) {
            // Build fallback from popular products or use local mock in bar builder
            const fallback = [];
            const sources = [d.popularProducts?.men, d.popularProducts?.women, d.popularProducts?.accessories];
            sources.forEach(arr => {
                if (Array.isArray(arr)) {
                    arr.forEach(p => fallback.push({ name: p.name, remaining: p.stock ?? 0, total: (p.stock ?? 0) + (p.sold ?? 0) }));
                }
            });
            items = fallback;
        }
        items = (items || [])
            .filter(i => (i?.total ?? 0) > 0)
            .map(i => ({ ...i, pct: (i.remaining / i.total) }))
            .sort((a,b) => a.pct - b.pct)
            .slice(0, 8);

        if (!items.length) {
            lowList.innerHTML = `<div class="odash-empty-hint">No data.</div>`;
        } else {
            lowList.innerHTML = items.map(p => `
                <div class="odash-list-item">
                    <span class="name">${p.name}</span>
                    <span class="sub">${Math.round(p.pct*100)}%</span>
                </div>
            `).join('');
        }
    }
}

function buildLineChart(range='weekly', d={}) {
    const ctx = document.getElementById('odash-line');
    if (!ctx) return;
    if (odashLineChart) odashLineChart.destroy();

    // Prefer server data if provided
    const fromServer = d.salesTrend?.[range];
    let labels, phys, resv;
    if (fromServer && Array.isArray(fromServer.labels)) {
        labels = fromServer.labels;
        phys = fromServer.physical || [];
        resv = fromServer.reservation || [];
    } else {
        // Compact-friendly mock
        const points = range === 'yearly' ? 12 : (range === 'monthly' ? 28 : 7);
        labels = Array.from({length: points}, (_, i) => range==='yearly' ? `M${i+1}` : `D${i+1}`);
        const baseP = range==='yearly' ? 3000 : range==='monthly' ? 1800 : 1400;
        const baseR = range==='yearly' ? 1800 : range==='monthly' ? 1100 : 800;
        phys = labels.map((_,i)=> Math.max(400, Math.round(baseP*(0.8+Math.sin(i/2)*0.1) + Math.random()*300)));
        resv = labels.map((_,i)=> Math.max(200, Math.round(baseR*(0.85+Math.cos(i/3)*0.08) + Math.random()*220)));
    }

    // Determine peaks for highlight
    const pMaxIdx = phys.indexOf(Math.max(...phys));
    const rMaxIdx = resv.indexOf(Math.max(...resv));

    odashLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Physical Sales',
                    data: phys,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    tension: 0.35,
                    pointRadius: labels.map((_,i)=> i===pMaxIdx ? 5 : 3),
                    pointBackgroundColor: labels.map((_,i)=> i===pMaxIdx ? '#1e40af' : '#2563eb'),
                    pointBorderWidth: labels.map((_,i)=> i===pMaxIdx ? 2 : 1),
                },
                {
                    label: 'Reservation Sales',
                    data: resv,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    tension: 0.35,
                    pointRadius: labels.map((_,i)=> i===rMaxIdx ? 5 : 3),
                    pointBackgroundColor: labels.map((_,i)=> i===rMaxIdx ? '#047857' : '#10b981'),
                    pointBorderWidth: labels.map((_,i)=> i===rMaxIdx ? 2 : 1),
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx)=> `₱${ctx.parsed.y.toLocaleString()}` } } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6b7280', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#6b7280', callback: (v)=> '₱'+v.toLocaleString() } }
            }
        }
    });
}

function buildBarChartAndStock(category='men', d={}) {
    const ctx = document.getElementById('odash-bar');
    const stockEl = document.getElementById('odash-stock-levels');
    if (!ctx || !stockEl) return;
    if (odashBarChart) odashBarChart.destroy();

    // Prefer backend payload if provided; otherwise mock catalog
    const serverCat = d.popularProducts?.[category];
    const catalog = serverCat && Array.isArray(serverCat) ? { [category]: serverCat } : {
        men: [
            { name: 'Nike Air Max', sold: 120, stock: 30 },
            { name: 'Adidas Ultraboost', sold: 95, stock: 18 },
            { name: 'Puma Suede', sold: 70, stock: 22 },
            { name: 'Converse Chuck 70', sold: 60, stock: 12 },
            { name: 'Vans Old Skool', sold: 88, stock: 15 },
        ],
        women: [
            { name: 'Nike Air Force 1', sold: 110, stock: 25 },
            { name: 'Adidas NMD', sold: 80, stock: 14 },
            { name: 'Puma Cali', sold: 65, stock: 20 },
            { name: 'Converse Platform', sold: 58, stock: 10 },
            { name: 'Vans Sk8-Hi', sold: 72, stock: 16 },
        ],
        accessories: [
            { name: 'Crease Protectors', sold: 140, stock: 40 },
            { name: 'Shoe Cleaner', sold: 130, stock: 35 },
            { name: 'Laces Pack', sold: 85, stock: 50 },
            { name: 'Insoles', sold: 76, stock: 28 },
            { name: 'Socks (3-Pack)', sold: 92, stock: 60 },
        ],
    };

    const data = catalog[category] || [];
    const labels = data.map(i=> i.name);
    const sold = data.map(i=> i.sold);

    odashBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Units Sold',
                data: sold,
                backgroundColor: '#3b82f6',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx)=> `${ctx.parsed.y} sold` } } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6b7280', maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } },
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#6b7280' } }
            }
        }
    });

    // Stock levels list
    stockEl.innerHTML = data.map(item => `
        <div class="odash-list-item">
            <span class="name">${item.name}</span>
            <span class="sub">${item.stock} left</span>
        </div>
    `).join('');
}

function renderStocksByBrandChart() {
    const ctx = document.getElementById('chart-stocks-brand');
    if (!ctx) return;

    // Mock data - replace with actual data from Laravel
    const data = {
        labels: ['Nike', 'Adidas', 'Puma', 'Converse', 'Vans'],
        datasets: [{
            data: [350, 280, 220, 180, 120],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function renderProductsSoldChart() {
    const ctx = document.getElementById('chart-sold-category');
    if (!ctx) return;

    // Mock data - replace with actual data from Laravel
    const data = {
        labels: ['Men\'s Shoes', 'Women\'s Shoes', 'Kids\' Shoes', 'Accessories'],
        datasets: [{
            data: [45, 35, 15, 5],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
        }]
    };

    new Chart(ctx, {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// --- KPI Update Functions ---
function updateSalesKPIs(data) {
    const totalTransactions = data.salesData.reduce((sum, item) => sum + parseInt(item.transactions), 0);
    const totalRevenue = data.salesData.reduce((sum, item) => sum + parseFloat(item.total), 0);
    const totalQuantity = data.salesData.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);

    updateElement('sales-kpi-transactions', totalTransactions);
    updateElement('sales-kpi-revenue', '₱' + totalRevenue.toLocaleString(undefined, {minimumFractionDigits: 2}));
    updateElement('sales-kpi-quantity', totalQuantity);
}

function updateReservationKPIs(data) {
    const totalReservations = data.reservationData.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    updateElement('reservation-kpi-total', totalReservations);
    updateElement('reservation-kpi-completed', Math.floor(totalReservations * 0.7)); // Mock data
    updateElement('reservation-kpi-cancelled', Math.floor(totalReservations * 0.1)); // Mock data
}

function updateDashboardKPIs(data) {
    updateElement('kpi-total-stocks', data.totalStock || 0);
    updateElement('kpi-inventory-items', data.totalProducts || 0);
    updateElement('kpi-products-sold', '₱' + (data.todaySales || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));
}

// --- Table Rendering Functions ---
function renderSalesTable(transactions) {
    const tbody = document.getElementById('sales-history-tbody');
    if (!tbody) return;

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

    if (!Array.isArray(transactions) || transactions.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="color:#6b7280; text-align:center;">No sales found for the selected period.</td></tr>`;
        return;
    }

    tbody.innerHTML = transactions.map((t) => {
        const products = t.products || '';
        const cashier = t.cashier_name || '—';
        const stype = (t.sale_type || '').toString().toUpperCase();
        return `
        <tr>
            <td>${t.transaction_id || ''}</td>
            <td>${stype}</td>
            <td>${cashier}</td>
            <td>${products}</td>
            <td>${money(t.discount_amount)}</td>
            <td>${money(t.total_amount)}</td>
            <td>${money(t.amount_paid)}</td>
            <td>${money(t.change_given)}</td>
            <td>${formatDateTime(t.sale_datetime || t.date || t.created_at)}</td>
        </tr>`;
    }).join('');
}

function renderReservationTable(reservationData) {
    const tbody = document.getElementById('reservation-logs-tbody');
    if (!tbody) return;

    tbody.innerHTML = reservationData.map((reservation, index) => `
        <tr>
            <td>RSV-${String(index + 1).padStart(4, '0')}</td>
            <td>Customer ${index + 1}</td>
            <td>customer${index + 1}@email.com</td>
            <td>+63 900 000 000${index}</td>
            <td>Sample Product</td>
            <td>${formatDate(reservation.date)}</td>
            <td>${formatDate(reservation.date)}</td>
            <td><span class="status-badge status-pending">Pending</span></td>
        </tr>
    `).join('');
}

// --- Reservation cards (reports) ---
function renderReservationCards(items) {
    const list = document.getElementById('reservation-card-list');
    if (!list) return;
    list.innerHTML = items.map(it => `
        <div class="resv-card">
            <div class="resv-grid">
                <div class="resv-field">
                    <span class="resv-label">Reservation ID</span>
                    <span class="resv-value">${it.id}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Customer Name</span>
                    <span class="resv-value">${it.customer}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Email</span>
                    <span class="resv-value">${it.email}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Item Reserved</span>
                    <span class="resv-value">${it.product}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Status</span>
                    <span class="resv-badge ${it.status}">${capitalize(it.status)}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Reservation Date</span>
                    <span class="resv-value">${formatDate(it.reservationDate)}</span>
                </div>
                <div class="resv-field">
                    <span class="resv-label">Pickup Date</span>
                    <span class="resv-value">${formatDate(it.pickupDate)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

function setupReservationStatusSwitch(items) {
    const switchWrap = document.getElementById('reservation-status-switch');
    if (!switchWrap) return;
    const apply = (status) => {
        const filtered = status === 'all' ? items : items.filter(i => i.status === status);
        renderReservationCards(filtered);
        // Toggle active state
        switchWrap.querySelectorAll('.switch-tab').forEach(btn => {
            const isActive = btn.getAttribute('data-status') === status;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    };
    switchWrap.querySelectorAll('.switch-tab').forEach(btn => {
        btn.addEventListener('click', () => apply(btn.getAttribute('data-status')));
    });
    // Default to 'all'
    apply('all');
}

function capitalize(s){ return (s||'').charAt(0).toUpperCase()+ (s||'').slice(1); }

// Note: mock reservation generator removed — real data should come from the backend API

function renderSupplyTable(supplyData) {
    const tbody = document.getElementById('supply-logs-tbody');
    if (!tbody) return;

    tbody.innerHTML = supplyData.map((supply, index) => `
        <tr>
            <td>SUP-${String(index + 1).padStart(4, '0')}</td>
            <td>Supplier ${index + 1}</td>
            <td>Contact ${index + 1}</td>
            <td>Brand ${index + 1}</td>
            <td>${supply.supplies}</td>
            <td>Philippines</td>
            <td>${formatDate(supply.date)}</td>
            <td>supplier${index + 1}@email.com</td>
            <td>+63 900 000 000${index}</td>
            <td><span class="status-badge status-active">Active</span></td>
        </tr>
    `).join('');
}

// Inventory Overview: render as horizontal cards and wire modal
function renderInventoryOverviewCards(data) {
    const list = document.getElementById('inventory-overview-list');
    if (!list) return;

    const items = Array.isArray(data.items) ? data.items : [];
    const fmt = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
    const formatCurrency = (v) => {
        const n = Number(v ?? 0);
        try { return fmt.format(n); } catch { return `₱${n.toFixed(2)}`; }
    };

    if (!items.length) {
        list.innerHTML = `<div style="text-align:center;color:#6b7280;padding:10px;">No inventory items found.</div>`;
        return;
    }

    // Keep a map for modal population
    window.__invItemsMap = Object.create(null);
    items.forEach(it => { if (it && it.id != null) window.__invItemsMap[String(it.id)] = it; });

    const pill = (text) => `<span class="inv-size">${text}</span>`;

    list.innerHTML = items.map(item => {
        const sizes = (item.sizes || '').split(',').map(s=>s.trim()).filter(Boolean).slice(0, 20).map(pill).join(' ');
        const img = item.image_url ? `<img src="${item.image_url}" alt="${item.name||''}">` : '';
            return `
            <div class="inv-card" data-id="${item.id}">
                <div class="inv-thumb">${img}</div>
                <div>
                    <div class="inv-info">
                        <div class="inv-field"><span class="inv-label">Name:</span><span class="inv-value inv-name">${item.name || ''}</span></div>
                        <div class="inv-field"><span class="inv-label">Category:</span><span class="inv-value"><span class="inv-badge">${item.category || 'N/A'}</span></span></div>
                        <div class="inv-field"><span class="inv-label">Brand:</span><span class="inv-value inv-brand">${String(item.brand||'').toUpperCase()}</span></div>
                        <div class="inv-field"><span class="inv-label">Color:</span><span class="inv-value"><span class="inv-badge gray">${item.color || 'N/A'}</span></span></div>
                        <div class="inv-field"><span class="inv-label">Sizes:</span><span class="inv-value"><div class="inv-sizes">${sizes || '<span style="color:#64748b;font-size:12px;">None</span>'}</div></span></div>
                        <div class="inv-field"><span class="inv-label">Stock:</span><span class="inv-value inv-stock">${Number(item.total_stock||0)} in stock</span></div>
                    </div>
                </div>
                <div class="inv-right">
                    <div class="inv-price">Price:&nbsp; ${formatCurrency(item.price)}</div>
                    <button class="btn-view" data-id="${item.id}">View</button>
                </div>
            </div>`;
    }).join('');

    // Wire up buttons
    list.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const it = window.__invItemsMap?.[String(id)];
            if (it) openProductDetailModal(it);
        });
    });
}

function openProductDetailModal(item) {
    const modal = document.getElementById('product-detail-modal');
    if (!modal) return;
    const imgEl = modal.querySelector('#pd-image');
    const nameEl = modal.querySelector('#pd-name');
    const brandEl = modal.querySelector('#pd-brand');
    const categoryEl = modal.querySelector('#pd-category');
    const colorEl = modal.querySelector('#pd-color');
    const priceEl = modal.querySelector('#pd-price');
    const stockEl = modal.querySelector('#pd-stock');
    const sizesEl = modal.querySelector('#pd-sizes');

    const fmt = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
    const formatCurrency = (v) => { try { return fmt.format(Number(v||0)); } catch { return `₱${Number(v||0).toFixed(2)}`; } };

    if (imgEl) imgEl.src = item.image_url || '';
    if (nameEl) nameEl.textContent = item.name || '';
    if (brandEl) brandEl.textContent = String(item.brand || '').toUpperCase();
    if (categoryEl) categoryEl.textContent = item.category || '-';
    if (colorEl) colorEl.textContent = item.color || '-';
    if (priceEl) priceEl.textContent = formatCurrency(item.price);
    if (stockEl) stockEl.textContent = `${Number(item.total_stock||0)} items`;
    if (sizesEl) {
        // Prefer sizes_stock (size:stock) when available to show per-size stock counts.
        const sizesStockRaw = item.sizes_stock || item.sizes || '';
        let sizeChips = [];
        if (sizesStockRaw) {
            // sizes_stock expected format: "5:3,5.5:2,6:3"
            sizeChips = sizesStockRaw.split(',').map(pair => {
                const [sizeRaw, stockRaw] = pair.split(':').map(p => p && p.trim());
                const size = sizeRaw || '';
                const stock = (typeof stockRaw !== 'undefined' && stockRaw !== null && stockRaw !== '') ? Number(stockRaw) : null;
                if (stock === null || isNaN(stock)) {
                    return `<span style="background:#eef2ff;color:#1e3a8a;padding:6px 10px;border-radius:8px;font-weight:700;font-size:.85rem;">${size}</span>`;
                }
                return `<span style="background:#fff5f8;border:1px solid #e6e6e6;color:#0f172a;padding:6px 10px;border-radius:8px;font-weight:700;font-size:.85rem;">${size} <span style="color:#16a34a;font-weight:800;margin-left:6px;">(${stock} in stock)</span></span>`;
            });
        }
        sizesEl.innerHTML = sizeChips.join(' ');
    }

    modal.style.display = 'block';
    const closeBtn = modal.querySelector('#pd-close');
    const close = () => { modal.style.display = 'none'; };
    closeBtn?.addEventListener('click', close, { once: true });
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); }, { once: true });
}

// --- Product List Rendering ---
function renderTopSellingProducts(topProducts) {
    const listElement = document.getElementById('top-selling-products-list');
    if (!listElement) return;

    listElement.innerHTML = topProducts.map(product => `
        <div class="most-sold-item">
            <span class="product-name">${product.name}</span>
            <span class="product-qty">${product.total_sold} sold</span>
        </div>
    `).join('');
}

function renderPopularReservedProducts(popularProducts) {
    const listElement = document.getElementById('popular-reserved-products-list');
    if (!listElement) return;

    listElement.innerHTML = popularProducts.map(product => `
        <div class="most-sold-item">
            <span class="product-name">${product.name}</span>
            <span class="product-qty">${product.reservation_count} reserved</span>
        </div>
    `).join('');
}

// --- Settings Functions ---
function setupSettingsTabs() {
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panels
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding panel
            this.classList.add('active');
            const targetPanel = document.getElementById(`settings-panel-${targetTab}`);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
        });
    });
}

function updateSystemStats(data) {
    // Update system statistics in settings
    console.log('System stats:', data);
}

// --- Event Listeners for Filters ---
document.addEventListener('DOMContentLoaded', function() {
    // Sales report filter
    const salesReportFilter = document.getElementById('sales-report-filter');
    if (salesReportFilter) {
        salesReportFilter.addEventListener('change', function() {
            loadSalesHistory(this.value);
        });
    }

    // Reservation report filter
    const reservationReportFilter = document.getElementById('reservation-report-filter');
    if (reservationReportFilter) {
        reservationReportFilter.addEventListener('change', function() {
            loadReservationLogs(this.value);
        });
    }

    // Search filters
    setupSearchFilters();
});

function setupSearchFilters() {
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Implement search functionality
            console.log('Searching for:', this.value);
        });
    });
}

// --- Utility Functions ---
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateLabel(dateString, period) {
    const date = new Date(dateString);
    
    switch(period) {
        case 'daily':
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        case 'weekly':
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        case 'monthly':
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
        case 'yearly':
            return date.getFullYear().toString();
        default:
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

// Initialize dashboard
function initializeDashboard() {
    console.log('Owner dashboard initialized with Laravel data:', window.laravelData);
}

// Export functions for global access
window.showSection = showSection || function() {};
window.initializeDashboard = initializeDashboard;
