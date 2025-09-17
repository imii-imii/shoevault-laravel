@extends('layouts.app')

@section('title', 'POS - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
@endpush

@section('content')
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text">
            <h2>ShoeVault</h2>
            <p>Point of Sale</p>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item nav-pos active">
            <a href="{{ route('pos.dashboard') }}" class="nav-link">
                <i class="fas fa-cash-register"></i>
                <span>POS</span>
            </a>
        </li>
        <li class="nav-item nav-reservation">
            <a href="{{ route('pos.reservations') }}" class="nav-link">
                <i class="fas fa-user-tie"></i>
                <span>Reservations</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('pos.sales-history') }}" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Sales History</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <img src="{{ asset('assets/images/profile.png') }}" alt="User">
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
            <h1 class="main-title">Point of Sale</h1>
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

    <!-- POS Content -->
    <div class="main-content-wrapper">
        <!-- Products Section -->
        <section class="products-section">
            <!-- Category Filter -->
            <div class="category-filter">
                <div class="category-tabs">
                    <button class="category-tab active" data-category="all">
                        <i class="fas fa-th-large"></i>
                        <span>All Products</span>
                    </button>
                    <button class="category-tab" data-category="men">
                        <i class="fas fa-male"></i>
                        <span>Men</span>
                    </button>
                    <button class="category-tab" data-category="women">
                        <i class="fas fa-female"></i>
                        <span>Women</span>
                    </button>
                    <button class="category-tab" data-category="accessories">
                        <i class="fas fa-gem"></i>
                        <span>Accessories</span>
                    </button>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-input" placeholder="Search shoes by name, brand, or model...">
                        <button class="clear-search" id="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="products-grid">
                <!-- Products will be dynamically loaded here -->
                <div class="loading-products">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading products...</p>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Cart Sidebar -->
<aside class="cart-sidebar" id="cart-sidebar">
    <div class="cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Current Sale</h3>
        <button class="clear-cart" id="clear-cart">
            <i class="fas fa-trash"></i>
            Clear All
        </button>
    </div>

    <div class="cart-items" id="cart-items">
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>No items in cart</p>
            <span>Add products to start a sale</span>
        </div>
    </div>

    <div class="cart-summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span id="subtotal">₱0.00</span>
        </div>
        <div class="summary-row">
            <span>Tax (12%):</span>
            <span id="tax">₱0.00</span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span id="total">₱0.00</span>
        </div>
    </div>

    <div class="cart-actions">
        <button class="process-sale-btn" id="process-sale" disabled>
            <i class="fas fa-credit-card"></i>
            Process Sale
        </button>
    </div>
</aside>
@endsection

@push('scripts')
<script>
// Time and date display
function updateDateTime() {
    const now = new Date();
    
    // Update time
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    };
    document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
    
    // Update date
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
}

// Update every second
setInterval(updateDateTime, 1000);
updateDateTime(); // Initial call

// Cart functionality
let cart = [];
let cartTotal = 0;
let allProducts = []; // Store all products from database

// Load products from database
async function loadProducts(category = 'all') {
    const grid = document.getElementById('products-grid');
    
    try {
        // Show loading state
        grid.innerHTML = `
            <div class="loading-products">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading products...</p>
            </div>
        `;
        
        // Fetch products from API
        const response = await fetch('{{ route("pos.products") }}?category=' + category);
        const data = await response.json();
        
        if (data.success) {
            allProducts = data.products;
            const filteredProducts = data.products;
            
            if (filteredProducts.length === 0) {
                grid.innerHTML = `
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <p>No products found in this category</p>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = filteredProducts.map(product => `
                <div class="product-card ${product.stock <= 0 ? 'out-of-stock' : product.stock <= product.min_stock ? 'low-stock' : ''}" data-id="${product.id}">
                    <div class="product-image">
                        <img src="${product.image_url ? '/'+product.image_url : 'https://via.placeholder.com/200x150/333/fff?text='+product.name.charAt(0)}" 
                             alt="${product.name}"
                             onerror="this.src='https://via.placeholder.com/200x150/333/fff?text=${product.name.charAt(0)}'">
                        <div class="stock-badge ${product.stock <= 0 ? 'out-of-stock' : product.stock <= product.min_stock ? 'low-stock' : ''}">
                            ${product.stock} in stock
                        </div>
                        ${product.stock <= 0 ? '<div class="out-of-stock-overlay">OUT OF STOCK</div>' : ''}
                    </div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="brand">${product.brand}</p>
                        <p class="price">₱${parseFloat(product.price).toLocaleString()}</p>
                        <button class="add-to-cart-btn" ${product.stock <= 0 ? 'disabled' : ''} onclick="addToCart(${product.id})">
                            <i class="fas fa-plus"></i>
                            ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            grid.innerHTML = `
                <div class="error-products">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error loading products</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading products:', error);
        grid.innerHTML = `
            <div class="error-products">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error loading products</p>
            </div>
        `;
    }
}
// Category filtering
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        loadProducts(this.dataset.category);
    });
});

// Add to cart function
function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) {
        alert('Product not found');
        return;
    }
    
    // Check stock availability
    if (product.stock <= 0) {
        alert('Product is out of stock');
        return;
    }
    
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        // Check if we can add more (don't exceed stock)
        if (existingItem.quantity >= product.stock) {
            alert(`Cannot add more. Only ${product.stock} available in stock.`);
            return;
        }
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            brand: product.brand,
            price: parseFloat(product.price),
            stock: product.stock,
            quantity: 1
        });
    }
    
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const processBtn = document.getElementById('process-sale');
    
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>No items in cart</p>
                <span>Add products to start a sale</span>
            </div>
        `;
        processBtn.disabled = true;
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="item-info">
                    <h5>${item.name}</h5>
                    <p>₱${item.price.toLocaleString()}</p>
                </div>
                <div class="item-controls">
                    <button onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)">+</button>
                </div>
                <div class="item-total">
                    ₱${(item.price * item.quantity).toLocaleString()}
                </div>
            </div>
        `).join('');
        processBtn.disabled = false;
    }
    
    updateCartSummary();
}

// Update quantity
function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;
    
    const product = allProducts.find(p => p.id === productId);
    
    item.quantity += change;
    
    // Check stock limits
    if (item.quantity > product.stock) {
        alert(`Cannot add more. Only ${product.stock} available in stock.`);
        item.quantity = product.stock;
        return;
    }
    
    if (item.quantity <= 0) {
        cart = cart.filter(item => item.id !== productId);
    }
    
    updateCartDisplay();
}

// Update cart summary
function updateCartSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = `₱${subtotal.toLocaleString()}`;
    document.getElementById('tax').textContent = `₱${tax.toLocaleString()}`;
    document.getElementById('total').textContent = `₱${total.toLocaleString()}`;
    
    return { subtotal, tax, total };
}

// Clear cart
document.getElementById('clear-cart').addEventListener('click', function() {
    if (cart.length === 0) return;
    
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCartDisplay();
    }
});

// Process sale
document.getElementById('process-sale').addEventListener('click', async function() {
    if (cart.length === 0) return;
    
    const amountPaid = prompt('Enter amount paid:');
    if (!amountPaid || isNaN(amountPaid) || parseFloat(amountPaid) <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    
    const summary = updateCartSummary();
    const paymentAmount = parseFloat(amountPaid);
    
    if (paymentAmount < summary.total) {
        alert('Insufficient payment amount');
        return;
    }
    
    const paymentMethod = prompt('Payment method (cash/card/gcash/maya):', 'cash');
    if (!['cash', 'card', 'gcash', 'maya'].includes(paymentMethod)) {
        alert('Invalid payment method');
        return;
    }
    
    try {
        // Prepare sale data
        const saleData = {
            items: cart.map(item => ({
                id: item.id,
                quantity: item.quantity
            })),
            subtotal: summary.subtotal,
            tax: summary.tax,
            total: summary.total,
            amount_paid: paymentAmount,
            payment_method: paymentMethod
        };
        
        // Process sale via API
        const response = await fetch('{{ route("pos.process-sale") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(saleData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            const change = result.change;
            alert(`Sale processed successfully!\nTransaction ID: ${result.transaction_id}\nChange: ₱${change.toLocaleString()}`);
            
            // Clear cart and reload products (to update stock)
            cart = [];
            updateCartDisplay();
            loadProducts(); // Reload to show updated stock
        } else {
            alert('Error processing sale: ' + result.message);
        }
    } catch (error) {
        console.error('Error processing sale:', error);
        alert('Error processing sale. Please try again.');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});
</script>
@endpush
