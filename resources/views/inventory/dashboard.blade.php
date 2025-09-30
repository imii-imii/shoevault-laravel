@extends('layouts.app')

@section('title', 'Inventory Management - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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
                <img src="{{ asset('assets/images/profile.png') }}" alt="Manager">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name }}</h4>
                <span>{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn" style="background: none; border: none; color: inherit; width: 100%; text-align: left;">
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
                    <select id="inventory-type-switcher" style="height:40px;min-width:160px;background:#2a6aff;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;padding:0 18px;cursor:pointer;box-shadow:0 2px 8px rgba(67,56,202,0.08);">
                        <option value="pos" {{ isset($inventoryType) && $inventoryType === 'pos' ? 'selected' : '' }}>POS Inventory</option>
                        <option value="reservation" {{ isset($inventoryType) && $inventoryType === 'reservation' ? 'selected' : '' }}>Reservation Inventory</option>
                    </select>
                </div>
            </div>
            
            <div class="toggle">
                <button id="toggle-card-view" style="position:absolute;right: 20px; bottom: 20px; z-index:10;background:#2a6aff;color:#fff;border:none;border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(67,56,202,0.18);font-size:1.2rem;cursor:pointer;">
                    <i class="fas fa-th-large" id="toggle-card-icon"></i>
                </button>
            </div>
            
            <div class="add-inventory-list" style="position:relative; display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; justify-content: flex-start;">
                <!-- Add Product Card/Button -->
                <div class="add-product-card" style="min-width:220px;max-width:220px;height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #a3bffa;border-radius:16px;cursor:pointer;background:#f7fafc;box-shadow:0 2px 8px rgba(67,56,202,0.08);" onclick="openAddProductModal()">
                    <i class="fas fa-plus" style="font-size:2rem;color:#2a6aff;"></i>
                    <span style="margin-top:12px;font-size:1.2rem;color:#2a6aff;font-weight:600;">Add Product</span>
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
                    style="min-width:220px;max-width:220px;height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(67,56,202,0.08);padding:18px;cursor:pointer;" onclick="openEditProductModal({{ $product->id }})">>
                    
                    <div style="width:128px;height:128px;background:#e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        @if($product->image_url)
                            <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                        @else
                            <i class="fas fa-image" style="font-size:3.2rem;color:#a0aec0;"></i>
                        @endif
                    </div>
                    
                    <div style="font-weight:700;font-size:0.88rem;text-align:center;">{{ $product->name }}</div>
                    <div style="font-size:0.8rem;color:#4a5568;text-align:center;margin-top:4px;">Size: {{ $product->sizes->pluck('size')->implode(', ') }}</div>
                    <div style="font-size:0.8rem;color:#4a5568;text-align:center;">Brand: {{ $product->brand }}</div>
                    <div style="font-size:0.8rem;color:#2a6aff;text-align:center;margin-top:4px;">Stock: {{ $product->sizes->sum('stock') }}</div>
                    
                    <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;min-width:120px;height:36px;border-radius:8px;font-size:0.8rem;margin-top:16px;padding:0;" onclick="event.stopPropagation(); openUpdateProductModal('{{ $product->id }}')">Update</button>
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
    <div class="modal-content" style="max-width:100%; min-height:480px; display:flex; flex-direction:column; justify-content:flex-start; align-items:stretch;">
        <div class="modal-header" style="padding:24px 32px 12px 32px; border-bottom:1px solid #e2e8f0;">
            <h3 style="margin:0; font-size:1.6rem; font-weight:700;">Add New Product</h3>
            <button class="close-btn" style="font-size:1.5rem; color:#718096; background:none; border:none; cursor:pointer;" onclick="closeAddProductModal()">&times;</button>
        </div>
        <form id="add-product-form" class="modal-form" style="flex:1; display:flex; flex-direction:row; gap:32px; overflow:hidden; min-height:0;" enctype="multipart/form-data">
            @csrf
            <div class="upload-card" style="flex:0 0 400px; display:flex; flex-direction:column; justify-content:space-between; height:480px;">
                <div class="upload-box" role="button" tabindex="0" style="flex:1;" onclick="document.getElementById('product-image').click()">
                    <div class="upload-drop" id="upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h4>Drop files here</h4>
                        <p>Supported format: PNG, JPG</p>
                        <span class="or-text">OR</span>
                        <span class="browse-link">Browse files</span>
                    </div>
                    <div class="image-preview" id="image-preview" style="display: none; width: 100%; height: 100%; position: relative;">
                        <img id="preview-img" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
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
            <div class="info-card" style="flex:1; display:flex; flex-direction:column; max-height:480px; overflow-y:auto; padding-right:8px;">
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
                    <label for="product-stock">Stock</label>
                    <input type="number" id="product-stock" name="stock" min="0" placeholder="Please enter quantity." required style="font-size: 1rem;">
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
                    <label for="product-size-tags">Sizes</label>
                    <div id="product-size-tags" style="font-size:1rem;display:flex;flex-wrap:wrap;gap:8px;padding:6px 0;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;min-height:38px;margin-bottom:6px;width:100%;"></div>
                    <div style="position:relative;display:flex;align-items:center;width:100%;">
                        <input type="number" id="product-size-input" placeholder="Type size and press Enter" min="1" max="50" style="width:100%;padding-right:36px;font-size:1rem;">
                        <button type="button" id="product-size-enter" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#2a6aff;padding:0;">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="product-color-tags">Colors</label>
                    <div id="product-color-tags" style="font-size:1rem;display:flex;flex-wrap:wrap;gap:8px;padding:6px 0;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;min-height:38px;margin-bottom:6px;width:100%;"></div>
                    <div style="position:relative;display:flex;align-items:center;width:100%;">
                        <input type="text" id="product-color-input" placeholder="Type color and press Enter" style="width:100%;padding-right:36px;font-size:1rem;">
                        <button type="button" id="product-color-enter" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#2a6aff;padding:0;">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
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
                        <h4>Drop files here</h4>
                        <p>Supported format: PNG, JPG</p>
                        <span class="or-text">OR</span>
                        <span class="browse-link">Browse files</span>
                    </div>
                    <div class="image-preview" id="edit-image-preview" style="width: 100%; height: 100%; position: relative;">
                        <img id="edit-preview-img" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
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
                    <label for="edit-product-stock">Stock</label>
                    <input type="number" id="edit-product-stock" name="stock" min="0" placeholder="Please enter quantity." required style="font-size: 1rem;">
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
                    <label for="edit-product-size-tags">Sizes</label>
                    <div id="edit-product-size-tags" style="font-size:1rem;display:flex;flex-wrap:wrap;gap:8px;padding:6px 0;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;min-height:38px;margin-bottom:6px;width:100%;"></div>
                    <div style="position:relative;display:flex;align-items:center;width:100%;">
                        <input type="number" id="edit-product-size-input" placeholder="Type size and press Enter" min="1" max="50" style="width:100%;padding-right:36px;font-size:1rem;">
                        <button type="button" id="edit-product-size-enter" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#2a6aff;padding:0;">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-product-color-tags">Colors</label>
                    <div id="edit-product-color-tags" style="font-size:1rem;display:flex;flex-wrap:wrap;gap:8px;padding:6px 0;border:1px solid #e2e8f0;border-radius:6px;background:#f7fafc;min-height:38px;margin-bottom:6px;width:100%;"></div>
                    <div style="position:relative;display:flex;align-items:center;width:100%;">
                        <input type="text" id="edit-product-color-input" placeholder="Type color and press Enter" style="width:100%;padding-right:36px;font-size:1rem;">
                        <button type="button" id="edit-product-color-enter" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#2a6aff;padding:0;">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
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
    newCard.style.cssText = 'min-width:220px;max-width:220px;height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(67,56,202,0.08);padding:18px;cursor:pointer;';
    newCard.setAttribute('onclick', `openEditProductModal('${product.id}')`);
    
    newCard.innerHTML = `
        <div style="width:128px;height:128px;background:#e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
            ${product.image_url ? 
                `<img src="${product.image_url}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">` : 
                '<i class="fas fa-image" style="font-size:3.2rem;color:#a0aec0;"></i>'
            }
        </div>
        
        <div style="font-weight:700;font-size:0.88rem;text-align:center;">${product.name}</div>
        <div style="font-size:0.8rem;color:#4a5568;text-align:center;margin-top:4px;">Size: ${product.available_sizes ? product.available_sizes.join(', ') : 'N/A'}</div>
        <div style="font-size:0.8rem;color:#4a5568;text-align:center;">Brand: ${product.brand}</div>
        <div style="font-size:0.8rem;color:#2a6aff;text-align:center;margin-top:4px;">Stock: ${product.total_stock || 0}</div>
        
        <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;min-width:120px;height:36px;border-radius:8px;font-size:0.8rem;margin-top:16px;padding:0;" onclick="event.stopPropagation(); openEditProductModal('${product.id}')">Update</button>
    `;
    
    // Add the new card at the end of the container (append to the end)
    cardContainer.appendChild(newCard);
}

// Function to update a product in both card and table view
function updateProductInView(product) {
    // Update the product card
    const productCard = document.querySelector(`[data-id="${product.id}"]`);
    if (productCard) {
        // Update data attributes
        productCard.setAttribute('data-name', product.name);
        productCard.setAttribute('data-brand', product.brand);
        productCard.setAttribute('data-category', product.category);
        productCard.setAttribute('data-price', product.price);
        productCard.setAttribute('data-stock', product.total_stock || 0);
        productCard.setAttribute('data-sizes', product.available_sizes ? product.available_sizes.join(', ') : '');
        productCard.setAttribute('data-color', product.color);
        productCard.setAttribute('data-image', product.image_url);
        
        // Update the card content
        productCard.innerHTML = `
            <div style="width:128px;height:128px;background:#e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                ${product.image_url ? 
                    `<img src="${product.image_url}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">` : 
                    '<i class="fas fa-image" style="font-size:3.2rem;color:#a0aec0;"></i>'
                }
            </div>
            
            <div style="font-weight:700;font-size:0.88rem;text-align:center;">${product.name}</div>
            <div style="font-size:0.8rem;color:#4a5568;text-align:center;margin-top:4px;">Size: ${product.available_sizes ? product.available_sizes.join(', ') : 'N/A'}</div>
            <div style="font-size:0.8rem;color:#4a5568;text-align:center;">Brand: ${product.brand}</div>
            <div style="font-size:0.8rem;color:#2a6aff;text-align:center;margin-top:4px;">Stock: ${product.total_stock || 0}</div>
            
            <button type="button" class="btn btn-primary browse-btn" style="display:flex;align-items:center;justify-content:center;min-width:120px;height:36px;border-radius:8px;font-size:0.8rem;margin-top:16px;padding:0;" onclick="event.stopPropagation(); openEditProductModal('${product.id}')">Update</button>
        `;
    }
    
    // Update the inventory data array
    const inventoryIndex = inventoryData.findIndex(item => item.id === product.id);
    if (inventoryIndex !== -1) {
        inventoryData[inventoryIndex] = product;
        // Re-render the table to show updated data
        renderInventoryTable();
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
            document.getElementById('upload-placeholder').style.display = 'none';
            document.getElementById('image-preview').style.display = 'block';
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
            document.getElementById('edit-upload-placeholder').style.display = 'none';
            document.getElementById('edit-image-preview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Enhanced remove image function
function removeImage(event) {
    event.stopPropagation();
    document.getElementById('product-image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('upload-placeholder').style.display = 'flex';
}

// Size and Color Tag Functionality
let sizeArray = [];
let colorArray = [];
let editSizeArray = [];
let editColorArray = [];

function addSizeTag(size) {
    if (size && !sizeArray.includes(size)) {
        sizeArray.push(size);
        renderSizeTags();
    }
}

function removeSizeTag(size) {
    sizeArray = sizeArray.filter(s => s !== size);
    renderSizeTags();
}

function renderSizeTags() {
    const container = document.getElementById('product-size-tags');
    container.innerHTML = sizeArray.map(size => `
        <span style="display:inline-flex;align-items:center;background:#2a6aff;color:white;padding:4px 8px;border-radius:4px;font-size:0.9rem;">
            ${size}
            <button type="button" onclick="removeSizeTag('${size}')" style="background:none;border:none;color:white;margin-left:6px;cursor:pointer;font-size:0.8rem;">&times;</button>
        </span>
    `).join('');
}

function addColorTag(color) {
    if (color && !colorArray.includes(color)) {
        colorArray.push(color);
        renderColorTags();
    }
}

function removeColorTag(color) {
    colorArray = colorArray.filter(c => c !== color);
    renderColorTags();
}

function renderColorTags() {
    const container = document.getElementById('product-color-tags');
    container.innerHTML = colorArray.map(color => `
        <span style="display:inline-flex;align-items:center;background:#2a6aff;color:white;padding:4px 8px;border-radius:4px;font-size:0.9rem;">
            ${color}
            <button type="button" onclick="removeColorTag('${color}')" style="background:none;border:none;color:white;margin-left:6px;cursor:pointer;font-size:0.8rem;">&times;</button>
        </span>
    `).join('');
}

// Edit modal tag functions
function addEditSizeTag(size) {
    if (size && !editSizeArray.includes(size)) {
        editSizeArray.push(size);
        renderEditSizeTags();
    }
}

function removeEditSizeTag(size) {
    editSizeArray = editSizeArray.filter(s => s !== size);
    renderEditSizeTags();
}

function renderEditSizeTags() {
    const container = document.getElementById('edit-product-size-tags');
    container.innerHTML = editSizeArray.map(size => `
        <span style="display:inline-flex;align-items:center;background:#2a6aff;color:white;padding:4px 8px;border-radius:4px;font-size:0.9rem;">
            ${size}
            <button type="button" onclick="removeEditSizeTag('${size}')" style="background:none;border:none;color:white;margin-left:6px;cursor:pointer;font-size:0.8rem;">&times;</button>
        </span>
    `).join('');
}

function addEditColorTag(color) {
    if (color && !editColorArray.includes(color)) {
        editColorArray.push(color);
        renderEditColorTags();
    }
}

function removeEditColorTag(color) {
    editColorArray = editColorArray.filter(c => c !== color);
    renderEditColorTags();
}

function renderEditColorTags() {
    const container = document.getElementById('edit-product-color-tags');
    container.innerHTML = editColorArray.map(color => `
        <span style="display:inline-flex;align-items:center;background:#2a6aff;color:white;padding:4px 8px;border-radius:4px;font-size:0.9rem;">
            ${color}
            <button type="button" onclick="removeEditColorTag('${color}')" style="background:none;border:none;color:white;margin-left:6px;cursor:pointer;font-size:0.8rem;">&times;</button>
        </span>
    `).join('');
}

// Add event listeners for size and color inputs
document.addEventListener('DOMContentLoaded', function() {
    // Size input functionality (Add Modal)
    const sizeInput = document.getElementById('product-size-input');
    const sizeEnterBtn = document.getElementById('product-size-enter');
    
    function addSizeFromInput() {
        const size = sizeInput.value.trim();
        if (size) {
            addSizeTag(size);
            sizeInput.value = '';
        }
    }
    
    sizeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSizeFromInput();
        }
    });
    
    sizeEnterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addSizeFromInput();
    });
    
    // Color input functionality (Add Modal)
    const colorInput = document.getElementById('product-color-input');
    const colorEnterBtn = document.getElementById('product-color-enter');
    
    function addColorFromInput() {
        const color = colorInput.value.trim();
        if (color) {
            addColorTag(color);
            colorInput.value = '';
        }
    }
    
    colorInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addColorFromInput();
        }
    });
    
    colorEnterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addColorFromInput();
    });

    // Size input functionality (Edit Modal)
    const editSizeInput = document.getElementById('edit-product-size-input');
    const editSizeEnterBtn = document.getElementById('edit-product-size-enter');
    
    function addEditSizeFromInput() {
        const size = editSizeInput.value.trim();
        if (size) {
            addEditSizeTag(size);
            editSizeInput.value = '';
        }
    }
    
    editSizeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEditSizeFromInput();
        }
    });
    
    editSizeEnterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addEditSizeFromInput();
    });
    
    // Color input functionality (Edit Modal)
    const editColorInput = document.getElementById('edit-product-color-input');
    const editColorEnterBtn = document.getElementById('edit-product-color-enter');
    
    function addEditColorFromInput() {
        const color = editColorInput.value.trim();
        if (color) {
            addEditColorTag(color);
            editColorInput.value = '';
        }
    }
    
    editColorInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEditColorFromInput();
        }
    });
    
    editColorEnterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addEditColorFromInput();
    });
});

// Enhanced modal functions
function openAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('add-product-form').reset();
    
    // Reset image preview
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('upload-placeholder').style.display = 'flex';
    document.getElementById('preview-img').src = '';
    
    // Reset size and color arrays
    sizeArray = [];
    colorArray = [];
    renderSizeTags();
    renderColorTags();
    
    // Reset size selections
    document.querySelectorAll('input[name="size_enabled[]"]').forEach(checkbox => {
        checkbox.checked = false;
        toggleSizeStock(checkbox);
    });
}

// Edit Product Modal Functions
function openEditProductModal(productId) {
    // Get product data from the card or fetch from server
    const productCard = document.querySelector(`[data-id="${productId}"]`);
    
    if (productCard) {
        // Populate the edit form with existing data
        document.getElementById('edit-product-id').value = productId;
        document.getElementById('edit-product-name').value = productCard.dataset.name;
        document.getElementById('edit-product-brand').value = productCard.dataset.brand;
        document.getElementById('edit-product-category').value = productCard.dataset.category;
        document.getElementById('edit-product-price').value = productCard.dataset.price;
        document.getElementById('edit-product-stock').value = productCard.dataset.stock;
        
        // Set image preview
        const imageUrl = productCard.dataset.image;
        if (imageUrl) {
            document.getElementById('edit-preview-img').src = imageUrl;
            document.getElementById('edit-image-preview').style.display = 'block';
            document.getElementById('edit-upload-placeholder').style.display = 'none';
        } else {
            document.getElementById('edit-image-preview').style.display = 'none';
            document.getElementById('edit-upload-placeholder').style.display = 'flex';
        }
        
        // Set sizes
        editSizeArray = productCard.dataset.sizes ? productCard.dataset.sizes.split(', ').filter(s => s.trim()) : [];
        renderEditSizeTags();
        
        // Set colors
        editColorArray = productCard.dataset.color ? productCard.dataset.color.split(', ').filter(c => c.trim()) : [];
        renderEditColorTags();
        
        // Show the modal
        document.getElementById('edit-product-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        // Fetch product data from server if not available in the card
        fetch(`{{ url('inventory/products') }}/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    document.getElementById('edit-product-id').value = product.id;
                    document.getElementById('edit-product-name').value = product.name;
                    document.getElementById('edit-product-brand').value = product.brand;
                    document.getElementById('edit-product-category').value = product.category;
                    document.getElementById('edit-product-price').value = product.price;
                    document.getElementById('edit-product-stock').value = product.total_stock;
                    
                    // Set image preview
                    if (product.image_url) {
                        document.getElementById('edit-preview-img').src = product.image_url;
                        document.getElementById('edit-image-preview').style.display = 'block';
                        document.getElementById('edit-upload-placeholder').style.display = 'none';
                    }
                    
                    // Set sizes and colors
                    editSizeArray = product.available_sizes || [];
                    editColorArray = product.color ? product.color.split(', ') : [];
                    renderEditSizeTags();
                    renderEditColorTags();
                    
                    // Show the modal
                    document.getElementById('edit-product-modal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            })
            .catch(error => {
                console.error('Error fetching product data:', error);
                alert('Error loading product data');
            });
    }
}

function closeEditProductModal() {
    document.getElementById('edit-product-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('edit-product-form').reset();
    
    // Reset image preview
    document.getElementById('edit-image-preview').style.display = 'none';
    document.getElementById('edit-upload-placeholder').style.display = 'flex';
    document.getElementById('edit-preview-img').src = '';
    
    // Reset size and color arrays
    editSizeArray = [];
    editColorArray = [];
    renderEditSizeTags();
    renderEditColorTags();
}

// Enhanced remove image function for edit modal
function removeEditImage(event) {
    event.stopPropagation();
    document.getElementById('edit-product-image').value = '';
    document.getElementById('edit-image-preview').style.display = 'none';
    document.getElementById('edit-upload-placeholder').style.display = 'flex';
}

// Delete current product function
function deleteCurrentProduct() {
    const productId = document.getElementById('edit-product-id').value;
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        deleteProduct(productId);
        closeEditProductModal();
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
    
    // Validate that at least one size is entered
    if (sizeArray.length === 0) {
        alert('Please enter at least one size for the product.');
        return;
    }
    
    // Validate that at least one color is entered
    if (colorArray.length === 0) {
        alert('Please enter at least one color for the product.');
        return;
    }
    
    // Get stock value
    const stockValue = document.getElementById('product-stock').value;
    if (!stockValue || parseInt(stockValue) < 0) {
        alert('Please enter a valid stock quantity.');
        return;
    }
    
    const formData = new FormData();
    
    // Add basic product data
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('name', document.getElementById('product-name').value);
    formData.append('brand', document.getElementById('product-brand').value);
    formData.append('category', document.getElementById('product-category').value);
    formData.append('price', document.getElementById('product-price').value);
    formData.append('image', imageInput.files[0]);
    
    // Add inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    formData.append('inventory_type', inventoryType);
    
    // Add color (singular) - use first color for now, or join them
    formData.append('color', colorArray.join(', '));
    
    // Add sizes array in the format the backend expects
    sizeArray.forEach((size, index) => {
        formData.append(`sizes[${index}][size]`, size);
        formData.append(`sizes[${index}][stock]`, stockValue);
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
    
    // Validate that at least one size is entered
    if (editSizeArray.length === 0) {
        alert('Please enter at least one size for the product.');
        return;
    }
    
    // Validate that at least one color is entered
    if (editColorArray.length === 0) {
        alert('Please enter at least one color for the product.');
        return;
    }
    
    // Get stock value
    const stockValue = document.getElementById('edit-product-stock').value;
    if (!stockValue || parseInt(stockValue) < 0) {
        alert('Please enter a valid stock quantity.');
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
    
    // Add inventory type
    const inventoryType = document.getElementById('inventory-type-switcher').value || 'pos';
    formData.append('inventory_type', inventoryType);
    
    // Add image if a new one was selected
    const imageInput = document.getElementById('edit-product-image');
    if (imageInput.files && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }
    
    // Add color (singular) - join all colors
    formData.append('color', editColorArray.join(', '));
    
    // Add sizes array in the format the backend expects
    editSizeArray.forEach((size, index) => {
        formData.append(`sizes[${index}][size]`, size);
        formData.append(`sizes[${index}][stock]`, stockValue);
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
    }
    
    // Load real inventory data from database
    loadInventoryData();
});

// Mock functions for buttons that aren't implemented yet
function openAddProductModal() {
    document.getElementById('add-product-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openUpdateProductModal(productId) {
    alert('Update Product Modal - To be implemented for product ID: ' + productId);
}

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
</script>
@endpush
