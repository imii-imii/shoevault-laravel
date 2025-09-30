// --- Sync Main Section Dropdown and Dashboard Filter ---
document.addEventListener('DOMContentLoaded', function() {
    const mainDropdown = document.getElementById('section-dropdown');
    const dashFilter = document.getElementById('dashboard-scope');
    const dashFilterContainer = document.querySelector('.dash-filter');
    // List of dashboard and report section IDs
    const dashboardSections = ['inventory-dashboard', 'pos-inventory', 'reservation-inventory'];
    const reportSections = [
        'reports-sales-history',
        'reports-reservation-logs',
        'reports-supply-logs',
        'reports-inventory-overview'
    ];
    function showSection(sectionId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = '';
            section.classList.add('active');
        }
        // Sync all dashboard-scope dropdowns to match the current section
        document.querySelectorAll('#dashboard-scope').forEach(function(filter) {
            if (sectionId === 'inventory-dashboard') filter.value = 'inventory';
            else if (sectionId === 'pos-inventory') filter.value = 'pos';
            else if (sectionId === 'reservation-inventory') filter.value = 'reservation';
        });
        // Sync report nav dropdowns
        document.querySelectorAll('[id^="reports-nav-dropdown-"]').forEach(function(drop) {
            if (reportSections.includes(sectionId)) drop.value = sectionId;
        });
        // Sync mainDropdown if present
        if (mainDropdown) {
            mainDropdown.value = sectionId;
        }
    }
    window.showSection = showSection;

    if (mainDropdown) {
        mainDropdown.addEventListener('change', function() {
            showSection(mainDropdown.value);
        });
    }
        // Support multiple dashboard filters (one per section)
        document.querySelectorAll('#dashboard-scope').forEach(function(filter) {
            filter.addEventListener('change', function() {
                if (filter.value === 'inventory') showSection('inventory-dashboard');
                else if (filter.value === 'pos') showSection('pos-inventory');
                else if (filter.value === 'reservation') showSection('reservation-inventory');
            });
        });
    // Initial state: show inventory dashboard
    showSection('inventory-dashboard');

    // Sidebar nav logic for Reports
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const section = item.getAttribute('data-section');
            if (section === 'reports') {
                showSection('reports-sales-history');
            }
        });
    });

    // Reports dropdown navigation logic
    document.querySelectorAll('[id^="reports-nav-dropdown-"]').forEach(function(drop) {
        drop.addEventListener('change', function() {
            showSection(drop.value);
        });
    });
});
// --- Dashboard Scope Filter Navigation ---
document.addEventListener('DOMContentLoaded', function() {
    const dashFilter = document.getElementById('dashboard-scope');
    // Remove Add Product button logic and ensure dropdown selected option is correct
    if (dashFilter) {
        dashFilter.addEventListener('change', function() {
            const val = dashFilter.value;
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
                section.classList.remove('active');
            });
            if (val === 'inventory') {
                document.getElementById('inventory-dashboard').style.display = '';
                document.getElementById('inventory-dashboard').classList.add('active');
                dashFilter.value = 'inventory';
            } else if (val === 'pos') {
                document.getElementById('pos-inventory').style.display = '';
                document.getElementById('pos-inventory').classList.add('active');
                dashFilter.value = 'pos';
            } else if (val === 'reservation') {
                document.getElementById('reservation-inventory').style.display = '';
                document.getElementById('reservation-inventory').classList.add('active');
                dashFilter.value = 'reservation';
            }
        });
        // Initial state: show inventory dashboard and set dropdown
        document.querySelectorAll('.content-section').forEach(section => {
            if (section.id === 'inventory-dashboard') {
                section.style.display = '';
                section.classList.add('active');
                dashFilter.value = 'inventory';
            } else {
                section.style.display = 'none';
                section.classList.remove('active');
            }
        });
    }
});
// --- POS Dashboard Data Example ---
const posTransactions = [
    { id: 1, date: '2025-09-01', amount: 5000, qty: 3, products: [{ name: 'Air Max', qty: 2 }, { name: 'Stan Smith', qty: 1 }] },
    { id: 2, date: '2025-09-02', amount: 3500, qty: 2, products: [{ name: 'Superstar', qty: 2 }] },
    { id: 3, date: '2025-09-03', amount: 8000, qty: 5, products: [{ name: 'Slides', qty: 3 }, { name: 'Classic', qty: 2 }] },
    { id: 4, date: '2025-09-04', amount: 12000, qty: 7, products: [{ name: 'Air Max', qty: 4 }, { name: 'Stan Smith', qty: 3 }] },
    { id: 5, date: '2025-09-05', amount: 4000, qty: 2, products: [{ name: 'Classic', qty: 2 }] },
];

function updatePOSKPIs() {
    const totalTransactions = posTransactions.length;
    const totalRevenue = posTransactions.reduce((sum, t) => sum + t.amount, 0);
    const totalQty = posTransactions.reduce((sum, t) => sum + t.qty, 0);
    document.getElementById('pos-kpi-transactions').innerHTML = `<i class="fas fa-receipt"></i> ${totalTransactions}`;
    document.getElementById('pos-kpi-revenue').innerHTML = `<i class="fas fa-money-bill-wave"></i> ₱${totalRevenue.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    document.getElementById('pos-kpi-quantity').innerHTML = `<i class="fas fa-boxes"></i> ${totalQty}`;
}

// --- Sales Report Chart ---
function getSalesReportData(filter) {
    // Mock: group by week, quarter, year
    if (filter === 'weekly') {
        return {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            data: [12000, 8000, 15000, 10000]
        };
    } else if (filter === 'quarterly') {
        return {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            data: [32000, 28000, 35000, 30000]
        };
    } else {
        return {
            labels: ['2023', '2024', '2025'],
            data: [120000, 135000, 142000]
        };
    }
}

let posSalesChart;
function renderSalesReportChart(filter = 'weekly') {
    const ctx = document.getElementById('pos-sales-report-chart').getContext('2d');
    const report = getSalesReportData(filter);
    if (posSalesChart) posSalesChart.destroy();
    // Linear regression calculation
    function linearRegression(y) {
        const n = y.length;
        const x = Array.from({length: n}, (_, i) => i+1);
        const sumX = x.reduce((a,b) => a+b, 0);
        const sumY = y.reduce((a,b) => a+b, 0);
        const sumXY = x.reduce((a,_,i) => a + x[i]*y[i], 0);
        const sumX2 = x.reduce((a,xi) => a + xi*xi, 0);
        const slope = (n*sumXY - sumX*sumY) / (n*sumX2 - sumX*sumX);
        const intercept = (sumY - slope*sumX) / n;
        return x.map(xi => slope*xi + intercept);
    }
    const regression = linearRegression(report.data);
    posSalesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: report.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: report.data,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    borderRadius: 8,
                    order: 1,
                },
                {
                    label: 'Trend',
                    data: regression,
                    type: 'line',
                    fill: false,
                    borderColor: '#00e676',
                    borderWidth: 3,
                    pointRadius: 0,
                    tension: 0.2,
                    order: 2,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                title: { display: false }
            },
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

// --- Most Sold Products List ---
function getMostSoldProducts() {
    // Aggregate mock data
    const productMap = {};
    posTransactions.forEach(t => {
        t.products.forEach(p => {
            productMap[p.name] = (productMap[p.name] || 0) + p.qty;
        });
    });
    // Sort by qty desc
    return Object.entries(productMap)
        .map(([name, qty]) => ({ name, qty }))
        .sort((a, b) => b.qty - a.qty);
}

function renderMostSoldProductsList() {
    const list = document.getElementById('most-sold-products-list');
    if (!list) return;
    list.innerHTML = '';
    const products = getMostSoldProducts();
    products.forEach(p => {
        const item = document.createElement('div');
        item.className = 'most-sold-item';
        item.innerHTML = `<span class="product-name">${p.name}</span><span class="product-qty">${p.qty} sold</span>`;
        list.appendChild(item);
    });
}

// --- POS Dashboard Init ---
document.addEventListener('DOMContentLoaded', function() {
    updatePOSKPIs();
    renderSalesReportChart();
    renderMostSoldProductsList();
    const filter = document.getElementById('sales-report-filter');
    if (filter) {
        filter.addEventListener('change', function() {
            renderSalesReportChart(filter.value);
        });
    }
});
// --- Dashboard Data Example ---
// Replace with your actual inventory and sales data source
const inventoryData = [
    { id: 1, name: 'Air Max', brand: 'Nike', category: 'men', stock: 20, price: 5000, sold: 12 },
    { id: 2, name: 'Superstar', brand: 'Adidas', category: 'women', stock: 15, price: 4500, sold: 8 },
    { id: 3, name: 'Classic', brand: 'Converse', category: 'kids', stock: 10, price: 3000, sold: 5 },
    { id: 4, name: 'Slides', brand: 'Nike', category: 'accessories', stock: 25, price: 1500, sold: 20 },
    { id: 5, name: 'Stan Smith', brand: 'Adidas', category: 'men', stock: 18, price: 4800, sold: 10 },
];

// --- KPI Cards ---
function updateKPIs() {
    const totalStocks = inventoryData.reduce((sum, item) => sum + item.stock, 0);
    const inventoryItems = inventoryData.length;
    const productsSold = inventoryData.reduce((sum, item) => sum + item.sold, 0);
    document.getElementById('kpi-total-stocks').innerHTML = `<i class="fas fa-cube"></i> ${totalStocks}`;
    document.getElementById('kpi-inventory-items').innerHTML = `<i class="fas fa-list-ul"></i> ${inventoryItems}`;
    document.getElementById('kpi-products-sold').innerHTML = `<i class="fas fa-shopping-bag"></i> ${productsSold}`;
}

// --- Bar Chart: Product Stocks by Brand ---
function renderStocksByBrandChart() {
    // Add more brands for demo
    const demoBrands = ['Nike', 'Adidas', 'Puma', 'Reebok', 'New Balance', 'Converse', 'Vans', 'Fila'];
    const brandMap = {};
    demoBrands.forEach(b => { brandMap[b] = Math.floor(Math.random() * 100) + 10; });
    const brands = Object.keys(brandMap);
    const stocks = Object.values(brandMap);
    const ctx = document.getElementById('chart-stocks-brand').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: brands,
            datasets: [{
                label: 'Stocks',
                data: stocks,
                backgroundColor: 'rgba(255,255,255,0.95)',
                borderColor: 'rgba(255,255,255,0.95)',
                borderWidth: 0,
                borderRadius: 10,
                barPercentage: 0.55,
                categoryPercentage: 0.65
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.15)' },
                    ticks: { color: '#fff', font: { weight: 'bold' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.15)' },
                    ticks: { color: '#fff', font: { weight: 'bold' } }
                }
            }
        }
    });
}

// --- Pie Chart: Products Sold by Category ---
function renderSoldByCategoryChart() {
    const categoryMap = { men: 0, women: 0, kids: 0, accessories: 0 };
    inventoryData.forEach(item => {
        if (categoryMap[item.category] !== undefined) {
            categoryMap[item.category] += item.sold;
        }
    });
    const categories = ['Men', 'Women', 'Accessories'];
    const soldCounts = [categoryMap.men, categoryMap.women, categoryMap.accessories];
    const ctx = document.getElementById('chart-sold-category').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categories,
            datasets: [{
                label: 'Products Sold',
                data: soldCounts,
                backgroundColor: [
                    '#00ab9ad0',   // teal
                    '#00d4ff',   // cyan
                    '#aff8d5ff',   // green
                ],
                borderColor: 'transparent',
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#fff', font: { weight: 'bold' } }
                },
                title: { display: false }
            }
        }
    });
}

// --- Initialize Dashboard ---
document.addEventListener('DOMContentLoaded', function() {
    updateKPIs();
    renderStocksByBrandChart();
    renderSoldByCategoryChart();
});
// Global variables
// --- Mock User Accounts Data ---
let userAccounts = [
    {
        id: 1,
        name: 'Juan Dela Cruz',
        role: 'Manager',
        enabled: true,
        img: 'profile.png'
    },
    {
        id: 2,
        name: 'Maria Santos',
        role: 'Cashier',
        enabled: false,
        img: 'profile.png'
    },
    {
        id: 3,
        name: 'Pedro Reyes',
        role: 'Owner',
        enabled: true,
        img: 'profile.png'
    }
];

function renderUserList() {
    const list = document.getElementById('user-list');
    if (!list) return;
    const search = (document.getElementById('user-search')?.value || '').toLowerCase();
    list.innerHTML = '';
    let filtered = userAccounts.filter(u => u.name.toLowerCase().includes(search));
    filtered.forEach(user => {
        const card = document.createElement('div');
        card.className = 'user-card';
        card.style = 'display:flex; align-items:center; gap:24px; background:#fff; border-radius:12px; box-shadow:0 2px 8px #0001; padding:18px 24px;';
            card.innerHTML = `
                <div style="width:56px; height:56px; border-radius:50%; overflow:hidden; background:#eee; display:flex; align-items:center; justify-content:center;">
                    <img src="${user.img}" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div style="flex:1; min-width:120px; font-weight:600;">${user.name}</div>
                <div>
                    <select class="filter-select user-role-select" data-id="${user.id}" style="min-width:110px;">
                        <option${user.role==='Manager'?' selected':''}>Manager</option>
                        <option${user.role==='Cashier'?' selected':''}>Cashier</option>
                    </select>
                </div>
                <div>
                    <select class="filter-select user-enabled-select" data-id="${user.id}" style="min-width:90px;">
                        <option value="true"${user.enabled?' selected':''}>Enabled</option>
                        <option value="false"${!user.enabled?' selected':''}>Disabled</option>
                    </select>
                </div>
                <div style="min-width:160px; display:flex; align-items:center; gap:6px;">
                    <input type="password" class="user-password-field" data-id="${user.id}" value="${user.password || ''}" readonly style="width:100px; border:none; background:#f5f5f5; padding:4px 8px; border-radius:6px; font-size:1em;">
                    <button class="btn btn-secondary user-password-eye" data-id="${user.id}" style="padding:2px 8px;"><i class="fas fa-eye"></i></button>
                </div>
                <button class="btn btn-danger user-remove-btn" data-id="${user.id}"><i class="fas fa-trash"></i> Remove</button>
            `;
        list.appendChild(card);
    });
}

function setupUserAccountEvents() {
    const search = document.getElementById('user-search');
    if (search) search.addEventListener('input', renderUserList);
    const addBtn = document.getElementById('add-user-btn');
    if (addBtn) addBtn.addEventListener('click', function() {
        document.getElementById('add-user-modal').style.display = 'flex';
    });
    // Modal close
    document.getElementById('close-add-user-modal').onclick = function() {
        document.getElementById('add-user-modal').style.display = 'none';
    };
    // Add user confirm
    document.getElementById('add-user-confirm-btn').onclick = function() {
        const name = document.getElementById('add-user-fullname').value.trim();
        const username = document.getElementById('add-user-username').value.trim();
        const email = document.getElementById('add-user-email').value.trim();
        const role = document.getElementById('add-user-role').value;
        if (!name || !username || !email) {
            alert('Please fill in all fields.');
            return;
        }
        // Generate random password
        const password = Array(10).fill(0).map(() => String.fromCharCode(Math.floor(Math.random()*26)+97)).join('') + Math.floor(Math.random()*1000);
        userAccounts.push({
            id: Date.now(),
            name,
            role,
            enabled: true,
            img: 'profile.png',
            password,
            passwordVisible: false
        });
        document.getElementById('add-user-modal').style.display = 'none';
        document.getElementById('add-user-fullname').value = '';
        document.getElementById('add-user-username').value = '';
        document.getElementById('add-user-email').value = '';
        document.getElementById('add-user-role').value = 'Manager';
        renderUserList();
    };
    // Password eye toggle
    document.getElementById('user-list').addEventListener('click', function(e) {
        if (e.target.closest('.user-password-eye')) {
            const id = +e.target.closest('.user-password-eye').getAttribute('data-id');
            const input = document.querySelector(`.user-password-field[data-id='${id}']`);
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                } else {
                    input.type = 'password';
                }
            }
        }
    });
    document.getElementById('user-list').addEventListener('change', function(e) {
        if (e.target.classList.contains('user-role-select')) {
            const id = +e.target.getAttribute('data-id');
            const user = userAccounts.find(u => u.id === id);
            if (user) user.role = e.target.value;
        }
        if (e.target.classList.contains('user-enabled-select')) {
            const id = +e.target.getAttribute('data-id');
            const user = userAccounts.find(u => u.id === id);
            if (user) user.enabled = e.target.value === 'true';
        }
        renderUserList();
    });
    document.getElementById('user-list').addEventListener('click', function(e) {
        if (e.target.closest('.user-remove-btn')) {
            const id = +e.target.closest('.user-remove-btn').getAttribute('data-id');
            userAccounts = userAccounts.filter(u => u.id !== id);
            renderUserList();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('user-list')) {
        renderUserList();
        setupUserAccountEvents();
    }
});
// --- Mock Sales History Data ---
// --- Mock Reservation Logs Data ---
// --- Mock Supply Logs Data ---
// --- Mock Inventory Overview Data ---
let inventoryOverview = [
    {
        name: 'Nike Air Max',
        brand: 'Nike',
        stock: 50,
        colors: 'Red, Black',
        sizes: '8, 9, 10',
        category: 'men'
    },
    {
        name: 'Adidas Ultraboost',
        brand: 'Adidas',
        stock: 30,
        colors: 'White, Blue',
        sizes: '7, 8, 9',
        category: 'women'
    },
    {
        name: 'Puma RS-X',
        brand: 'Puma',
        stock: 20,
        colors: 'Green, Black',
        sizes: '6, 7, 8',
        category: 'kids'
    },
    {
        name: 'Converse Chuck Taylor',
        brand: 'Converse',
        stock: 40,
        colors: 'Black, White',
        sizes: '8, 9, 10, 11',
        category: 'men'
    },
    {
        name: 'Nike Cap',
        brand: 'Nike',
        stock: 15,
        colors: 'Black',
        sizes: 'One Size',
        category: 'accessories'
    }
];

function renderInventoryOverviewTable() {
    const tbody = document.getElementById('inventory-overview-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    let filtered = filterInventoryOverview();
    filtered.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.name}</td>
            <td>${row.brand}</td>
            <td>${row.stock}</td>
            <td>${row.colors}</td>
            <td>${row.sizes}</td>
        `;
        tbody.appendChild(tr);
    });
}

function filterInventoryOverview() {
    let filtered = [...inventoryOverview];
    // Search
    const search = (document.getElementById('inventory-search')?.value || '').toLowerCase();
    if (search) {
        filtered = filtered.filter(row =>
            row.name.toLowerCase().includes(search) ||
            row.brand.toLowerCase().includes(search) ||
            row.colors.toLowerCase().includes(search) ||
            row.sizes.toLowerCase().includes(search)
        );
    }
    // Brand filter
    const brand = document.getElementById('inventory-brand-filter')?.value;
    if (brand) {
        filtered = filtered.filter(row => row.brand === brand);
    }
    // Category filter
    const category = document.getElementById('inventory-category-filter')?.value;
    if (category) {
        filtered = filtered.filter(row => row.category === category);
    }
    // Sorting
    const sort = document.getElementById('inventory-sort-filter')?.value;
    if (sort === 'name-asc') {
        filtered.sort((a, b) => a.name.localeCompare(b.name));
    } else if (sort === 'name-desc') {
        filtered.sort((a, b) => b.name.localeCompare(a.name));
    } else if (sort === 'brand-asc') {
        filtered.sort((a, b) => a.brand.localeCompare(b.brand));
    } else if (sort === 'brand-desc') {
        filtered.sort((a, b) => b.brand.localeCompare(a.brand));
    } else if (sort === 'stock-desc') {
        filtered.sort((a, b) => b.stock - a.stock);
    } else if (sort === 'stock-asc') {
        filtered.sort((a, b) => a.stock - b.stock);
    }
    return filtered;
}

function setupInventoryOverviewEvents() {
    ['inventory-search', 'inventory-brand-filter', 'inventory-category-filter', 'inventory-sort-filter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            renderInventoryOverviewTable();
        });
        if (el && el.tagName === 'SELECT') el.addEventListener('change', () => {
            renderInventoryOverviewTable();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Render Inventory Overview if section exists
    if (document.getElementById('inventory-overview-tbody')) {
        renderInventoryOverviewTable();
        setupInventoryOverviewEvents();
    }
});
let supplyLogs = [
    {
        id: 3001,
        name: 'Nike Philippines',
        contact: 'Juan Dela Cruz',
        brand: 'Nike',
        stock: 200,
        country: 'Philippines',
        lastUpdate: '2025-09-10',
        email: 'nikeph@example.com',
        phone: '09171234567',
        status: 'active'
    },
    {
        id: 3002,
        name: 'Adidas Manila',
        contact: 'Maria Santos',
        brand: 'Adidas',
        stock: 150,
        country: 'Philippines',
        lastUpdate: '2025-09-15',
        email: 'adidasmanila@example.com',
        phone: '09181234567',
        status: 'active'
    },
    {
        id: 3003,
        name: 'Puma Supply',
        contact: 'Pedro Reyes',
        brand: 'Puma',
        stock: 100,
        country: 'Philippines',
        lastUpdate: '2025-09-18',
        email: 'pumaph@example.com',
        phone: '09191234567',
        status: 'inactive'
    }
];

function renderSupplyLogsTable() {
    const tbody = document.getElementById('supply-logs-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    let filtered = filterSupplyLogs();
    filtered.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.id}</td>
            <td>${row.name}</td>
            <td>${row.contact}</td>
            <td>${row.brand || '-'}</td>
            <td>${row.stock != null ? row.stock : '-'}</td>
            <td>${row.country || '-'}</td>
            <td>${row.lastUpdate || '-'}</td>
            <td>${row.email}</td>
            <td>${row.phone}</td>
            <td><span class="status-badge ${row.status === 'active' ? 'status-active' : 'status-inactive'}">${row.status}</span></td>
        `;
        tbody.appendChild(tr);
    });
}

function filterSupplyLogs() {
    let filtered = [...supplyLogs];
    // Search
    const search = (document.getElementById('supply-search')?.value || '').toLowerCase();
    if (search) {
        filtered = filtered.filter(row =>
            row.id.toString().includes(search) ||
            row.name.toLowerCase().includes(search) ||
            row.contact.toLowerCase().includes(search) ||
            row.brand.toLowerCase().includes(search)
        );
    }
    // Sorting
    const sort = document.getElementById('supply-sort-filter')?.value;
        if (sort === 'date-desc') {
            filtered.sort((a, b) => new Date(b.lastUpdate) - new Date(a.lastUpdate));
        } else if (sort === 'date-asc') {
            filtered.sort((a, b) => new Date(a.lastUpdate) - new Date(b.lastUpdate));
    } else if (sort === 'id-desc') {
        filtered.sort((a, b) => b.id - a.id);
    } else if (sort === 'id-asc') {
        filtered.sort((a, b) => a.id - b.id);
    }
    return filtered;
}

function setupSupplyLogsEvents() {
    ['supply-search', 'supply-sort-filter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            renderSupplyLogsTable();
        });
        if (el && el.tagName === 'SELECT') el.addEventListener('change', () => {
            renderSupplyLogsTable();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Render Supply Logs if section exists
    if (document.getElementById('supply-logs-tbody')) {
        renderSupplyLogsTable();
        setupSupplyLogsEvents();
    }
});
let reservationLogs = [
    {
        id: 2001,
        name: 'Anna Cruz',
        email: 'anna@email.com',
        phone: '09171234567',
        products: 'Nike Air Max',
        dateReserved: '2025-09-15',
        pickupDate: '2025-09-20',
        status: 'completed',
    },
    {
        id: 2002,
        name: 'Ben Santos',
        email: 'ben@email.com',
        phone: '09181234567',
        products: 'Adidas Ultraboost',
        dateReserved: '2025-09-10',
        pickupDate: '2025-09-18',
        status: 'cancelled',
    },
    {
        id: 2003,
        name: 'Carla Reyes',
        email: 'carla@email.com',
        phone: '09191234567',
        products: 'Puma RS-X',
        dateReserved: '2025-08-25',
        pickupDate: '2025-08-30',
        status: 'completed',
    },
    {
        id: 2004,
        name: 'David Lee',
        email: 'david@email.com',
        phone: '09201234567',
        products: 'Converse Chuck Taylor',
        dateReserved: '2025-07-12',
        pickupDate: '2025-07-20',
        status: 'cancelled',
    },
];

function renderReservationLogsTable() {
    const tbody = document.getElementById('reservation-logs-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    let filtered = filterReservationLogs();
    filtered.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.id}</td>
            <td>${row.name}</td>
            <td>${row.email}</td>
            <td>${row.phone}</td>
            <td>${row.products}</td>
            <td>${row.dateReserved}</td>
            <td>${row.pickupDate}</td>
            <td>${row.status.charAt(0).toUpperCase() + row.status.slice(1)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateReservationLogsKPIs() {
    let filtered = filterReservationLogs();
    let total = filtered.length;
    let completed = filtered.filter(row => row.status === 'completed').length;
    let cancelled = filtered.filter(row => row.status === 'cancelled').length;
    document.getElementById('reservation-kpi-total').textContent = total;
    document.getElementById('reservation-kpi-completed').textContent = completed;
    document.getElementById('reservation-kpi-cancelled').textContent = cancelled;
}

function filterReservationLogs() {
    let filtered = [...reservationLogs];
    // Search
    const search = (document.getElementById('reservation-search')?.value || '').toLowerCase();
    if (search) {
        filtered = filtered.filter(row =>
            row.id.toString().includes(search) ||
            row.name.toLowerCase().includes(search) ||
            row.email.toLowerCase().includes(search) ||
            row.products.toLowerCase().includes(search)
        );
    }
    // Status filter
    const status = document.getElementById('reservation-status-filter')?.value;
    if (status) {
        filtered = filtered.filter(row => row.status === status);
    }
    // Sorting
    const sort = document.getElementById('reservation-sort-filter')?.value;
    if (sort === 'date-desc') {
        filtered.sort((a, b) => new Date(b.dateReserved) - new Date(a.dateReserved));
    } else if (sort === 'date-asc') {
        filtered.sort((a, b) => new Date(a.dateReserved) - new Date(b.dateReserved));
    } else if (sort === 'id-desc') {
        filtered.sort((a, b) => b.id - a.id);
    } else if (sort === 'id-asc') {
        filtered.sort((a, b) => a.id - b.id);
    }
    return filtered;
}

function setupReservationLogsEvents() {
    ['reservation-search', 'reservation-status-filter', 'reservation-sort-filter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            renderReservationLogsTable();
            updateReservationLogsKPIs();
        });
        if (el && el.tagName === 'SELECT') el.addEventListener('change', () => {
            renderReservationLogsTable();
            updateReservationLogsKPIs();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Render Reservation Logs if section exists
    if (document.getElementById('reservation-logs-tbody')) {
        renderReservationLogsTable();
        updateReservationLogsKPIs();
        setupReservationLogsEvents();
    }
});
let salesHistory = [
    {
        id: 1001,
        products: 'Nike Air Max, Adidas Ultraboost',
        quantity: 3,
        totalPrice: 15000,
        cashReceived: 16000,
        change: 1000,
        date: '2025-09-20',
        time: '14:23',
    },
    {
        id: 1002,
        products: 'Puma RS-X',
        quantity: 1,
        totalPrice: 6000,
        cashReceived: 7000,
        change: 1000,
        date: '2025-09-19',
        time: '10:15',
    },
    {
        id: 1003,
        products: 'Converse Chuck Taylor, Nike Air Max',
        quantity: 2,
        totalPrice: 9000,
        cashReceived: 10000,
        change: 1000,
        date: '2025-08-30',
        time: '16:45',
    },
    {
        id: 1004,
        products: 'Adidas Stan Smith',
        quantity: 1,
        totalPrice: 5000,
        cashReceived: 5000,
        change: 0,
        date: '2025-07-12',
        time: '11:00',
    },
];

function renderSalesHistoryTable() {
    const tbody = document.getElementById('sales-history-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    let filtered = filterSalesHistory();
    filtered.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.id}</td>
            <td>${row.products}</td>
            <td>${row.quantity}</td>
            <td>₱${row.totalPrice.toLocaleString()}</td>
            <td>₱${row.cashReceived.toLocaleString()}</td>
            <td>₱${row.change.toLocaleString()}</td>
            <td>${row.date}</td>
            <td>${row.time}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateSalesHistoryKPIs() {
    let filtered = filterSalesHistory();
    let totalTransactions = filtered.length;
    let totalQuantity = filtered.reduce((sum, row) => sum + row.quantity, 0);
    let totalSales = filtered.reduce((sum, row) => sum + row.totalPrice, 0);
    document.getElementById('sales-kpi-transactions').textContent = totalTransactions;
    document.getElementById('sales-kpi-quantity').textContent = totalQuantity;
    document.getElementById('sales-kpi-total').textContent = '₱' + totalSales.toLocaleString();
}

function filterSalesHistory() {
    let filtered = [...salesHistory];
    // Search
    const search = (document.getElementById('sales-search')?.value || '').toLowerCase();
    if (search) {
        filtered = filtered.filter(row =>
            row.id.toString().includes(search) ||
            row.products.toLowerCase().includes(search)
        );
    }
    // Month filter
    const month = document.getElementById('sales-month-filter')?.value;
    if (month) {
        filtered = filtered.filter(row => row.date.split('-')[1] === month.padStart(2, '0'));
    }
    // Year filter
    const year = document.getElementById('sales-year-filter')?.value;
    if (year) {
        filtered = filtered.filter(row => row.date.split('-')[0] === year);
    }
    // Sorting
    const sort = document.getElementById('sales-sort-filter')?.value;
    if (sort === 'orderid-desc') {
        filtered.sort((a, b) => b.id - a.id);
    } else if (sort === 'orderid-asc') {
        filtered.sort((a, b) => a.id - b.id);
    } else if (sort === 'date-desc') {
        filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
    } else if (sort === 'date-asc') {
        filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
    }
    return filtered;
}

function setupSalesHistoryEvents() {
    ['sales-search', 'sales-month-filter', 'sales-year-filter', 'sales-sort-filter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            renderSalesHistoryTable();
            updateSalesHistoryKPIs();
        });
        if (el && el.tagName === 'SELECT') el.addEventListener('change', () => {
            renderSalesHistoryTable();
            updateSalesHistoryKPIs();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Render Sales History if section exists
    if (document.getElementById('sales-history-tbody')) {
        renderSalesHistoryTable();
        updateSalesHistoryKPIs();
        setupSalesHistoryEvents();
    }
});
let inventory = [];
let reservations = [];
let suppliers = [];

// --- Mock data for suppliers with orderReceivedDate ---
if (!localStorage.getItem('suppliers')) {
    suppliers = [
        {
            id: 1,
            name: 'Nike Philippines',
            contact: 'Juan Dela Cruz',
            brand: 'Nike',
            stock: 200,
            country: 'Philippines',
            orderReceivedDate: '2025-09-10',
            email: 'nikeph@example.com',
            phone: '09171234567',
            status: 'active'
        },
        {
            id: 2,
            name: 'Adidas Manila',
            contact: 'Maria Santos',
            brand: 'Adidas',
            stock: 150,
            country: 'Philippines',
            orderReceivedDate: '2025-09-15',
            email: 'adidasmanila@example.com',
            phone: '09181234567',
            status: 'active'
        },
        {
            id: 3,
            name: 'Puma Supply',
            contact: 'Pedro Reyes',
            brand: 'Puma',
            stock: 100,
            country: 'Philippines',
            orderReceivedDate: '2025-09-18',
            email: 'pumaph@example.com',
            phone: '09191234567',
            status: 'inactive'
        }
    ];
    localStorage.setItem('suppliers', JSON.stringify(suppliers));
}
let cart = [];
let activities = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
    loadSampleData();
    renderInventoryTable();
    updateDashboard();
    updateTimeAndDate();
    setInterval(updateTimeAndDate, 1000);
    renderCharts();
    renderActivityList();
});

// Update time and date display
function updateTimeAndDate() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');

    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('en-US', {
            hour12: true,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
}

// Initialize the application
function initializeApp() {
    // Load data from localStorage
    inventory = JSON.parse(localStorage.getItem('inventory')) || [];
    reservations = JSON.parse(localStorage.getItem('reservations')) || [];
    suppliers = JSON.parse(localStorage.getItem('suppliers')) || [];
    activities = JSON.parse(localStorage.getItem('activities')) || [];

    // Set current date for reservation form
    const today = new Date().toISOString().split('T')[0];
    const reservationDateInput = document.getElementById('reservation-date');
    if (reservationDateInput) {
        reservationDateInput.min = today;
    }
}

// Setup event listeners
function setupEventListeners() {
    // Navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.parentElement.getAttribute('data-section');
            navigateToSection(section);
        });
    });

    // Search functionality
    const searchInventory = document.getElementById('search-inventory');
    if (searchInventory) searchInventory.addEventListener('input', filterInventory);

    const searchReservation = document.getElementById('search-reservation');
    if (searchReservation) searchReservation.addEventListener('input', filterReservations);

    const searchSupplier = document.getElementById('search-supplier');
    if (searchSupplier) searchSupplier.addEventListener('input', filterSuppliers);

    const posSearch = document.getElementById('pos-search');
    if (posSearch) posSearch.addEventListener('input', filterPOSProducts);

    // Form submissions
    const addInventoryForm = document.getElementById('add-inventory-form');
    if (addInventoryForm) {
        addInventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            createInventoryItem();
        });
    }

    // Upload functionality
    const uploadBox = document.querySelector('.upload-box');
    if (uploadBox) {
        uploadBox.addEventListener('click', function() {
            // Trigger file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.onchange = handleFileUpload;
            fileInput.click();
        });
    }

    const browseBtn = document.querySelector('.browse-btn');
    if (browseBtn) {
        browseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.onchange = handleFileUpload;
            fileInput.click();
        });
    }

    // Quick links on dashboard
    document.querySelectorAll('.quick-card').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-go');
            if (target) {
                navigateToSection(target);
            }
        });
    });

    // Dashboard scope filter
    const scopeSelect = document.getElementById('dashboard-scope');
    if (scopeSelect) {
        scopeSelect.addEventListener('change', function() {
            updateDashboard();
            renderCharts();
            updateDashAddButton();
        });
        // initialize state
        updateDashAddButton();
    }

}

// Navigation function
function navigateToSection(section) {
    // Normalize parent inventory to dashboard
    if (section === 'inventory') {
        section = 'inventory-dashboard';
    }
    // Update active nav item
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-section="${section}"]`).classList.add('active');

    // Update page title
    const pageTitle = document.getElementById('page-title');
    const titles = {
        'inventory': 'Dashboard',
        'inventory-dashboard': 'Dashboard',
        'inventory-list': 'Inventory - Inventory List',
        'pos-inventory': 'POS',
        'reservation-inventory': 'Reservation Inventory',
        'inventory-reports': 'Inventory Reports',
        'reservation': 'Reservation Management',
        'supplier': 'Supplier Management',
        'settings': 'Settings'
    };
    pageTitle.textContent = titles[section] || 'Reports';

    // Show/hide content sections
    document.querySelectorAll('.content-section').forEach(content => {
        content.classList.remove('active');
    });
    const targetSection = document.getElementById(section);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // Load section-specific data
    switch (section) {
        case 'inventory-list':
            renderInventoryTable();
            break;
        case 'inventory-dashboard':
            updateDashboard();
            renderCharts();
            renderActivityList();
            break;
        case 'pos-inventory':
            renderPOSProducts();
            break;
        case 'reservation-inventory':
            renderReservationTable();
            break;
        case 'reservation':
            renderReservationTable();
            break;
        case 'supplier':
            renderSupplierTable();
            break;
        case 'inventory-reports':
            renderReports();
            break;
        case 'settings':
            setupSettings();
            break;
    }
}

// Settings logic
function setupSettings() {
    // Tabs
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const key = this.getAttribute('data-tab');
            panels.forEach(p => p.classList.remove('active'));
            const active = document.getElementById(`settings-panel-${key}`);
            if (active) active.classList.add('active');
        });
    });

    // Avatar upload
    const avatarBtn = document.getElementById('settings-avatar-btn');
    const avatarInput = document.getElementById('settings-avatar');
    const avatarPreview = document.getElementById('settings-avatar-preview');
    const avatarRemove = document.getElementById('settings-avatar-remove');
    if (avatarBtn && avatarInput && avatarPreview) {
        avatarBtn.onclick = () => avatarInput.click();
        avatarInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = ev => { avatarPreview.src = ev.target.result; };
                reader.readAsDataURL(file);
            }
        };
    }
    if (avatarRemove && avatarPreview) {
        avatarRemove.onclick = () => { avatarPreview.src = 'profile.png'; };
    }

    // Save handlers (demo persistence to localStorage)
    const profileSave = document.getElementById('settings-profile-save');
    if (profileSave) {
        profileSave.onclick = () => {
            const profile = {
                name: document.getElementById('settings-name').value,
                username: document.getElementById('settings-username').value,
                email: document.getElementById('settings-email').value,
                phone: document.getElementById('settings-phone').value,
                bio: document.getElementById('settings-bio').value,
            };
            localStorage.setItem('settings_profile', JSON.stringify(profile));
            showNotification('Profile saved', 'success');
        };
    }

    const passwordSave = document.getElementById('settings-password-save');
    if (passwordSave) {
        passwordSave.onclick = () => {
            const newPass = document.getElementById('settings-new-password').value;
            const confirm = document.getElementById('settings-confirm-password').value;
            if (!newPass || newPass !== confirm) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            showNotification('Password updated', 'success');
        };
    }

    const prefSave = document.getElementById('settings-preferences-save');
    if (prefSave) {
        prefSave.onclick = () => {
            const prefs = {
                theme: document.getElementById('settings-theme').value,
                accent: document.getElementById('settings-accent').value,
                language: document.getElementById('settings-language').value,
                currency: document.getElementById('settings-currency').value,
            };
            localStorage.setItem('settings_preferences', JSON.stringify(prefs));
            showNotification('Preferences saved', 'success');
        };
    }

    const notifSave = document.getElementById('settings-notifications-save');
    if (notifSave) {
        notifSave.onclick = () => {
            const notif = {
                sales: document.getElementById('notif-sales').checked,
                inventory: document.getElementById('notif-inventory').checked,
                reservations: document.getElementById('notif-reservations').checked,
            };
            localStorage.setItem('settings_notifications', JSON.stringify(notif));
            showNotification('Notifications saved', 'success');
        };
    }

    const resetBtn = document.getElementById('settings-reset');
    if (resetBtn) {
        resetBtn.onclick = () => {
            if (confirm('This will clear inventory, reservations, suppliers and settings. Continue?')) {
                localStorage.clear();
                inventory = [];
                reservations = [];
                suppliers = [];
                activities = [];
                renderInventoryTable();
                renderReservationTable();
                renderSupplierTable();
                updateDashboard();
                showNotification('All data reset', 'success');
            }
        };
    }
}

// Dashboard KPIs
function updateDashboard() {
    // Bind KPIs to scoped dataset while keeping the same card labels
    const data = getScopedDataset();
    const totalStock = data.reduce((sum, i) => sum + (i.quantity || 0), 0);
    const totalValue = data.reduce((sum, i) => sum + ((i.quantity || 0) * (i.price || 0)), 0);
    const avgPrice = data.length ? (data.reduce((s, i) => s + (i.price || 0), 0) / data.length) : 0;
    const brandCount = new Set(data.map(i => (i.brand || ''))).size;

    const kpiSales = document.getElementById('kpi-sales');
    const kpiRevenue = document.getElementById('kpi-revenue');
    const kpiAvg = document.getElementById('kpi-avg');
    const kpiOps = document.getElementById('kpi-ops');
    if (kpiSales) kpiSales.textContent = totalStock.toString();
    if (kpiRevenue) kpiRevenue.textContent = `₱${totalValue.toFixed(2)}`;
    if (kpiAvg) kpiAvg.textContent = `₱${avgPrice.toFixed(2)}`;
    if (kpiOps) kpiOps.textContent = brandCount.toString();
}

// Charts (gradient blue)
let marketChart, donutChart, areaChart;

function getBlueGradient(ctx) {
    const g = ctx.createLinearGradient(0, 0, 0, 180);
    g.addColorStop(0, 'rgba(59,130,246,0.9)');
    g.addColorStop(1, 'rgba(67,56,202,0.2)');
    return g;
}

function renderCharts() {
    const marketCanvas = document.getElementById('chart-market');
    const donutCanvas = document.getElementById('chart-donut');
    const areaCanvas = document.getElementById('chart-area');
    if (!marketCanvas || !donutCanvas || !areaCanvas || !window.Chart) return;

    const scoped = getScopedDataset();
    // Bar: Top brands by total quantity
    const brandToQty = scoped.reduce((map, item) => {
        const brand = (item.brand || 'Unknown');
        map[brand] = (map[brand] || 0) + (item.quantity || 0);
        return map;
    }, {});
    const brandEntries = Object.entries(brandToQty).sort((a, b) => b[1] - a[1]).slice(0, 10);
    const barLabels = brandEntries.map(([b]) => b);
    const barData = brandEntries.map(([, q]) => q);
    const ctxBar = marketCanvas.getContext('2d');
    const barGrad = getBlueGradient(ctxBar);
    marketChart && marketChart.destroy();
    marketChart = new Chart(ctxBar, {
        type: 'bar',
        data: { labels: barLabels, datasets: [{ label: 'Stock by Brand', data: barData, backgroundColor: barGrad, borderRadius: 6 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#eef2f7' } } } }
    });

    // Donut: Category distribution by quantity
    const normalizeCategory = (c) => {
        const v = (c || '').toLowerCase();
        if (v.includes('men')) return 'Men';
        if (v.includes('women') || v.includes('woman') || v.includes('ladies')) return 'Women';
        if (v.includes('kid')) return 'Kids';
        if (v.includes('access')) return 'Accessories';
        return 'Other';
    };
    const categoryToQty = scoped.reduce((map, item) => {
        const cat = normalizeCategory(item.category);
        map[cat] = (map[cat] || 0) + (item.quantity || 0);
        return map;
    }, {});
    const catLabels = ['Men', 'Women', 'Kids', 'Accessories', 'Other'].filter(l => categoryToQty[l]);
    const catData = catLabels.map(l => categoryToQty[l]);
    const ctxD = donutCanvas.getContext('2d');
    const donutGrad1 = getBlueGradient(ctxD);
    const donutGrad2 = ctxD.createLinearGradient(0, 0, 0, 180);
    donutGrad2.addColorStop(0, 'rgba(99,102,241,0.4)');
    donutGrad2.addColorStop(1, 'rgba(99,102,241,0.1)');
    const donutGrad3 = ctxD.createLinearGradient(0, 0, 0, 180);
    donutGrad3.addColorStop(0, 'rgba(59,130,246,0.25)');
    donutGrad3.addColorStop(1, 'rgba(59,130,246,0.08)');
    const donutColors = [donutGrad1, donutGrad2, donutGrad3, 'rgba(37,99,235,0.2)', 'rgba(99,102,241,0.2)'];
    donutChart && donutChart.destroy();
    donutChart = new Chart(ctxD, {
        type: 'doughnut',
        data: { labels: catLabels, datasets: [{ data: catData, backgroundColor: donutColors.slice(0, catLabels.length), borderWidth: 0 }] },
        options: { cutout: '70%', plugins: { legend: { display: false } } }
    });

    // Area: Inventory value trend grouped over 5 buckets (M-F)
    const values = [...scoped].slice(0, 25).map(i => (i.quantity || 0) * (i.price || 0));
    const buckets = [0, 0, 0, 0, 0];
    values.forEach((v, idx) => { buckets[idx % 5] += v; });
    const ctxA = areaCanvas.getContext('2d');
    const areaGrad = getBlueGradient(ctxA);
    areaChart && areaChart.destroy();
    areaChart = new Chart(ctxA, {
        type: 'line',
        data: { labels: ['M', 'T', 'W', 'T', 'F'], datasets: [{ label: 'Inventory Value', data: buckets.map(v => Math.round(v)), fill: true, backgroundColor: areaGrad, borderColor: 'rgba(59,130,246,1)', tension: 0.35, pointBackgroundColor: '#fff', pointBorderColor: 'rgba(59,130,246,1)', pointRadius: 3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#eef2f7' } } } }
    });
}

// Activity list (right panel)
function renderActivityList() {
    const container = document.getElementById('activity-list');
    const dateSpan = document.getElementById('today-date');
    if (!container) return;
    if (dateSpan) dateSpan.textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' });

    // Simple dynamic list based on inventory changes
    const items = [
        { title: 'Low Stock Items', sub: `${(getScopedDataset().filter(i=> (i.quantity||0) <= 5).length)} items`, type: 'down' },
        { title: 'Brands Tracked', sub: `${(new Set(getScopedDataset().map(i=> i.brand))).size} brands`, type: 'up' },
        { title: 'Total Categories', sub: `${(new Set(getScopedDataset().map(i=> (i.category||'Other')))).size} types`, type: 'up' }
    ];
    container.innerHTML = '';
    items.forEach(i => {
        const el = document.createElement('div');
        el.className = 'activity-item';
        const arrow = i.type === 'up' ? 'fa-arrow-up' : 'fa-arrow-down';
        el.innerHTML = `
            <div class="activity-icon"><i class="fas ${arrow}"></i></div>
            <div class="activity-text">
                <div class="activity-title">${i.title}</div>
                <div class="activity-sub">${i.sub}</div>
            </div>
        `;
        container.appendChild(el);
    });
}

// (Font size preference removed — small is now mandatory)

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear any session data
        localStorage.removeItem('currentUser');
        alert('Logged out successfully!');
        // In a real application, you would redirect to login page
        // window.location.href = 'login.html';
    }
}

// Modal functions
function openAddProductModal() {
    document.getElementById('add-product-modal').classList.add('active');
    document.getElementById('modal-overlay').classList.add('active');
}

function openAddInventoryModal() {
    document.getElementById('add-product-modal').classList.add('active');
    document.getElementById('modal-overlay').classList.add('active');
}

function openAddReservationModal() {
    populateReservationProducts();
    document.getElementById('add-reservation-modal').classList.add('active');
    document.getElementById('modal-overlay').classList.add('active');
}

function openAddSupplierModal() {
    document.getElementById('add-supplier-modal').classList.add('active');
    document.getElementById('modal-overlay').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.getElementById('modal-overlay').classList.remove('active');
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
    document.getElementById('modal-overlay').classList.remove('active');
}

// Data management functions
function saveData() {
    localStorage.setItem('inventory', JSON.stringify(inventory));
    localStorage.setItem('reservations', JSON.stringify(reservations));
    localStorage.setItem('suppliers', JSON.stringify(suppliers));
    localStorage.setItem('activities', JSON.stringify(activities));
}

function addActivity(type, message) {
    const activity = {
        id: Date.now(),
        type: type,
        message: message,
        timestamp: new Date().toISOString()
    };
    activities.unshift(activity);
    if (activities.length > 50) activities.pop(); // Keep only last 50 activities
    saveData();
    updateDashboard();
}

// Load sample data
function loadSampleData() {
    if (inventory.length === 0) {
        inventory = [
            { id: 576, name: 'Ultraboost 22', brand: 'Adidas', category: 'Sports', quantity: 7, price: 8200, sizes: ['10', '11', '12'], status: 'active' },
            { id: 577, name: 'Dunk Low', brand: 'Nike', category: 'Men', quantity: 20, price: 6000, sizes: ['9', '10'], status: 'active' },
            { id: 578, name: '574', brand: 'New Balance', category: 'Men', quantity: 15, price: 4500, sizes: ['6', '7', '8', '9'], status: 'active' },
            { id: 579, name: 'Air Jordan 1', brand: 'Nike', category: 'Men', quantity: 18, price: 9500, sizes: ['5', '6', '7'], status: 'active' },
            { id: 580, name: 'Slip-On', brand: 'Vans', category: 'Men', quantity: 12, price: 3200, sizes: ['10', '11'], status: 'active' },
            { id: 581, name: 'Cloudswift', brand: 'On Cloud', category: 'Women', quantity: 13, price: 8500, sizes: ['7', '8', '9'], status: 'active' },
            { id: 582, name: 'Yeezy Boost 700', brand: 'Adidas', category: 'Men', quantity: 5, price: 16000, sizes: ['8', '9', '10'], status: 'active' },
            { id: 583, name: 'Samba OG', brand: 'Adidas', category: 'Women', quantity: 9, price: 7000, sizes: ['7', '8'], status: 'active' },
            { id: 584, name: 'Air Force 1', brand: 'Nike', category: 'Men', quantity: 16, price: 5500, sizes: ['8', '9', '10'], status: 'active' },
            { id: 585, name: '2002R', brand: 'New Balance', category: 'Men', quantity: 11, price: 6800, sizes: ['7', '8', '9'], status: 'active' }
        ];
    }

    if (suppliers.length === 0) {
        suppliers = [
            { id: 1, name: 'TechCorp Inc.', contact: 'John Smith', brand: 'Electra', stock: 120, country: 'USA', email: 'john@techcorp.com', phone: '+1-555-0123', status: 'active' },
            { id: 2, name: 'Fashion World', contact: 'Sarah Johnson', brand: 'Moda', stock: 80, country: 'Italy', email: 'sarah@fashionworld.com', phone: '+1-555-0456', status: 'active' },
            { id: 3, name: 'Book Publishers Ltd.', contact: 'Mike Brown', brand: 'PaperPro', stock: 200, country: 'UK', email: 'mike@bookpublishers.com', phone: '+1-555-0789', status: 'active' }
        ];
    }

    if (reservations.length === 0) {
        reservations = [
            { id: 1, customer: 'Alice Johnson', product: 'Laptop', quantity: 1, date: '2024-01-15', status: 'pending' },
            { id: 2, customer: 'Bob Wilson', product: 'Smartphone', quantity: 2, date: '2024-01-16', status: 'confirmed' }
        ];
    }

    saveData();
}

// Activity functions
function addActivity(type, message) {
    const activity = {
        id: Date.now(),
        type: type,
        message: message,
        timestamp: new Date().toISOString()
    };
    activities.unshift(activity);
    if (activities.length > 50) activities.pop(); // Keep only last 50 activities
    saveData();
}



// Inventory functions
function renderInventoryTable() {
    const tbody = document.getElementById('inventory-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    inventory.forEach(item => {
        const row = document.createElement('tr');
        const statusClass = item.quantity > 0 ? 'status-active' : 'status-low';
        const statusText = item.quantity > 0 ? 'In Stock' : 'Out of Stock';

        row.innerHTML = `
            <td>#${item.id}</td>
            <td>${item.name}</td>
            <td>${item.brand || 'N/A'}</td>
            <td>${item.category}</td>
            <td>${item.sizes ? item.sizes.join(',') : 'N/A'}</td>
            <td>${item.quantity}</td>
            <td>₱${item.price.toFixed(2)}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-secondary" onclick="editProduct(${item.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteProduct(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function filterInventory() {
    const searchTerm = document.getElementById('search-inventory').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;

    const filtered = inventory.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchTerm) ||
            item.brand.toLowerCase().includes(searchTerm);
        const matchesCategory = !categoryFilter || item.category === categoryFilter;
        return matchesSearch && matchesCategory;
    });

    renderFilteredInventory(filtered);
}

function renderFilteredInventory(filteredItems) {
    const tbody = document.getElementById('inventory-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    filteredItems.forEach(item => {
        const row = document.createElement('tr');
        const statusClass = item.quantity > 0 ? 'status-active' : 'status-low';
        const statusText = item.quantity > 0 ? 'In Stock' : 'Out of Stock';

        row.innerHTML = `
            <td>#${item.id}</td>
            <td>${item.name}</td>
            <td>${item.brand || 'N/A'}</td>
            <td>${item.category}</td>
            <td>${item.sizes ? item.sizes.join(',') : 'N/A'}</td>
            <td>${item.quantity}</td>
            <td>₱${item.price.toFixed(2)}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-secondary" onclick="editProduct(${item.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteProduct(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function editProduct(id) {
    const product = inventory.find(item => item.id === id);
    if (product) {
        // In a real application, you would populate a modal with the product data
        alert(`Edit product: ${product.name}`);
    }
}

function deleteProduct(id) {
    const product = inventory.find(item => item.id === id);
    if (product && confirm(`Are you sure you want to delete ${product.name}?`)) {
        inventory = inventory.filter(item => item.id !== id);
        saveData();
        addActivity('delete', `Deleted product: ${product.name}`);
        renderInventoryTable();
    }
}

// POS functions
function renderPOSProducts() {
    const productList = document.getElementById('product-list');
    if (!productList) return;

    productList.innerHTML = '';
    inventory.forEach(item => {
        if (item.quantity > 0) {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            productCard.onclick = () => addToCart(item);

            productCard.innerHTML = `
                <h4>${item.name}</h4>
                <p>${item.category}</p>
                <p>Stock: ${item.quantity}</p>
                <div class="price">₱${item.price.toFixed(2)}</div>
            `;
            productList.appendChild(productCard);
        }
    });
}

function filterPOSProducts() {
    const searchTerm = document.getElementById('pos-search').value.toLowerCase();
    const productList = document.getElementById('product-list');
    if (!productList) return;

    productList.innerHTML = '';
    inventory.forEach(item => {
        if (item.quantity > 0 && item.name.toLowerCase().includes(searchTerm)) {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            productCard.onclick = () => addToCart(item);

            productCard.innerHTML = `
                <h4>${item.name}</h4>
                <p>${item.category}</p>
                <p>Stock: ${item.quantity}</p>
                <div class="price">₱${item.price.toFixed(2)}</div>
            `;
            productList.appendChild(productCard);
        }
    });
}

function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1
        });
    }
    updateCart();
    showNotification(`${product.name} added to cart`, 'success');
}

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    if (!cartItems || !cartTotal) return;

    cartItems.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p>₱${item.price.toFixed(2)} x ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
            </div>
        `;
        cartItems.appendChild(cartItem);
        total += item.price * item.quantity;
    });

    cartTotal.textContent = total.toFixed(2);
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            cart = cart.filter(item => item.id !== productId);
        }
        updateCart();
    }
}

function processSale() {
    if (cart.length === 0) {
        showNotification('Cart is empty', 'error');
        return;
    }

    // Update inventory quantities
    cart.forEach(cartItem => {
        const inventoryItem = inventory.find(item => item.id === cartItem.id);
        if (inventoryItem) {
            inventoryItem.quantity -= cartItem.quantity;
        }
    });

    // Clear cart
    cart = [];
    updateCart();
    renderPOSProducts();
    renderInventoryTable();
    saveData();
    showNotification('Sale completed successfully!', 'success');
}

function createInventoryItem() {
    const productName = document.getElementById('product-name').value;
    const brand = document.getElementById('product-brand').value;
    const category = document.getElementById('product-category').value;
    const stock = parseInt(document.getElementById('product-stock').value);
    const price = parseFloat(document.getElementById('product-price').value);
    const sizes = document.getElementById('product-sizes').value;

    if (!productName || !brand || !category || !stock || !price || !sizes) {
        showNotification('Please fill in all fields', 'error');
        return;
    }

    const newProduct = {
        id: Date.now(),
        name: productName,
        brand: brand,
        category: category,
        quantity: stock,
        price: price,
        sizes: sizes.split(',').map(s => s.trim()),
        status: 'active',
        image: null
    };

    inventory.push(newProduct);
    saveData();
    addActivity('add', `Added new inventory item: ${newProduct.name}`);

    // Reset form
    clearForm();

    // Show success message
    showNotification('Product added successfully!', 'success');
}

function clearForm() {
    const form = document.getElementById('add-inventory-form');
    if (form) {
        form.reset();
    }
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Store the image data (in a real app, you'd upload to server)
                const imageData = e.target.result;
                showNotification('Image uploaded successfully!', 'success');

                // Update upload box to show preview
                const uploadBox = document.querySelector('.upload-box');
                if (uploadBox) {
                    uploadBox.innerHTML = `
                        <img src="${imageData}" alt="Product Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <p style="margin-top: 10px; color: var(--gray-600);">Image uploaded successfully</p>
                    `;
                }
            };
            reader.readAsDataURL(file);
        } else {
            showNotification('Please select a valid image file', 'error');
        }
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add to body
    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}





// Reservation functions
function renderReservationTable() {
    const tbody = document.getElementById('reservation-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    reservations.forEach(reservation => {
        const row = document.createElement('tr');
        const statusClass = `status-${reservation.status}`;

        row.innerHTML = `
            <td>${reservation.id}</td>
            <td>${reservation.customer}</td>
            <td>${reservation.product}</td>
            <td>${reservation.quantity}</td>
            <td>${reservation.date}</td>
            <td><span class="status-badge ${statusClass}">${reservation.status}</span></td>
            <td>
                <button class="btn btn-secondary" onclick="updateReservationStatus(${reservation.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteReservation(${reservation.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function filterReservations() {
    const searchTerm = document.getElementById('search-reservation').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;

    const filtered = reservations.filter(reservation => {
        const matchesSearch = reservation.customer.toLowerCase().includes(searchTerm) ||
            reservation.product.toLowerCase().includes(searchTerm);
        const matchesStatus = !statusFilter || reservation.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    renderFilteredReservations(filtered);
}

function renderFilteredReservations(filteredItems) {
    const tbody = document.getElementById('reservation-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    filteredItems.forEach(reservation => {
        const row = document.createElement('tr');
        const statusClass = `status-${reservation.status}`;

        row.innerHTML = `
            <td>${reservation.id}</td>
            <td>${reservation.customer}</td>
            <td>${reservation.product}</td>
            <td>${reservation.quantity}</td>
            <td>${reservation.date}</td>
            <td><span class="status-badge ${statusClass}">${reservation.status}</span></td>
            <td>
                <button class="btn btn-secondary" onclick="updateReservationStatus(${reservation.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteReservation(${reservation.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populateReservationProducts() {
    const select = document.getElementById('reservation-product');
    if (!select) return;

    select.innerHTML = '<option value="">Select Product</option>';
    inventory.forEach(item => {
        if (item.quantity > 0) {
            const option = document.createElement('option');
            option.value = item.name;
            option.textContent = `${item.name} ($${item.price.toFixed(2)})`;
            select.appendChild(option);
        }
    });
}

function handleAddReservation(e) {
    e.preventDefault();

    const newReservation = {
        id: Date.now(),
        customer: document.getElementById('customer-name').value,
        product: document.getElementById('reservation-product').value,
        quantity: parseInt(document.getElementById('reservation-quantity').value),
        date: document.getElementById('reservation-date').value,
        status: 'pending'
    };

    reservations.push(newReservation);
    saveData();
    addActivity('reservation', `New reservation: ${newReservation.customer} - ${newReservation.product}`);
    showNotification('Reservation created successfully!', 'success');

    closeModal('add-reservation-modal');
    e.target.reset();
    renderReservationTable();
}

function updateReservationStatus(id) {
    const reservation = reservations.find(r => r.id === id);
    if (reservation) {
        const statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        const currentIndex = statuses.indexOf(reservation.status);
        const nextIndex = (currentIndex + 1) % statuses.length;
        reservation.status = statuses[nextIndex];

        saveData();
        renderReservationTable();
        showNotification(`Reservation status updated to ${reservation.status}`, 'info');
    }
}

function deleteReservation(id) {
    const reservation = reservations.find(r => r.id === id);
    if (reservation && confirm(`Are you sure you want to delete this reservation?`)) {
        reservations = reservations.filter(r => r.id !== id);
        saveData();
        renderReservationTable();
        showNotification('Reservation deleted', 'success');
    }
}

// Supplier functions
function renderSupplierTable() {
    const tbody = document.getElementById('supplier-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    suppliers.forEach(supplier => {
        const row = document.createElement('tr');
        const statusClass = supplier.status === 'active' ? 'status-active' : 'status-inactive';
        row.innerHTML = `
            <td>${supplier.id}</td>
            <td>${supplier.name}</td>
            <td>${supplier.contact}</td>
            <td>${supplier.brand || '-'}</td>
            <td>${supplier.stock != null ? supplier.stock : '-'}</td>
            <td>${supplier.country || '-'}</td>
            <td>${supplier.orderReceivedDate || '-'}</td>
            <td>${supplier.email}</td>
            <td>${supplier.phone}</td>
            <td><span class="status-badge ${statusClass}">${supplier.status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

// --- Sorting for Supplier Table by Order Received Date ---
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-supplier-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const val = sortSelect.value;
            if (val === 'date-desc') {
                suppliers.sort((a, b) => new Date(b.orderReceivedDate || '') - new Date(a.orderReceivedDate || ''));
            } else if (val === 'date-asc') {
                suppliers.sort((a, b) => new Date(a.orderReceivedDate || '') - new Date(b.orderReceivedDate || ''));
            } else if (val === 'name-asc') {
                suppliers.sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, {sensitivity: 'base'}));
            } else if (val === 'name-desc') {
                suppliers.sort((a, b) => (b.name || '').localeCompare(a.name || '', undefined, {sensitivity: 'base'}));
            }
            renderSupplierTable();
        });
    }
});

function filterSuppliers() {
    const searchTerm = document.getElementById('search-supplier').value.toLowerCase();

    const filtered = suppliers.filter(supplier => {
        return supplier.name.toLowerCase().includes(searchTerm) ||
            supplier.contact.toLowerCase().includes(searchTerm) ||
            supplier.email.toLowerCase().includes(searchTerm);
    });

    renderFilteredSuppliers(filtered);
}

function renderFilteredSuppliers(filteredItems) {
    const tbody = document.getElementById('supplier-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    filteredItems.forEach(supplier => {
        const row = document.createElement('tr');
        const statusClass = supplier.status === 'active' ? 'status-active' : 'status-inactive';

        row.innerHTML = `
            <td>${supplier.id}</td>
            <td>${supplier.name}</td>
            <td>${supplier.contact}</td>
            <td>${supplier.email}</td>
            <td>${supplier.phone}</td>
            <td><span class="status-badge ${statusClass}">${supplier.status}</span></td>
            <td>
                <button class="btn btn-secondary" onclick="editSupplier(${supplier.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteSupplier(${supplier.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handleAddSupplier(e) {
    e.preventDefault();

    const newSupplier = {
        id: Date.now(),
        name: document.getElementById('supplier-name').value,
        contact: document.getElementById('supplier-contact').value,
        brand: document.getElementById('supplier-brand').value,
        stock: parseInt(document.getElementById('supplier-stock').value) || 0,
        country: document.getElementById('supplier-country').value,
        sizes: (document.getElementById('supplier-sizes').value || '').split(',').map(s => s.trim()).filter(Boolean),
        email: document.getElementById('supplier-email').value,
        phone: document.getElementById('supplier-phone').value,
        status: 'active'
    };

    suppliers.push(newSupplier);
    saveData();
    addActivity('add', `Added new supplier: ${newSupplier.name}`);
    showNotification('Supplier added successfully!', 'success');

    closeModal('add-supplier-modal');
    e.target.reset();
    renderSupplierTable();
}

function editSupplier(id) {
    const supplier = suppliers.find(s => s.id === id);
    if (supplier) {
        // In a real application, you would populate a modal with the supplier data
        alert(`Edit supplier: ${supplier.name}`);
    }
}

function deleteSupplier(id) {
    const supplier = suppliers.find(s => s.id === id);
    if (supplier && confirm(`Are you sure you want to delete ${supplier.name}?`)) {
        suppliers = suppliers.filter(s => s.id !== id);
        saveData();
        addActivity('add', `Deleted supplier: ${supplier.name}`);
        renderSupplierTable();
        showNotification('Supplier deleted', 'success');
    }
}

// Reports functions
function renderReports() {
    // In a real application, you would generate charts and detailed reports
    // For now, we'll just show some basic statistics
    const topProductsList = document.getElementById('top-products-list');
    if (topProductsList) {
        topProductsList.innerHTML = '';

        // Sort products by quantity (for demo purposes)
        const sortedProducts = [...inventory].sort((a, b) => b.quantity - a.quantity);

        sortedProducts.slice(0, 5).forEach((product, index) => {
            const item = document.createElement('div');
            item.style.padding = '10px';
            item.style.borderBottom = '1px solid #ecf0f1';
            item.innerHTML = `
                <strong>${index + 1}. ${product.name}</strong><br>
                <small>Stock: ${product.quantity} | Price: $${product.price.toFixed(2)}</small>
            `;
            topProductsList.appendChild(item);
        });
    }
}

// Helpers: get dataset based on dashboard scope
function getScopedDataset() {
    const scope = document.getElementById('dashboard-scope');
    const sel = scope ? scope.value : 'inventory';
    if (sel === 'reservation') {
        // Map reservations to pseudo-inventory entries for aggregation
        return reservations.map(r => ({ brand: r.product || 'Reservation', category: 'Reservations', quantity: r.quantity || 0, price: 0 }));
    }
    if (sel === 'pos') {
        // Show only items with stock (as POS relevant set)
        return inventory.filter(i => (i.quantity || 0) > 0);
    }
    return inventory;
}

// Toggle dashboard 'Add Product' button based on scope
function updateDashAddButton() {
    const scope = document.getElementById('dashboard-scope');
    const btn = document.getElementById('dash-add-btn');
    if (!scope || !btn) return;
    const v = scope.value;
    if (v === 'pos' || v === 'reservation') {
        btn.style.display = 'inline-flex';
    } else {
        btn.style.display = 'none';
    }
}

function generateReport() {
    const reportType = document.getElementById('report-type').value;
    alert(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} report generated!`);
    // In a real application, this would generate and download a report file
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}