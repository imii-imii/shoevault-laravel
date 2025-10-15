document.addEventListener('DOMContentLoaded', function() {
    // Robustly parse currency-like strings to number (supports commas, multiple dots)
    function parseCurrencyToNumber(val) {
        if (typeof val === 'number') return val;
        if (!val) return 0;
        let s = String(val).replace(/[^0-9.,]/g, ''); // keep digits, comma, dot
        // remove commas (treat as thousands)
        s = s.replace(/,/g, '');
        // if multiple dots, remove all except the last one (treat earlier as thousands)
        const parts = s.split('.');
        if (parts.length > 2) {
            const last = parts.pop();
            s = parts.join('') + '.' + last;
        }
        const n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }
    // ================= LARAVEL-POWERED CATEGORY FILTERING =================
    const categoryButtons = document.querySelectorAll('.res-portal-category-btn');
    const productsGrid = document.getElementById('products');
    
    const priceMinEl = document.getElementById('priceMin');
    const priceMaxEl = document.getElementById('priceMax');
    const priceApplyBtn = document.getElementById('priceApply');
    const priceToggleBtn = document.querySelector('.price-toggle-btn');
    const pricePanel = document.getElementById('pricePanel');

    function buildFilterUrl(category) {
        const params = new URLSearchParams();
        if (category) params.set('category', category);
        const min = priceMinEl && priceMinEl.value ? parseInt(priceMinEl.value, 10) : '';
        const max = priceMaxEl && priceMaxEl.value ? parseInt(priceMaxEl.value, 10) : '';
        if (!isNaN(min) && min !== '') params.set('minPrice', min);
        if (!isNaN(max) && max !== '') params.set('maxPrice', max);
        return `/api/products/filter?${params.toString()}`;
    }

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
            fetch(buildFilterUrl(category), {
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

    if (priceApplyBtn) {
        const triggerFetch = () => {
            const activeBtn = document.querySelector('.res-portal-category-btn.active');
            const category = activeBtn ? activeBtn.dataset.category : 'All';
            productsGrid.innerHTML = '<div class="loading-spinner">Loading products...</div>';
            fetch(buildFilterUrl(category), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
            .then(r => r.text())
            .then(html => { productsGrid.innerHTML = html; attachProductCardListeners(); })
            .catch(() => { productsGrid.innerHTML = '<div class="error-message">Error loading products. Please try again.</div>'; });
        };
        priceApplyBtn.addEventListener('click', triggerFetch);
        [priceMinEl, priceMaxEl].forEach(el => el && el.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); triggerFetch(); }}));
    }

    // Mobile: slide-out price panel toggle
    if (priceToggleBtn && pricePanel) {
        const filterBar = document.querySelector('.res-portal-filter-bar');

        function alignPanelLeftOfButton() {
            const barRect = filterBar ? filterBar.getBoundingClientRect() : null;
            const btnRect = priceToggleBtn ? priceToggleBtn.getBoundingClientRect() : null;
            if (!barRect || !btnRect) return;
            const panelWidth = Math.min(420, Math.round(window.innerWidth * 0.92));
            // Position panel so its right edge touches the button's left edge
            let left = (btnRect.left - barRect.left) - panelWidth - 8; // 8px gutter
            const minLeft = 8;
            const maxLeft = Math.max(minLeft, (barRect.width - panelWidth) - 8);
            left = Math.min(Math.max(left, minLeft), maxLeft);
            const top = (btnRect.top - barRect.top) + (btnRect.height / 2) - (pricePanel.offsetHeight / 2);
            pricePanel.style.left = `${left}px`;
            pricePanel.style.top = `${Math.max(0, top)}px`;
        }

        const closePanel = () => {
            pricePanel.classList.remove('open');
            priceToggleBtn.setAttribute('aria-expanded', 'false');
        };
        const openPanel = () => {
            // Ensure size known before aligning
            pricePanel.style.opacity = '0';
            pricePanel.style.pointerEvents = 'none';
            pricePanel.classList.add('open');
            // Wait a frame to measure height accurately
            requestAnimationFrame(() => {
                alignPanelLeftOfButton();
                pricePanel.style.opacity = '';
                pricePanel.style.pointerEvents = '';
            });
            priceToggleBtn.setAttribute('aria-expanded', 'true');
        };
        priceToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (pricePanel.classList.contains('open')) closePanel(); else openPanel();
        });
        document.addEventListener('click', (e) => {
            if (!pricePanel.contains(e.target) && !priceToggleBtn.contains(e.target)) {
                closePanel();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closePanel();
        });
        window.addEventListener('resize', () => { if (pricePanel.classList.contains('open')) alignPanelLeftOfButton(); });
        window.addEventListener('scroll', () => { if (pricePanel.classList.contains('open')) alignPanelLeftOfButton(); }, true);
    }

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
    if (modalOverlay) modalOverlay.addEventListener('click', closeModal);
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
    if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);

    // ================= ENHANCED ADD TO CART WITH LARAVEL DATA =================
    if (modalAddToCartBtn) modalAddToCartBtn.addEventListener('click', function() {
        const selectedSize = document.querySelector('input[name="size"]:checked');
        
        if (!selectedSize) {
            alert('Please select a size');
            return;
        }
        
        const productData = JSON.parse(this.dataset.productData);
        const selectedSizeData = productData.sizes.find(s => s.id == selectedSize.dataset.sizeId);
        
        console.log('Product data:', productData); // Debug log
        console.log('Selected size data:', selectedSizeData); // Debug log
        
        if (!selectedSizeData || !selectedSizeData.is_available || selectedSizeData.stock <= 0) {
            alert('Selected size is not available');
            return;
        }
        
        // Calculate final price with size adjustment
        const basePrice = parseFloat(productData.price) || 0;
        const sizeAdjustment = parseFloat(selectedSizeData.price_adjustment) || 0;
        const finalPrice = basePrice + sizeAdjustment;
        
        console.log('Price calculation:', { basePrice, sizeAdjustment, finalPrice }); // Debug log
        
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
    const cartClearBtn = document.querySelector('.cart-clear-btn');
    // Mobile cart modal elements
    const cartModalOverlay = document.getElementById('cartModalOverlay');
    const cartModalItemsContainer = document.getElementById('cartModalItems');
    const cartModalTotalEl = document.getElementById('cartModalTotal');
    const cartModalCloseBtn = document.getElementById('cartModalClose');
    const cartModalCheckoutBtn = document.querySelector('.cart-modal-checkout');
    const cartModalClearBtn = document.querySelector('.cart-modal-clear');
    const isMobile = () => window.matchMedia('(max-width: 700px)').matches;
    
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
        console.log('Adding to cart:', data); // Debug log
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
        
        console.log('Cart after adding:', cart); // Debug log
        console.log('Saving to localStorage with key:', 'sv_cart'); // Debug log
        saveCart();
        renderCart();

    // Do not automatically open the cart on add-to-cart (per UX request)
    }

    function formatCurrency(n) {
        return '₱ ' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function renderCartInto(container, totalOutEl) {
        if (!container || !totalOutEl) return;
        container.innerHTML = '';
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <div class="cart-empty-icon"><i class="fas fa-bag-shopping"></i></div>
                    <div class="cart-empty-title">Your cart is empty</div>
                    <div class="cart-empty-subtitle">Add some shoes to get started!</div>
                </div>`;
            totalOutEl.textContent = '₱ 0.00';
            return;
        }
        let total = 0;
        cart.forEach(item => {
            // Use the priceNumber which already includes size adjustments
            const priceNum = (typeof item.priceNumber === 'number')
                ? item.priceNumber
                : parseCurrencyToNumber(item.price);
            total += priceNum * (item.qty || 0);
            const div = document.createElement('div');
            div.className = 'cart-item trendy';
            div.innerHTML = `
                <div class="cart-item-left">
                    <div class="cart-item-img">${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<i class=\"fas fa-shoe-prints\"></i>'}</div>
                </div>
                <div class="cart-item-center">
                    <div class="cart-item-title">${item.name}</div>
                    <div class="cart-item-meta">
                        <div class="cart-item-info-line">${item.brand} • Size ${item.size} • ${item.color}</div>
                        <div class="cart-item-actions">
                            <div class="cart-item-qty">
                                <button class="qty-btn ${item.qty <= 1 ? 'disabled' : ''}" data-key="${item.key}" data-delta="-1">−</button>
                                <span class="qty-val">${item.qty}</span>
                                <button class="qty-btn ${item.qty >= item.maxStock ? 'disabled' : ''}" data-key="${item.key}" data-delta="1">+</button>
                            </div>
                            <button class="cart-remove-btn" data-remove="${item.key}" title="Remove"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="cart-item-price">${item.price}</div>
                </div>`;
            container.appendChild(div);
        });
        totalOutEl.textContent = formatCurrency(total);
        attachCartItemListenersFor(container);
    }

    function renderCart() {
        // desktop dropdown
        if (cartItemsContainer && cartTotalEl) {
            renderCartInto(cartItemsContainer, cartTotalEl);
        }
        // mobile modal
        if (cartModalItemsContainer && cartModalTotalEl) {
            renderCartInto(cartModalItemsContainer, cartModalTotalEl);
        }
        // enable/disable checkout btns
    const hasItems = cart.length > 0;
    if (checkoutBtn) checkoutBtn.disabled = !hasItems;
    if (cartModalCheckoutBtn) cartModalCheckoutBtn.disabled = !hasItems;
    if (cartClearBtn) cartClearBtn.disabled = !hasItems;
    if (cartModalClearBtn) cartModalClearBtn.disabled = !hasItems;
        // badge count
        const count = cart.reduce((s,i)=> s + i.qty, 0);
        updateCartBadge(count);
    }

    function attachCartItemListenersFor(container) {
        container.querySelectorAll('.qty-btn').forEach(btn => {
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
        container.querySelectorAll('.cart-remove-btn').forEach(btn => {
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
        if (isMobile() || cartSticky) return;
        clearTimeout(hoverTimeout);
        openCart();
    });

    cartBtn.addEventListener('mouseleave', () => {
        if (isMobile() || cartSticky) return;
        hoverTimeout = setTimeout(() => closeCart(), 250);
    });

    cartDropdown.addEventListener('mouseenter', () => { if (!isMobile()) clearTimeout(hoverTimeout); });

    cartDropdown.addEventListener('mouseleave', () => {
        if (isMobile() || cartSticky) return;
        hoverTimeout = setTimeout(() => closeCart(), 250);
    });

    cartBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (isMobile()) {
            // open cart modal on mobile
            if (cartModalOverlay) {
                document.body.classList.add('cart-modal-open');
                cartModalOverlay.style.display = 'flex';
            }
            return;
        }
        cartSticky = !cartSticky;
        if (cartSticky) {
            openCart();
            cartDropdown.classList.add('sticky');
        } else {
            cartDropdown.classList.remove('sticky');
            closeCart();
        }
    });

    // Mobile cart modal close & checkout
    if (cartModalCloseBtn) {
        cartModalCloseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (cartModalOverlay) { cartModalOverlay.style.display = 'none'; document.body.classList.remove('cart-modal-open'); }
        });
    }
    if (cartModalOverlay) {
        cartModalOverlay.addEventListener('click', (e) => {
            if (e.target === cartModalOverlay) {
                cartModalOverlay.style.display = 'none';
                document.body.classList.remove('cart-modal-open');
            }
        });
    }
    if (cartModalCheckoutBtn) {
        cartModalCheckoutBtn.addEventListener('click', () => {
            window.location.href = '/form';
        });
    }

    // Mobile clear all behavior
    if (cartModalClearBtn) {
        cartModalClearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            cart = [];
            saveCart();
            renderCart();
        });
    }

    // Clear all handler replaces close button
    if (cartClearBtn) {
        cartClearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (cart.length === 0) return;
            if (!confirm('Clear all items from Reservation Cart?')) return;
            cart = [];
            saveCart();
            renderCart();
        });
    }

    // Checkout handler
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            window.location.href = '/form';
        });
    }

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