// Owner Dashboard JavaScript - Laravel Integration
// CSRF Token setup
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Set up AJAX defaults
if (csrfToken) {
    // For fetch requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (!options.headers) {
            options.headers = {};
        }
        if (options.method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method.toUpperCase())) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        return originalFetch(url, options);
    };
}

// Global variables
let salesChart, reservationChart, inventoryChart;
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

        // Sync report navigation dropdowns
        syncReportDropdowns(sectionId);

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
                loadReservationLogs();
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

    // Sidebar navigation
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
        item.addEventListener('click', function() {
            const section = item.getAttribute('data-section');
            if (section === 'reports') {
                showSection('reports-sales-history');
            } else {
                showSection(section);
            }
        });
    });

    // Report dropdown navigation
    document.querySelectorAll('[id^="reports-nav-dropdown-"]').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            showSection(dropdown.value);
        });
    });

    // Settings tabs
    setupSettingsTabs();

    // Initialize with dashboard
    showSection('inventory-dashboard');
});

// --- Data Loading Functions ---
async function loadSalesHistory(period = 'weekly') {
    try {
        const response = await fetch(`${laravelRoutes.salesHistory}?period=${period}`);
        const data = await response.json();
        
        if (response.ok) {
            updateSalesKPIs(data);
            renderSalesChart(data.salesData, period);
            renderTopSellingProducts(data.topProducts);
            renderSalesTable(data.salesData);
        } else {
            console.error('Failed to load sales history:', data.message);
        }
    } catch (error) {
        console.error('Error loading sales history:', error);
    }
}

async function loadReservationLogs(period = 'weekly') {
    try {
        const response = await fetch(`${laravelRoutes.reservationLogs}?period=${period}`);
        const data = await response.json();
        
        if (response.ok) {
            updateReservationKPIs(data);
            renderReservationChart(data.reservationData, period);
            renderPopularReservedProducts(data.popularProducts);
            renderReservationTable(data.reservationData);
        } else {
            console.error('Failed to load reservation logs:', data.message);
        }
    } catch (error) {
        console.error('Error loading reservation logs:', error);
    }
}

async function loadSupplyLogs() {
    try {
        const response = await fetch(laravelRoutes.supplyLogs);
        const data = await response.json();
        
        if (response.ok) {
            renderSupplyTable(data.supplyData);
        } else {
            console.error('Failed to load supply logs:', data.message);
        }
    } catch (error) {
        console.error('Error loading supply logs:', error);
    }
}

async function loadInventoryOverview() {
    try {
        const response = await fetch(laravelRoutes.inventoryOverview);
        const data = await response.json();
        
        if (response.ok) {
            renderInventoryOverviewTable(data);
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
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadSettings() {
    try {
        const response = await fetch(laravelRoutes.settings);
        const data = await response.json();
        
        if (response.ok) {
            updateSystemStats(data);
        } else {
            console.error('Failed to load settings:', data.message);
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
function renderSalesTable(salesData) {
    const tbody = document.getElementById('sales-history-tbody');
    if (!tbody) return;

    tbody.innerHTML = salesData.map((sale, index) => `
        <tr>
            <td>TXN-${String(index + 1).padStart(4, '0')}</td>
            <td>Customer ${index + 1}</td>
            <td>Sample Product</td>
            <td>${sale.transactions}</td>
            <td>₱${parseFloat(sale.total).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td>Cash</td>
            <td>${formatDate(sale.date)}</td>
            <td><span class="status-badge status-completed">Completed</span></td>
        </tr>
    `).join('');
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

function renderInventoryOverviewTable(data) {
    const tbody = document.getElementById('inventory-overview-tbody');
    if (!tbody) return;

    // Mock implementation - replace with actual data processing
    const mockData = [
        { name: 'Nike Air Max', brand: 'Nike', stock: 50, colors: 'Black, White, Red', sizes: '6-12' },
        { name: 'Adidas Ultraboost', brand: 'Adidas', stock: 35, colors: 'Blue, Gray', sizes: '7-11' },
        { name: 'Puma Suede', brand: 'Puma', stock: 25, colors: 'Green, Yellow', sizes: '6-10' }
    ];

    tbody.innerHTML = mockData.map(item => `
        <tr>
            <td>${item.name}</td>
            <td>${item.brand}</td>
            <td>${item.stock}</td>
            <td>${item.colors}</td>
            <td>${item.sizes}</td>
        </tr>
    `).join('');
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
