document.addEventListener('DOMContentLoaded', function() {
    // ================= LARAVEL-POWERED CATEGORY FILTERING =================
    const categoryButtons = document.querySelectorAll('.res-portal-category-btn');
    const productsGrid = document.getElementById('products');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Get category from data attribute
            const category = this.dataset.category;
            
            // Show loading state
            productsGrid.innerHTML = '<div class="loading-spinner">Loading products...</div>';
            
            // Fetch filtered products from Laravel
            fetch(`/api/products/filter?category=${encodeURIComponent(category)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(html => {
                productsGrid.innerHTML = html;
                // Re-attach event listeners for new product cards
                attachProductCardListeners();
            })
            .catch(error => {
                console.error('Error filtering products:', error);
                productsGrid.innerHTML = '<div class="error-message">Error loading products. Please try again.</div>';
            });
        });
    });

    // ================= LARAVEL-POWERED MODAL FUNCTIONALITY =================
    const productModal = document.getElementById('productModal');
    const modalOverlay = document.querySelector('.product-modal-overlay');
    const modalCloseBtn = document.querySelector('.modal-close-btn');
    const modalCancelBtn = document.querySelector('.modal-cancel');
    const modalAddToCartBtn = document.querySelector('.modal-add-to-cart');

    function attachProductCardListeners() {
        const addToCartBtns = document.querySelectorAll('.res-portal-add-cart-btn');
        addToCartBtns.forEach(btn => {
            btn.removeEventListener('click', handleAddToCartClick); // Remove existing listener
            btn.addEventListener('click', handleAddToCartClick);
        });
    }

    function handleAddToCartClick(e) {
        e.preventDefault();
        const productCard = this.closest('.res-portal-product-card');
        const productId = productCard.dataset.productId;
        
        // Show loading in modal
        openModalWithLoading();
        
        // Fetch product details from Laravel
        fetch(`/api/products/${productId}/details`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(productData => {
            populateModal(productData);
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            closeModal();
            alert('Error loading product details. Please try again.');
        });
    }

    function openModalWithLoading() {
        // Set loading state
        document.getElementById('modalProductName').textContent = 'Loading...';
        document.getElementById('modalProductBrand').textContent = '';
        document.getElementById('modalProductPrice').textContent = '';
        document.getElementById('modalProductStock').textContent = '';
        document.getElementById('modalSizeOptions').innerHTML = '<div class="loading">Loading sizes...</div>';
        document.getElementById('modalColorDisplay').textContent = 'Loading...';
        
        productModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function populateModal(productData) {
        // Populate basic info
        document.getElementById('modalProductName').textContent = productData.name;
        document.getElementById('modalProductBrand').textContent = productData.brand;
        document.getElementById('modalProductPrice').textContent = productData.formatted_price;
        document.getElementById('modalProductStock').textContent = `Stocks: ${productData.total_stock}`;
        document.getElementById('modalColorDisplay').textContent = productData.color || 'Various Colors Available';

        // Handle image
        const modalImage = document.getElementById('modalProductImage');
        const placeholder = document.querySelector('.product-placeholder');
        
        if (productData.image_url) {
            modalImage.src = productData.image_url;
            modalImage.style.display = 'block';
            placeholder.style.display = 'none';
        } else {
            modalImage.style.display = 'none';
            placeholder.style.display = 'flex';
        }

        // Populate sizes dynamically
        populateModalSizes(productData.sizes);
        
        // Store product data for cart
        modalAddToCartBtn.dataset.productData = JSON.stringify(productData);
    }

    function populateModalSizes(sizes) {
        const sizeOptionsContainer = document.getElementById('modalSizeOptions');
        sizeOptionsContainer.innerHTML = '';

        if (sizes && sizes.length > 0) {
            sizes.forEach(sizeData => {
                const label = document.createElement('label');
                label.className = 'size-option-label';
                
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'size';
                input.value = sizeData.size;
                input.dataset.sizeId = sizeData.id;
                input.dataset.stock = sizeData.stock;
                input.dataset.priceAdjustment = sizeData.price_adjustment || 0;
                
                const span = document.createElement('span');
                span.className = 'size-option';
                span.textContent = sizeData.size;
                
                // Disable if out of stock
                if (!sizeData.is_available || sizeData.stock <= 0) {
                    input.disabled = true;
                    label.classList.add('out-of-stock');
                    span.textContent += ' (Out)';
                }
                
                label.appendChild(input);
                label.appendChild(span);
                sizeOptionsContainer.appendChild(label);

                // Add click handler for size selection
                label.addEventListener('click', function() {
                    if (!input.disabled) {
                        document.querySelectorAll('.size-option-label').forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                    }
                });
            });
        } else {
            sizeOptionsContainer.innerHTML = '<div class="no-sizes">No sizes available</div>';
        }
    }

    function closeModal() {
        productModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Modal close handlers
    modalOverlay.addEventListener('click', closeModal);
    modalCloseBtn.addEventListener('click', closeModal);
    modalCancelBtn.addEventListener('click', closeModal);

    // ================= ENHANCED ADD TO CART WITH LARAVEL DATA =================
    modalAddToCartBtn.addEventListener('click', function() {
        const selectedSize = document.querySelector('input[name="size"]:checked');
        
        if (!selectedSize) {
            alert('Please select a size');
            return;
        }
        
        const productData = JSON.parse(this.dataset.productData);
        const selectedSizeData = productData.sizes.find(s => s.id == selectedSize.dataset.sizeId);
        
        if (!selectedSizeData || !selectedSizeData.is_available || selectedSizeData.stock <= 0) {
            alert('Selected size is not available');
            return;
        }
        
        // Calculate final price with size adjustment
        const finalPrice = productData.price + (selectedSizeData.price_adjustment || 0);
        
        addToCart({
            id: productData.id,
            name: productData.name,
            brand: productData.brand,
            size: selectedSizeData.size,
            sizeId: selectedSizeData.id,
            color: productData.color || 'Default',
            price: `₱ ${finalPrice.toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`,
            priceNumber: finalPrice,
            image: productData.image_url,
            maxStock: selectedSizeData.stock
        });
        
        closeModal();
    });

    // ================= CART FUNCTIONALITY (PRESERVED FROM ORIGINAL) =================
    const cartItemsContainer = document.getElementById('cartItems');
    const cartTotalEl = document.getElementById('cartTotal');
    const checkoutBtn = document.querySelector('.cart-checkout-btn');
    const cartBtn = document.querySelector('.res-portal-cart-btn');
    const cartDropdown = document.getElementById('cartDropdown');
    const cartCloseBtn = document.querySelector('.cart-close-btn');
    
    let cart = [];
    let cartSticky = false;
    let hoverTimeout;

    function loadCart() {
        try {
            const raw = localStorage.getItem('sv_cart');
            if (raw) { cart = JSON.parse(raw); }
        } catch(e) { cart = []; }
        renderCart();
    }

    function saveCart() {
        localStorage.setItem('sv_cart', JSON.stringify(cart));
    }

    function addToCart(data) {
        const key = `${data.id}__${data.sizeId}__${data.color}`;
        let existing = cart.find(i => i.key === key);
        
        if (existing) {
            if (existing.qty < data.maxStock) {
                existing.qty += 1;
            } else {
                alert(`Only ${data.maxStock} items available in stock`);
                return;
            }
        } else {
            cart.push({
                key,
                id: data.id,
                name: data.name,
                brand: data.brand,
                price: data.price,
                priceNumber: data.priceNumber,
                size: data.size,
                sizeId: data.sizeId,
                color: data.color,
                image: data.image || null,
                qty: 1,
                maxStock: data.maxStock
            });
        }
        
        saveCart();
        renderCart();
        
        // Auto open cart & make sticky on first add
        cartSticky = true;
        openCart();
        cartDropdown.classList.add('sticky');
    }

    function formatCurrency(n) {
        return '₱ ' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function renderCart() {
        cartItemsContainer.innerHTML = '';
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
            cartTotalEl.textContent = '₱ 0.00';
            checkoutBtn.disabled = true;
            updateCartBadge(0);
            return;
        }
        
        checkoutBtn.disabled = false;
        let total = 0;
        let totalItems = 0;
        
        cart.forEach(item => {
            total += item.priceNumber * item.qty;
            totalItems += item.qty;
            
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-img">${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<span>No Img</span>'}</div>
                <div class="cart-item-info">
                    <div class="cart-item-title">${item.name}</div>
                    <div class="cart-item-meta">${item.brand} • Size ${item.size} • ${item.color}</div>
                    <div class="cart-item-price">${item.price}</div>
                    <div class="cart-item-meta">Qty: 
                        <button class="qty-btn ${item.qty <= 1 ? 'disabled' : ''}" data-key="${item.key}" data-delta="-1">−</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn ${item.qty >= item.maxStock ? 'disabled' : ''}" data-key="${item.key}" data-delta="1">+</button>
                        <button class="cart-remove-btn" data-remove="${item.key}">Remove</button>
                    </div>
                </div>
            `;
            cartItemsContainer.appendChild(div);
        });
        
        cartTotalEl.textContent = formatCurrency(total);
        updateCartBadge(totalItems);
        
        // Attach event listeners for quantity and remove buttons
        attachCartItemListeners();
    }

    function attachCartItemListeners() {
        cartItemsContainer.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (btn.classList.contains('disabled')) return;
                
                const key = btn.getAttribute('data-key');
                const delta = parseInt(btn.getAttribute('data-delta'), 10);
                const item = cart.find(i => i.key === key);
                
                if (!item) return;
                
                const newQty = item.qty + delta;
                if (newQty <= 0) {
                    cart = cart.filter(i => i.key !== key);
                } else if (newQty <= item.maxStock) {
                    item.qty = newQty;
                } else {
                    alert(`Only ${item.maxStock} items available in stock`);
                    return;
                }
                
                saveCart();
                renderCart();
            });
        });
        
        cartItemsContainer.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.getAttribute('data-remove');
                cart = cart.filter(i => i.key !== key);
                saveCart();
                renderCart();
            });
        });
    }

    function updateCartBadge(count) {
        let badge = cartBtn.querySelector('.cart-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            cartBtn.appendChild(badge);
        }
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }

    // Cart dropdown interactions
    function openCart() {
        cartDropdown.classList.add('open');
        if (cartSticky) { cartDropdown.classList.add('sticky'); }
        else { cartDropdown.classList.remove('sticky'); }
    }
    
    function closeCart() {
        if (!cartSticky) { cartDropdown.classList.remove('open'); }
    }

    // Cart hover and click handlers
    cartBtn.addEventListener('mouseenter', () => {
        if (cartSticky) return;
        clearTimeout(hoverTimeout);
        openCart();
    });

    cartBtn.addEventListener('mouseleave', () => {
        if (cartSticky) return;
        hoverTimeout = setTimeout(() => closeCart(), 250);
    });

    cartDropdown.addEventListener('mouseenter', () => {
        clearTimeout(hoverTimeout);
    });

    cartDropdown.addEventListener('mouseleave', () => {
        if (cartSticky) return;
        hoverTimeout = setTimeout(() => closeCart(), 250);
    });

    cartBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        cartSticky = !cartSticky;
        if (cartSticky) {
            openCart();
            cartDropdown.classList.add('sticky');
        } else {
            cartDropdown.classList.remove('sticky');
            closeCart();
        }
    });

    cartCloseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        cartSticky = false;
        cartDropdown.classList.remove('sticky');
        closeCart();
    });

    // Checkout handler
    checkoutBtn.addEventListener('click', () => {
        window.location.href = '/form';
    });

    // Navigation handlers (preserved from original)
    const navButtons = document.querySelectorAll('.res-portal-nav-btn');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const sectionId = this.classList.contains('nav-home') ? 'slider' :
                            this.classList.contains('nav-services') ? 'services' :
                            this.classList.contains('nav-testimonials') ? 'testimonials' :
                            this.classList.contains('nav-about') ? 'about-us' :
                            'contact';
            
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            } else {
                const redirectUrl = `/#${sectionId}`;
                localStorage.setItem('scrollTarget', sectionId);
                window.location.href = redirectUrl;
            }
        });
    });

    // Initialize
    attachProductCardListeners();
    loadCart();
});