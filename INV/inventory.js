// Global variables
let inventory = [];
let reservations = [];
let suppliers = [];
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
        'inventory': 'Inventory Dashboard',
        'inventory-dashboard': 'Inventory Dashboard',
        'inventory-list': 'Inventory - Inventory List',
        'pos-inventory': 'POS',
        'reservation-inventory': 'Reservation Inventory',
        'inventory-reports': 'Inventory Reports',
        'reservation': 'Reservation Management',
        'supplier': 'Supplier Management',
        'settings': 'Settings'
    };
    pageTitle.textContent = titles[section] || 'Inventory Management';

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
            <td>${supplier.sizes ? supplier.sizes.join(', ') : '-'}</td>
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