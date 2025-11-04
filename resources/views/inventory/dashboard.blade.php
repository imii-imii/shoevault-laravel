@extends('layouts.app')

@section('title', 'Inventory Management - ShoeVault Batangas')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
/* Gradient pill logout button */
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
.logout-btn i{font-size:1rem}
/* Remove Image Button Styles */
.remove-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(252, 248, 248, 0.08);
    border: none;
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
    cursor: pointer;
    padding: 0;
    transition: background 0.2s;
}
.remove-image:hover {
    background: rgba(239,68,68,0.15);
}
.remove-image i, .remove-image svg {
    display: block;
    margin: 0;
    font-size: 1.3rem;
    color: #e0e1e4ff;
    pointer-events: none;
}

/* Modal System Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
}

.modal-overlay.active {
    display: block !important;
}

.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10000;
    max-width: 90vw;
    max-height: 90vh;
    display: none;
}

.modal.active {
    display: flex !important;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Filter Bar Styles */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #fff;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(67, 56, 202, 0.08);
}

.filter-search {
    flex: 1;
    position: relative;
}

.filter-search input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}

.filter-search input:focus {
    outline: none;
    border-color: #2a6aff;
}

.filter-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}

.filter-categories {
    display: flex;
    gap: 8px;
    align-items: center;
}

.filter-categories span {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    border-color: #2a6aff;
    color: #2a6aff;
}

.filter-btn.active {
    background: #2a6aff;
    border-color: #2a6aff;
    color: #fff;
}

/* Upload box stacked (vertical) layout */
.upload-drop {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 18px !important;
    text-align: center !important;
}

.upload-drop i {
    font-size: 2rem !important;
    color: #2a6aff !important;
    margin: 0 !important;
}

.upload-drop h4 {
    font-size: 0.95rem !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    margin: 0 !important;
}

.upload-drop p {
    font-size: 0.85rem !important;
    color: #64748b !important;
    margin: 0 !important;
}

.upload-drop .or-text {
    color: #94a3b8 !important;
    font-size: 0.85rem !important;
    margin: 0 !important;
}

.upload-drop .browse-link {
    color: #2a6aff !important;
    font-weight: 600 !important;
    font-size: 0.85rem !important;
    cursor: pointer !important;
    margin-top: 4px !important;
}

/* State: when an image is present, hide placeholder and show preview only */
.upload-box.has-image .upload-drop { display: none !important; }
.upload-box.has-image .image-preview { display: block !important; }

/* Notification Styles */
.notification-wrapper { position: relative; }
.notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
.notification-bell:hover { background:#f3f4f6; color:#1f2937; }
.notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
.notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:9999; }
.notification-wrapper.open .notification-dropdown { display:block; }
.notification-list { max-height:300px; overflow-y:auto; }
.notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }
</style>
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
                <i class="fas fa-box-open"></i>
                <span>Add Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.suppliers') }}" class="nav-link">
                <i class="fas fa-people-carry-box"></i>
                <span>Supplier</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.reservation-reports') }}" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Reservation Reports</span>
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
                <img src="{{ auth()->user() && auth()->user()->profile_picture && file_exists(public_path(auth()->user()->profile_picture)) ? asset(auth()->user()->profile_picture) : asset('assets/images/profile.png') }}" 
                     alt="Manager" 
                     class="sidebar-avatar-img">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name }}</h4>
                <span>{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: inline;">
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
            <h1 class="main-title" id="page-title">Add Inventory</h1>
        </div>
        <div class="header-right" style="position:relative;">
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span id="current-time">Loading...</span>
            </div>
            <div class="date-display" style="display:flex;align-items:center;gap:12px;">
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
        <!-- Add Inventory Section -->
        <section id="add-inventory" class="content-section active">
            <!-- Product Details Modal -->
            <div id="product-details-modal" class="modal" style="max-width: 600px; display:none;">
                <div class="modal-content" style="max-width:100%; min-height:400px; display:flex; flex-direction:column;">
                    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between; border-bottom:1px solid #e2e8f0; padding:24px 32px 12px 32px;">
                        <h3 style="margin:0; font-size:1.28rem; font-weight:700;">Product Details</h3>
                        <button class="close-btn" style="font-size:1.2rem; color:#718096; background:none; border:none; cursor:pointer;" onclick="closeProductDetailsModal()">&times;</button>
                    </div>
                    <div id="product-details-content" style="display:flex; flex-direction:row; gap:40px; margin:32px; align-items:center; justify-content:flex-start; min-height:320px;font-size: 1.2rem;">
                        <!-- Details will be injected by JS -->
                    </div>
                </div>
            </div>
            <div id="modal-overlay" class="modal-overlay" style="display:none;"></div>
            
            <div class="section-header">
                <h2 style="display:flex;align-items:center;gap:12px;"><i class="fas fa-box-open"></i> Add Inventory</h2>
                <div style="margin-left:auto;display:flex;align-items:center;gap:16px;">
                    <div id="type-toggle" style="display:flex;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:9999px;padding:4px;gap:4px;box-shadow:0 2px 8px rgba(67,56,202,0.08);">
                        @php $type = isset($inventoryType) ? $inventoryType : 'pos'; @endphp
                        <button type="button" data-type="pos" class="type-chip" style="border:none;border-radius:9999px;padding:8px 14px;font-weight:700;font-size:.9rem;cursor:pointer;{{ ($type==='pos') ? 'background:#2a6aff;color:#fff;' : 'background:transparent;color:#334155;' }}">POS</button>
                        <button type="button" data-type="reservation" class="type-chip" style="border:none;border-radius:9999px;padding:8px 14px;font-weight:700;font-size:.9rem;cursor:pointer;{{ ($type==='reservation') ? 'background:#2a6aff;color:#fff;' : 'background:transparent;color:#334155;' }}">Reservation</button>
                    </div>
                    <select id="inventory-type-switcher" style="display:none;">
                        <option value="pos" {{ ($type === 'pos') ? 'selected' : '' }}>POS Inventory</option>
                        <option value="reservation" {{ ($type === 'reservation') ? 'selected' : '' }}>Reservation Inventory</option>
                    </select>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="product-search" placeholder="Search shoes by name, brand, or model...">
                </div>
                <div class="filter-categories">
                    <span>Category:</span>
                    <button class="filter-btn active" data-category="all">All Products</button>
                    <button class="filter-btn" data-category="men">Men</button>
                    <button class="filter-btn" data-category="women">Women</button>
                    <button class="filter-btn" data-category="accessories">Accessories</button>
                </div>
            </div>
            
            <div class="toggle">
                <button id="toggle-card-view" style="position:absolute;right: 20px; bottom: 20px; z-index:10;background:#2a6aff;color:#fff;border:none;border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(67,56,202,0.18);font-size:1.2rem;cursor:pointer;">
                    <i class="fas fa-th-large" id="toggle-card-icon"></i>
                </button>
            </div>
            
            <div class="add-inventory-list" style="position:relative; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; align-items: start; align-content: start; padding: 0; width:100%;">
                <!-- Add Product Card/Button (card mode reference) -->
                <div class="product-card add-product-card" style="position:relative;min-width:220px;max-width:240px;height:340px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:16px;background:#f8fbff;box-shadow:none;padding:18px;cursor:pointer;border:2px dashed #a3bffa;" onclick="openAddProductModal()">
                    <div class="icon-box" style="width:48px;height:48px;border-radius:12px;background:#e6efff;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i class="fas fa-plus" style="font-size:1.2rem;color:#2a6aff;"></i>
                    </div>
                    <div class="add-text" style="font-weight:700;color:#2a6aff;font-size:1rem;">Add Product</div>
                </div>
                
                @foreach($products as $product)
                <div class="product-card" 
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}" 
                    data-brand="{{ $product->brand }}" 
                    data-category="{{ $product->category }}" 
                    data-price="{{ $product->price }}" 
                    data-stock="{{ $product->sizes->sum('stock') }}" 
                    data-sizes="{{ $product->sizes->pluck('size')->implode(', ') }}" 
                    data-color="{{ $product->color }}"
                    data-image="{{ $product->image_url }}"
                    style="position:relative;min-width:220px;max-width:240px;height:340px;display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(67,56,202,0.08);padding:0px 18px 18px 18px;cursor:pointer;" onclick="openProductDetailsModal('{{ $product->id }}')">

                    <!-- Category tag top-right -->
                    @php $cat = strtolower($product->category ?? ''); @endphp
                    <div class="category-tag" style="position:absolute;top:10px;right:10px;color:#fff;font-size:.7rem;font-weight:700;padding:4px 8px;border-radius:999px;letter-spacing:.5px; {{ $cat==='men' ? 'background:linear-gradient(135deg,#3b82f6,#1d4ed8)' : ($cat==='women' ? 'background:linear-gradient(135deg,#ec4899,#be185d)' : 'background:linear-gradient(135deg,#10b981,#047857)') }} ">{{ strtoupper($product->category) }}</div>

                    <!-- Top image spanning full width with rounded top corners -->
                    <div class="card-image-top" style="width:calc(100% + 36px);height:200px;background:#e2e8f0;margin:-18px -18px 10px -18px;border-radius:16px 16px 0 0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                        @if(isset($product->image_url) && $product->image_url && strlen(trim($product->image_url)) > 0)
                            <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;object-position:center;" 
                                 onerror="this.src='{{ asset('assets/images/no-image-available.jpg') }}'">
                        @else
                            <img src="{{ asset('assets/images/no-image-available.jpg') }}" alt="No image available" style="width:100%;height:100%;object-fit:cover;object-position:center;">
                        @endif
                    </div>

                    <div class="pd-info" style="display:flex;flex-direction:column;gap:2px;padding:0 2px;">
                        <div class="pd-name" style="font-weight:700;font-size:0.95rem;color:#111827;">{{ $product->name }}</div>
                        <div class="pd-brand" style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;font-weight:600;">{{ $product->brand }}</div>
                        <div class="pd-color" style="font-size:0.8rem;color:#374151;">{{ $product->color ?: '—' }}</div>
                        <div class="pd-category" style="display:none;">{{ ucfirst($product->category) }}</div>
                        <span class="pd-price" style="font-size:0.95rem;font-weight:800;color:#111827;margin-top:6px;">₱ {{ number_format((float)$product->price, 0, '.', ',') }}</span>
                        <span class="pd-stock" style="font-size:0.78rem;color:#2a6aff;font-weight:600;margin-top:-18px;align-self:flex-end;">{{ $product->sizes->sum('stock') }} in stock</span>
                        <div class="pd-sizes" style="font-size:0.78rem;color:#374151;margin-top:6px;">Sizes: {{ $product->sizes->pluck('size')->implode(', ') }}</div>
                    </div>
                    
                    <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;width:calc(100% + 36px);height:32px;border-radius:0 0 16px 16px;font-size:0.85rem;margin:8px -18px -18px -18px;padding:0;" onclick="event.stopPropagation(); openEditProductModal('{{ $product->id }}')">Update</button>
                </div>
                @endforeach
            </div>
        </section>

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
<div id="add-product-modal" class="modal" style="background: none; border: none; box-shadow: none;">
    <div class="modal-content" style="max-width:100%; min-height:560px; display:flex; flex-direction:column; justify-content:flex-start; align-items:stretch;">
        <div class="modal-header" style="padding:24px 32px 12px 32px; border-bottom:1px solid #e2e8f0;">
            <h3 style="margin:0; font-size:1.6rem; font-weight:700;">Add New Product</h3>
            <button class="close-btn" style="font-size:1.5rem; color:#718096; background:none; border:none; cursor:pointer;" onclick="closeAddProductModal()">&times;</button>
        </div>
        <form id="add-product-form" class="modal-form" style="flex:1; display:flex; flex-direction:row; gap:32px; overflow:hidden; min-height:0;" enctype="multipart/form-data">
            @csrf
            <div class="upload-card" style="flex:0 0 440px; display:flex; flex-direction:column; justify-content:space-between; height:560px;">
                <div class="upload-box" role="button" tabindex="0" style="flex:1;" onclick="document.getElementById('product-image').click()">
                    <div class="upload-drop" id="upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div style="display:flex;flex-direction:column;gap:2px;">
                            <h4>Drop files</h4>
                            <p>Supported format: PNG, JPG</p>
                        </div>
                        <span class="or-text">OR</span>
                        <span class="browse-link">Browse files</span>
                    </div>
                    <div class="image-preview" id="image-preview" style="display: none; width: 100%; height: 100%; position: relative;">
                        <img id="preview-img" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; object-position: center; border-radius: 8px;">
                        <button type="button" class="remove-image" onclick="removeImage(event)" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <input type="file" id="product-image" name="image" accept="image/*" style="display: none;" required>
                <div class="upload-actions" style="margin-top:18px; display:flex; gap:12px;">
                    <button type="button" class="btn btn-secondary" style="height:40px;display:flex;align-items:center;" onclick="closeAddProductModal()">Cancel</button>
                    <button type="button" class="btn btn-primary browse-btn" style="height:40px;display:flex;align-items:center;margin: 0;">Upload</button>
                </div>
            </div>
            <div class="info-card" style="flex:1; display:flex; flex-direction:column; max-height:560px; overflow-y:auto; padding-right:8px;">
                <div class="info-title" style="font-weight:600; font-size:1.1rem; margin-bottom:12px;">Inventory item Information</div>
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="name" placeholder="Please enter item name." required style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="product-brand">Brand</label>
                    <input type="text" id="product-brand" name="brand" placeholder="Please enter brand name." style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="product-category">Category</label>
                    <select id="product-category" name="category" required style="font-size: 1rem;">
                        <option value="">Select Category</option>
                        <option value="men">Men's Shoes</option>
                        <option value="women">Women's Shoes</option>
                        <option value="accessories">Accessories</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="product-price">Price</label>
                    <input type="number" id="product-price" name="price" min="0" step="0.01" placeholder="Please enter item price" required style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="product-size-stock-container">Sizes & Stock</label>
                    <div id="product-size-stock-container" style="border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;padding:12px;margin-bottom:6px;">
                        <div id="product-size-stock-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
                        <div style="position:relative;display:flex;align-items:center;gap:8px;width:100%;">
                            <input type="number" id="product-size-input" placeholder="Size" min="1" max="50" style="width:100px;font-size:0.9rem;">
                            <input type="number" id="product-stock-input" placeholder="Stock" min="0" style="width:100px;font-size:0.9rem;">
                            <button type="button" id="product-size-stock-add" style="background:#2a6aff;color:white;border:none;border-radius:4px;padding:6px 12px;cursor:pointer;font-size:0.9rem;">
                                Add Size
                            </button>
                        </div>
                        <div id="total-stock-display" style="margin-top:8px;font-weight:600;color:#2a6aff;font-size:0.9rem;">Total Stock: 0</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="product-color">Color</label>
                    <input type="text" id="product-color" name="color" placeholder="Enter product color" style="width:100%;font-size:1rem;" required>
                </div>
                <div style="flex:1;"></div>
                <div class="modal-create" style="margin-top:18px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary browse-btn" style="min-width:220px;max-width:220px;height:40px;font-size:0.88rem;font-weight:600;border-radius:16px;display:flex;align-items:center;justify-content:center;">Add Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="edit-product-modal" class="modal" style="background: none; border: none; box-shadow: none; display: none;">
    <div class="modal-content" style="max-width:100%; min-height:480px; display:flex; flex-direction:column; justify-content:flex-start; align-items:stretch;">
        <div class="modal-header" style="padding:24px 32px 12px 32px; border-bottom:1px solid #e2e8f0;">
            <h3 style="margin:0; font-size:1.6rem; font-weight:700;">Edit Product</h3>
            <button class="close-btn" style="font-size:1.5rem; color:#718096; background:none; border:none; cursor:pointer;" onclick="closeEditProductModal()">&times;</button>
        </div>
        <form id="edit-product-form" class="modal-form" style="flex:1; display:flex; flex-direction:row; gap:32px; overflow:hidden; min-height:0;" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-product-id" name="id">
            <div class="upload-card" style="flex:0 0 400px; display:flex; flex-direction:column; justify-content:space-between; height:480px;">
                <div class="upload-box" role="button" tabindex="0" style="flex:1;" onclick="document.getElementById('edit-product-image').click()">
                    <div class="upload-drop" id="edit-upload-placeholder" style="display: none;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div style="display:flex;flex-direction:column;gap:2px;">
                            <h4>Drop files</h4>
                            <p>Supported format: PNG, JPG</p>
                        </div>
                        <span class="or-text">OR</span>
                        <span class="browse-link">Browse files</span>
                    </div>
                    <div class="image-preview" id="edit-image-preview" style="width: 100%; height: 100%; position: relative;">
                        <img id="edit-preview-img" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; object-position: center; border-radius: 8px;">
                        <button type="button" class="remove-image" onclick="removeEditImage(event)" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <input type="file" id="edit-product-image" name="image" accept="image/*" style="display: none;">
                <div class="upload-actions" style="margin-top:18px; display:flex; gap:12px;">
                    <button type="button" class="btn btn-secondary" style="height:40px;display:flex;align-items:center;" onclick="closeEditProductModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" style="height:40px;display:flex;align-items:center;margin: 0;" onclick="deleteCurrentProduct()">Delete Product</button>
                </div>
            </div>
            <div class="info-card" style="flex:1; display:flex; flex-direction:column; max-height:480px; overflow-y:auto; padding-right:8px;">
                <div class="info-title" style="font-weight:600; font-size:1.1rem; margin-bottom:12px;">Edit Product Information</div>
                <div class="form-group">
                    <label for="edit-product-name">Product Name</label>
                    <input type="text" id="edit-product-name" name="name" placeholder="Please enter item name." required style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="edit-product-brand">Brand</label>
                    <input type="text" id="edit-product-brand" name="brand" placeholder="Please enter brand name." style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="edit-product-category">Category</label>
                    <select id="edit-product-category" name="category" required style="font-size: 1rem;">
                        <option value="">Select Category</option>
                        <option value="men">Men's Shoes</option>
                        <option value="women">Women's Shoes</option>
                        <option value="accessories">Accessories</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-product-price">Price</label>
                    <input type="number" id="edit-product-price" name="price" min="0" step="0.01" placeholder="Please enter item price" required style="font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label for="edit-product-size-stock-container">Sizes & Stock</label>
                    <div id="edit-product-size-stock-container" style="border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;padding:12px;margin-bottom:6px;">
                        <div id="edit-product-size-stock-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
                        <div style="position:relative;display:flex;align-items:center;gap:8px;width:100%;">
                            <input type="number" id="edit-product-size-input" placeholder="Size" min="1" max="50" style="width:100px;font-size:0.9rem;">
                            <input type="number" id="edit-product-stock-input" placeholder="Stock" min="0" style="width:100px;font-size:0.9rem;">
                            <button type="button" id="edit-product-size-stock-add" style="background:#2a6aff;color:white;border:none;border-radius:4px;padding:12px 15px;cursor:pointer;font-size:0.9rem;">
                                Add Size
                            </button>
                        </div>
                        <div id="edit-total-stock-display" style="margin-top:8px;font-weight:600;color:#2a6aff;font-size:0.9rem;">Total Stock: 0</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-product-color">Color</label>
                    <input type="text" id="edit-product-color" name="color" placeholder="Enter product color" style="width:100%;font-size:1rem;" required>
                </div>
                <div style="flex:1;"></div>
                <div class="modal-create" style="margin-top:18px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary browse-btn" style="min-width:220px;max-width:220px;height:40px;font-size:0.88rem;font-weight:600;border-radius:16px;display:flex;align-items:center;justify-content:center;">Update Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Navigation functionality
// Settings tab functionality
function initializeSettings() {
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panels
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding panel
            this.classList.add('active');
            const panel = document.getElementById(`settings-panel-${tabId}`);
            if (panel) {
                panel.classList.add('active');
            }
        });
    });
}

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
    const typeSel = document.getElementById('inventory-type-switcher');
    const type = typeSel ? typeSel.value : 'pos';
    const url = new URL('{{ route("inventory.data") }}', window.location.origin);
    url.searchParams.set('type', type);
    fetch(url.toString())
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
            // Show empty state instead of fallback data
            inventoryData = [];
            updateKPIs({
                total_products: 0,
                low_stock_items: 0,
                total_categories: 3,
                inventory_value: 0
            });
            renderInventoryTable();
        });
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
                <span class="stock-count ${item.total_stock <= 5 ? 'low' : ''}">${item.total_stock || 0}</span>
            </td>
            <td>₱${parseFloat(item.price).toLocaleString()}</td>
            <td>
                <span class="status-badge ${item.stock_status || (item.total_stock <= 0 ? 'out-of-stock' : item.total_stock <= 5 ? 'low-stock' : 'in-stock')}">
                    ${item.total_stock <= 0 ? 'Out of Stock' : 
                      item.total_stock <= 5 ? 'Low Stock' : 'In Stock'}
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
            <span class="stock-count ${product.total_stock <= 5 ? 'low' : ''}">${product.total_stock || 0}</span>
        </td>
        <td>₱${parseFloat(product.price).toLocaleString()}</td>
        <td>
            <span class="status-badge ${product.total_stock <= 0 ? 'out-of-stock' : 
                  product.total_stock <= 5 ? 'low-stock' : 'in-stock'}">
                ${product.total_stock <= 0 ? 'Out of Stock' : 
                  product.total_stock <= 5 ? 'Low Stock' : 'In Stock'}
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
    
    // Add the new row at the end (append to the end)
    tbody.appendChild(newRow);
}

// Function to add a single product to the card view
function addProductToCardView(product) {
    const cardContainer = document.querySelector('.add-inventory-list');
    
    // Create the new product card
    const newCard = document.createElement('div');
    newCard.className = 'product-card';
    newCard.setAttribute('data-id', product.id);
    newCard.setAttribute('data-name', product.name);
    newCard.setAttribute('data-brand', product.brand);
    newCard.setAttribute('data-category', product.category);
    newCard.setAttribute('data-price', product.price);
    newCard.setAttribute('data-stock', product.total_stock || 0);
    newCard.setAttribute('data-sizes', product.available_sizes ? product.available_sizes.join(', ') : '');
    newCard.setAttribute('data-color', product.color);
    newCard.setAttribute('data-image', product.image_url);
    newCard.style.cssText = 'min-width:240px;max-width:220px;height:340px;display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(67,56,202,0.08);padding:0px 18px 18px 18px;cursor:pointer;';
    newCard.setAttribute('onclick', `openProductDetailsModal('${product.id}')`);
    
    const catLower = (product.category || '').toLowerCase();
    const catStyle = catLower.includes('men')
        ? 'background:linear-gradient(135deg,#3b82f6,#1d4ed8)'
        : (catLower.includes('women')
            ? 'background:linear-gradient(135deg,#ec4899,#be185d)'
            : 'background:linear-gradient(135deg,#10b981,#047857)');
    newCard.innerHTML = `
        <div class="category-tag" style="position:absolute;top:10px;right:10px;color:#fff;font-size:.7rem;font-weight:700;padding:4px 8px;border-radius:999px;letter-spacing:.5px; ${catStyle}">${(product.category || '').toUpperCase()}</div>
    <div class="card-image-top" style="width:calc(100% + 36px);height:200px;background:#e2e8f0;margin:-18px -18px 10px -18px;border-radius:16px 16px 0 0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
            ${product.image_url && product.image_url.trim() ? 
                `<img src="${product.image_url.startsWith('http') ? product.image_url : '/'+product.image_url}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;object-position:center;" onerror="this.src='/assets/images/no-image-available.jpg'">` : 
                `<img src="/assets/images/no-image-available.jpg" alt="No image available" style="width:100%;height:100%;object-fit:cover;object-position:center;">`
            }
        </div>
        <div class="pd-info" style="display:flex;flex-direction:column;gap:2px;padding:0 2px;">
            <div class="pd-name" style="font-weight:700;font-size:0.95rem;color:#111827;">${product.name}</div>
            <div class="pd-brand" style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;font-weight:600;">${product.brand || ''}</div>
            <div class="pd-color" style="font-size:0.8rem;color:#374151;">${product.color || '—'}</div>
            <div class="pd-category" style="display:none;">${(product.category || '').charAt(0).toUpperCase() + (product.category || '').slice(1)}</div>
            <span class="pd-price" style="font-size:0.95rem;font-weight:800;color:#111827;margin-top:6px;">₱ ${(parseFloat(product.price)||0).toLocaleString()}</span>
            <span class="pd-stock" style="font-size:0.78rem;color:#2a6aff;font-weight:600;margin-top:-18px;align-self:flex-end;">${product.total_stock || 0} in stock</span>
            <div class="pd-sizes" style="font-size:0.78rem;color:#374151;margin-top:6px;">Sizes: ${product.available_sizes ? product.available_sizes.join(', ') : 'N/A'}</div>
        </div>
    <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;width:calc(100% + 36px);height:32px;border-radius:0 0 16px 16px;font-size:0.85rem;margin:8px -18px -18px -18px;padding:0;" onclick="event.stopPropagation(); openEditProductModal('${product.id}')">Update</button>
    `;
    
    // Add the new card at the end of the container (append to the end)
    cardContainer.appendChild(newCard);

    // Ensure the new card respects the current view mode
    if (typeof window.currentCardView !== 'undefined') {
        applyCardViewToCards(window.currentCardView);
    }
}

// Function to update a product in both card and table view
function updateProductInView(product) {
    // Derive sizes/stock if backend returned raw sizes
    const derivedSizes = Array.isArray(product.available_sizes)
        ? product.available_sizes
        : (Array.isArray(product.sizes) ? product.sizes.map(s => s.size) : []);
    const derivedTotalStock = (product.total_stock !== undefined && product.total_stock !== null)
        ? Number(product.total_stock)
        : (Array.isArray(product.sizes) ? product.sizes.reduce((sum, s) => sum + (parseInt(s.stock, 10) || 0), 0) : 0);
    // Normalize for downstream UI usage
    product.available_sizes = derivedSizes;
    product.total_stock = derivedTotalStock;

    // Update the product card
    const productCard = document.querySelector(`[data-id="${product.id}"]`);
    if (productCard) {
        // Update data attributes
        productCard.setAttribute('data-name', product.name);
        productCard.setAttribute('data-brand', product.brand);
        productCard.setAttribute('data-category', product.category);
        productCard.setAttribute('data-price', product.price);
        productCard.setAttribute('data-stock', (product.total_stock || 0).toString());
        productCard.setAttribute('data-sizes', derivedSizes.length ? derivedSizes.join(', ') : '');
        productCard.setAttribute('data-color', product.color);
        productCard.setAttribute('data-image', product.image_url);
        
        // Update the card content
        const catLower = (product.category || '').toLowerCase();
        const catStyle = catLower.includes('men')
            ? 'background:linear-gradient(135deg,#3b82f6,#1d4ed8)'
            : (catLower.includes('women')
                ? 'background:linear-gradient(135deg,#ec4899,#be185d)'
                : 'background:linear-gradient(135deg,#10b981,#047857)');
        productCard.innerHTML = `
            <div class="category-tag" style="position:absolute;top:10px;right:10px;color:#fff;font-size:.7rem;font-weight:700;padding:4px 8px;border-radius:999px;letter-spacing:.5px; ${catStyle}">${(product.category || '').toUpperCase()}</div>
            <div class="card-image-top" style="width:calc(100% + 36px);height:200px;background:#e2e8f0;margin:-18px -18px 10px -18px;border-radius:16px 16px 0 0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                ${product.image_url && product.image_url.trim() ? 
                    `<img src="${product.image_url.startsWith('http') ? product.image_url : '/'+product.image_url}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;object-position:center;" onerror="this.src='/assets/images/no-image-available.jpg'">` : 
                    `<img src="/assets/images/no-image-available.jpg" alt="No image available" style="width:100%;height:100%;object-fit:cover;object-position:center;">`
                }
            </div>
            <div class="pd-info" style="display:flex;flex-direction:column;gap:2px;padding:0 2px;">
                <div class="pd-name" style="font-weight:700;font-size:0.95rem;color:#111827;">${product.name}</div>
                <div class="pd-brand" style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;font-weight:600;">${product.brand || ''}</div>
                <div class="pd-color" style="font-size:0.8rem;color:#374151;">${product.color || '—'}</div>
                <div class="pd-category" style="display:none;">${(product.category || '').charAt(0).toUpperCase() + (product.category || '').slice(1)}</div>
                <span class="pd-price" style="font-size:0.95rem;font-weight:800;color:#111827;margin-top:6px;">₱ ${(parseFloat(product.price)||0).toLocaleString()}</span>
                <span class="pd-stock" style="font-size:0.78rem;color:#2a6aff;font-weight:600;margin-top:-18px;align-self:flex-end;">${derivedTotalStock || 0} in stock</span>
                <div class="pd-sizes" style="font-size:0.78rem;color:#374151;margin-top:6px;">Sizes: ${derivedSizes.length ? derivedSizes.join(', ') : 'N/A'}</div>
            </div>
            <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;width:calc(100% + 36px);height:32px;border-radius:0 0 16px 16px;font-size:0.85rem;margin:8px -18px -18px -18px;padding:0;" onclick="event.stopPropagation(); openEditProductModal('${product.id}')">Update</button>
        `;
    }
    
    // Update the inventory data array
    const inventoryIndex = inventoryData.findIndex(item => item.id === product.id);
    if (inventoryIndex !== -1) {
        // Merge and normalize fields so table reflects new sizes/stock immediately
        inventoryData[inventoryIndex] = {
            ...inventoryData[inventoryIndex],
            ...product,
            total_stock: product.total_stock,
            available_sizes: product.available_sizes,
        };
        // Re-render the table to show updated data
        renderInventoryTable();
    }
    // Re-apply current card view mode so updated card keeps layout
    if (typeof window.currentCardView !== 'undefined') {
        applyCardViewToCards(window.currentCardView);
    }
}

// Modal functions
function openAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'flex';
}

function closeAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'none';
    document.getElementById('add-product-form').reset();
    // Reset image preview
    const addUploadBox = document.querySelector('#add-product-form .upload-box');
    if (addUploadBox) addUploadBox.classList.remove('has-image');
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

// ===== Card view toggle (grid <-> horizontal) =====
window.currentCardView = 'grid';

function applyCardViewToCards(mode) {
    const container = document.querySelector('.add-inventory-list');
    const cards = Array.from(container.querySelectorAll('.product-card'));
    // Adjust container layout
    if (mode === 'horizontal') {
        // switch to a vertical list
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.flexWrap = 'nowrap';
        container.style.alignItems = 'stretch';
        container.style.gap = '12px';
    } else {
        // grid layout for cards
        container.style.display = 'grid';
        container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(220px, 1fr))';
        container.style.gap = '20px';
        container.style.alignItems = 'start';
        container.style.alignContent = 'start';
    }

    // helper to add a label before content once
    const addLabel = (el, label) => {
        if (!el) return;
        if (!el.dataset) return;
        if (el.dataset.hasLabel === '1') return;
        const labelSpan = document.createElement('span');
        labelSpan.className = 'pd-label';
        labelSpan.textContent = label + ' ';
        labelSpan.style.fontWeight = '700';
        labelSpan.style.color = '#111827';
        labelSpan.style.marginRight = '6px';
        el.insertBefore(labelSpan, el.firstChild);
        el.dataset.hasLabel = '1';
        el.style.display = 'flex';
        el.style.alignItems = 'center';
        el.style.gap = '6px';
    };
    // helper to remove labels and restore defaults
    const removeLabel = (el) => {
        if (!el) return;
        const first = el.firstElementChild;
        if (first && first.classList.contains('pd-label')) first.remove();
        if (el.dataset) delete el.dataset.hasLabel;
        el.style.display = '';
        el.style.alignItems = '';
        el.style.gap = '';
    };

    cards.forEach(card => {
        const isAddCard = card.classList.contains('add-product-card');
        const imgDiv = card.querySelector('.card-image-top');
        const infoDiv = imgDiv ? imgDiv.nextElementSibling : card.querySelector('.pd-info');
        const btn = card.querySelector('button.btn');
        const catTag = card.querySelector('.category-tag');

        if (mode === 'horizontal') {
            // Card as a horizontal row with: [image] [details grid 2x3] [right panel price+update]
            card.style.minWidth = '100%';
            card.style.maxWidth = '100%';
            card.style.height = 'auto';
            card.style.display = 'flex';
            card.style.flexDirection = 'row';
            card.style.alignItems = 'center';
            card.style.justifyContent = 'flex-start';
            card.style.padding = '12px 16px';

            if (isAddCard) {
                // Transform add card into horizontal banner
                const icon = card.querySelector('.icon-box');
                const text = card.querySelector('.add-text');
                if (icon) { icon.style.width = '56px'; icon.style.height = '56px'; icon.style.margin = '0 12px 0 0'; }
                if (text) { text.style.fontSize = '0.95rem'; text.style.margin = '0'; }
                card.style.border = '2px dashed #a3bffa';
                card.style.background = '#f8fbff';
                card.style.flexDirection = 'row';
                card.style.justifyContent = 'flex-start';
            } else {
                if (catTag) catTag.style.display = 'none'; // hide category pill in horizontal
                if (imgDiv) {
                    imgDiv.style.width = '88px';
                    imgDiv.style.height = '88px';
                    imgDiv.style.margin = '0 16px 0 0';
                    imgDiv.style.borderRadius = '12px';
                    imgDiv.style.overflow = 'hidden';
                }
                if (infoDiv) {
                    infoDiv.style.flex = '1';
                    infoDiv.style.padding = '0 8px';
                    // Turn info into 2 columns x 3 rows grid
                    infoDiv.style.display = 'grid';
                    infoDiv.style.gridTemplateColumns = '1fr 1fr';
                    infoDiv.style.gridTemplateRows = 'auto auto auto';
                    infoDiv.style.columnGap = '24px';
                    infoDiv.style.rowGap = '8px';
                    // Map fields into cells (Row1: Name | Category) (Row2: Brand | Color) (Row3: Sizes | Stock)
                    const nameEl = infoDiv.querySelector('.pd-name');
                    const brandEl = infoDiv.querySelector('.pd-brand');
                    const colorEl = infoDiv.querySelector('.pd-color');
                    const priceEl = infoDiv.querySelector('.pd-price');
                    const catEl = infoDiv.querySelector('.pd-category');
                    const sizesEl = infoDiv.querySelector('.pd-sizes');
                    const stockEl = infoDiv.querySelector('.pd-stock');
                    // Labels
                    addLabel(nameEl, 'Name:');
                    addLabel(brandEl, 'Brand:');
                    addLabel(stockEl, 'Stock:');
                    // We'll place Price on the right panel, so don't label it here
                    // Uniform fonts
                    [nameEl, brandEl, colorEl, priceEl, catEl, sizesEl, stockEl].forEach(el => { if (el) { el.style.fontSize = '0.95rem'; el.style.margin = '0'; el.style.lineHeight = '1.3'; } });
                    const allLabels = infoDiv.querySelectorAll('.pd-label');
                    allLabels.forEach(l => { l.style.fontSize = '0.95rem'; });
                    // Grid positions (2 columns x 3 rows)
                    if (nameEl) { nameEl.style.gridColumn = '1 / 2'; nameEl.style.gridRow = '1'; }
                    if (brandEl) { brandEl.style.gridColumn = '1 / 2'; brandEl.style.gridRow = '2'; brandEl.style.textAlign = 'left'; }
                    // Color in col 2 row 2 as pill(s)
                    if (colorEl) {
                        colorEl.style.gridColumn = '2 / 3';
                        colorEl.style.gridRow = '2';
                        if (!colorEl.dataset.original) colorEl.dataset.original = (card.getAttribute('data-color') || colorEl.textContent.trim());
                        const colors = (card.getAttribute('data-color') || '').split(',').map(c => c.trim()).filter(Boolean);
                        const pills = colors.map(c => `<span class=\"color-pill\" style=\"background:#e5e7eb;color:#111827;padding:2px 10px;border-radius:999px;font-size:.9rem;font-weight:700;display:inline-block;margin-right:6px;\">${c}</span>`).join(' ');
                        colorEl.innerHTML = `<span class=\"pd-label\" style=\"font-weight:700;color:#111827;margin-right:6px;\">Color:</span> ${pills || '<span style=\"color:#6b7280;\">—</span>'}`;
                        colorEl.dataset.pills = '1';
                    }
                    // Sizes in col 1 row 3 with chips
                    if (sizesEl) {
                        sizesEl.style.gridColumn = '1 / 2';
                        sizesEl.style.gridRow = '3';
                        if (!sizesEl.dataset.original) sizesEl.dataset.original = (card.getAttribute('data-sizes') || sizesEl.textContent.replace(/Sizes:\\s*/i,'').trim());
                        const sizes = (card.getAttribute('data-sizes') || '').split(',').map(s => s.trim()).filter(Boolean);
                        const pills = sizes.map(s => `<span class=\"size-pill\" style=\"background:#e6efff;color:#1e40af;padding:2px 10px;border-radius:999px;font-size:.9rem;font-weight:700;display:inline-block;margin-right:6px;\">${s}</span>`).join(' ');
                        sizesEl.innerHTML = `<span class=\"pd-label\" style=\"font-weight:700;color:#111827;margin-right:6px;\">Sizes:</span> ${pills || '<span style=\"color:#6b7280;\">N/A</span>'}`;
                        sizesEl.dataset.pills = '1';
                    }
                    // Category in col 2 row 1 as pill
                    if (catEl) {
                        catEl.style.display = 'block';
                        catEl.style.gridColumn = '2 / 3';
                        catEl.style.gridRow = '1';
                        // Replace with a pill element
                        const cat = (card.getAttribute('data-category') || catEl.textContent || '').toLowerCase();
                        const pillStyle = cat.includes('men') ? 'background:#111827;color:#fff' : (cat.includes('women') ? 'background:#111827;color:#fff' : 'background:#111827;color:#fff');
                        catEl.innerHTML = `<span class=\"pd-label\" style=\"font-weight:700;color:#111827;margin-right:6px;\">Category:</span> <span style=\"${pillStyle};padding:2px 10px;border-radius:999px;font-size:.9rem;font-weight:700;display:inline-block;\">${cat || '-'}</span>`;
                    }
                    // Stock in col 2 row 3
                    if (stockEl) {
                        stockEl.style.gridColumn = '2 / 3';
                        stockEl.style.gridRow = '3';
                        stockEl.style.justifySelf = 'start';
                        stockEl.style.fontWeight = '700';
                        stockEl.style.color = '#111827';
                    }

                    // Create or reuse right panel for Price + Update button
                    let right = card.querySelector('.inv-right');
                    if (!right) {
                        right = document.createElement('div');
                        right.className = 'inv-right';
                        right.style.display = 'flex';
                        right.style.flexDirection = 'column';
                        right.style.alignItems = 'flex-end';
                        right.style.gap = '8px';
                        right.style.marginLeft = 'auto';
                        right.style.minWidth = '140px';
                        card.appendChild(right);
                    }
                    if (priceEl) {
                        // Move price to right panel
                        right.appendChild(priceEl);
                        priceEl.style.gridColumn = '';
                        priceEl.style.gridRow = '';
                        priceEl.style.justifySelf = '';
                        priceEl.style.margin = '0';
                        priceEl.style.fontSize = '1rem';
                        priceEl.style.fontWeight = '800';
                    }
                }
                if (btn) {
                    // Place button under price on the right panel
                    let right = card.querySelector('.inv-right');
                    if (!right) {
                        right = document.createElement('div');
                        right.className = 'inv-right';
                        right.style.display = 'flex';
                        right.style.flexDirection = 'column';
                        right.style.alignItems = 'flex-end';
                        right.style.gap = '8px';
                        right.style.marginLeft = 'auto';
                        right.style.minWidth = '140px';
                        card.appendChild(right);
                    }
                    right.appendChild(btn);
                    btn.style.width = 'auto';
                    btn.style.margin = '0';
                    btn.style.borderRadius = '10px';
                    btn.style.minWidth = '96px';
                    btn.style.height = '34px';
                    btn.style.alignSelf = 'flex-end';
                    btn.style.fontSize = '0.85rem';
                    btn.style.padding = '0 12px';
                }
            }
        } else {
            // Card as a compact grid tile
            if (isAddCard) {
                // Restore to centered small dashed card
                card.style.minWidth = '220px';
                card.style.maxWidth = '240px';
                card.style.height = '340px';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';
                card.style.alignItems = 'center';
                card.style.justifyContent = 'center';
                card.style.padding = '18px';
                const icon = card.querySelector('.icon-box');
                const text = card.querySelector('.add-text');
                if (icon) { icon.style.width = '48px'; icon.style.height = '48px'; icon.style.margin = '0 0 10px 0'; }
                if (text) { text.style.fontSize = '1rem'; }
            } else {
                card.style.minWidth = '220px';
                card.style.maxWidth = '240px';
                card.style.height = '340px';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';
                card.style.alignItems = 'stretch';
                card.style.justifyContent = 'flex-start';
                card.style.padding = '0px 18px 18px 18px';

                if (catTag) catTag.style.display = '';
                if (imgDiv) {
                    imgDiv.style.width = 'calc(100% + 36px)';
                    imgDiv.style.height = '200px';
                    imgDiv.style.margin = '0px -18px 10px -18px';
                    imgDiv.style.borderRadius = '16px 16px 0 0';
                    imgDiv.style.overflow = 'hidden';
                    imgDiv.style.display = 'flex';
                    imgDiv.style.alignItems = 'center';
                    imgDiv.style.justifyContent = 'center';
                    
                }
                if (infoDiv) {
                    infoDiv.style.flex = '';
                    infoDiv.style.padding = '0 2px';
                    // Reset grid to vertical stack
                    infoDiv.style.display = 'flex';
                    infoDiv.style.flexDirection = 'column';
                    infoDiv.style.gap = '2px';
                    // Reset child overrides
                    const nameEl = infoDiv.querySelector('.pd-name');
                    const brandEl = infoDiv.querySelector('.pd-brand');
                    const colorEl = infoDiv.querySelector('.pd-color');
                    const priceEl = infoDiv.querySelector('.pd-price');
                    const catEl = infoDiv.querySelector('.pd-category');
                    const sizesEl = infoDiv.querySelector('.pd-sizes');
                    const stockEl = infoDiv.querySelector('.pd-stock');
                    // If right panel exists, move price and button back and remove panel
                    const right = card.querySelector('.inv-right');
                    if (right) {
                        const btnInside = right.querySelector('button.btn');
                        if (priceEl) {
                            // place price before stock if available, else at end
                            if (stockEl) {
                                infoDiv.insertBefore(priceEl, stockEl);
                            } else {
                                infoDiv.appendChild(priceEl);
                            }
                        }
                        if (btnInside) {
                            card.appendChild(btnInside);
                        }
                        right.remove();
                    }
                    // Remove labels
                    removeLabel(nameEl); removeLabel(brandEl); removeLabel(priceEl); removeLabel(catEl); removeLabel(stockEl);
                    [nameEl, brandEl, colorEl, priceEl, catEl, sizesEl, stockEl].forEach(el => { if (el) { el.style.fontSize = ''; el.style.margin = ''; el.style.lineHeight = ''; } });
                    if (nameEl) { nameEl.style.gridColumn = ''; }
                    if (brandEl) { brandEl.style.gridColumn = ''; brandEl.style.textAlign = ''; }
                    if (colorEl) {
                        colorEl.style.gridColumn = ''; colorEl.style.textAlign = '';
                        // Restore color text
                        if (colorEl.dataset && colorEl.dataset.original) {
                            colorEl.textContent = colorEl.dataset.original || '—';
                            delete colorEl.dataset.pills;
                        }
                    }
                    if (priceEl) { priceEl.style.gridColumn = ''; priceEl.style.justifySelf = ''; priceEl.style.marginTop = '6px'; }
                    if (catEl) { catEl.style.display = 'none'; catEl.style.gridColumn = ''; }
                    if (sizesEl) {
                        sizesEl.style.gridColumn = '';
                        if (sizesEl.dataset && sizesEl.dataset.original) {
                            sizesEl.textContent = `Sizes: ${sizesEl.dataset.original}`;
                            delete sizesEl.dataset.pills;
                        }
                    }
                    if (stockEl) { stockEl.style.gridColumn = ''; stockEl.style.marginTop = '-18px'; stockEl.style.justifySelf = ''; }
                }
                if (btn) {
                    // full-bleed bar button in grid mode
                    btn.style.width = 'calc(100% + 36px)';
                    btn.style.margin = '8px -18px -18px -18px';
                    btn.style.borderRadius = '0 0 16px 16px';
                    btn.style.minWidth = '';
                    btn.style.height = '32px';
                    btn.style.fontSize = '0.85rem';
                    btn.style.padding = '0';
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    // Handle profile picture errors - fallback to default image
    const sidebarAvatar = document.querySelector('.sidebar-avatar-img');
    if (sidebarAvatar) {
        sidebarAvatar.addEventListener('error', function() {
            this.src = '{{ asset("assets/images/profile.png") }}';
            this.onerror = null; // Prevent infinite loops
        });
    }
    
    const toggleBtn = document.getElementById('toggle-card-view');
    const icon = document.getElementById('toggle-card-icon');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(){
            window.currentCardView = (window.currentCardView === 'grid') ? 'horizontal' : 'grid';
            applyCardViewToCards(window.currentCardView);
            // Swap icon
            if (icon) {
                icon.classList.remove('fa-th-large','fa-bars');
                icon.classList.add(window.currentCardView === 'grid' ? 'fa-th-large' : 'fa-bars');
            }
        });
        // Initial apply
        applyCardViewToCards(window.currentCardView);
    }

    // Filter Bar Functionality
    const searchInput = document.getElementById('product-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    let activeCategory = 'all';

    // Search filter
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterProducts();
        });
    }

    // Category filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeCategory = this.getAttribute('data-category');
            filterProducts();
        });
    });

    function filterProducts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const productCards = document.querySelectorAll('.product-card:not(.add-product-card)');

        productCards.forEach(card => {
            const name = card.getAttribute('data-name')?.toLowerCase() || '';
            const brand = card.getAttribute('data-brand')?.toLowerCase() || '';
            const category = card.getAttribute('data-category')?.toLowerCase() || '';

            const matchesSearch = name.includes(searchTerm) || brand.includes(searchTerm);
            const matchesCategory = activeCategory === 'all' || category === activeCategory;

            // Preserve original display value so toggling filters restores correct layout
            if (!card.dataset.origDisplay) {
                // Use computed style as fallback (covers cases where inline display was overwritten)
                card.dataset.origDisplay = window.getComputedStyle(card).display || 'block';
            }

            if (matchesSearch && matchesCategory) {
                // Restore the original display (usually 'flex' for product cards)
                card.style.display = card.dataset.origDisplay;
            } else {
                card.style.display = 'none';
            }
        });
    }
});

// Category change handler - removed since we show all sizes regardless of category

// Enhanced image upload functionality
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
            const addUploadBox = document.querySelector('#add-product-form .upload-box');
            if (addUploadBox) addUploadBox.classList.add('has-image');
        }
        reader.readAsDataURL(file);
    }
});

// Edit product image upload functionality
document.getElementById('edit-product-image').addEventListener('change', function(e) {
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
            document.getElementById('edit-preview-img').src = e.target.result;
            const editUploadBox = document.querySelector('#edit-product-form .upload-box');
            if (editUploadBox) editUploadBox.classList.add('has-image');
        }
        reader.readAsDataURL(file);
    }
});

// Enhanced remove image function
function removeImage(event) {
    event.stopPropagation();
    document.getElementById('product-image').value = '';
    const addUploadBox = document.querySelector('#add-product-form .upload-box');
    if (addUploadBox) addUploadBox.classList.remove('has-image');
}

// Size and Stock Management Functionality
let sizeStockArray = [];
let editSizeStockArray = [];

function addSizeStock(size, stock) {
    const existingIndex = sizeStockArray.findIndex(item => item.size === size);
    if (existingIndex >= 0) {
        // Update existing size stock
        sizeStockArray[existingIndex].stock = stock;
    } else {
        // Add new size and stock
        sizeStockArray.push({ size: size, stock: parseInt(stock) });
    }
    renderSizeStockList();
    updateTotalStock();
}

function removeSizeStock(size) {
    sizeStockArray = sizeStockArray.filter(item => item.size !== size);
    renderSizeStockList();
    updateTotalStock();
}

function renderSizeStockList() {
    const container = document.getElementById('product-size-stock-list');
    container.innerHTML = sizeStockArray.map(item => `
        <div style="display:flex;align-items:center;justify-content:space-between;background:white;padding:8px 12px;border-radius:4px;border:1px solid #e2e8f0;">
            <span style="font-weight:600;">Size ${item.size}</span>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:#666;">Stock: ${item.stock}</span>
                <button type="button" onclick="removeSizeStock('${item.size}')" style="background:#ef4444;color:white;border:none;border-radius:2px;padding:2px 6px;cursor:pointer;font-size:0.8rem;">&times;</button>
            </div>
        </div>
    `).join('');
}

function updateTotalStock() {
    const total = sizeStockArray.reduce((sum, item) => sum + item.stock, 0);
    document.getElementById('total-stock-display').textContent = `Total Stock: ${total}`;
}

// Edit modal size/stock functions
function addEditSizeStock(size, stock) {
    const existingIndex = editSizeStockArray.findIndex(item => item.size === size);
    if (existingIndex >= 0) {
        // Update existing size stock
        editSizeStockArray[existingIndex].stock = stock;
    } else {
        // Add new size and stock
        editSizeStockArray.push({ size: size, stock: parseInt(stock) });
    }
    renderEditSizeStockList();
    updateEditTotalStock();
}

function removeEditSizeStock(size) {
    editSizeStockArray = editSizeStockArray.filter(item => item.size !== size);
    renderEditSizeStockList();
    updateEditTotalStock();
}

function renderEditSizeStockList() {
    const container = document.getElementById('edit-product-size-stock-list');
    container.innerHTML = editSizeStockArray.map(item => `
        <div style="display:flex;align-items:center;justify-content:space-between;background:white;padding:8px 12px;border-radius:4px;border:1px solid #e2e8f0;">
            <span style="font-weight:600;">Size ${item.size}</span>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:#666;">Stock: ${item.stock}</span>
                <button type="button" onclick="removeEditSizeStock('${item.size}')" style="background:#ef4444;color:white;border:none;border-radius:2px;padding:2px 6px;cursor:pointer;font-size:0.8rem;">&times;</button>
            </div>
        </div>
    `).join('');
}

function updateEditTotalStock() {
    const total = editSizeStockArray.reduce((sum, item) => sum + item.stock, 0);
    document.getElementById('edit-total-stock-display').textContent = `Total Stock: ${total}`;
}

// Add event listeners for size/stock inputs
document.addEventListener('DOMContentLoaded', function() {
    // Size/Stock input functionality (Add Modal)
    const sizeInput = document.getElementById('product-size-input');
    const stockInput = document.getElementById('product-stock-input');
    const addBtn = document.getElementById('product-size-stock-add');
    
    function addSizeStockFromInput() {
        const size = sizeInput.value.trim();
        const stock = stockInput.value.trim();
        
        if (size && stock && parseInt(stock) >= 0) {
            addSizeStock(size, stock);
            sizeInput.value = '';
            stockInput.value = '';
        } else {
            alert('Please enter valid size and stock values');
        }
    }
    
    addBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addSizeStockFromInput();
    });
    
    sizeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            stockInput.focus();
        }
    });
    
    stockInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSizeStockFromInput();
        }
    });

    // Size/Stock input functionality (Edit Modal)
    const editSizeInput = document.getElementById('edit-product-size-input');
    const editStockInput = document.getElementById('edit-product-stock-input');
    const editAddBtn = document.getElementById('edit-product-size-stock-add');
    
    function addEditSizeStockFromInput() {
        const size = editSizeInput.value.trim();
        const stock = editStockInput.value.trim();
        
        if (size && stock && parseInt(stock) >= 0) {
            addEditSizeStock(size, stock);
            editSizeInput.value = '';
            editStockInput.value = '';
        } else {
            alert('Please enter valid size and stock values');
        }
    }
    
    editAddBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addEditSizeStockFromInput();
    });
    
    editSizeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            editStockInput.focus();
        }
    });
    
    editStockInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEditSizeStockFromInput();
        }
    });
});

// Enhanced modal functions
function openAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    const overlay = document.getElementById('modal-overlay');
    modal.classList.add('active');
    overlay.classList.add('active');
    overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    const overlay = document.getElementById('modal-overlay');
    modal.classList.remove('active');
    overlay.classList.remove('active');
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('add-product-form').reset();
    
    // Reset image preview state
    const addUploadBox = document.querySelector('#add-product-form .upload-box');
    if (addUploadBox) addUploadBox.classList.remove('has-image');
    document.getElementById('preview-img').src = '';
    
    // Reset size/stock arrays
    sizeStockArray = [];
    renderSizeStockList();
    updateTotalStock();
}

// Edit Product Modal Functions
function openEditProductModal(productId) {
    // Get current inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    
    // Fetch product data from server
    fetch(`{{ url('inventory/products') }}/${productId}?type=${inventoryType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                document.getElementById('edit-product-id').value = product.id;
                document.getElementById('edit-product-name').value = product.name;
                document.getElementById('edit-product-brand').value = product.brand;
                document.getElementById('edit-product-category').value = product.category;
                document.getElementById('edit-product-price').value = product.price;
                document.getElementById('edit-product-color').value = product.color || '';
                
                // Set image preview
                const editUploadBox = document.querySelector('#edit-product-form .upload-box');
                if (product.image_url) {
                    // Ensure the image URL is properly formed
                    const imageUrl = product.image_url.startsWith('http') ? product.image_url : `{{ asset('') }}${product.image_url}`;
                    document.getElementById('edit-preview-img').src = imageUrl;
                    if (editUploadBox) editUploadBox.classList.add('has-image');
                } else {
                    if (editUploadBox) editUploadBox.classList.remove('has-image');
                }
                
                // Set sizes and stock
                editSizeStockArray = [];
                if (product.sizes && product.sizes.length > 0) {
                    product.sizes.forEach(sizeData => {
                        editSizeStockArray.push({
                            size: sizeData.size,
                            stock: sizeData.stock
                        });
                    });
                }
                renderEditSizeStockList();
                updateEditTotalStock();
                
                // Show the modal
                const modal = document.getElementById('edit-product-modal');
                const overlay = document.getElementById('modal-overlay');
                modal.classList.add('active');
                overlay.classList.add('active');
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        })
        .catch(error => {
            console.error('Error fetching product data:', error);
            alert('Error loading product data');
        });
}

function closeEditProductModal() {
    const modal = document.getElementById('edit-product-modal');
    const overlay = document.getElementById('modal-overlay');
    modal.classList.remove('active');
    overlay.classList.remove('active');
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('edit-product-form').reset();
    
    // Reset image preview state
    const editUploadBox = document.querySelector('#edit-product-form .upload-box');
    if (editUploadBox) editUploadBox.classList.remove('has-image');
    document.getElementById('edit-preview-img').src = '';
    
    // Reset size/stock arrays
    editSizeStockArray = [];
    renderEditSizeStockList();
    updateEditTotalStock();
}

// Enhanced remove image function for edit modal
function removeEditImage(event) {
    event.stopPropagation();
    document.getElementById('edit-product-image').value = '';
    const editUploadBox = document.querySelector('#edit-product-form .upload-box');
    if (editUploadBox) editUploadBox.classList.remove('has-image');
}

// Product Details Modal Functions
function openProductDetailsModal(productId) {
    // Get current inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    
    // Fetch product data from server
    fetch(`{{ url('inventory/products') }}/${productId}?type=${inventoryType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                
                // Populate the details content
                const detailsContent = document.getElementById('product-details-content');
                detailsContent.innerHTML = `
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                        <div style="width: 200px; height: 200px; background: #e2e8f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            ${product.image_url && product.image_url.trim() ? 
                                `<img src="${product.image_url.startsWith('http') ? product.image_url : '{{ asset("") }}' + product.image_url}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('assets/images/no-image-available.jpg') }}'">` : 
                                `<img src="{{ asset('assets/images/no-image-available.jpg') }}" alt="No image available" style="width: 100%; height: 100%; object-fit: cover;">`
                            }
                        </div>
                    </div>
                    <div style="flex: 2; display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <h4 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #1a1a1a;">${product.name}</h4>
                            <p style="margin: 0; font-size: 1rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">${product.brand}</p>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="font-size: 0.9rem; color: #374151; font-weight: 600;">Category:</label>
                                <p style="margin: 4px 0 0 0; font-size: 1rem; color: #1a1a1a;">${product.category}</p>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; color: #374151; font-weight: 600;">Color:</label>
                                <p style="margin: 4px 0 0 0; font-size: 1rem; color: #1a1a1a;">${product.color || '—'}</p>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; color: #374151; font-weight: 600;">Price:</label>
                                <p style="margin: 4px 0 0 0; font-size: 1.2rem; font-weight: 700; color: #2a6aff;">₱${parseFloat(product.price).toLocaleString()}</p>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; color: #374151; font-weight: 600;">Total Stock:</label>
                                <p style="margin: 4px 0 0 0; font-size: 1.2rem; font-weight: 700; color: #059669;">${product.sizes ? product.sizes.reduce((total, size) => total + parseInt(size.stock), 0) : 0} items</p>
                            </div>
                        </div>
                        
                        ${product.sizes && product.sizes.length > 0 ? `
                            <div>
                                <label style="font-size: 0.9rem; color: #374151; font-weight: 600; margin-bottom: 8px; display: block;">Available Sizes:</label>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    ${product.sizes.map(size => `
                                        <span style="background: #f3f4f6; padding: 6px 12px; border-radius: 6px; font-size: 0.9rem; color: #374151; font-weight: 500;">
                                            ${size.size} (${size.stock} in stock)
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        ` : '<p style="color: #6b7280;">No sizes available</p>'}
                        
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <button onclick="closeProductDetailsModal(); openEditProductModal(${product.id})" 
                                    style="background: #2a6aff; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-right: 12px;">
                                Edit Product
                            </button>
                            <button onclick="closeProductDetailsModal()" 
                                    style="background: #f3f4f6; color: #374151; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                Close
                            </button>
                        </div>
                    </div>
                `;
                
                // Show the modal
                const modal = document.getElementById('product-details-modal');
                const overlay = document.getElementById('modal-overlay');
                modal.classList.add('active');
                overlay.classList.add('active');
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        })
        .catch(error => {
            console.error('Error fetching product data:', error);
            alert('Error loading product data');
        });
}

function closeProductDetailsModal() {
    const modal = document.getElementById('product-details-modal');
    const overlay = document.getElementById('modal-overlay');
    modal.classList.remove('active');
    overlay.classList.remove('active');
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Clear the content
    document.getElementById('product-details-content').innerHTML = '';
}

// Delete current product function
function deleteCurrentProduct() {
    const productId = document.getElementById('edit-product-id').value;
    // Use the shared delete flow with a single confirmation and close on success
    const result = deleteProduct(productId);
    if (result && typeof result.then === 'function') {
        result.then(res => {
            if (res && res.success) {
                closeEditProductModal();
            }
        });
    }
}

// Enhanced toggle size stock function
function toggleSizeStock(checkbox) {
    const sizeItem = checkbox.closest('.size-item');
    const stockInput = sizeItem.querySelector('.stock-input');
    
    if (checkbox.checked) {
        stockInput.disabled = false;
        stockInput.required = true;
        stockInput.focus();
        sizeItem.classList.add('active');
        sizeItem.style.opacity = '1';
    } else {
        stockInput.disabled = true;
        stockInput.required = false;
        stockInput.value = '';
        sizeItem.classList.remove('active');
        sizeItem.style.opacity = '0.6';
    }
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
    
    // Validate that at least one size/stock is entered
    if (sizeStockArray.length === 0) {
        alert('Please add at least one size with stock for the product.');
        return;
    }
    
    // Validate that color is entered
    const colorValue = document.getElementById('product-color').value.trim();
    if (!colorValue) {
        alert('Please enter a color for the product.');
        return;
    }
    
    const formData = new FormData();
    
    // Add basic product data
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('name', document.getElementById('product-name').value);
    formData.append('brand', document.getElementById('product-brand').value);
    formData.append('category', document.getElementById('product-category').value);
    formData.append('price', document.getElementById('product-price').value);
    formData.append('color', colorValue);
    formData.append('image', imageInput.files[0]);
    
    // Add inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    formData.append('inventory_type', inventoryType);
    
    // Add sizes with individual stock amounts
    sizeStockArray.forEach((item, index) => {
        formData.append(`sizes[${index}][size]`, item.size);
        formData.append(`sizes[${index}][stock]`, item.stock);
        formData.append(`sizes[${index}][price_adjustment]`, 0);
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
            // Close modal and show success message
            closeAddProductModal();
            
            // Show a more user-friendly success message
            const successMessage = `${data.product.name} has been added successfully!`;
            showSuccessMessage(successMessage);
            
            // Refresh the page after a short delay to show the success message
            setTimeout(() => {
                window.location.reload();
            }, 1500);
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

// Edit product form submission
document.getElementById('edit-product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate that at least one size/stock is entered
    if (editSizeStockArray.length === 0) {
        alert('Please add at least one size with stock for the product.');
        return;
    }
    
    // Validate that color is entered
    const colorValue = document.getElementById('edit-product-color').value.trim();
    if (!colorValue) {
        alert('Please enter a color for the product.');
        return;
    }
    
    const productId = document.getElementById('edit-product-id').value;
    const formData = new FormData();
    
    // Add basic product data
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    formData.append('name', document.getElementById('edit-product-name').value);
    formData.append('brand', document.getElementById('edit-product-brand').value);
    formData.append('category', document.getElementById('edit-product-category').value);
    formData.append('price', document.getElementById('edit-product-price').value);
    formData.append('color', colorValue);
    
    // Add inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    formData.append('inventory_type', inventoryType);
    
    // Add image if a new one was selected
    const imageInput = document.getElementById('edit-product-image');
    if (imageInput.files && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }
    
    // Add sizes with individual stock amounts
    editSizeStockArray.forEach((item, index) => {
        formData.append(`sizes[${index}][size]`, item.size);
        formData.append(`sizes[${index}][stock]`, item.stock);
        formData.append(`sizes[${index}][price_adjustment]`, 0);
    });
    
    // Debug: Check form data
    console.log('Edit form data being sent:');
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log(pair[0] + ': File - ' + pair[1].name + ' (Size: ' + pair[1].size + ')');
        } else {
            console.log(pair[0] + ': ' + pair[1]);
        }
    }
    
    fetch(`{{ url('inventory/products') }}/${productId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Edit response status:', response.status);
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
        console.log('Edit parsed data:', data);
        if (data.success) {
            // Update the product card and table immediately
            updateProductInView(data.product);
            
            // Close modal and show success message
            closeEditProductModal();
            
            // Show a more user-friendly success message
            const successMessage = `${data.product.name} has been updated successfully!`;
            showSuccessMessage(successMessage);
        } else {
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                for (let field in data.errors) {
                    errorMessage += field + ': ' + data.errors[field].join(', ') + '\n';
                }
                alert(errorMessage);
            } else {
                alert('Error updating product: ' + (data.message || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating product: ' + error.message);
    });
});

// Edit and delete functions
function editProduct(id) {
    // TODO: Implement edit functionality
    alert(`Edit product ${id} - Feature coming soon`);
}

function deleteProduct(id, options = {}) {
    const { skipConfirm = false } = options;
    if (!skipConfirm) {
        const ok = confirm('Are you sure you want to delete this product?');
        if (!ok) return Promise.resolve({ success: false, cancelled: true });
    }
    const typeSel = document.getElementById('inventory-type-switcher');
    const type = typeSel ? typeSel.value : 'pos';
    const delUrl = new URL(`{{ url('inventory/products') }}/${id}`, window.location.origin);
    delUrl.searchParams.set('type', type);
    return fetch(delUrl.toString(), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optionally show a quick toast before reload
            showSuccessMessage('Product deleted successfully!');
            // Hard reload to ensure all views (cards + table) reflect removal
            setTimeout(() => { window.location.reload(); }, 600);
            return { success: true };
        } else {
            alert('Error deleting product: ' + data.message);
            return { success: false, message: data.message };
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting product');
        return { success: false, error };
    });
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
            filteredData = inventoryData.filter(item => item.total_stock > 5);
            break;
        case 'low-stock':
            filteredData = inventoryData.filter(item => item.total_stock <= 5 && item.total_stock > 0);
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
    const addModal = document.getElementById('add-product-modal');
    const editModal = document.getElementById('edit-product-modal');
    
    if (e.target === addModal) {
        closeAddProductModal();
    }
    
    if (e.target === editModal) {
        closeEditProductModal();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing navigation...');
    
    updateDateTime();
    setInterval(updateDateTime, 1000);
    initializeSettings();
    
    // Load inventory data
    loadInventoryData();
    
    console.log('Navigation initialization complete');
    
    // Set up inventory type switcher
    const inventoryTypeSwitcher = document.getElementById('inventory-type-switcher');
    if (inventoryTypeSwitcher) {
        inventoryTypeSwitcher.addEventListener('change', function(e) {
            const selectedType = this.value;
            console.log('Switching to inventory type:', selectedType);
            
            // Redirect to dashboard with type parameter
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('type', selectedType);
            window.location.href = currentUrl.toString();
        });
        // Wire segmented control
        const typeToggle = document.getElementById('type-toggle');
        if (typeToggle) {
            typeToggle.querySelectorAll('.type-chip').forEach(btn => {
                btn.addEventListener('click', function(){
                    const t = this.getAttribute('data-type');
                    // Update hidden select value and dispatch change
                    inventoryTypeSwitcher.value = t;
                    inventoryTypeSwitcher.dispatchEvent(new Event('change'));
                });
            });
        }
    }
    
    // Load real inventory data from database
    loadInventoryData();
    
    // Initialize notification system
    if (typeof NotificationManager !== 'undefined') {
        const notificationManager = new NotificationManager();
        notificationManager.init();
        window.notificationManager = notificationManager; // Make it globally accessible
    }
    
    // Modal overlay click handler
    const modalOverlay = document.getElementById('modal-overlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeAddProductModal();
                closeEditProductModal();
            }
        });
    }
});

function openAddSupplierModal() {
    alert('Add Supplier Modal - To be implemented');
}

function editSupplier(supplierId) {
    alert('Edit Supplier - To be implemented for supplier ID: ' + supplierId);
}

function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        alert('Delete Supplier - To be implemented for supplier ID: ' + supplierId);
    }
}

function updateReservationStatus(reservationId, status) {
    alert(`Reservation ${reservationId} status would be updated to: ${status} (Demo Mode)`);
}

// Modal overlay click handler
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('modal-overlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            // Only close if clicking directly on the overlay (not on modal content)
            if (e.target === modalOverlay) {
                // Check which modal is open and close it
                const addModal = document.getElementById('add-product-modal');
                const editModal = document.getElementById('edit-product-modal');
                const detailsModal = document.getElementById('product-details-modal');
                
                if (addModal && addModal.classList.contains('active')) {
                    closeAddProductModal();
                } else if (editModal && editModal.classList.contains('active')) {
                    closeEditProductModal();
                } else if (detailsModal && detailsModal.classList.contains('active')) {
                    closeProductDetailsModal();
                }
            }
        });
    }
});
</script>

<script src="{{ asset('js/notifications.js') }}"></script>
@endpush