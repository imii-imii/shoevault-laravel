document.addEventListener('DOMContentLoaded', function() {
    // Handle category buttons
    const categoryButtons = document.querySelectorAll('.res-portal-category-btn');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const category = this.textContent.trim();
            filterProductsByCategory(category);
        });
    });

    // Function to filter products by category
    function filterProductsByCategory(category) {
        const productCards = document.querySelectorAll('.res-portal-product-card');
        
        productCards.forEach(card => {
            if (category === 'All') {
                card.style.display = 'block';
            } else {
                const productCategory = card.dataset.productCategory;
                if (productCategory && productCategory.toLowerCase() === category.toLowerCase()) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }

    // Modal functionality
    const productModal = document.getElementById('productModal');
    const modalOverlay = document.querySelector('.product-modal-overlay');
    const modalCloseBtn = document.querySelector('.modal-close-btn');
    const modalCancelBtn = document.querySelector('.modal-cancel');
    const addToCartBtns = document.querySelectorAll('.res-portal-add-cart-btn');
    const modalAddToCartBtn = document.querySelector('.modal-add-to-cart');

    // Function to open modal
    function openModal(productCard) {
        const title = productCard.querySelector('.res-portal-product-title').textContent;
        const brand = productCard.querySelector('.res-portal-product-brand').textContent;
        const price = productCard.querySelector('.res-portal-product-price').textContent;
        const stock = productCard.querySelector('.res-portal-product-stock').textContent;
        const imgDiv = productCard.querySelector('.res-portal-product-img');
        const imgEl = imgDiv.querySelector('img');
        const imgUrl = imgEl ? imgEl.src : (imgDiv.style.backgroundImage?.replace(/url\(['"](.+)['"]\)/, '$1') || '');

        // Get product data attributes
        const productId = productCard.dataset.productId;
        const productCategory = productCard.dataset.productCategory;
        const productColor = productCard.dataset.productBrand; // We'll get color from backend data

        document.getElementById('modalProductName').textContent = title;
        document.getElementById('modalProductBrand').textContent = brand;
        document.getElementById('modalProductPrice').textContent = price;
        document.getElementById('modalProductStock').textContent = stock;

        const modalImage = document.getElementById('modalProductImage');
        const placeholder = document.querySelector('.product-placeholder');

        if (imgUrl) {
            modalImage.src = imgUrl;
            modalImage.style.display = 'block';
            placeholder.style.display = 'none';
        } else {
            modalImage.style.display = 'none';
            placeholder.style.display = 'flex';
        }

        // Populate dynamic sizes if window.productsData exists
        if (window.productsData && productId) {
            const productData = window.productsData.find(p => p.id == productId);
            if (productData) {
                populateModalSizes(productData.sizes);
                populateModalColor(productData.color);
            }
        }

        productModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    // Function to populate modal sizes dynamically
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
                input.dataset.stock = sizeData.stock;
                input.dataset.priceAdjustment = sizeData.price_adjustment || 0;
                
                const span = document.createElement('span');
                span.className = 'size-option';
                span.textContent = sizeData.size;
                
                // Disable if out of stock
                if (sizeData.stock <= 0) {
                    input.disabled = true;
                    label.classList.add('out-of-stock');
                    span.textContent += ' (Out)';
                }
                
                label.appendChild(input);
                label.appendChild(span);
                sizeOptionsContainer.appendChild(label);
            });
        } else {
            // Fallback to default sizes if no dynamic data
            const defaultSizes = ['35', '36', '37', '38', '39', '40'];
            defaultSizes.forEach(size => {
                const label = document.createElement('label');
                label.className = 'size-option-label';
                label.innerHTML = `
                    <input type="radio" name="size" value="${size}">
                    <span class="size-option">${size}</span>
                `;
                sizeOptionsContainer.appendChild(label);
            });
        }
    }

    // Function to populate modal color
    function populateModalColor(color) {
        const colorDisplayContainer = document.getElementById('modalColorDisplay');
        if (color) {
            colorDisplayContainer.textContent = color;
        } else {
            colorDisplayContainer.textContent = 'Various Colors Available';
        }
    }

    // Function to close modal
    function closeModal() {
        productModal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Add click event listeners for all "Add to Cart" buttons
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productCard = this.closest('.res-portal-product-card');
            openModal(productCard);
        });
    });

    // Close modal when clicking overlay
    modalOverlay.addEventListener('click', closeModal);

    // Close modal when clicking close button
    modalCloseBtn.addEventListener('click', closeModal);

    // Close modal when clicking cancel button
    modalCancelBtn.addEventListener('click', closeModal);

    // Handle size selection
    const sizeOptions = document.querySelectorAll('.size-option-label');
    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            sizeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Handle color selection
    const colorOptions = document.querySelectorAll('.color-option-label');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Handle modal Add to Cart button
    modalAddToCartBtn.addEventListener('click', function() {
        const selectedSize = document.querySelector('input[name="size"]:checked');
        const colorDisplay = document.getElementById('modalColorDisplay');

        if (!selectedSize) {
            alert('Please select a size');
            return;
        }
        
        // Get color from display instead of radio button
        const color = colorDisplay.textContent.trim() || 'Default';
        
        const name = document.getElementById('modalProductName').textContent.trim();
        const brand = document.getElementById('modalProductBrand').textContent.trim();
        const priceDisplay = document.getElementById('modalProductPrice').textContent.trim();
        const stockText = document.getElementById('modalProductStock').textContent.trim();
        
        // Get image
        const imageEl = document.getElementById('modalProductImage');
        const image = imageEl.style.display === 'block' ? imageEl.src : null;
        
        // Extract numeric price
        const priceNumber = parseFloat(priceDisplay.replace(/[^\d.-]/g, ''));
        
        // Calculate final price with size adjustment if any
        const sizeAdjustment = parseFloat(selectedSize.dataset.priceAdjustment || 0);
        const finalPrice = priceNumber + sizeAdjustment;
        
        addToCart({
            name: name,
            brand: brand,
            size: selectedSize.value,
            color: color,
            price: `₱ ${finalPrice.toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`,
            priceNumber: finalPrice,
            image: image
        });
        
        closeModal();
    });

    // Handle navigation buttons
    const navButtons = document.querySelectorAll('.res-portal-nav-btn');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Get the corresponding section ID
            const sectionId = this.classList.contains('nav-home') ? 'slider' :
                            this.classList.contains('nav-services') ? 'services' :
                            this.classList.contains('nav-testimonials') ? 'testimonials' :
                            this.classList.contains('nav-about') ? 'about-us' :
                            'contact';
            
            // Get the section element
            const section = document.getElementById(sectionId);
            if (section) {
                // Smooth scroll to the section
                section.scrollIntoView({ behavior: 'smooth' });
            } else {
                // If section not found on current page, redirect to home page with anchor
                const redirectUrl = `/#${sectionId}`;
                localStorage.setItem('scrollTarget', sectionId);
                window.location.href = redirectUrl;
            }
        });
    });

    // Cart dropdown interactions
    const cartBtn = document.querySelector('.res-portal-cart-btn');
    const cartDropdown = document.getElementById('cartDropdown');
    const cartCloseBtn = document.querySelector('.cart-close-btn');
    let cartSticky = false;
    let hoverTimeout;

    function openCart(temp=false){
        cartDropdown.classList.add('open');
        if(cartSticky){ cartDropdown.classList.add('sticky'); }
        else { cartDropdown.classList.remove('sticky'); }
    }
    function closeCart(){
        if(!cartSticky){ cartDropdown.classList.remove('open'); }
    }

    cartBtn.addEventListener('mouseenter', ()=>{
        if(cartSticky) return; // if sticky keep as is
        clearTimeout(hoverTimeout);
        openCart(true);
    });
    cartBtn.addEventListener('mouseleave', ()=>{
        if(cartSticky) return;
        hoverTimeout = setTimeout(()=>closeCart(), 250);
    });

    cartDropdown.addEventListener('mouseenter', ()=>{
        clearTimeout(hoverTimeout);
    });
    cartDropdown.addEventListener('mouseleave', ()=>{
        if(cartSticky) return;
        hoverTimeout = setTimeout(()=>closeCart(), 250);
    });

    cartBtn.addEventListener('click', (e)=>{
        e.stopPropagation();
        cartSticky = !cartSticky;
        if(cartSticky){
            openCart();
            cartDropdown.classList.add('sticky');
        } else {
            cartDropdown.classList.remove('sticky');
            closeCart();
        }
    });

    cartCloseBtn.addEventListener('click', (e)=>{
        e.stopPropagation();
        cartSticky = false;
        cartDropdown.classList.remove('sticky');
        closeCart();
    });

    document.addEventListener('click', (e)=>{
        if(cartSticky) return; // don't auto-close when sticky
        if(!cartDropdown.contains(e.target) && e.target !== cartBtn){
            closeCart();
        }
    });

    // ================= CART DATA & RENDERING =================
    const cartItemsContainer = document.getElementById('cartItems');
    const cartTotalEl = document.getElementById('cartTotal');
    const checkoutBtn = document.querySelector('.cart-checkout-btn');

    // Simple in-memory + localStorage sync cart (Laravel compatible structure)
    // Each item: { id, name, brand, price, priceNumber, stock, size, color, image, qty }
    let cart = [];

    function loadCart(){
        try {
            const raw = localStorage.getItem('sv_cart');
            if(raw){ cart = JSON.parse(raw); }
        } catch(e){ cart = []; }
        renderCart();
    }

    function saveCart(){
        localStorage.setItem('sv_cart', JSON.stringify(cart));
    }

    function parsePrice(p){
        // expects format like "₱ 5,995" – strip non-digits
        const num = parseFloat(p.replace(/[^0-9.]/g,'').replace(/,(?=\d{3})/g,''));
        return isNaN(num)?0:num;
    }

    function addToCart(data){
        // Create a composite key (id + size + color). If no id, derive from name.
        const baseId = data.id || data.name.toLowerCase().replace(/[^a-z0-9]+/g,'-');
        const key = `${baseId}__${data.size}__${data.color}`;
        let existing = cart.find(i=>i.key === key);
        if(existing){
            existing.qty += 1;
        } else {
            cart.push({
                key,
                id: baseId,
                name: data.name,
                brand: data.brand,
                price: data.priceDisplay,
                priceNumber: data.priceNumber,
                stock: data.stock,
                size: data.size,
                color: data.color,
                image: data.image || null,
                qty: 1
            });
        }
        saveCart();
        renderCart();
        // Auto open cart & make sticky on first add
        cartSticky = true;
        openCart();
        cartDropdown.classList.add('sticky');
    }

    function formatCurrency(n){
        return '₱ ' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function renderCart(){
        cartItemsContainer.innerHTML = '';
        if(cart.length === 0){
            cartItemsContainer.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
            cartTotalEl.textContent = '₱ 0.00';
            checkoutBtn.disabled = true;
            return;
        }
        checkoutBtn.disabled = false;
        let total = 0;
        cart.forEach(item => {
            total += item.priceNumber * item.qty;
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-img">${ item.image ? `<img src="${item.image}" alt="${item.name}">` : '<span>No Img</span>' }</div>
                <div class="cart-item-info">
                    <div class="cart-item-title">${item.name}</div>
                    <div class="cart-item-meta">${item.brand} • Size ${item.size} • ${item.color}</div>
                    <div class="cart-item-price">${item.price}</div>
                    <div class="cart-item-meta">Qty: 
                        <button class="qty-btn" data-key="${item.key}" data-delta="-1">−</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" data-key="${item.key}" data-delta="1">+</button>
                        <button class="cart-remove-btn" data-remove="${item.key}">Remove</button>
                    </div>
                </div>
            `;
            cartItemsContainer.appendChild(div);
        });
        cartTotalEl.textContent = formatCurrency(total);
        // Delegate quantity & remove
        cartItemsContainer.querySelectorAll('.qty-btn').forEach(btn=>{
            btn.addEventListener('click', (e)=>{
                const key = btn.getAttribute('data-key');
                const delta = parseInt(btn.getAttribute('data-delta'),10);
                const it = cart.find(i=>i.key===key);
                if(!it) return;
                it.qty += delta;
                if(it.qty <= 0){ cart = cart.filter(i=>i.key!==key); }
                saveCart();
                renderCart();
            });
        });
        cartItemsContainer.querySelectorAll('.cart-remove-btn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const key = btn.getAttribute('data-remove');
                cart = cart.filter(i=>i.key!==key);
                saveCart();
                renderCart();
            });
        });
    }

    // Override modal add to cart handler to use new logic
    modalAddToCartBtn.addEventListener('click', function() {
        const selectedSize = document.querySelector('input[name="size"]:checked');
        const selectedColor = document.querySelector('input[name="color"]:checked');

        if (!selectedSize) { alert('Please select a size'); return; }
        if (!selectedColor) { alert('Please select a color'); return; }

        const name = document.getElementById('modalProductName').textContent.trim();
        const brand = document.getElementById('modalProductBrand').textContent.trim();
        const priceDisplay = document.getElementById('modalProductPrice').textContent.trim();
        const stockText = document.getElementById('modalProductStock').textContent.trim();
        const stockMatch = stockText.match(/(\d+)/);
        const stock = stockMatch ? parseInt(stockMatch[1],10) : null;
        const imageEl = document.getElementById('modalProductImage');
        const image = imageEl && imageEl.style.display !== 'none' ? imageEl.src : null;

        addToCart({
            name,
            brand,
            priceDisplay,
            priceNumber: parsePrice(priceDisplay),
            stock,
            size: selectedSize.value,
            color: selectedColor.value,
            image
        });

        closeModal();
    }, { once: false });

    // Simulated checkout placeholder (Laravel compatibility idea)
        checkoutBtn.addEventListener('click', async () => {
            await handleCheckoutClick();
        });

    // Mobile cart modal elements
    const cartModalOverlay = document.getElementById('cartModalOverlay');
    const cartModalItems = document.getElementById('cartModalItems');
    const cartModalTotal = document.getElementById('cartModalTotal');
    const cartModalClose = document.getElementById('cartModalClose');
    const cartModalCheckout = document.querySelector('.cart-modal-checkout');
    // Redirect reserve button in mobile cart modal as well
    if(cartModalCheckout){
        cartModalCheckout.addEventListener('click', async () => {
            await handleCheckoutClick();
        });
    }

    function isMobile(){ return window.matchMedia('(max-width:700px)').matches; }

    function openCartUI(){
        if(isMobile()){
            cartModalOverlay.style.display='flex';
            document.body.classList.add('cart-modal-open');
        } else {
            openCart();
        }
    }
    function closeCartUI(nonSticky=true){
        if(isMobile()){
            cartModalOverlay.style.display='none';
            document.body.classList.remove('cart-modal-open');
        } else {
            if(nonSticky) closeCart();
        }
    }

    cartModalClose?.addEventListener('click', ()=>{ cartSticky=false; closeCartUI(); });
    cartModalOverlay?.addEventListener('click', (e)=>{ if(e.target === cartModalOverlay){ cartSticky=false; closeCartUI(); }});

    // Override existing cartBtn events for mobile
    cartBtn.addEventListener('click', (e)=>{
        if(isMobile()){
            e.stopPropagation();
            cartSticky = true; // treat modal as persistent
            openCartUI();
        }
    });

    // Sync render to both dropdown & modal
    const originalRenderCart = renderCart;
    renderCart = function(){
        originalRenderCart();
        // replicate content to modal if mobile
        if(cartModalItems){
            if(cart.length === 0){
                cartModalItems.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
                cartModalTotal.textContent='₱ 0.00';
                cartModalCheckout.disabled = true;
            } else {
                cartModalItems.innerHTML='';
                let t=0;
                cart.forEach(item=>{
                    t += item.priceNumber * item.qty;
                    const div=document.createElement('div');
                    div.className='cart-item';
                    div.innerHTML = `
                        <div class="cart-item-img">${ item.image ? `<img src="${item.image}" alt="${item.name}">` : '<span>No Img</span>' }</div>
                        <div class="cart-item-info">
                          <div class="cart-item-title">${item.name}</div>
                          <div class="cart-item-meta">${item.brand} • Size ${item.size} • ${item.color}</div>
                          <div class="cart-item-price">${item.price}</div>
                          <div class="cart-item-meta">Qty: 
                            <button class="qty-btn" data-key="${item.key}" data-delta="-1">−</button>
                            <span class="qty-val">${item.qty}</span>
                            <button class="qty-btn" data-key="${item.key}" data-delta="1">+</button>
                            <button class="cart-remove-btn" data-remove="${item.key}">Remove</button>
                          </div>
                        </div>`;
                    cartModalItems.appendChild(div);
                });
                cartModalTotal.textContent = formatCurrency(t);
                cartModalCheckout.disabled = false;
                // attach events
                cartModalItems.querySelectorAll('.qty-btn').forEach(btn=>{
                    btn.addEventListener('click', ()=>{
                        const key=btn.getAttribute('data-key');
                        const delta=parseInt(btn.getAttribute('data-delta'),10);
                        const it=cart.find(i=>i.key===key); if(!it) return;
                        it.qty += delta; if(it.qty<=0) cart = cart.filter(i=>i.key!==key);
                        saveCart(); renderCart();
                    });
                });
                cartModalItems.querySelectorAll('.cart-remove-btn').forEach(btn=>{
                    btn.addEventListener('click', ()=>{ const key=btn.getAttribute('data-remove'); cart = cart.filter(i=>i.key!==key); saveCart(); renderCart(); });
                });
            }
        }
    }

    // Handle checkout button click - check for pending reservations first
    async function handleCheckoutClick() {
        // Check if user is logged in
        if (!window.customerData || !window.customerData.email) {
            alert('Please log in to make a reservation.');
            window.location.href = '/customer/login';
            return;
        }

        try {
            // Show loading state
            const originalText = checkoutBtn ? checkoutBtn.textContent : cartModalCheckout.textContent;
            if (checkoutBtn) checkoutBtn.textContent = 'Checking...';
            if (cartModalCheckout) cartModalCheckout.textContent = 'Checking...';
            
            // Check for pending reservations
            const response = await fetch('/api/check-pending-reservations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            const data = await response.json();

            // Restore button text
            if (checkoutBtn) checkoutBtn.textContent = originalText;
            if (cartModalCheckout) cartModalCheckout.textContent = originalText;

            if (response.status === 401) {
                // Not authenticated
                alert('Please log in to make a reservation.');
                window.location.href = '/customer/login';
                return;
            }

            if (data.success && !data.hasPending) {
                // No pending reservations, proceed to form
                window.location.href = '/form';
            } else if (data.hasPending) {
                // Has pending reservations, show error
                const pendingIds = data.pendingReservations?.join(', ') || 'Unknown';
                alert(`You already have pending reservation(s): ${pendingIds}\n\nPlease wait for your current reservation(s) to be completed or cancelled before making a new one.`);
            } else {
                // Error occurred
                alert(data.message || 'An error occurred while checking for pending reservations. Please try again.');
            }

        } catch (error) {
            console.error('Error checking pending reservations:', error);
            
            // Restore button text
            if (checkoutBtn) checkoutBtn.textContent = 'Reserve';
            if (cartModalCheckout) cartModalCheckout.textContent = 'Reserve';
            
            alert('Network error. Please check your connection and try again.');
        }
    }

    loadCart();
});