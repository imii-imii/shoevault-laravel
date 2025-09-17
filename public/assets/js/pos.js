// ===== POS SYSTEM JAVASCRIPT =====
// ===== TAB SWITCHING =====
function showTab(tab) {
    // Sidebar nav highlight
    document.querySelector('.nav-pos').classList.remove('active');
    document.querySelector('.nav-reservation').classList.remove('active');
    if (tab === 'pos') {
        document.querySelector('.nav-pos').classList.add('active');
    } else {
        document.querySelector('.nav-reservation').classList.add('active');
    }
    // Main content switching
    document.getElementById('tab-pos').style.display = tab === 'pos' ? '' : 'none';
    document.getElementById('tab-reservation').style.display = tab === 'reservation' ? '' : 'none';
    document.getElementById('tab-sales-history').style.display = tab === 'sales-history' ? '' : 'none';

    // Cart sidebar visibility
    var cartSidebar = document.getElementById('cart-sidebar');
    if (cartSidebar) {
        cartSidebar.style.display = tab === 'pos' ? '' : 'none';
    }

    // Header layout adjustment
    var header = document.querySelector('.header');
    var headerRight = document.querySelector('.header-right');
    if (header) {
        if (tab === 'reservation' || tab === 'sales-history') {
            header.style.marginRight = '0';
            if (headerRight) {
                headerRight.style.justifyContent = 'flex-end';
                headerRight.style.marginLeft = 'auto';
                headerRight.style.marginRight = '32px';
            }
        } else {
            header.style.marginRight = '440px';
            if (headerRight) {
                headerRight.style.justifyContent = '';
                headerRight.style.marginLeft = '50px';
                headerRight.style.marginRight = '';
            }
        }
    }

    // Header title change
    var headerTitle = document.querySelector('.main-title');
    if (headerTitle) {
        if (tab === 'reservation') headerTitle.textContent = 'Upcoming Reservations';
        else if (tab === 'sales-history') headerTitle.textContent = 'Sales History';
        else headerTitle.textContent = 'Point of Sale';
    }

    // Reservation tab full width
    var reservationWrapper = document.getElementById('tab-reservation');
    if (reservationWrapper) {
        if (tab === 'reservation') {
            reservationWrapper.classList.add('reservation-full');
        } else {
            reservationWrapper.classList.remove('reservation-full');
        }
    }
}

window.showTab = showTab;

// ===== SESSION MANAGEMENT =====
function checkSession() {
    const session = localStorage.getItem('pos_session');
    if (!session) {
        // Redirect to login if no session
        window.location.href = 'login.html';
        return;
    }
    
    const sessionData = JSON.parse(session);
    const now = new Date().getTime();
    
    // Check if session is still valid (24 hours)
    if (now - sessionData.timestamp > 24 * 60 * 60 * 1000) {
        // Clear expired session and redirect to login
        localStorage.removeItem('pos_session');
        window.location.href = 'login.html';
        return;
    }
    
    // Update user info in the UI
    updateUserInfo(sessionData.user);
}

function updateUserInfo(user) {
    const userDetails = document.querySelector('.user-details h4');
    if (userDetails) {
        userDetails.textContent = user.name;
    }
    
    // Update role in sidebar
    const roleSpan = document.querySelector('.user-details span');
    if (roleSpan) {
        roleSpan.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    }
}

function logout() {
    // Clear session
    localStorage.removeItem('pos_session');
    
    // Show logout notification
    showNotification('Logged out successfully', 'info');
    
    // Redirect to login page
    setTimeout(() => {
        window.location.href = 'login.html';
    }, 1000);
}

// ===== PRODUCT DATA =====
const PRODUCTS = [
    // Men's Shoes
    {
        id: 1,
        name: "Nike Air Max 270",
        brand: "Nike",
        price: 8499,
        category: "men",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 15,
        image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
        description: "Comfortable running shoes with Air Max technology"
    },
    {
        id: 2,
        name: "Adidas Ultraboost 22",
        brand: "Adidas",
        price: 12999,
        category: "men",
        sizes: ["8", "9", "10", "11", "12"],
        stock: 12,
        image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
        description: "Premium running shoes with Boost technology"
    },
    {
        id: 3,
        name: "Jordan Air 1 Retro High",
        brand: "Nike",
        price: 15999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11"],
        stock: 8,
        image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
        description: "Classic basketball shoes with Air technology"
    },
    {
        id: 4,
        name: "Converse Chuck Taylor",
        brand: "Converse",
        price: 3999,
        category: "accessories",
        sizes: ["6", "7", "8", "9", "10", "11", "12"],
        stock: 25,
        image: "https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop",
        description: "Timeless canvas sneakers"
    },
    {
        id: 5,
        name: "Vans Old Skool",
        brand: "Vans",
        price: 4499,
        category: "accessories",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 18,
        image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop",
        description: "Classic skate shoes with side stripe"
    },

    // Women's Shoes
    {
        id: 6,
        name: "Nike Air Force 1 '07",
        brand: "Nike",
        price: 6999,
        category: "women",
        sizes: ["5", "6", "7", "8", "9", "10"],
        stock: 20,
        image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
        description: "Iconic lifestyle sneakers"
    },
    {
        id: 7,
        name: "Adidas Stan Smith",
        brand: "Adidas",
        price: 5499,
        category: "women",
        sizes: ["5", "6", "7", "8", "9", "10"],
        stock: 16,
        image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
        description: "Classic tennis shoes"
    },
    {
        id: 8,
        name: "Puma RS-X",
        brand: "Puma",
        price: 6499,
        category: "women",
        sizes: ["6", "7", "8", "9", "10"],
        stock: 14,
        image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
        description: "Retro-inspired running shoes"
    },
    {
        id: 9,
        name: "New Balance 574",
        brand: "New Balance",
        price: 5999,
        category: "women",
        sizes: ["5", "6", "7", "8", "9", "10"],
        stock: 22,
        image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
        description: "Comfortable lifestyle sneakers"
    },
    {
        id: 10,
        name: "Reebok Classic",
        brand: "Reebok",
        price: 3999,
        category: "women",
        sizes: ["6", "7", "8", "9", "10"],
        stock: 19,
        image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
        description: "Timeless athletic shoes"
    },

    // Kids' Shoes
    {
        id: 11,
        name: "Nike Kids Revolution",
        brand: "Nike",
        price: 2999,
        category: "women",
        sizes: ["1", "2", "3", "4", "5", "6"],
        stock: 30,
        image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
        description: "Comfortable kids running shoes"
    },
    {
        id: 12,
        name: "Adidas Kids Cloudfoam",
        brand: "Adidas",
        price: 3499,
        category: "women",
        sizes: ["1", "2", "3", "4", "5", "6"],
        stock: 25,
        image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
        description: "Soft and comfortable kids shoes"
    },
    {
        id: 13,
        name: "Puma Kids Softride",
        brand: "Puma",
        price: 2799,
        category: "men",
        sizes: ["2", "3", "4", "5", "6"],
        stock: 28,
        image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
        description: "Lightweight kids athletic shoes"
    },

    // Sports Shoes
    {
        id: 14,
        name: "Nike ZoomX Vaporfly",
        brand: "Nike",
        price: 24999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 6,
        image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
        description: "Elite racing shoes for marathon runners"
    },
    {
        id: 15,
        name: "Adidas Predator Edge",
        brand: "Adidas",
        price: 18999,
        category: "men",
        sizes: ["8", "9", "10", "11", "12"],
        stock: 10,
        image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
        description: "Professional soccer cleats"
    },
    {
        id: 16,
        name: "Under Armour Curry 9",
        brand: "Under Armour",
        price: 12999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 12,
        image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
        description: "Basketball shoes with superior traction"
    },

    // Casual Shoes
    {
        id: 17,
        name: "Sperry Top-Sider",
        brand: "Sperry",
        price: 4999,
        category: "menl",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 35,
        image: "https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop",
        description: "Classic boat shoes"
    },
    {
        id: 18,
        name: "Crocs Classic Clog",
        brand: "Crocs",
        price: 2499,
        category: "men",
        sizes: ["6", "7", "8", "9", "10", "11", "12"],
        stock: 50,
        image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
        description: "Comfortable and versatile clogs"
    },
    {
        id: 19,
        name: "Birkenstock Arizona",
        brand: "Birkenstock",
        price: 5999,
        category: "women",
        sizes: ["6", "7", "8", "9", "10", "11"],
        stock: 22,
        image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
        description: "Premium comfort sandals"
    },
    {
        id: 20,
        name: "Nike Air Jordan 4",
        brand: "Nike",
        price: 18999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 5,
        image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
        description: "Retro basketball sneakers"
    },
    {
        id: 21,
        name: "Adidas Yeezy Boost 350",
        brand: "Adidas",
        price: 29999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 3,
        image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
        description: "Limited edition lifestyle sneakers"
    },
    {
        id: 22,
        name: "Nike Zoom Fly 4",
        brand: "Nike",
        price: 9999,
        category: "sports",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 15,
        image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
        description: "Performance running shoes"
    },
    {
        id: 23,
        name: "Adidas Terrex Free Hiker",
        brand: "Adidas",
        price: 15999,
        category: "men",
        sizes: ["8", "9", "10", "11", "12"],
        stock: 8,
        image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
        description: "Hiking shoes with Boost technology"
    },
    {
        id: 24,
        name: "Nike Kids Air Jordan 1",
        brand: "Nike",
        price: 5999,
        category: "men",
        sizes: ["1", "2", "3", "4", "5", "6"],
        stock: 18,
        image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
        description: "Kids basketball shoes"
    },
    {
        id: 25,
        name: "Adidas Kids Gazelle",
        brand: "Adidas",
        price: 3999,
        category: "men",
        sizes: ["1", "2", "3", "4", "5", "6"],
        stock: 20,
        image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
        description: "Classic kids sneakers"
    },
    {
        id: 26,
        name: "Nike Air Max 90",
        brand: "Nike",
        price: 8999,
        category: "women",
        sizes: ["5", "6", "7", "8", "9", "10"],
        stock: 12,
        image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
        description: "Iconic lifestyle sneakers"
    },
    {
        id: 27,
        name: "Adidas NMD R1",
        brand: "Adidas",
        price: 11999,
        category: "women",
        sizes: ["5", "6", "7", "8", "9", "10"],
        stock: 9,
        image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
        description: "Modern lifestyle sneakers"
    },
    {
        id: 28,
        name: "Puma Future Rider",
        brand: "Puma",
        price: 5499,
        category: "men",
        sizes: ["6", "7", "8", "9", "10", "11", "12"],
        stock: 25,
        image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
        description: "Retro-inspired lifestyle sneakers"
    },
    {
        id: 29,
        name: "Dr. Martens 1460",
        brand: "Dr. Martens",
        price: 8999,
        category: "men",
        sizes: ["7", "8", "9", "10", "11"],
        stock: 15,
        image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop",
        description: "Iconic leather boots"
    },
    {
        id: 30,
        name: "Timberland 6-Inch",
        brand: "Timberland",
        price: 11999,
        category: "men",
        sizes: ["8", "9", "10", "11", "12"],
        stock: 12,
        image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
        description: "Premium waterproof boots"
    },
    {
        id: 31,
        name: "Clarks Desert Boot",
        brand: "Clarks",
        price: 6499,
        category: "accessories",
        sizes: ["7", "8", "9", "10", "11"],
        stock: 20,
        image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
        description: "Classic suede desert boots"
    },
    {
        id: 32,
        name: "Leather Shoe Laces",
        brand: "Premium",
        price: 299,
        category: "accessories",
        sizes: ["Standard"],
        stock: 50,
        image: "https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop",
        description: "High-quality leather shoe laces"
    },
    {
        id: 33,
        name: "Shoe Cleaning Kit",
        brand: "CarePro",
        price: 899,
        category: "accessories",
        sizes: ["One Size"],
        stock: 30,
        image: "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400&h=400&fit=crop",
        description: "Complete shoe cleaning and care kit"
    },
    {
        id: 34,
        name: "Shoe Insoles",
        brand: "ComfortPlus",
        price: 599,
        category: "accessories",
        sizes: ["7", "8", "9", "10", "11", "12"],
        stock: 40,
        image: "https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop",
        description: "Memory foam shoe insoles for comfort"
    },
    {
        id: 35,
        name: "Shoe Polish Set",
        brand: "ShineMaster",
        price: 399,
        category: "accessories",
        sizes: ["Standard"],
        stock: 25,
        image: "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400&h=400&fit=crop",
        description: "Professional shoe polish and brush set"
    }
];

// ===== STATE MANAGEMENT =====
let currentState = {
    selectedCategory: 'all',
    searchQuery: '',
    cart: [],
    paymentAmount: '',
    currentTime: new Date()
};

// ===== UTILITY FUNCTIONS =====
function formatPrice(price) {
    return `₱ ${price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(date) {
    return date.toLocaleDateString('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(date) {
    return date.toLocaleTimeString('en-PH', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function generateReceiptNumber() {
    return Math.floor(Math.random() * 900000) + 100000;
}

function calculateTax(amount) {
    return amount * 0.12; // 12% tax
}

function calculateTotal() {
    const subtotal = currentState.cart.reduce((total, item) => total + item.subtotal, 0);
    const tax = calculateTax(subtotal);
    return subtotal + tax;
}

// ===== PRODUCT RENDERING =====
function renderProducts() {
    const productsGrid = document.getElementById('products-grid');
    const filteredProducts = getFilteredProducts();

    // Update category tab counts
    updateCategoryCounts();

    if (filteredProducts.length === 0) {
        productsGrid.innerHTML = `
            <div class="no-products">
                <i class="fas fa-search"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search or category filter</p>
            </div>
        `;
        return;
    }

    productsGrid.innerHTML = filteredProducts.map(product => `
        <div class="product-card" data-product-id="${product.id}">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}" loading="lazy">
                <div class="product-badge">${product.category.toUpperCase()}</div>
                ${product.stock === 0 ? '<div class="out-of-stock-overlay">Out of Stock</div>' : ''}
                ${product.stock > 0 && product.stock <= 5 ? '<div class="low-stock-overlay">Low Stock</div>' : ''}
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-brand">${product.brand}</p>
                <div class="product-details">
                    <span class="product-price">${formatPrice(product.price)}</span>
                    <span class="product-stock ${product.stock === 0 ? 'out-of-stock' : product.stock <= 5 ? 'low-stock' : ''}">${product.stock} in stock</span>
                </div>
                <div class="product-sizes">
                    <span class="size-label">Sizes:</span>
                    ${product.sizes.map(size => `<span class="size-badge">${size}</span>`).join('')}
                </div>
                <button class="add-to-cart-btn" onclick="openProductModal(${product.id})" ${product.stock === 0 ? 'disabled' : ''}>
                    <i class="fas fa-plus"></i>
                    ${product.stock === 0 ? 'Out of Stock' : 'Add to Cart'}
                </button>
            </div>
        </div>
    `).join('');
}

function getFilteredProducts() {
    let filtered = PRODUCTS;
    
    // Filter by category
    if (currentState.selectedCategory !== 'all') {
        filtered = filtered.filter(product => product.category === currentState.selectedCategory);
    }
    
    // Filter by search query
    if (currentState.searchQuery.trim()) {
        const query = currentState.searchQuery.toLowerCase();
        filtered = filtered.filter(product => 
            product.name.toLowerCase().includes(query) ||
            product.brand.toLowerCase().includes(query) ||
            product.description.toLowerCase().includes(query)
        );
    }
    
    return filtered;
}

function updateCategoryCounts() {
    const categories = ['all', 'men', 'women', 'kids', 'sports', 'casual'];
    
    categories.forEach(category => {
        const tab = document.querySelector(`[data-category="${category}"]`);
        if (tab) {
            const count = category === 'all' ? PRODUCTS.length : PRODUCTS.filter(p => p.category === category).length;
            const span = tab.querySelector('span');
            if (span) {
                span.textContent = span.textContent.replace(/\s*\(\d+\)\s*$/, '') + ` (${count})`;
            }
        }
    });
}

// ===== CART MANAGEMENT =====
function addToCart(productId) {
    const product = PRODUCTS.find(p => p.id === productId);
    if (!product || product.stock === 0) return;
    
    // Check if product is already in cart
    const existingItem = currentState.cart.find(item => item.id === productId);
    
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
            // Deduct stock when quantity increases
            product.stock--;
        } else {
            showNotification('Maximum stock reached for this item', 'warning');
            return;
        }
    } else {
        currentState.cart.push({
            id: product.id,
            name: product.name,
            brand: product.brand,
            price: product.price,
            quantity: 1,
            subtotal: product.price,
            size: product.sizes[0] // Default to first available size
        });
        // Deduct stock when adding new item
        product.stock--;
    }
    
    renderCart();
    updateCartSummary();
    renderProducts(); // Re-render products to show updated stock
    showNotification(`${product.name} added to cart`, 'success');
}

function removeFromCart(productId) {
    const itemToRemove = currentState.cart.find(item => item.id === productId);
    if (itemToRemove) {
        // Restore stock when item is removed
        const product = PRODUCTS.find(p => p.id === productId);
        if (product) {
            product.stock += itemToRemove.quantity;
        }
    }
    
    currentState.cart = currentState.cart.filter(item => item.id !== productId);
    renderCart();
    updateCartSummary();
    renderProducts(); // Re-render products to show updated stock
}

function updateQuantity(productId, newQuantity) {
    const item = currentState.cart.find(item => item.id === productId);
    const product = PRODUCTS.find(p => p.id === productId);
    
    if (!item || !product) return;
    
    if (newQuantity <= 0) {
        removeFromCart(productId);
        return;
    }
    
    const oldQuantity = item.quantity;
    const quantityDifference = newQuantity - oldQuantity;
    
    // Check if we can add more items (for quantity increase)
    if (quantityDifference > 0 && quantityDifference > product.stock) {
        showNotification('Cannot exceed available stock', 'warning');
        return;
    }
    
    // Update stock based on quantity change
    if (quantityDifference > 0) {
        // Increasing quantity - deduct stock
        product.stock -= quantityDifference;
    } else if (quantityDifference < 0) {
        // Decreasing quantity - restore stock
        product.stock += Math.abs(quantityDifference);
    }
    
    item.quantity = newQuantity;
    item.subtotal = item.quantity * item.price;
    
    renderCart();
    updateCartSummary();
    renderProducts(); // Re-render products to show updated stock
}

function renderCart() {
    const cartItems = document.getElementById('cart-items');
    
    if (currentState.cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-bag"></i>
                <h3>Your cart is empty</h3>
                <p>Add some shoes to get started!</p>
            </div>
        `;
        return;
    }
    
    cartItems.innerHTML = currentState.cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-left">
                <h4 class="cart-item-name">${item.name}</h4>
                <span class="cart-item-size">Size: ${item.size}</span>
            </div>
            <div class="cart-item-right">
                <span class="cart-item-price">${formatPrice(item.subtotal)}</span>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span class="quantity-display">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
            </div>
        </div>
    `).join('');
}

function updateCartSummary() {
    const subtotal = currentState.cart.reduce((total, item) => total + item.subtotal, 0);
    const tax = calculateTax(subtotal);
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = formatPrice(subtotal);
    document.getElementById('tax-amount').textContent = formatPrice(tax);
    document.getElementById('total-amount').textContent = formatPrice(total);
    
    // Update payment input if total changed
    if (currentState.paymentAmount && parseFloat(currentState.paymentAmount) < total) {
        currentState.paymentAmount = '';
        document.getElementById('payment-amount').value = '';
        updateChangeDisplay();
    }
}

function clearCart() {
    // Restore stock for all items in cart
    currentState.cart.forEach(item => {
        const product = PRODUCTS.find(p => p.id === item.id);
        if (product) {
            product.stock += item.quantity;
        }
    });
    
    currentState.cart = [];
    currentState.paymentAmount = '';
    renderCart();
    updateCartSummary();
    document.getElementById('payment-amount').value = '';
    updateChangeDisplay();
    renderProducts(); // Re-render products to show updated stock
    showNotification('Cart cleared', 'clear');
}

// ===== PAYMENT PROCESSING =====
function handleNumberPad(number) {
    if (number === '.') {
        if (!currentState.paymentAmount.includes('.')) {
            currentState.paymentAmount += number;
        }
    } else {
        currentState.paymentAmount += number;
    }
    
    document.getElementById('payment-amount').value = currentState.paymentAmount;
    updateChangeDisplay();
}

function handleQuickAmount(amount) {
    currentState.paymentAmount = amount.toString();
    document.getElementById('payment-amount').value = currentState.paymentAmount;
    updateChangeDisplay();
}

function clearPayment() {
    currentState.paymentAmount = '';
    document.getElementById('payment-amount').value = '';
    updateChangeDisplay();
}

function updateChangeDisplay() {
    const paymentAmount = parseFloat(currentState.paymentAmount) || 0;
    const total = calculateTotal();
    const change = paymentAmount - total;
    
    const changeDisplay = document.getElementById('change-display');
    const changeAmount = document.getElementById('change-amount');
    
    if (paymentAmount > 0) {
        changeDisplay.style.display = 'flex';
        changeAmount.textContent = formatPrice(Math.max(0, change));
    } else {
        changeDisplay.style.display = 'none';
    }
}

function completeTransaction() {
    if (currentState.cart.length === 0) {
        showNotification('Cart is empty', 'warning');
        return;
    }
    
    const paymentAmount = parseFloat(currentState.paymentAmount) || 0;
    const total = calculateTotal();
    
    if (paymentAmount < total) {
        showNotification('Insufficient payment amount', 'error');
        return;
    }
    
    // Generate receipt
    const receipt = {
        number: generateReceiptNumber(),
        date: currentState.currentTime,
        items: currentState.cart,
        subtotal: currentState.cart.reduce((total, item) => total + item.subtotal, 0),
        tax: calculateTax(currentState.cart.reduce((total, item) => total + item.subtotal, 0)),
        total: total,
        payment: paymentAmount,
        change: paymentAmount - total
    };
    
    // Stock is already deducted when items are added to cart
    // No need to deduct again during transaction completion
    
    // Clear cart and payment
    clearCart();
    
    // Show receipt
    showReceipt(receipt);
    
    // Show success notification
    showNotification('Transaction completed successfully!', 'success');
}

function cancelTransaction() {
    // Restore stock for all items in cart
    currentState.cart.forEach(item => {
        const product = PRODUCTS.find(p => p.id === item.id);
        if (product) {
            product.stock += item.quantity;
        }
    });
    
    currentState.cart = [];
    currentState.paymentAmount = '';
    renderCart();
    updateCartSummary();
    document.getElementById('payment-amount').value = '';
    updateChangeDisplay();
    renderProducts(); // Re-render products to show updated stock
    showNotification('Transaction cancelled', 'cancel');
}

// ===== RECEIPT GENERATION =====
function showReceipt(receipt) {
    // Populate receipt data
    document.getElementById('receipt-number').textContent = receipt.number;
    document.getElementById('receipt-date').textContent = formatDate(receipt.date);
    document.getElementById('receipt-subtotal').textContent = formatPrice(receipt.subtotal);
    document.getElementById('receipt-tax').textContent = formatPrice(receipt.tax);
    document.getElementById('receipt-total').textContent = formatPrice(receipt.total);
    document.getElementById('receipt-cash').textContent = formatPrice(receipt.payment);
    document.getElementById('receipt-change').textContent = formatPrice(receipt.change);
    
    // Populate receipt items
    const receiptItems = document.getElementById('receipt-items');
    receiptItems.innerHTML = receipt.items.map(item => `
        <div class="receipt-item">
            <div class="receipt-item-name">${item.name} (${item.size})</div>
            <div class="receipt-item-details">
                ${item.quantity} × ${formatPrice(item.price)} = ${formatPrice(item.subtotal)}
            </div>
        </div>
    `).join('');
    
    // Show modal
    const modal = document.getElementById('receipt-modal');
    modal.classList.add('show');
}

function closeReceipt() {
    const modal = document.getElementById('receipt-modal');
    modal.classList.remove('show');
}

function printReceipt() {
    const receiptContent = document.querySelector('.receipt-modal').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - D'Kamp Batangas</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .receipt-header { text-align: center; margin-bottom: 20px; }
                .receipt-item { display: flex; justify-content: space-between; margin: 5px 0; }
                .receipt-summary { border-top: 1px solid #ccc; margin-top: 20px; padding-top: 10px; }
                .receipt-row { display: flex; justify-content: space-between; margin: 5px 0; }
                .total { font-weight: bold; font-size: 1.1em; }
                @media print { body { font-size: 12px; } }
            </style>
        </head>
        <body>
            ${receiptContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}
// ===== NOTIFICATIONS =====
function showNotification(message, type = 'info') {
    const notification = document.getElementById('success-notification');
    const icon = notification.querySelector('i');
    const text = notification.querySelector('span');
    
    // Update notification content
    text.textContent = message;
    
    // Use a single gradient background for all notifications
    const commonGradient = 'linear-gradient(135deg, #1e3a8a 0%, #a3bffa 100%)'; // Blue gradient matching the theme
    notification.style.background = commonGradient;
    
    // Update icon based on type
    switch (type) {
        case 'success':
            icon.className = 'fas fa-check-circle';
            break;
        case 'error':
            icon.className = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle';
            break;
        case 'info':
            icon.className = 'fas fa-info-circle';
            break;
        case 'clear':
            icon.className = 'fas fa-trash';
            break;
        case 'cancel':
            icon.className = 'fas fa-times';
            break;
    }
    
    // Show notification
    notification.classList.add('show');
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Update category filter event listener (unchanged, uses 'info')
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentState.selectedCategory = this.dataset.category;
        renderProducts();
        const categoryName = this.querySelector('span').textContent.replace(/\s*\(\d+\)\s*$/, '');
        showNotification(`Showing ${categoryName}`, 'info');
    });
});

// Update addToCart function (unchanged, uses 'success')
function addToCart(productId) {
    const product = PRODUCTS.find(p => p.id === productId);
    if (!product || product.stock === 0) return;
    
    const existingItem = currentState.cart.find(item => item.id === productId);
    
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
        } else {
            showNotification('Maximum stock reached for this item', 'warning');
            return;
        }
    } else {
        currentState.cart.push({
            id: product.id,
            name: product.name,
            brand: product.brand,
            price: product.price,
            quantity: 1,
            subtotal: product.price,
            size: product.sizes[0]
        });
    }
    
    renderCart();
    updateCartSummary();
    showNotification(`${product.name} added to cart`, 'success');
}

// Update clearCart function (unchanged, uses 'clear')
function clearCart() {
    currentState.cart = [];
    currentState.paymentAmount = '';
    renderCart();
    updateCartSummary();
    document.getElementById('payment-amount').value = '';
    updateChangeDisplay();
    showNotification('Cart cleared', 'clear');
}

// Update cancelTransaction function (unchanged, uses 'cancel')
function cancelTransaction() {
    clearCart();
    showNotification('Transaction cancelled', 'cancel');
}

// ===== TIME UPDATES =====
function updateTime() {
    currentState.currentTime = new Date();
    document.getElementById('current-time').textContent = formatTime(currentState.currentTime);
    document.getElementById('current-date').textContent = formatDate(currentState.currentTime);
}

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    // Check session first
    checkSession();
    
    // Initialize the application
    updateCategoryCounts(); // Initialize category counts first
    renderProducts();
    updateTime();
    
    // Set up time updates
    setInterval(updateTime, 1000);
    
    // Category filter
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Update state and re-render
            currentState.selectedCategory = this.dataset.category;
            renderProducts();
            
            // Show notification for category change
            const categoryName = this.querySelector('span').textContent;
            showNotification(`Showing ${categoryName}`, 'info');
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');
    
    searchInput.addEventListener('input', function() {
        currentState.searchQuery = this.value;
        renderProducts();
    });
    
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentState.searchQuery = '';
        renderProducts();
    });
    
    // Number pad
    document.querySelectorAll('.num-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const number = this.dataset.number;
            if (number === 'clear') {
                clearPayment();
            } else {
                handleNumberPad(number);
            }
        });
    });
    
    // Quick amount buttons
    document.querySelectorAll('.quick-amount-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Check if it's a quick action button
            if (this.id === 'quick-cancel') {
                cancelTransaction();
            } else if (this.id === 'quick-print') {
                // Only show receipt if there are items in cart
                if (currentState.cart.length > 0) {
                    const receipt = {
                        items: currentState.cart,
                        subtotal: currentState.subtotal,
                        tax: currentState.tax,
                        total: currentState.total,
                        paymentAmount: currentState.paymentAmount || 0,
                        change: (currentState.paymentAmount || 0) - currentState.total,
                        receiptNumber: generateReceiptNumber(),
                        date: currentState.currentTime,
                        cashier: 'Lauren Smith'
                    };
                    showReceipt(receipt);
                } else {
                    showNotification('No items in cart to print receipt', 'warning');
                }
            } else {
                // Handle amount buttons
                const amount = parseInt(this.dataset.amount);
                handleQuickAmount(amount);
            }
        });
    });
    
    // Action buttons
    document.getElementById('clear-cart').addEventListener('click', clearCart);
    document.getElementById('clear-payment').addEventListener('click', clearPayment);
    document.getElementById('cancel-transaction').addEventListener('click', cancelTransaction);
    document.getElementById('complete-transaction').addEventListener('click', completeTransaction);
    
    // Modal close on overlay click
    document.getElementById('receipt-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReceipt();
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close receipt modal
        if (e.key === 'Escape') {
            closeReceipt();
        }
        
        // Enter to complete transaction
        if (e.key === 'Enter' && e.ctrlKey) {
            completeTransaction();
        }
        
        // Number keys for payment input
        if (e.key >= '0' && e.key <= '9' || e.key === '.') {
            if (document.activeElement.id === 'payment-amount') {
                return; // Let the input handle it
            }
            handleNumberPad(e.key);
        }
    });
    
    // Payment input handling
    document.getElementById('payment-amount').removeAttribute('readonly');
    document.getElementById('payment-amount').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d.]/g, '');
        // Only allow one decimal point
        if ((value.match(/\./g) || []).length > 1) {
            value = value.replace(/\.(?=.*\.)/, '');
        }
        // Limit to 2 decimal places
        if (value.includes('.')) {
            const parts = value.split('.');
            value = parts[0] + '.' + parts[1].slice(0,2);
        }
        e.target.value = value;
        currentState.paymentAmount = value;
        updateChangeDisplay();
    });
    
    // Prevent non-numeric input in payment field
    document.getElementById('payment-amount').addEventListener('keypress', function(e) {
        if (!/[0-9.]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // Product modal open/close
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.dataset.productId;
            openProductModal(productId);
        });
    });
    
    document.getElementById('close-modal-btn').addEventListener('click', closeProductModal);
});

// ===== EXPOSE FUNCTIONS FOR HTML =====
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateQuantity = updateQuantity;
window.clearCart = clearCart;
window.handleNumberPad = handleNumberPad;
window.handleQuickAmount = handleQuickAmount;
window.clearPayment = clearPayment;
window.completeTransaction = completeTransaction;
window.cancelTransaction = cancelTransaction;
window.showReceipt = showReceipt;
window.closeReceipt = closeReceipt;
window.printReceipt = printReceipt;
window.showNotification = showNotification;
window.logout = logout;
window.renderSalesHistoryTable = renderSalesHistoryTable;
window.renderSalesHistoryCards = renderSalesHistoryCards;

// ===== MODAL FUNCTIONS =====
function openProductModal(productId) {
    const product = PRODUCTS.find(p => p.id === productId);
    if (!product) return;
    document.getElementById('modal-product-image').src = product.image;
    document.getElementById('modal-product-name').textContent = product.name;
    document.getElementById('modal-product-brand').textContent = product.brand;
    document.getElementById('modal-product-description').textContent = product.description;
    document.getElementById('modal-product-price').textContent = formatPrice(product.price);
    // Render size options as buttons
    const sizeOptions = document.getElementById('modal-size-options');
    sizeOptions.innerHTML = product.sizes.map(size => `
        <label class="size-option-label">
            <input type="radio" name="modal-size" value="${size}" style="display:none;">
            <span class="size-option">${size}</span>
        </label>
    `).join('');
    // Add click event for size buttons
    Array.from(sizeOptions.querySelectorAll('.size-option-label')).forEach(label => {
        label.onclick = function() {
            Array.from(sizeOptions.querySelectorAll('input[type=radio]')).forEach(input => input.checked = false);
            label.querySelector('input[type=radio]').checked = true;
            Array.from(sizeOptions.querySelectorAll('.size-option-label')).forEach(l => l.classList.remove('selected'));
            label.classList.add('selected');
        };
    });
    // Show modal
    document.getElementById('product-modal').classList.add('show');
    // Set up add to cart button
    const addBtn = document.getElementById('modal-add-to-cart-btn');
    addBtn.onclick = function() {
        const selectedRadio = sizeOptions.querySelector('input[type=radio]:checked');
        if (!selectedRadio) {
            alert('Please select a size.');
            return;
        }
        addToCartWithSize(productId, selectedRadio.value);
        closeProductModal();
    };
}

function closeProductModal() {
    document.getElementById('product-modal').classList.remove('show');
}

function addToCartWithSize(productId, size) {
    const product = PRODUCTS.find(p => p.id === productId);
    if (!product || product.stock === 0) return;
    // Check if product with same size is already in cart
    const existingItem = currentState.cart.find(item => item.id === productId && item.size === size);
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
        }
    } else {
        currentState.cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            size: size,
            quantity: 1,
            subtotal: product.price,
        });
    }
    renderCart();
    updateCartSummary();
    renderProducts();
    showNotification(`${product.name} (Size ${size}) added to cart`, 'success');
}

// ===== RESERVATION TAB INTERACTIVITY =====
const reservationData = [
    // Placeholder for PHP integration: Replace with PHP-generated data
    {
        id: 'R-1001', name: 'John Doe', email: 'john@example.com', phone: '09171234567', products: 'Nike Air Max 270, Adidas Ultraboost 22', dateReserved: '2025-09-01', pickupDate: '2025-09-06', status: 'Pending', days: '1 day left'
    },
    {
        id: 'R-1002', name: 'Jane Smith', email: 'jane@example.com', phone: '09181234567', products: 'Puma RS-X', dateReserved: '2025-09-02', pickupDate: '2025-09-05', status: 'Completed', days: 'Picked up'
    },
    {
        id: 'R-1003', name: 'Mark Lee', email: 'mark@example.com', phone: '09191234567', products: 'Converse Chuck Taylor', dateReserved: '2025-08-28', pickupDate: '2025-09-03', status: 'Cancelled', days: '2 days since cancelled'
    }
];
let reservationStatusFilter = 'all';
let reservationSearchQuery = '';

function renderReservationTable() {
    // Placeholder for PHP: Use PHP to filter and output rows
    let filtered = reservationData.filter(row => {
        const statusMatch = reservationStatusFilter === 'all' || row.status.toLowerCase() === reservationStatusFilter;
        const searchMatch = reservationSearchQuery.trim() === '' || (
            row.id.toLowerCase().includes(reservationSearchQuery) ||
            row.name.toLowerCase().includes(reservationSearchQuery) ||
            row.email.toLowerCase().includes(reservationSearchQuery) ||
            row.phone.toLowerCase().includes(reservationSearchQuery) ||
            row.products.toLowerCase().includes(reservationSearchQuery)
        );
        return statusMatch && searchMatch;
    });
    const tbody = document.getElementById('reservation-table-body');
    tbody.innerHTML = filtered.map(row => `
        <tr>
            <td>${row.id}</td>
            <td>${row.name}</td>
            <td>${row.email}</td>
            <td>${row.phone}</td>
            <td>${row.products}</td>
            <td>${row.dateReserved}</td>
            <td>${row.pickupDate}</td>
            <td>${row.status}</td>
            <td>${row.days}</td>
        </tr>
    `).join('');
}

document.addEventListener('DOMContentLoaded', function() {
    // Reservation tab: category filter
    document.querySelectorAll('.reservation-tab').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.reservation-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            reservationStatusFilter = btn.getAttribute('data-status').toLowerCase();
            renderReservationTable();
        });
    });
    // Reservation tab: search
    const searchInput = document.getElementById('reservation-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            reservationSearchQuery = searchInput.value.toLowerCase();
            renderReservationTable();
        });
    }
    // Reservation tab: clear search
    const clearBtn = document.getElementById('clear-reservation-search');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            reservationSearchQuery = '';
            if (searchInput) searchInput.value = '';
            renderReservationTable();
        });
    }
    // Initial render
    renderReservationTable();
});

// ===== SALES HISTORY TAB RENDERING =====
document.addEventListener('DOMContentLoaded', function() {
    // Sales History Tab logic
    const salesSearchInput = document.getElementById('sales-history-search');
    const salesClearSearch = document.getElementById('clear-sales-history-search');
    const salesTimeTabs = document.querySelectorAll('.sales-history-time-tab');
    const salesPriceTabs = document.querySelectorAll('.sales-history-price-tab');
    // Search
    if (salesSearchInput) {
        salesSearchInput.addEventListener('input', function() {
            salesSearchQuery = this.value.trim().toLowerCase();
            renderSalesHistoryTable();
        });
    }
    if (salesClearSearch) {
        salesClearSearch.addEventListener('click', function() {
            salesSearchInput.value = '';
            salesSearchQuery = '';
            renderSalesHistoryTable();
        });
    }
    // Time category tabs
    salesTimeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            salesTimeTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            salesTimeFilter = this.getAttribute('data-time') || 'all';
            renderSalesHistoryTable();
        });
    });
    // Price range tabs
    salesPriceTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            salesPriceTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            salesPriceFilter = this.getAttribute('data-price') || 'all';
            renderSalesHistoryTable();
        });
    });
    // Initial render
    renderSalesHistoryTable();
});

// ===== SALES HISTORY TAB INTERACTIVITY =====
const salesHistoryData = [
    {
        customerId: 'C-2001',
        products: 'Nike Air Max 270, Adidas Ultraboost 22',
        quantity: 2,
        totalPrice: 21498,
        cashReceived: 22000,
        change: 502,
        time: '08:15 AM'
    },
    {
        customerId: 'C-2002',
        products: 'Puma RS-X',
        quantity: 1,
        totalPrice: 6499,
        cashReceived: 7000,
        change: 501,
        time: '10:30 AM'
    },
    {
        customerId: 'C-2003',
        products: 'Converse Chuck Taylor, Vans Old Skool',
        quantity: 2,
        totalPrice: 8498,
        cashReceived: 9000,
        change: 502,
        time: '02:45 PM'
    },
    {
        customerId: 'C-2004',
        products: 'Nike Air Force 1',
        quantity: 1,
        totalPrice: 6999,
        cashReceived: 7000,
        change: 1,
        time: '05:10 PM'
    },
    {
        customerId: 'C-2005',
        products: 'Adidas Stan Smith',
        quantity: 1,
        totalPrice: 5499,
        cashReceived: 6000,
        change: 501,
        time: '06:05 PM'
    }
];
let salesTimeFilter = 'all';
let salesPriceFilter = 'all';
let salesSearchQuery = '';

function renderSalesHistoryTable() {
    const tbody = document.getElementById('sales-history-table-body');
    tbody.innerHTML = '';
    salesHistoryData.forEach(sale => {
        // Filter by time category
        let timeMatch = false;
        if (salesTimeFilter === 'all') timeMatch = true;
        else if (salesTimeFilter === 'morning') timeMatch = /^(0?[7-9]|10|11):\d{2} (AM)$/i.test(sale.time);
        else if (salesTimeFilter === 'afternoon') timeMatch = /^(12|0?1|0?2|0?3|0?4|0?5|0?6):\d{2} (PM)$/i.test(sale.time);
        // Filter by price range
        let priceMatch = false;
        if (salesPriceFilter === 'all') priceMatch = true;
        else if (salesPriceFilter === 'low') priceMatch = sale.totalPrice >= 0 && sale.totalPrice <= 3000;
        else if (salesPriceFilter === 'mid') priceMatch = sale.totalPrice > 3000 && sale.totalPrice <= 20000;
        else if (salesPriceFilter === 'high') priceMatch = sale.totalPrice > 20000;
        // Filter by search
        let searchMatch = salesSearchQuery === '' || sale.customerId.toLowerCase().includes(salesSearchQuery) || sale.products.toLowerCase().includes(salesSearchQuery);
        if (timeMatch && priceMatch && searchMatch) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${sale.customerId}</td>
                <td>${sale.products}</td>
                <td>${sale.quantity}</td>
                <td>₱ ${sale.totalPrice.toLocaleString()}</td>
                <td>₱ ${sale.cashReceived.toLocaleString()}</td>
                <td>₱ ${sale.change.toLocaleString()}</td>
                <td>${sale.time}</td>
            `;
            tbody.appendChild(tr);
        }
    });
}

function renderSalesHistoryCards() {
    // Calculate number of products sold
    let productsSold = salesHistoryData.reduce((sum, row) => sum + row.quantity, 0);
    // Calculate total sales
    let totalSales = salesHistoryData.reduce((sum, row) => sum + row.totalPrice, 0);
    // Calculate number of transactions
    let transactions = salesHistoryData.length;
    document.getElementById('card-products-sold').textContent = productsSold;
    document.getElementById('card-total-sales').textContent = '₱ ' + totalSales.toLocaleString();
    document.getElementById('card-transactions').textContent = transactions;
}

document.addEventListener('DOMContentLoaded', function() {
    // Sales history tab: time filter
    document.querySelectorAll('.sales-time-filter button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sales-time-filter button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            salesTimeFilter = this.getAttribute('data-time');
            renderSalesHistoryTable();
        });
    });
    // Sales history tab: price filter
    document.querySelectorAll('.sales-price-filter button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sales-price-filter button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            salesPriceFilter = this.getAttribute('data-price');
            renderSalesHistoryTable();
        });
    });
    // Sales history tab: search
    const searchInput = document.getElementById('sales-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            salesSearchQuery = searchInput.value.toLowerCase();
            renderSalesHistoryTable();
        });
    }
    // Sales history tab: clear search
    const clearBtn = document.getElementById('clear-sales-search');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            salesSearchQuery = '';
            if (searchInput) searchInput.value = '';
            renderSalesHistoryTable();
        });
    }
    // Dropdown event listeners for Sales History tab
    const timeDropdown = document.getElementById('sales-history-time-dropdown');
    const priceDropdown = document.getElementById('sales-history-price-dropdown');
    if (timeDropdown) {
        timeDropdown.addEventListener('change', function() {
            salesTimeFilter = this.value;
            renderSalesHistoryTable();
        });
    }
    if (priceDropdown) {
        priceDropdown.addEventListener('change', function() {
            salesPriceFilter = this.value;
            renderSalesHistoryTable();
        });
    }
    // Initial render
    renderSalesHistoryTable();
    renderSalesHistoryCards();
});