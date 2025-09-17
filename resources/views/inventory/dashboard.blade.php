@extends('layouts.app')

@section('title', 'Inventory Management - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text">
            <h2>ShoeVault Batangas</h2>
            <p>Inventory Management</p>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item active">
            <a href="{{ route('inventory.dashboard') }}" class="nav-link">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.suppliers') }}" class="nav-link">
                <i class="fas fa-user-tie"></i>
                <span>Suppliers</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.settings') }}" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <img src="{{ asset('assets/images/profile.png') }}" alt="Manager">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name }}</h4>
                <span>{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
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
            <h1 class="main-title">Inventory Management</h1>
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
        </div>
    </header>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Inventory Dashboard Section -->
        <section class="content-section">
            <div class="dash-filter">
                <div class="filter-left">
                    <h2>Dashboard Overview</h2>
                </div>
                <div class="filter-right">
                    <button class="btn btn-primary" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="kpi-content">
                        <h3 id="total-products">0</h3>
                        <p>Total Products</p>
                        <span class="kpi-trend positive">
                            <i class="fas fa-arrow-up"></i> +5.2%
                        </span>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon low-stock">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="kpi-content">
                        <h3 id="low-stock-items">0</h3>
                        <p>Low Stock Items</p>
                        <span class="kpi-trend negative">
                            <i class="fas fa-arrow-down"></i> Need Attention
                        </span>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="kpi-content">
                        <h3 id="total-categories">3</h3>
                        <p>Categories</p>
                        <span class="kpi-trend neutral">
                            <i class="fas fa-minus"></i> Stable
                        </span>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon value">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="kpi-content">
                        <h3 id="inventory-value">₱0</h3>
                        <p>Inventory Value</p>
                        <span class="kpi-trend positive">
                            <i class="fas fa-arrow-up"></i> +12.8%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="inventory-table-container">
                <div class="table-header">
                    <h3>Product Inventory</h3>
                    <div class="table-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search-inventory" placeholder="Search products...">
                        </div>
                        <select id="category-filter" class="filter-select">
                            <option value="all">All Categories</option>
                            <option value="men">Men's Shoes</option>
                            <option value="women">Women's Shoes</option>
                            <option value="accessories">Accessories</option>
                        </select>
                        <select id="stock-filter" class="filter-select">
                            <option value="all">All Stock Levels</option>
                            <option value="in-stock">In Stock</option>
                            <option value="low-stock">Low Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="inventory-table" id="inventory-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Product ID</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Color</th>
                                <th>Sizes</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-tbody">
                            <tr class="loading-row">
                                <td colspan="10">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <span>Loading inventory...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Add Product Modal -->
<div class="modal" id="add-product-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button class="modal-close" onclick="closeAddProductModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="add-product-form" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="product-name">Product Name</label>
                        <input type="text" id="product-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="product-brand">Brand</label>
                        <input type="text" id="product-brand" name="brand" required>
                    </div>
                    <div class="form-group">
                        <label for="product-category">Category</label>
                        <select id="product-category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="men">Men's Shoes</option>
                            <option value="women">Women's Shoes</option>
                            <option value="accessories">Accessories</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product-color">Color</label>
                        <input type="text" id="product-color" name="color" placeholder="e.g., Black, White, Red" required>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="product-sizes">Size & Stock Management <span style="color: red;">*</span></label>
                        <div class="sizes-container">
                            <div class="size-stock-inputs" id="size-stock-inputs">
                                <div class="size-grid">
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="4" onchange="toggleSizeStock(this)">
                                            <span class="size-label">4</span>
                                        </label>
                                        <input type="number" name="sizes[4][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[4][size]" value="4">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="4.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">4.5</span>
                                        </label>
                                        <input type="number" name="sizes[4.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[4.5][size]" value="4.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">5</span>
                                        </label>
                                        <input type="number" name="sizes[5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[5][size]" value="5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="5.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">5.5</span>
                                        </label>
                                        <input type="number" name="sizes[5.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[5.5][size]" value="5.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="6" onchange="toggleSizeStock(this)">
                                            <span class="size-label">6</span>
                                        </label>
                                        <input type="number" name="sizes[6][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[6][size]" value="6">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="6.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">6.5</span>
                                        </label>
                                        <input type="number" name="sizes[6.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[6.5][size]" value="6.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="7" onchange="toggleSizeStock(this)">
                                            <span class="size-label">7</span>
                                        </label>
                                        <input type="number" name="sizes[7][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[7][size]" value="7">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="7.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">7.5</span>
                                        </label>
                                        <input type="number" name="sizes[7.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[7.5][size]" value="7.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="8" onchange="toggleSizeStock(this)">
                                            <span class="size-label">8</span>
                                        </label>
                                        <input type="number" name="sizes[8][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[8][size]" value="8">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="8.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">8.5</span>
                                        </label>
                                        <input type="number" name="sizes[8.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[8.5][size]" value="8.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="9" onchange="toggleSizeStock(this)">
                                            <span class="size-label">9</span>
                                        </label>
                                        <input type="number" name="sizes[9][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[9][size]" value="9">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="9.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">9.5</span>
                                        </label>
                                        <input type="number" name="sizes[9.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[9.5][size]" value="9.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="10" onchange="toggleSizeStock(this)">
                                            <span class="size-label">10</span>
                                        </label>
                                        <input type="number" name="sizes[10][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[10][size]" value="10">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="10.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">10.5</span>
                                        </label>
                                        <input type="number" name="sizes[10.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[10.5][size]" value="10.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="11" onchange="toggleSizeStock(this)">
                                            <span class="size-label">11</span>
                                        </label>
                                        <input type="number" name="sizes[11][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[11][size]" value="11">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="11.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">11.5</span>
                                        </label>
                                        <input type="number" name="sizes[11.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[11.5][size]" value="11.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="12" onchange="toggleSizeStock(this)">
                                            <span class="size-label">12</span>
                                        </label>
                                        <input type="number" name="sizes[12][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[12][size]" value="12">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="12.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">12.5</span>
                                        </label>
                                        <input type="number" name="sizes[12.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[12.5][size]" value="12.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="13" onchange="toggleSizeStock(this)">
                                            <span class="size-label">13</span>
                                        </label>
                                        <input type="number" name="sizes[13][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[13][size]" value="13">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="13.5" onchange="toggleSizeStock(this)">
                                            <span class="size-label">13.5</span>
                                        </label>
                                        <input type="number" name="sizes[13.5][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[13.5][size]" value="13.5">
                                    </div>
                                    <div class="size-stock-row">
                                        <label class="size-toggle">
                                            <input type="checkbox" name="size_enabled[]" value="14" onchange="toggleSizeStock(this)">
                                            <span class="size-label">14</span>
                                        </label>
                                        <input type="number" name="sizes[14][stock]" class="stock-input" placeholder="Stock" min="0" disabled>
                                        <input type="hidden" name="sizes[14][size]" value="14">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small style="color: #666; font-size: 0.8rem;">Select sizes and set individual stock levels for each size.</small>
                    </div>
                    <div class="form-group">
                        <label for="product-price">Price (₱)</label>
                        <input type="number" id="product-price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="product-min-stock">Minimum Stock Level</label>
                        <input type="number" id="product-min-stock" name="min_stock" min="0" required>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="product-image">Product Image <span style="color: red;">*</span></label>
                        <input type="file" id="product-image" name="image" accept="image/*" class="file-input" required>
                        <small style="color: #666; font-size: 0.8rem;">Please select an image for the product (JPEG, PNG, JPG, GIF - Max 2MB)</small>
                        <div class="file-preview" id="image-preview" style="display: none;">
                            <img id="preview-img" src="" alt="Image preview" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                            <button type="button" onclick="removeImage()" class="btn-remove-image">Remove</button>
                        </div>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="product-description">Description (Optional)</label>
                        <textarea id="product-description" name="description" rows="3" placeholder="Enter product description..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Time and date display
function updateDateTime() {
    const now = new Date();
    
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    };
    document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
    
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
}

setInterval(updateDateTime, 1000);
updateDateTime();

// Inventory data
let inventoryData = [];

// Load real inventory data from API
function loadInventoryData() {
    fetch('{{ route("inventory.data") }}')
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data); // Debug log
            inventoryData = data.products;
            console.log('Inventory Data:', inventoryData); // Debug log
            updateKPIs(data.stats);
            renderInventoryTable();
        })
        .catch(error => {
            console.error('Error loading inventory data:', error);
            // Fallback to sample data
            loadSampleData();
        });
}

// Fallback sample data
function loadSampleData() {
    inventoryData = [
        {
            id: 1,
            product_id: "SV-MEN-ABC123",
            name: "Nike Air Max 270",
            brand: "Nike",
            category: "men",
            total_stock: 15,
            min_stock: 5,
            price: 7500,
            available_sizes: ["8", "9", "10", "11"],
            stock_status: "in-stock",
            image_url: null
        },
        {
            id: 2,
            product_id: "SV-WOM-XYZ456", 
            name: "Adidas Ultraboost 22",
            brand: "Adidas",
            category: "women", 
            total_stock: 3,
            min_stock: 5,
            price: 8200,
            available_sizes: ["6", "7", "8"],
            stock_status: "low-stock",
            image_url: null
        },
        {
            id: 3,
            product_id: "SV-ACC-DEF789",
            name: "Leather Belt",
            brand: "ShoeVault",
            category: "accessories",
            total_stock: 25,
            min_stock: 10,
            price: 1200,
            available_sizes: ["S", "M", "L"],
            stock_status: "in-stock",
            image_url: null
        }
    ];
    
    const stats = {
        total_products: inventoryData.length,
        low_stock_items: inventoryData.filter(item => item.total_stock <= item.min_stock).length,
        total_categories: 3,
        inventory_value: inventoryData.reduce((sum, item) => sum + (item.price * item.total_stock), 0)
    };
    
    updateKPIs(stats);
    renderInventoryTable();
}

// Update KPI cards
function updateKPIs(stats) {
    document.getElementById('total-products').textContent = stats.total_products || 0;
    document.getElementById('low-stock-items').textContent = stats.low_stock_items || 0;
    document.getElementById('total-categories').textContent = stats.total_categories || 3;
    document.getElementById('inventory-value').textContent = `₱${(stats.inventory_value || 0).toLocaleString()}`;
}

// Render inventory table
function renderInventoryTable() {
    const tbody = document.getElementById('inventory-tbody');
    
    if (inventoryData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No products in inventory</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = inventoryData.map(item => `
        <tr>
            <td>
                <div class="product-cell">
                    <div class="product-image">
                        <img src="${item.image_url ? '/'+item.image_url : 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/300px-No_image_available.svg.png'}" 
                             alt="${item.name}" 
                             onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/300px-No_image_available.svg.png'">
                    </div>
                    <span>${item.name}</span>
                </div>
            </td>
            <td>
                <code class="product-id">${item.product_id || 'Not Set'}</code>
            </td>
            <td>${item.brand}</td>
            <td>
                <span class="category-badge ${item.category}">
                    ${item.category.charAt(0).toUpperCase() + item.category.slice(1)}
                </span>
            </td>
            <td>
                <span class="color-display">${item.color || 'Not specified'}</span>
            </td>
            <td>
                <div class="sizes-display">
                    ${item.available_sizes && item.available_sizes.length > 0 ? 
                        item.available_sizes.map(size => `<span class="size-badge">${size}</span>`).join(' ') : 
                        '<span class="no-sizes">No sizes</span>'}
                </div>
            </td>
            <td>
                <span class="stock-count ${item.total_stock <= item.min_stock ? 'low' : ''}">${item.total_stock || 0}</span>
            </td>
            <td>₱${parseFloat(item.price).toLocaleString()}</td>
            <td>
                <span class="status-badge ${item.stock_status || (item.total_stock <= 0 ? 'out-of-stock' : item.total_stock <= item.min_stock ? 'low-stock' : 'in-stock')}">
                    ${item.total_stock <= 0 ? 'Out of Stock' : 
                      item.total_stock <= item.min_stock ? 'Low Stock' : 'In Stock'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon edit" onclick="editProduct(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" onclick="deleteProduct(${item.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Function to add a single product to the table
function addProductToTable(product) {
    const tbody = document.getElementById('inventory-tbody');
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td>
            <div class="product-cell">
                <div class="product-image">
                    <img src="${product.image_url ? '/'+product.image_url : 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/300px-No_image_available.svg.png'}" 
                         alt="${product.name}" 
                         onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/300px-No_image_available.svg.png'">
                </div>
                <span>${product.name}</span>
            </div>
        </td>
        <td>
            <code class="product-id">${product.product_id || 'Not Set'}</code>
        </td>
        <td>${product.brand}</td>
        <td>
            <span class="category-badge ${product.category}">
                ${product.category.charAt(0).toUpperCase() + product.category.slice(1)}
            </span>
        </td>
        <td>
            <span class="color-display">${product.color || 'Not specified'}</span>
        </td>
        <td>
            <div class="sizes-display">
                ${product.available_sizes && product.available_sizes.length > 0 ? 
                    product.available_sizes.map(size => `<span class="size-badge">${size}</span>`).join(' ') : 
                    '<span class="no-sizes">No sizes</span>'}
            </div>
        </td>
        <td>
            <span class="stock-count ${product.total_stock <= product.min_stock ? 'low' : ''}">${product.total_stock || 0}</span>
        </td>
        <td>₱${parseFloat(product.price).toLocaleString()}</td>
        <td>
            <span class="status-badge ${product.total_stock <= 0 ? 'out-of-stock' : 
                  product.total_stock <= product.min_stock ? 'low-stock' : 'in-stock'}">
                ${product.total_stock <= 0 ? 'Out of Stock' : 
                  product.total_stock <= product.min_stock ? 'Low Stock' : 'In Stock'}
            </span>
        </td>
        <td>
            <div class="action-buttons">
                <button class="btn-icon edit" onclick="editProduct(${product.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon delete" onclick="deleteProduct(${product.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    // Remove loading row if it exists
    const loadingRow = tbody.querySelector('.loading-row');
    if (loadingRow) {
        loadingRow.remove();
    }
    
    // Add the new row at the top
    tbody.insertBefore(newRow, tbody.firstChild);
}

// Modal functions
function openAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'flex';
}

function closeAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'none';
    document.getElementById('add-product-form').reset();
    // Reset image preview
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
    // Reset size selections
    document.querySelectorAll('input[name="size_enabled[]"]').forEach(checkbox => {
        checkbox.checked = false;
        toggleSizeStock(checkbox); // This will disable and clear the stock inputs
    });
}

// Show success message
function showSuccessMessage(message) {
    // Create a temporary success notification
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 10000;
        font-weight: 500;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Update KPI counts
function updateKPICounts() {
    // Count total products in table
    const rows = document.querySelectorAll('#inventory-tbody tr:not(.loading-row)');
    document.getElementById('total-products').textContent = rows.length;
    
    // Count low stock items
    const lowStockItems = document.querySelectorAll('.status-badge.low-stock').length;
    document.getElementById('low-stock-items').textContent = lowStockItems;
}

// Clear all size selections
function clearSizeSelections() {
    document.querySelectorAll('.size-group').forEach(group => {
        group.style.display = 'none';
    });
    document.querySelectorAll('input[name="size_enabled[]"]').forEach(checkbox => {
        checkbox.checked = false;
        toggleSizeStock(checkbox);
    });
}

// Toggle size stock inputs
function toggleSizeStock(checkbox) {
    const sizeRow = checkbox.closest('.size-stock-row');
    const stockInput = sizeRow.querySelector('.stock-input');
    
    if (checkbox.checked) {
        stockInput.disabled = false;
        stockInput.required = true;
        stockInput.focus();
    } else {
        stockInput.disabled = true;
        stockInput.required = false;
        stockInput.value = '';
    }
}

// Category change handler - removed since we show all sizes regardless of category

// Image preview functionality
document.getElementById('product-image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            alert('File size must be less than 2MB');
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

function removeImage() {
    document.getElementById('product-image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
}

// Add product form submission
document.getElementById('add-product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Check if image is required and selected
    const imageInput = document.getElementById('product-image');
    if (!imageInput.files || imageInput.files.length === 0) {
        alert('Please select a product image before adding the product.');
        return;
    }
    
    // Validate that at least one size is selected
    const selectedSizes = document.querySelectorAll('input[name="size_enabled[]"]:checked');
    if (selectedSizes.length === 0) {
        alert('Please select at least one size for the product.');
        return;
    }
    
    // Validate stock inputs for selected sizes
    let hasValidStock = false;
    for (let checkbox of selectedSizes) {
        const sizeRow = checkbox.closest('.size-stock-row');
        const stockInput = sizeRow.querySelector('.stock-input');
        if (stockInput.value && parseInt(stockInput.value) >= 0) {
            hasValidStock = true;
            break;
        }
    }
    
    if (!hasValidStock) {
        alert('Please enter stock quantities for selected sizes.');
        return;
    }
    
    const formData = new FormData();
    
    // Add basic product data
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('name', document.getElementById('product-name').value);
    formData.append('brand', document.getElementById('product-brand').value);
    formData.append('category', document.getElementById('product-category').value);
    formData.append('color', document.getElementById('product-color').value);
    formData.append('price', document.getElementById('product-price').value);
    formData.append('min_stock', document.getElementById('product-min-stock').value);
    formData.append('description', document.getElementById('product-description').value);
    formData.append('image', imageInput.files[0]);
    
    // Add size data
    let sizeIndex = 0;
    selectedSizes.forEach(checkbox => {
        const sizeValue = checkbox.value;
        const sizeRow = checkbox.closest('.size-stock-row');
        const stockInput = sizeRow.querySelector('.stock-input');
        
        if (stockInput.value) {
            formData.append(`sizes[${sizeIndex}][size]`, sizeValue);
            formData.append(`sizes[${sizeIndex}][stock]`, stockInput.value);
            formData.append(`sizes[${sizeIndex}][price_adjustment]`, 0); // Default to 0 since all sizes have same price
            sizeIndex++;
        }
    });
    
    // Debug: Check form data
    console.log('Form data being sent:');
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log(pair[0] + ': File - ' + pair[1].name + ' (Size: ' + pair[1].size + ')');
        } else {
            console.log(pair[0] + ': ' + pair[1]);
        }
    }
    
    fetch('{{ route("inventory.products.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response is not valid JSON:', text);
                throw new Error('Server returned invalid response: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            // Add the new product to the table immediately
            addProductToTable(data.product);
            
            // Update KPI counts
            updateKPICounts();
            
            // Close modal and show success message
            closeAddProductModal();
            
            // Show a more user-friendly success message
            const successMessage = `${data.product.name} has been added successfully!`;
            showSuccessMessage(successMessage);
        } else {
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                for (let field in data.errors) {
                    errorMessage += field + ': ' + data.errors[field].join(', ') + '\n';
                }
                alert(errorMessage);
            } else {
                alert('Error adding product: ' + (data.message || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product: ' + error.message);
    });
});

// Edit and delete functions
function editProduct(id) {
    // TODO: Implement edit functionality
    alert(`Edit product ${id} - Feature coming soon`);
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch(`{{ url('inventory/products') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadInventoryData(); // Reload data
                alert('Product deleted successfully!');
            } else {
                alert('Error deleting product: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product');
        });
    }
}

// Search functionality
document.getElementById('search-inventory').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredData = inventoryData.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.brand.toLowerCase().includes(searchTerm) ||
        (item.product_id && item.product_id.toLowerCase().includes(searchTerm)) ||
        (item.sku && item.sku.toLowerCase().includes(searchTerm))
    );
    
    renderFilteredTable(filteredData);
});

// Filter functionality
document.getElementById('category-filter').addEventListener('change', function(e) {
    const category = e.target.value;
    const filteredData = category === 'all' ? 
        inventoryData : 
        inventoryData.filter(item => item.category === category);
    
    renderFilteredTable(filteredData);
});

document.getElementById('stock-filter').addEventListener('change', function(e) {
    const stockLevel = e.target.value;
    let filteredData = inventoryData;
    
    switch(stockLevel) {
        case 'in-stock':
            filteredData = inventoryData.filter(item => item.total_stock > item.min_stock);
            break;
        case 'low-stock':
            filteredData = inventoryData.filter(item => item.total_stock <= item.min_stock && item.total_stock > 0);
            break;
        case 'out-of-stock':
            filteredData = inventoryData.filter(item => item.total_stock <= 0);
            break;
    }
    
    renderFilteredTable(filteredData);
});

// Render filtered table
function renderFilteredTable(data) {
    const tbody = document.getElementById('inventory-tbody');
    const tempData = inventoryData;
    inventoryData = data;
    renderInventoryTable();
    inventoryData = tempData;
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('add-product-modal');
    if (e.target === modal) {
        closeAddProductModal();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadInventoryData();
});
</script>
@endpush
