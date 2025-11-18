<!DOCTYPE html>
<html lang="en">
<head>
  <!-- SEO Meta Tags -->
  @if(isset($meta, $structuredData))
    <x-seo-meta :meta="$meta" :structuredData="$structuredData" />
  @else
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShoeVault Reservation Portal</title>
  @endif
  
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/reservation-portal.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <style>
    /* Desktop nav buttons only visible on desktop */
    .res-portal-desktop-nav { display: flex; }
    @media (max-width: 900px) {
      .res-portal-desktop-nav { display: none !important; }
    }
    /* Mobile nav only visible on mobile */
    .res-portal-nav { display: none !important; }
    @media (max-width: 900px) {
      .res-portal-nav { display: flex !important; }
    }
    .res-portal-logo-link {
      display: inline-block;
      text-decoration: none;
      transition: transform 0.2s ease;
    }
    
    .res-portal-logo-link:hover {
      transform: scale(1.05);
    }
    
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

    /* Login Required Modal */
    .login-required { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(2,6,23,.5); backdrop-filter: blur(4px); z-index: 5000; }
    .login-required.is-open { display: flex; }
    .login-required-card { width: min(420px, 92vw); background: #ffffff; border-radius: 16px; border: 1px solid rgba(2,6,23,.06); box-shadow: 0 18px 48px rgba(2,6,23,.18); padding: 20px 18px; margin-right: 20px; margin-left: 20px; transform: scale(.94); opacity: 0; animation: lr-zoom .28s ease forwards; }
    .login-required-head { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
    .login-required-head .ico { width:36px; height:36px; display:grid; place-items:center; border-radius:10px; background:#eef2ff; color:#3730a3; }
    .login-required-title { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; }
    .login-required-text { color:#475569; margin:8px 2px 14px 2px; }
    .login-required-actions { display:flex; gap:10px; justify-content:flex-end; }
    .lr-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 14px; border-radius:12px; border:1px solid #e5e7eb; background:#f8fafc; color:#0f172a; font-weight:800; cursor:pointer; transition: transform .05s ease, filter .15s ease, box-shadow .15s ease; }
    .lr-btn:hover { filter: brightness(1.03); }
    .lr-btn:active { transform: translateY(1px); }
    .lr-btn.primary { border:1px solid transparent; background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%) padding-box, linear-gradient(135deg, #000e2e 0%, #2343ce 100%) border-box; color:#0b1220; box-shadow: 0 12px 28px -14px rgba(35,67,206,.28); }
    @keyframes lr-zoom { to { transform: scale(1); opacity: 1; } }

    /* Login / Signup Coachmark (guest only) */
    .user-status-container { position: relative; }
    .login-coachmark { position: absolute; top: 54px; right: 0; width: min(260px, 70vw); background: #0f172a; color: #fff; padding: 14px 16px 12px 16px; border-radius: 14px; box-shadow: 0 16px 40px -10px rgba(15,23,42,.45); font-size: .85rem; line-height: 1.25rem; display: none; z-index: 4200; border: 1px solid rgba(255,255,255,.08); }
    .login-coachmark.show { display: block; animation: cm-pop .42s cubic-bezier(.22,1.19,.4,1) forwards; }
    .login-coachmark h4 { margin: 0 0 6px 0; font-size: .9rem; font-weight: 800; letter-spacing: .3px; display: flex; align-items: center; gap: 6px; }
    .login-coachmark h4 i { font-size: .9rem; color: #60a5fa; }
    .login-coachmark p { margin: 0; opacity: .85; }
    .login-coachmark-actions { margin-top: 10px; display: flex; gap: 8px; }
    .login-coachmark-btn { flex: 1; height: 34px; border-radius: 10px; font-size: .75rem; font-weight: 700; letter-spacing: .5px; display: inline-flex; align-items: center; justify-content: center; gap:6px; cursor: pointer; border: 1px solid rgba(255,255,255,.14); background: #1e293b; color: #fff; transition: background .15s ease, transform .15s ease; }
    .login-coachmark-btn.primary { background: linear-gradient(135deg,#3b82f6,#1d4ed8); border: 1px solid #2563eb; }
    .login-coachmark-btn:hover { background:#334155; }
    .login-coachmark-btn.primary:hover { filter: brightness(1.05); }
    .login-coachmark-btn:active { transform: translateY(1px); }
    .login-coachmark-close { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 8px; background: rgba(255,255,255,.08); color:#fff; display:grid; place-items:center; cursor:pointer; font-size:.75rem; border: 1px solid rgba(255,255,255,.12); transition: background .15s ease; }
    .login-coachmark-close:hover { background: rgba(255,255,255,.16); }
    .login-coachmark:before { content:""; position:absolute; top:-10px; right:18px; width:18px; height:18px; background:#0f172a; transform: rotate(45deg); border-radius:4px; border:1px solid rgba(255,255,255,.08); box-shadow: 0 10px 20px -6px rgba(15,23,42,.35); }
    @keyframes cm-pop { 0% { transform: translateY(-6px) scale(.92); opacity:0; } 60% { transform: translateY(2px) scale(1.02); opacity:1; } 100% { transform: translateY(0) scale(1); opacity:1; } }
    @media (max-width: 700px) { .login-coachmark { top: 50px; right: 4px; } .login-coachmark:before { right: 34px; } }
  </style>
</head>
<body>
  <nav class="res-portal-navbar">
    <a href="{{ route('reservation.home') }}" class="res-portal-logo-link">
      <img src="{{ asset('reservation-assets/shoevault-logo.png') }}" alt="Logo" class="res-portal-logo">
    </a>
    <div class="res-portal-search desktop-only">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search for shoes, brands, or models...">
    </div>
    <!-- Desktop nav buttons inserted here -->
    <div class="res-portal-desktop-nav desktop-only" style="display:flex;align-items:center;gap:5px;margin-right:18px;">
      <button class="res-portal-nav-btn nav-home" onclick="document.getElementById('products').scrollIntoView({behavior:'smooth'});">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span class="nav-label">Home</span>
      </button>
      <button class="res-portal-nav-btn nav-services" onclick="document.getElementById('services')?.scrollIntoView({behavior:'smooth'});">
        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
        <span class="nav-label">Services</span>
      </button>
      <button class="res-portal-nav-btn nav-testimonials" onclick="document.getElementById('testimonials')?.scrollIntoView({behavior:'smooth'});">
        <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
        <span class="nav-label">Testimonials</span>
      </button>
      <button class="res-portal-nav-btn nav-about" onclick="document.getElementById('about-us')?.scrollIntoView({behavior:'smooth'});">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-label">About Us</span>
      </button>
      <button class="res-portal-nav-btn nav-contact" onclick="document.getElementById('contact')?.scrollIntoView({behavior:'smooth'});">
        <span class="nav-icon"><i class="fas fa-envelope"></i></span>
        <span class="nav-label">Contact Us</span>
      </button>
    </div>
    <div class="cart-container" style="display:flex;align-items:center;gap:10px;">
      <button class="res-portal-cart-btn" title="View Cart">
        <i class="fas fa-shopping-cart"></i>
      </button>
      <div class="user-status-container">
        @if(auth('customer')->check())
          <div class="sv-user">
            <button class="sv-user-btn" type="button" aria-expanded="false" aria-haspopup="menu">
              <span class="sv-user-avatar">{{ strtoupper(substr((auth('customer')->user()->username ?? auth('customer')->user()->fullname ?? auth('customer')->user()->name ?? auth('customer')->user()->email ?? 'C'), 0, 1)) }}</span>
                <span class="sv-user-name">{{ auth('customer')->user()->username ?? auth('customer')->user()->first_name ?? auth('customer')->user()->name ?? auth('customer')->user()->fullname ?? 'Customer' }}</span>
              <i class="fas fa-chevron-down" style="font-size:0.75rem;"></i>
            </button>
            <div class="sv-user-menu" role="menu">
              <div class="sv-user-meta">
                <div class="sv-user-email">{{ auth('customer')->user()->email }}</div>
              </div>
              <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button type="submit" class="sv-user-item danger" role="menuitem">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Logout</span>
                </button>
              </form>
            </div>
          </div>
        @else
          <!-- When not logged in -->
          <a href="{{ route('customer.login') }}" class="res-portal-profile-btn login-btn" title="Sign in / Create account" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:999px;border:1px solid rgba(0,0,0,.06);background:#ffffff;-webkit-tap-highlight-color: transparent;box-shadow:0 6px 16px rgba(0,0,0,.08);color:#0f172a;transition:filter .15s ease, transform .05s ease;">
            <i class="fas fa-user"></i>
          </a>
          <!-- Coachmark tooltip encouraging login/signup -->
          <div class="login-coachmark" id="loginCoachmark" role="alert" aria-live="polite">
            <button class="login-coachmark-close" type="button" aria-label="Dismiss" title="Dismiss">&times;</button>
            <h4><i class="fas fa-user-plus" aria-hidden="true"></i><span>Welcome!</span></h4>
            <p>Create an account or log in to reserve shoes faster, save sizes, and track your cart.</p>
            <div class="login-coachmark-actions">
              <a href="{{ route('customer.login') }}" class="login-coachmark-btn primary" aria-label="Go to login or signup">
                <i class="fas fa-lock-open"></i> Login / Signup
              </a>
              <button type="button" class="login-coachmark-btn" data-coachmark-dismiss>Later</button>
            </div>
          </div>
        @endif
      </div>
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
      <div class="res-portal-filters-container">
        <div class="res-portal-filter-left">
          <div class="res-portal-brands-filter">
            <div class="brands-label"><i class="fas fa-tags"></i> Brand:</div>
            <div class="brands-list">
              <!-- Desktop: Brand chips, Mobile: Dropdown -->
              <div class="brands-chips desktop-only">
                <button class="brand-chip {{ ($selectedBrand ?? 'All') === 'All' ? 'active' : '' }}" data-brand="All">All</button>
                @foreach(($brands ?? []) as $brand)
                  <button class="brand-chip {{ ($selectedBrand === $brand) ? 'active' : '' }}" data-brand="{{ $brand }}">{{ $brand }}</button>
                @endforeach
              </div>
              <div class="brands-dropdown mobile-only">
                <button class="brands-dropdown-btn" id="brandsDropdownBtn">
                  <span id="brandsDropdownSelected">{{ $selectedBrand ?? 'All' }}</span>
                  <i class="fas fa-chevron-down"></i>
                </button>
                <div class="brands-dropdown-list" id="brandsDropdownList" style="display:none;">
                  <div class="brands-dropdown-option {{ ($selectedBrand ?? 'All') === 'All' ? 'active' : '' }}" data-brand="All">All</div>
                  @foreach(($brands ?? []) as $brand)
                    <div class="brands-dropdown-option {{ ($selectedBrand === $brand) ? 'active' : '' }}" data-brand="{{ $brand }}">{{ $brand }}</div>
                  @endforeach
                </div>
              </div>
            </div>
            <style>
                  /* Brand filter mobile dropdown */
                  .brands-dropdown { display: none; position: relative; width: 100%; }
                  .brands-dropdown-btn {
                    width: 100%;
                    background: #f3f6fd;
                    color: #2343ce;
                    border: 1px solid #e5eafe;
                    border-radius: 999px;
                    padding: 8px 16px;
                    font-size: 1rem;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    cursor: pointer;
                    transition: background 0.18s, color 0.18s, box-shadow 0.18s;
                    box-shadow: 0 2px 8px rgba(35,67,206,0.07);
                    outline: none;
                  }
                  .brands-dropdown-btn:active, .brands-dropdown-btn:focus {
                    background: linear-gradient(90deg, #2343ce 0%, #2a6aff 100%);
                    color: #fff;
                  }
                  .brands-dropdown-list {
                    position: sticky;
                    top: 110%;
                    left: 0;
                    width: 100%;
                    background: #fff;
                    border-radius: 14px;
                    box-shadow: 0 8px 32px rgba(35,67,206,0.13);
                    border: 1px solid #e5eafe;
                    z-index: 100;
                    max-height: 220px;
                    overflow-y: auto;
                    margin-top: 4px;
                    padding: 4px 0;
                    animation: brandsDropdownIn .22s cubic-bezier(.22,1.19,.4,1);
                  }
                  @keyframes brandsDropdownIn {
                    0% { opacity: 0; transform: translateY(-8px) scale(0.98); }
                    100% { opacity: 1; transform: translateY(0) scale(1); }
                  }
                  .brands-dropdown-option {
                    padding: 10px 18px;
                    font-size: 1rem;
                    color: #2343ce;
                    cursor: pointer;
                    transition: background 0.15s, color 0.15s;
                  }
                  .brands-dropdown-option.active, .brands-dropdown-option:hover {
                    background: linear-gradient(90deg, #2343ce 0%, #2a6aff 100%);
                    color: #fff;
                  }
                  @media (max-width: 900px) {
                    .brands-chips { display: none !important; }
                    .brands-dropdown { display: block !important; width: calc(100vw - 40px) !important; margin: 0 20px 0 20px !important; max-width: unset !important; }
                    .brands-dropdown-btn { width: 85%; }
                    .brands-dropdown-list { width: 85%; min-width: 0; }
                  }
                  @media (min-width: 901px) {
                    .brands-chips { display: flex !important; }
                    .brands-dropdown { display: none !important; }
                  }
              </style>
          </div>
        </div>
        <div class="res-portal-filter-right">
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
        </div>
      </div>
      <style>
        .res-portal-filters-container {
          display: inline-flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 32px;
          width: 100%;
          max-width: 100%;
          margin: 0;
          padding: 0 18px;
          box-sizing: border-box;
        }
        .res-portal-filter-left {
          flex: 1;
          min-width: 180px;
          max-width: 100%;
          display: flex;
          align-items: flex-start;
        }
        .res-portal-filter-right {
          flex: 2;
          min-width: 220px;
          max-width: 100%;
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          gap: 10px;
        }
        .res-portal-brands-filter {
          display: flex;
          align-items: center;
          gap: 10px;
          flex-wrap: wrap;
          background: #f7faff;
          border-radius: 12px;
          padding: 12px 18px;
          width: 100%;
        }
        .brands-label {
          font-weight: 700;
          color: #2343ce;
          font-size: 1rem;
          display: flex;
          align-items: center;
          gap: 6px;
          letter-spacing: 0.2px;
        }
        .brands-label i {
          color: #2343ce;
          font-size: 1.1em;
        }
        .brands-list {
          display: flex;
          gap: 8px;
          flex-wrap: wrap;
        }
        .brand-chip {
          background: #f3f6fd;
          color: #2343ce;
          border: none;
          border-radius: 999px;
          padding: 5px 16px;
          font-size: 0.98rem;
          font-weight: 600;
          cursor: pointer;
          transition: background 0.18s, color 0.18s, box-shadow 0.18s;
          box-shadow: 0 2px 8px rgba(35,67,206,0.07);
          outline: none;
        }
        .brand-chip.active, .brand-chip:hover, .brand-chip:focus {
          background: linear-gradient(90deg, #2343ce 0%, #2a6aff 100%);
          color: #fff;
          box-shadow: 0 4px 16px rgba(35,67,206,0.13);
        }
        .res-portal-filter-right {
          background: #f7faff;
          border-radius: 12px;
          padding: 16px 18px 14px 18px;
        }
        .res-portal-price-compact {
          width: 100%;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: flex-end;
          margin-bottom: 8px;
        }
        .res-portal-price-filter {
        }
        .res-portal-categories {
          width: 100%;
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
          justify-content: flex-end;
        }
        @media (max-width: 900px) {
          .res-portal-filters-container {
            flex-direction: column;
            gap: 14px;
            padding: 0 6px;
            max-width: 100vw;
          }
          .res-portal-filter-left, .res-portal-filter-right {
            max-width: 100%;
            min-width: 0;
            width: 100%;
            align-items: stretch;
          }
          .res-portal-filter-right {
            padding: 12px 8px 10px 8px;
          }
          .res-portal-brands-filter {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            margin: 0;
            padding: 0;
          }
          .brands-label {
            font-size: 0.95rem;
          }
          .brands-list {
            gap: 6px;
          }
          .brand-chip {
            font-size: 0.92rem;
            padding: 4px 12px;
          }
          .res-portal-categories {
            justify-content: flex-start;
            gap: 6px;
          }
        }
      </style>
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

  <!-- Customer Data for JavaScript -->
  <script>
        // Brand filter interactivity (chips and mobile dropdown)
        document.addEventListener('DOMContentLoaded', function() {
          // Desktop chips
          document.querySelectorAll('.brand-chip').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
              const brand = this.getAttribute('data-brand');
              const url = new URL(window.location.href);
              url.searchParams.set('brand', brand);
              url.searchParams.delete('page'); // Reset pagination if any
              window.location.href = url.toString();
            });
          });
          // Mobile dropdown
          var dropdownBtn = document.getElementById('brandsDropdownBtn');
          var dropdownList = document.getElementById('brandsDropdownList');
          var dropdownSelected = document.getElementById('brandsDropdownSelected');
          if (dropdownBtn && dropdownList) {
            dropdownBtn.addEventListener('click', function(e) {
              e.stopPropagation();
              dropdownList.style.display = dropdownList.style.display === 'block' ? 'none' : 'block';
            });
            document.querySelectorAll('.brands-dropdown-option').forEach(function(opt) {
              opt.addEventListener('click', function(e) {
                const brand = this.getAttribute('data-brand');
                dropdownList.style.display = 'none';
                if (dropdownSelected) dropdownSelected.textContent = this.textContent;
                const url = new URL(window.location.href);
                url.searchParams.set('brand', brand);
                url.searchParams.delete('page');
                window.location.href = url.toString();
              });
            });
            document.addEventListener('click', function(e) {
              if (!dropdownList.contains(e.target) && !dropdownBtn.contains(e.target)) {
                dropdownList.style.display = 'none';
              }
            });
          }
        });
    window.customerData = @json(auth('customer')->user());
    window.IS_CUSTOMER_LOGGED_IN = {{ auth('customer')->check() ? 'true' : 'false' }};
  </script>

  <!-- Products Data for JavaScript -->
  <script src="{{ asset('js/reservation-portal-laravel.js') }}"></script>

  <!-- Login Required Modal -->
  <div class="login-required" id="loginRequiredModal" aria-hidden="true" role="dialog" aria-label="Login required">
    <div class="login-required-card">
      <div class="login-required-head">
        <div class="ico"><i class="fas fa-lock"></i></div>
        <h3 class="login-required-title">You need to log in</h3>
      </div>
      <p class="login-required-text">You need to log in first to use this feature. Would you like to log in now?</p>
      <div class="login-required-actions">
        <button type="button" class="lr-btn" id="lrCancelBtn">Not now</button>
        <button type="button" class="lr-btn primary" id="lrLoginBtn" data-login-url="{{ route('customer.login') }}">
          <i class="fas fa-user"></i>
          Login
        </button>
      </div>
    </div>
  </div>

  <script>
    // Guard to avoid double-binding
    (function(){
      if (window.__lrBound) return; window.__lrBound = true;

      const modal = document.getElementById('loginRequiredModal');
      const cancelBtn = document.getElementById('lrCancelBtn');
      const loginBtn = document.getElementById('lrLoginBtn');

      function showLoginRequired(){
        if (!modal) return;
        modal.classList.add('is-open');
      }
      function hideLoginRequired(){
        modal?.classList.remove('is-open');
      }

      // Dismiss on ESC or backdrop click
      document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') hideLoginRequired(); });
      modal?.addEventListener('click', (e)=>{ if (e.target === modal) hideLoginRequired(); });
      cancelBtn?.addEventListener('click', hideLoginRequired);

      loginBtn?.addEventListener('click', ()=>{
        const baseUrl = loginBtn.getAttribute('data-login-url') || '#';
        const returnParam = encodeURIComponent(window.location.href);
        window.location.href = `${baseUrl}?return=${returnParam}`;
      });

      // Intercept clicks on Add-to-Cart buttons only when NOT logged in
      // NOTE: We don't intercept cart button here anymore - it's handled by reservation-portal-laravel.js
      document.addEventListener('click', (e)=>{
        const addBtn = e.target.closest?.('.res-portal-add-cart-btn');
        const notLoggedIn = !window.IS_CUSTOMER_LOGGED_IN;
        if (addBtn && notLoggedIn) {
          e.preventDefault();
          e.stopPropagation();
          showLoginRequired();
        }
      }, true);

      // Enhance cart button state based on login
      const cartBtnEl = document.querySelector('.res-portal-cart-btn');
      if (cartBtnEl) {
        if (window.IS_CUSTOMER_LOGGED_IN) {
          cartBtnEl.title = 'View Cart';
          cartBtnEl.style.opacity = '1';
          cartBtnEl.style.cursor = 'pointer';
        } else {
          cartBtnEl.title = 'Please login to view cart';
          cartBtnEl.style.opacity = '0.6';
          cartBtnEl.style.cursor = 'not-allowed';
        }
      }

      // Coachmark: Encourage login/signup by pointing to the profile icon (guests only)
      if (!window.IS_CUSTOMER_LOGGED_IN) {
        const coach = document.getElementById('loginCoachmark');
        const profileBtn = document.querySelector('.res-portal-profile-btn.login-btn');
        if (coach && profileBtn) {
          const showCoach = () => {
            coach.classList.add('show');
            coach.setAttribute('aria-hidden', 'false');
          };
          const dismissCoach = () => {
            coach.classList.remove('show');
            coach.setAttribute('aria-hidden', 'true');
            window.removeEventListener('scroll', onScrollOnce);
          };
          const onScrollOnce = () => { if ((window.scrollY || 0) > 60) dismissCoach(); };

          // Wire up events
          coach.addEventListener('click', (e)=>{
            // Allow clicks on buttons to proceed normally; dismiss otherwise
            const isAction = e.target.closest && (e.target.closest('.login-coachmark-btn') || e.target.closest('.login-coachmark-close'));
            if (!isAction) dismissCoach();
          });
          coach.querySelector('[data-coachmark-dismiss]')?.addEventListener('click', dismissCoach);
          coach.querySelector('.login-coachmark-close')?.addEventListener('click', dismissCoach);
          profileBtn.addEventListener('click', dismissCoach, { once: true });
          window.addEventListener('scroll', onScrollOnce, { passive: true });
          setTimeout(dismissCoach, 10000);

          // Show after a short delay to avoid jank with navbar animation
          setTimeout(showCoach, 600);
        }
      }

      // User menu interactions (when logged in)
      if (window.IS_CUSTOMER_LOGGED_IN) {
        const svUser = document.querySelector('.sv-user');
        if (svUser) {
          const btn = svUser.querySelector('.sv-user-btn');
          const menu = svUser.querySelector('.sv-user-menu');
          const toggle = () => {
            const open = menu.classList.toggle('open');
            btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
          };
          const close = () => { menu.classList.remove('open'); btn?.setAttribute('aria-expanded','false'); };
          btn?.addEventListener('click', (e)=>{ e.stopPropagation(); toggle(); });
          document.addEventListener('click', (e)=>{ if (!svUser.contains(e.target)) close(); });
          document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });
        }
      }
    })();
  </script>

  <!-- Mobile search collapse/expand controller -->
  <script>
    (function(){
      if (window.__svSearchBound) return; window.__svSearchBound = true;
      const searchEl = document.querySelector('.res-portal-search.mobile-only');
      if (!searchEl) return;

      let lastY = window.scrollY || 0;
      let lockExpanded = false;
      let lockBase = 0;
      const START = 10;
      const RANGE = 220;

      function clamp01(n){ return Math.max(0, Math.min(1, n)); }

      function setCollapse(value){
        const v = clamp01(value);
        searchEl.style.setProperty('--collapse', String(v));
        const input = searchEl.querySelector('input');
        if (input) input.style.pointerEvents = v >= 0.98 ? 'none' : 'auto';
      }

      function computeCollapse(y){
        if (lockExpanded) return 0;
        const d = Math.max(0, y - START);
        return clamp01(d / RANGE);
      }

      function onScroll(){
        const y = window.scrollY || 0;
        if (lockExpanded && Math.abs(y - lockBase) > 40) {
          lockExpanded = false;
        }
        setCollapse(computeCollapse(y));
        lastY = y;
      }

      function onClick(e){
        const current = parseFloat(getComputedStyle(searchEl).getPropertyValue('--collapse') || '0') || 0;
        if (current >= 0.98) {
          lockExpanded = true;
          lockBase = window.scrollY || 0;
          setCollapse(0);
          const input = searchEl.querySelector('input');
          if (input) {
            setTimeout(()=> input.focus(), 60);
          }
        }
      }

      setCollapse(0);
      window.addEventListener('scroll', onScroll, { passive: true });
      searchEl.addEventListener('click', onClick, true);
    })();
  </script>

  <!-- Force scroll to top on initial load to avoid unwanted anchor/history jumps -->
  <script>
    (function(){
      try {
        if ('scrollRestoration' in history) {
          // Prevent browser from restoring previous scroll position
          history.scrollRestoration = 'manual';
        }
      } catch (e) {
        // ignore (some browsers or CSPs may restrict)
      }

      // Ensure we run after everything has loaded
      function forceTop() {
        try { window.scrollTo(0, 0); } catch(e) {}
        // Run again shortly to counteract any late anchor scrolls
        setTimeout(function(){ try{ window.scrollTo(0,0); } catch(e){} }, 60);
      }

      if (document.readyState === 'complete') {
        forceTop();
      } else {
        window.addEventListener('load', forceTop, { passive: true });
      }
    })();
  </script>

  <!-- Anime.js Interactive Animations -->
  <script>
    (function() {
      if (typeof anime === 'undefined') return;
      if (window.__animePortalInit) return;
      window.__animePortalInit = true;

      // Wait for DOM to be fully ready
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnimations);
      } else {
        initAnimations();
      }

      function initAnimations() {
        // 1. Animate navbar entrance (avoid transforms to prevent breaking fixed children on mobile)
        anime({
          targets: '.res-portal-navbar',
          opacity: [0, 1],
          duration: 600,
          easing: 'easeOutQuad'
        });

        // 2. Logo bounce in
        anime({
          targets: '.res-portal-logo',
          scale: [0.7, 1],
          rotate: [-8, 0],
          duration: 700,
          delay: 200,
          easing: 'easeOutElastic(1, 0.6)'
        });

        // 3. Search bar expand
        anime({
          targets: '.res-portal-search.desktop-only',
          scaleX: [0.9, 1],
          opacity: [0, 1],
          duration: 600,
          delay: 300,
          easing: 'easeOutQuad'
        });

        // 4. Skip animations on cart button and user status container to avoid interfering with interactions (mobile-safe)

        // 5. Mobile search slide down
        anime({
          targets: '.res-portal-search.mobile-only',
          translateY: [-20, 0],
          opacity: [0, 1],
          duration: 600,
          delay: 350,
          easing: 'easeOutQuad'
        });

        // 6. Banner entrance
        anime({
          targets: '.res-portal-banner',
          translateY: [40, 0],
          opacity: [0, 1],
          duration: 800,
          delay: 500,
          easing: 'easeOutExpo'
        });

        // 7. Banner content stagger
        anime({
          targets: ['.banner-title', '.banner-tagline'],
          translateY: [20, 0],
          opacity: [0, 1],
          duration: 600,
          delay: anime.stagger(150, {start: 700}),
          easing: 'easeOutQuad'
        });

        // 8. Banner CTA buttons
        anime({
          targets: '.banner-cta-btn',
          scale: [0.85, 1],
          opacity: [0, 1],
          duration: 500,
          delay: anime.stagger(100, {start: 1000}),
          easing: 'easeOutElastic(1, 0.7)'
        });

        // 9. Navigation buttons slide in
        anime({
          targets: '.res-portal-nav-btn',
          translateY: [30, 0],
          opacity: [0, 1],
          duration: 600,
          delay: anime.stagger(60, {start: 600}),
          easing: 'easeOutExpo'
        });

        // 10. Category buttons pop in
        anime({
          targets: '.res-portal-category-btn',
          scale: [0.85, 1],
          opacity: [0, 1],
          duration: 500,
          delay: anime.stagger(40, {start: 800}),
          easing: 'easeOutElastic(1, 0.8)'
        });

        // 11. Price filter expand
        anime({
          targets: '.res-portal-price-filter',
          scaleX: [0.9, 1],
          opacity: [0, 1],
          duration: 600,
          delay: 900,
          easing: 'easeOutQuad'
        });

        // 12. Product cards entrance with stagger from center
        anime({
          targets: '.res-portal-product-card',
          translateY: [40, 0],
          opacity: [0, 1],
          scale: [0.92, 1],
          duration: 700,
          delay: anime.stagger(50, {start: 1000, from: 'center'}),
          easing: 'easeOutExpo'
        });


        // 14. Continuous pulse for floating button
        anime({
          targets: '.floating-conversion-btn',
          scale: [1, 1.08, 1],
          duration: 2200,
          delay: 2200,
          loop: true,
          easing: 'easeInOutQuad'
        });

        // 15. Product card hover effects
        document.querySelectorAll('.res-portal-product-card').forEach(card => {
          card.addEventListener('mouseenter', function() {
            anime({
              targets: this,
              translateY: -10,
              scale: 1.03,
              duration: 350,
              easing: 'easeOutCubic'
            });
            
            const btn = this.querySelector('.res-portal-add-cart-btn');
            if (btn) {
              anime({
                targets: btn,
                scale: 1,
                duration: 300,
                easing: 'easeOutElastic(1, 0.6)'
              });
            }
          });

          card.addEventListener('mouseleave', function() {
            anime({
              targets: this,
              translateY: 0,
              scale: 1,
              duration: 350,
              easing: 'easeOutCubic'
            });

            const btn = this.querySelector('.res-portal-add-cart-btn');
            if (btn) {
              anime({
                targets: btn,
                scale: 1,
                duration: 300,
                easing: 'easeOutQuad'
              });
            }
          });
        });

        // 16. Button click feedback
        document.querySelectorAll('.res-portal-add-cart-btn, .banner-cta-btn, .res-portal-category-btn, .price-apply, .res-portal-nav-btn').forEach(btn => {
          btn.addEventListener('click', function(e) {
            anime({
              targets: this,
              scale: [1, 0.92, 1, 1],
              duration: 400,
              easing: 'easeOutElastic(1, 0.8)'
            });
          });
        });

        // 17. Cart badge animation
        const originalUpdateBadge = window.updateCartBadge;
        if (typeof originalUpdateBadge === 'function') {
          window.updateCartBadge = function(...args) {
            originalUpdateBadge.apply(this, args);
            const badge = document.querySelector('.cart-badge');
            if (badge && badge.style.display !== 'none') {
              anime({
                targets: badge,
                scale: [0.5, 1.3, 1],
                rotate: [0, 12, -12, 0],
                duration: 600,
                easing: 'easeOutElastic(1, 0.6)'
              });
            }
          };
        }

        // 18. Modal entrance animation
        const productModal = document.getElementById('productModal');
        if (productModal) {
          const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
              if (mutation.attributeName === 'style') {
                const display = productModal.style.display;
                if (display === 'flex') {
                  anime({
                    targets: '.product-modal-overlay',
                    opacity: [0, 1],
                    duration: 300,
                    easing: 'easeOutQuad'
                  });
                  anime({
                    targets: '.product-modal-card',
                    scale: [0.7, 1],
                    opacity: [0, 1],
                    duration: 450,
                    easing: 'easeOutExpo'
                  });
                  
                  setTimeout(() => {
                    anime({
                      targets: '.modal-size-options .size-option-label',
                      scale: [0.8, 1],
                      opacity: [0, 1],
                      duration: 400,
                      delay: anime.stagger(40),
                      easing: 'easeOutElastic(1, 0.7)'
                    });
                  }, 200);
                }
              }
            });
          });
          observer.observe(productModal, { attributes: true });
        }

        // 19. Category filter switch animation
        document.querySelectorAll('.res-portal-category-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            // Fade out current products
            anime({
              targets: '.res-portal-product-card',
              opacity: [1, 0],
              translateY: [0, 20],
              scale: [1, 0.95],
              duration: 300,
              easing: 'easeInQuad',
              complete: () => {
                // Wait for products to reload, then fade them back in
                setTimeout(() => {
                  const newCards = document.querySelectorAll('.res-portal-product-card');
                  if (newCards.length > 0) {
                    anime({
                      targets: newCards,
                      translateY: [30, 0],
                      opacity: [0, 1],
                      scale: [0.9, 1],
                      duration: 600,
                      delay: anime.stagger(40, {from: 'center'}),
                      easing: 'easeOutExpo'
                    });
                  }
                }, 150);
              }
            });
          });
        });

        // 20. (Removed) User menu dropdown animation — avoid animating menu open to prevent interaction issues on mobile

        // 21. Cart dropdown animation
        const cartBtn = document.querySelector('.res-portal-cart-btn');
        if (cartBtn) {
          cartBtn.addEventListener('mouseenter', function() {
            setTimeout(() => {
              const dropdown = document.querySelector('.cart-dropdown.open');
              if (dropdown && window.matchMedia('(min-width: 701px)').matches) {
                anime({
                  targets: dropdown,
                  translateY: [-12, 0],
                  opacity: [0, 1],
                  duration: 350,
                  easing: 'easeOutQuad'
                });
              }
            }, 50);
          });
        }

        // 22. Loading spinner animation
        const productsContainer = document.getElementById('products');
        if (productsContainer) {
          const loadingObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
              mutation.addedNodes.forEach((node) => {
                if (node.classList && node.classList.contains('loading-spinner')) {
                  anime({
                    targets: node,
                    opacity: [0, 1],
                    scale: [0.8, 1],
                    duration: 400,
                    easing: 'easeOutQuad'
                  });
                }
              });
            });
          });
          loadingObserver.observe(productsContainer, { childList: true, subtree: true });
        }

        // 23. Pagination entrance
        const paginationObserver = new MutationObserver((mutations) => {
          const pagination = document.querySelector('.sv-pagination');
          if (pagination && !pagination.dataset.animated) {
            pagination.dataset.animated = 'true';
            anime({
              targets: pagination,
              translateY: [20, 0],
              opacity: [0, 1],
              duration: 500,
              easing: 'easeOutQuad'
            });
            anime({
              targets: '.sv-pagination-btn',
              scale: [0.85, 1],
              opacity: [0, 1],
              duration: 400,
              delay: anime.stagger(30),
              easing: 'easeOutElastic(1, 0.7)'
            });
          }
        });
        if (productsContainer && productsContainer.parentNode) {
          paginationObserver.observe(productsContainer.parentNode, { childList: true });
        }

        // 24. Banner CTA hover effects
        document.querySelectorAll('.banner-cta-btn').forEach(btn => {
          btn.addEventListener('mouseenter', function() {
            anime({
              targets: this,
              translateY: -4,
              scale: 1.05,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
          btn.addEventListener('mouseleave', function() {
            anime({
              targets: this,
              translateY: 0,
              scale: 1,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
        });

        // 25. Floating button hover
        const floatingBtn = document.querySelector('.floating-conversion-btn');
        if (floatingBtn) {
          floatingBtn.addEventListener('mouseenter', function() {
            anime({
              targets: this,
              scale: 1.15,
              rotate: 5,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
          floatingBtn.addEventListener('mouseleave', function() {
            anime({
              targets: this,
              scale: 1,
              rotate: 0,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
        }

        // 26. Nav button hover effects
        document.querySelectorAll('.res-portal-nav-btn').forEach(btn => {
          btn.addEventListener('mouseenter', function() {
            anime({
              targets: this.querySelector('.nav-icon'),
              scale: 1.2,
              rotate: 5,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
          btn.addEventListener('mouseleave', function() {
            anime({
              targets: this.querySelector('.nav-icon'),
              scale: 1,
              rotate: 0,
              duration: 300,
              easing: 'easeOutQuad'
            });
          });
        });

        // 27. Category button active state animation
        document.querySelectorAll('.res-portal-category-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            anime({
              targets: this,
              scale: [1, 1.1, 1],
              duration: 400,
              easing: 'easeOutElastic(1, 0.7)'
            });
          });
        });

        // 28. Price filter toggle animation (mobile)
        const priceToggle = document.querySelector('.price-toggle-btn');
        if (priceToggle) {
          priceToggle.addEventListener('click', function() {
            setTimeout(() => {
              const panel = document.querySelector('.res-portal-price-filter.open');
              if (panel) {
                anime({
                  targets: panel,
                  translateX: [20, 0],
                  opacity: [0, 1],
                  duration: 350,
                  easing: 'easeOutQuad'
                });
              }
            }, 50);
          });
        }

        // 29. (Removed) Cart modal entrance (mobile) — no overlay/window animations to avoid forced close/focus conflicts

        // 30. Login required modal animation
        const loginModal = document.getElementById('loginRequiredModal');
        if (loginModal) {
          const loginObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
              if (mutation.attributeName === 'class') {
                if (loginModal.classList.contains('is-open')) {
                  anime({
                    targets: '.login-required-card',
                    scale: [0.8, 1],
                    opacity: [0, 1],
                    duration: 400,
                    easing: 'easeOutExpo'
                  });
                }
              }
            });
          });
          loginObserver.observe(loginModal, { attributes: true });
        }
      }
    })();
  </script>
</body>
</html>
