<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShoeVault Reservation Portal</title>
  <link rel="stylesheet" href="{{ asset('css/reservation-portal.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/reservation-portal-laravel.js') }}"></script>
  <style>
    .no-products-message {
      grid-column: 1 / -1;
      text-align: center;
      padding: 3rem 2rem;
      color: #6b7280;
    }
    
    .no-products-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    
    .no-products-message h3 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      color: #374151;
    }
    
    .no-products-message p {
      font-size: 1rem;
      opacity: 0.8;
    }

    .modal-color-display {
      padding: 0.5rem 0;
      font-weight: 600;
      color: #374151;
      text-transform: capitalize;
    }
    
    .res-portal-product-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <nav class="res-portal-navbar">
    <img src="{{ asset('reservation-assets/logo.png') }}" alt="Logo" class="res-portal-logo">
    <div class="res-portal-search desktop-only">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search for shoes, brands, or models...">
    </div>
    <div class="cart-container">
      <button class="res-portal-cart-btn" title="View Cart">
        <i class="fas fa-shopping-cart"></i>
      </button>
      <div class="cart-dropdown" id="cartDropdown">
        <div class="cart-dropdown-header">
          <div class="cart-header-title">
            <i class="fas fa-shopping-cart cart-header-icon" aria-hidden="true"></i>
            <span>Reservation Cart</span>
          </div>
          <button class="cart-clear-btn" type="button" title="Clear all items">Clear all</button>
        </div>
        <div class="cart-dropdown-body" id="cartItems">
          <div class="cart-empty">
            <div class="cart-empty-icon"><i class="fas fa-bag-shopping"></i></div>
            <div class="cart-empty-title">Your cart is empty</div>
            <div class="cart-empty-subtitle">Add some shoes to get started!</div>
          </div>
        </div>
        <div class="cart-dropdown-footer">
          <div class="cart-total-row">
            <span>Total:</span>
            <span class="cart-total" id="cartTotal">₱ 0.00</span>
          </div>
          <button class="cart-checkout-btn" disabled>Reserve</button>
        </div>
      </div>
    </div>
  </nav>
  <div class="res-portal-main-mobile">
    <div class="res-portal-search mobile-only">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search for shoes, brands, or models...">
    </div>
    <div class="res-portal-banner">
      <div class="res-portal-banner-content">
        <h2 class="banner-title"><span class="badge-new">NEW</span> Reserve Your Pair Instantly</h2>
        <p class="banner-tagline">Try our faster reservation experience — lock in your size before it sells out. Tap a product or start below.</p>
        <div class="banner-cta">
          <a href="#products" class="banner-cta-btn primary">Browse Products</a>
          <a href="{{ route('reservation.size-converter') }}" class="banner-cta-btn ghost">Size Converter</a>
        </div>
      </div>
      <span class="pulse-light pl-1"></span>
      <span class="pulse-light pl-2"></span>
      <span class="pulse-light pl-3"></span>
    </div>
    <div class="res-portal-nav">
      <button class="res-portal-nav-btn nav-home">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span class="nav-label">Home</span>
      </button>
      <button class="res-portal-nav-btn nav-services">
        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
        <span class="nav-label">Services</span>
      </button>
      <button class="res-portal-nav-btn nav-testimonials">
        <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
        <span class="nav-label">Testimonials</span>
      </button>
      <button class="res-portal-nav-btn nav-about">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-label">About Us</span>
      </button>
      <button class="res-portal-nav-btn nav-contact">
        <span class="nav-icon"><i class="fas fa-envelope"></i></span>
        <span class="nav-label">Contact Us</span>
      </button>
    </div>
    <div class="res-portal-filter-bar">
      <div class="res-portal-categories">
        <button class="res-portal-category-btn {{ ($selectedCategory ?? 'All') === 'All' ? 'active' : '' }}" data-category="All">All</button>
        @php
          $staticCats = ['men' => 'Men', 'women' => 'Women', 'accessories' => 'Accessories'];
          $lowerCats = collect($categories ?? [])->map(fn($c) => strtolower($c));
        @endphp
        @foreach($staticCats as $value => $label)
          <button class="res-portal-category-btn {{ (strtolower($selectedCategory ?? '') === $value) ? 'active' : '' }}" data-category="{{ $value }}">{{ $label }}</button>
        @endforeach
        @foreach(($categories ?? []) as $category)
          @if(!in_array(strtolower($category), array_keys($staticCats)))
            <button class="res-portal-category-btn {{ ($selectedCategory === $category) ? 'active' : '' }}" data-category="{{ $category }}">{{ $category }}</button>
          @endif
        @endforeach
      </div>
      <div class="res-portal-price-compact">
        <button type="button" class="price-toggle-btn mobile-only" aria-expanded="false" aria-controls="pricePanel">
          <i class="fas fa-filter"></i>
          <span class="toggle-label">Filter</span>
        </button>
        <div class="res-portal-price-filter" aria-label="Price Range" id="pricePanel">
          <i class="fas fa-peso-sign"></i>
          <input type="number" id="priceMin" class="price-input" placeholder="Min" min="0" step="100">
          <span class="dash">—</span>
          <input type="number" id="priceMax" class="price-input" placeholder="Max" min="0" step="100">
          <button id="priceApply" class="price-apply" title="Apply price filter">
            <i class="fas fa-filter"></i>
          </button>
        </div>
      </div>
    </div>
  <div class="res-portal-products-grid" id="products">
      @include('reservation.partials.product-grid', ['products' => $products])
    </div>
  </div>

  <!-- Floating Shoe Conversion Button (now inside main container) -->
  <a href="{{ route('reservation.size-converter') }}" class="floating-conversion-btn" aria-label="Open Shoe Size Conversion" title="Shoe Size Conversion">
    <i class="fas fa-ruler-horizontal"></i>
    <span class="floating-conversion-label">Convert Size</span>
  </a>

  <!-- Product Modal -->
  <div class="product-modal" id="productModal" style="display: none;">
    <div class="product-modal-overlay"></div>
    <div class="product-modal-card">
      <button class="modal-close-btn">
        <i class="fas fa-times"></i>
      </button>
      <div class="product-modal-content">
        <div class="product-modal-image">
          <div class="product-placeholder">
            <i class="fas fa-image"></i>
          </div>
          <img src="" alt="Product Image" id="modalProductImage" style="display: none;">
        </div>
        <div class="product-modal-info">
          <h2 id="modalProductName"></h2>
          <p class="product-brand" id="modalProductBrand"></p>
          <div class="product-modal-price" id="modalProductPrice"></div>
          <p class="product-stock" id="modalProductStock"></p>
          <div class="product-modal-sizes">
            <label>Select Size:</label>
            <div class="modal-size-options" id="modalSizeOptions">
              <!-- Size options will be populated dynamically by JavaScript -->
            </div>
          </div>
          <div class="product-modal-colors">
            <label>Color:</label>
            <div class="modal-color-display" id="modalColorDisplay">
              <!-- Color will be displayed from product data -->
            </div>
          </div>
        </div>
      </div>
      <div class="product-modal-actions">
        <button class="modal-action-btn modal-cancel">Cancel</button>
        <button class="modal-action-btn modal-add-to-cart">
          <i class="fas fa-shopping-cart"></i>
          Add to Cart
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Cart Modal -->
  <div class="cart-modal-overlay" id="cartModalOverlay" style="display:none;">
    <div class="cart-modal-window" role="dialog" aria-label="Reservation Cart" aria-modal="true">
      <div class="cart-modal-header">
        <div class="cart-header-title">
          <i class="fas fa-shopping-cart cart-header-icon" aria-hidden="true"></i>
          <span>Reservation Cart</span>
        </div>
        <button class="cart-modal-close" id="cartModalClose" aria-label="Close Cart">&times;</button>
      </div>
      <div class="cart-modal-body">
        <div class="cart-modal-items" id="cartModalItems">
          <div class="cart-empty">
            <div class="cart-empty-icon"><i class="fas fa-bag-shopping"></i></div>
            <div class="cart-empty-title">Your cart is empty</div>
            <div class="cart-empty-subtitle">Add some shoes to get started!</div>
          </div>
        </div>
      </div>
        <div class="cart-modal-footer">
        <div class="cart-total-row">
          <span>Total:</span>
          <span class="cart-total" id="cartModalTotal">₱ 0.00</span>
        </div>
        <div class="cart-modal-actions">
          <button class="cart-clear-btn cart-modal-clear" type="button" title="Clear all items" disabled>
            <i class="fas fa-trash-can"></i>
            <span>Clear all</span>
          </button>
          <button class="cart-checkout-btn cart-modal-checkout" disabled>Reserve</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating Shoe Conversion Button moved inside main container (see below) -->

  <!-- Products Data for JavaScript -->
  <script src="{{ asset('js/reservation-portal-laravel.js') }}"></script>
  
