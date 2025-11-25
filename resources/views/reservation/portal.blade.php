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
    /* Desktop nav buttons - ensure consistent display across all desktop scales (100%, 125%, etc) */
    .res-portal-desktop-nav { 
      display: flex !important; 
      align-items: center;
      gap: 10px;
    }
    @media (max-width: 1160px) {
      .res-portal-desktop-nav { display: none !important; }
    }
    /* Mobile nav only visible on mobile */
    .res-portal-nav { display: none !important; }
    @media (max-width: 1160px) {
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
    /* Futuristic user menu */
    .sv-user { position:relative; }
    .sv-user-menu { position:absolute; top:110%; right:0; width:min(300px,80vw); padding:16px 16px 14px; border-radius:22px; background:linear-gradient(145deg,rgba(255,255,255,.92) 0%, rgba(245,249,255,.88) 65%, rgba(238,245,255,.82) 100%); backdrop-filter:blur(22px) saturate(180%); -webkit-backdrop-filter:blur(22px) saturate(180%); border:1px solid rgba(35,67,206,.18); box-shadow:0 30px 54px -18px rgba(8,32,96,.35), 0 12px 32px -12px rgba(8,32,96,.28); display:none; transform-origin:top right; overflow:hidden; }
    .sv-user-menu:before { content:''; position:absolute; inset:0; background:linear-gradient(115deg,rgba(2,13,39,.05),rgba(9,44,128,.09) 55%,rgba(42,106,255,.08)); pointer-events:none; }
    .sv-user-menu:after { content:''; position:absolute; inset:0; background:radial-gradient(circle at top right, rgba(42,106,255,.35), transparent 70%); mix-blend-mode:overlay; opacity:.55; pointer-events:none; }
    .sv-user-menu.open { display:block !important; animation:userMenuIn .42s cubic-bezier(.22,1.2,.4,1); }
    @keyframes userMenuIn { 0% { opacity:0; transform:translateY(-12px) scale(.94); } 55% { opacity:1; transform:translateY(4px) scale(1.02); } 100% { opacity:1; transform:translateY(0) scale(1); } }
    .sv-user-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:14px; font-size:.78rem; font-weight:700; letter-spacing:.5px; color:#0f172a; background:linear-gradient(135deg,rgba(255,255,255,.65),rgba(245,249,255,.55)); text-decoration:none; transition:background .35s ease, transform .25s ease, box-shadow .35s ease; position:relative; overflow:hidden; }
    .sv-user-item:before { content:''; position:absolute; inset:0; background:linear-gradient(110deg, transparent 0%, rgba(255,255,255,.28) 50%, transparent 70%); opacity:0; transition:opacity .5s ease; }
    .sv-user-item:hover { background:linear-gradient(135deg,rgba(255,255,255,.85),rgba(245,249,255,.75)); box-shadow:0 10px 24px -8px rgba(8,32,96,.25); transform:translateY(-2px); }
    .sv-user-item:hover:before { opacity:1; }
    .sv-user-item.danger { background:linear-gradient(135deg,rgba(255,245,245,.70),rgba(255,230,230,.65)); color:#b91c1c; border:1px solid rgba(220,38,38,.25); }
    .sv-user-item.danger:hover { background:linear-gradient(135deg,rgba(255,230,230,.92),rgba(255,245,245,.85)); box-shadow:0 12px 26px -10px rgba(220,38,38,.45); }
    
    /* Pagination Styles */
    .pagination-wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
      margin: 2rem 0 3rem 0;
      padding: 0 1rem;
    }
    
    .sv-pagination {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: white;
      padding: 1rem 1.5rem;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(35, 67, 206, 0.12);
      border: 1px solid rgba(35, 67, 206, 0.08);
    }
    
    .sv-pagination-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border: 1px solid #e5eafe;
      background: #f8fafc;
      color: #64748b;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .sv-pagination-btn:hover:not(:disabled) {
      background: linear-gradient(135deg, #2343ce 0%, #2a6aff 100%);
      color: white;
      border-color: #2343ce;
      transform: translateY(-1px);
    }
    
    .sv-pagination-btn.active {
      background: linear-gradient(135deg, #2343ce 0%, #2a6aff 100%);
      color: white;
      border-color: #2343ce;
      box-shadow: 0 4px 12px rgba(35, 67, 206, 0.3);
    }
    
    .sv-pagination-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      background: #f3f4f6;
    }
    
    .sv-pagination-dots {
      color: #9ca3af;
      font-weight: 500;
      padding: 0 0.25rem;
    }
    
    .sv-pagination-info {
      margin-left: 1rem;
      color: #6b7280;
      font-size: 0.875rem;
      font-weight: 500;
      white-space: nowrap;
    }
    
    @media (max-width: 768px) {
      .pagination-wrapper {
        justify-content: center;
        margin: 1.5rem 0 2rem 0;
        padding: 0 0.5rem;
      }
      
      .sv-pagination {
        padding: 0.75rem 1rem;
        gap: 0.25rem;
      }
      
      .sv-pagination-btn {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
      }
      
      .sv-pagination-info {
        margin-left: 0.5rem;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <!-- Desktop popular sidebars -->
  @if(isset($menPopularProducts) && isset($womenPopularProducts))
    <div class="popular-sidebar left desktop-only">
      <h3 class="popular-sidebar-title"><i class="fas fa-fire"></i> Popular Men</h3>
      @foreach($menPopularProducts as $p)
        <div class="res-portal-product-card popular-card" data-product-id="{{ $p->product_id }}" data-product-name="{{ $p->name }}" data-product-brand="{{ $p->brand }}" data-product-price="{{ number_format($p->price, 2) }}" data-product-category="{{ $p->category }}">
          <!-- Trending/Popular badges for sidebar -->
          @if(isset($p->is_trending) && $p->is_trending)
          <div class="trending-badge sidebar-badge" style="position:absolute;top:8px;left:8px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:.55rem;font-weight:700;padding:2px 6px;border-radius:999px;letter-spacing:.2px;z-index:5;">
            🔥 HOT
          </div>
          @elseif(isset($p->is_popular) && $p->is_popular)
          <div class="popular-badge sidebar-badge" style="position:absolute;top:8px;left:8px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:.55rem;font-weight:700;padding:2px 6px;border-radius:999px;letter-spacing:.2px;z-index:5;">
            ⭐ TOP
          </div>
          @endif
          
          <div class="res-portal-product-img card-image-top">
            @if($p->image_url)
              <img src="{{ asset($p->image_url) }}" alt="{{ $p->name }}" loading="lazy">
            @else
              <i class="fas fa-image" style="font-size:1.6rem;color:#a0aec0;"></i>
            @endif
          </div>
          <div class="res-portal-product-info">
            <div class="res-portal-product-title" title="{{ $p->name }}">{{ Str::limit($p->name, 40) }}</div>
            <div class="res-portal-product-brand">{{ strtoupper($p->brand) }}</div>
            <span class="res-portal-product-price">₱ {{ number_format((float)$p->price, 0, '.', ',') }}</span>
            <span class="res-portal-product-stock">{{ $p->available_total_stock ?? $p->getTotalStock() }} in stock</span>
          </div>
          <button class="res-portal-add-cart-btn" data-available-sizes="{{ ($p->available_size_labels ?? null) ?: $p->sizes->where('is_available', true)->where('stock', '>', 0)->pluck('size')->join(',') }}">Add to Cart</button>
        </div>
      @endforeach
    </div>
    <div class="popular-sidebar right desktop-only">
      <h3 class="popular-sidebar-title"><i class="fas fa-fire"></i> Popular Women</h3>
      @foreach($womenPopularProducts as $p)
        <div class="res-portal-product-card popular-card" data-product-id="{{ $p->product_id }}" data-product-name="{{ $p->name }}" data-product-brand="{{ $p->brand }}" data-product-price="{{ number_format($p->price, 2) }}" data-product-category="{{ $p->category }}">
          <!-- Trending/Popular badges for sidebar -->
          @if(isset($p->is_trending) && $p->is_trending)
          <div class="trending-badge sidebar-badge" style="position:absolute;top:8px;left:8px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:.55rem;font-weight:700;padding:2px 6px;border-radius:999px;letter-spacing:.2px;z-index:5;">
            🔥 HOT
          </div>
          @elseif(isset($p->is_popular) && $p->is_popular)
          <div class="popular-badge sidebar-badge" style="position:absolute;top:8px;left:8px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:.55rem;font-weight:700;padding:2px 6px;border-radius:999px;letter-spacing:.2px;z-index:5;">
            ⭐ TOP
          </div>
          @endif
          
          <div class="res-portal-product-img card-image-top">
            @if($p->image_url)
              <img src="{{ asset($p->image_url) }}" alt="{{ $p->name }}" loading="lazy">
            @else
              <i class="fas fa-image" style="font-size:1.6rem;color:#a0aec0;"></i>
            @endif
          </div>
          <div class="res-portal-product-info">
            <div class="res-portal-product-title" title="{{ $p->name }}">{{ Str::limit($p->name, 40) }}</div>
            <div class="res-portal-product-brand">{{ strtoupper($p->brand) }}</div>
            <span class="res-portal-product-price">₱ {{ number_format((float)$p->price, 0, '.', ',') }}</span>
            <span class="res-portal-product-stock">{{ $p->available_total_stock ?? $p->getTotalStock() }} in stock</span>
          </div>
          <button class="res-portal-add-cart-btn" data-available-sizes="{{ ($p->available_size_labels ?? null) ?: $p->sizes->where('is_available', true)->where('stock', '>', 0)->pluck('size')->join(',') }}">Add to Cart</button>
        </div>
      @endforeach
    </div>
  @endif
  <nav class="res-portal-navbar">
    <a href="{{ route('reservation.home') }}" class="res-portal-logo-link">
      <img src="{{ asset('reservation-assets/shoevault-logo.png') }}" alt="Logo" class="res-portal-logo">
    </a>
    <div class="res-portal-search desktop-only">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search for shoes, brands, colors, or models...">
    </div>
    <!-- Desktop nav buttons inserted here -->
    <div class="res-portal-desktop-nav desktop-only" style="display:flex;align-items:center;gap:10px;margin-right:24px;">
      <button class="res-portal-nav-btn nav-home" onclick="goToSection('products')" title="Home">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span class="nav-label">Home</span>
      </button>
      <button class="res-portal-nav-btn nav-services" onclick="goToSection('services')" title="Services">
        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
        <span class="nav-label">Services</span>
      </button>
      <button class="res-portal-nav-btn nav-testimonials" onclick="goToSection('testimonials')" title="Testimonials">
        <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
        <span class="nav-label">Testimonials</span>
      </button>
      <button class="res-portal-nav-btn nav-about" onclick="goToSection('about-us')" title="About Us">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-label">About Us</span>
      </button>
      <button class="res-portal-nav-btn nav-contact" onclick="goToSection('contact')" title="Contact Us">
        <span class="nav-icon"><i class="fas fa-envelope"></i></span>
        <span class="nav-label">Contact Us</span>
      </button>
    </div>
    <div class="cart-container" style="display:flex;align-items:center;gap:10px;">
      <button class="res-portal-cart-btn" title="View Cart" id="cartButton">
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
              <a href="{{ route('customer.dashboard') }}" class="sv-user-item" role="menuitem">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
              </a>
              <div class="sv-user-divider" style="height: 1px; background-color: #e2e8f0; margin: 8px 0;"></div>
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
      <input type="text" placeholder="Search for shoes, brands, colors, or models...">
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
      <button class="res-portal-nav-btn nav-home" onclick="goToSection('products')">
        <span class="nav-icon"><i class="fas fa-home"></i></span>
        <span class="nav-label">Home</span>
      </button>
      <button class="res-portal-nav-btn nav-services" onclick="goToSection('services')">
        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
        <span class="nav-label">Services</span>
      </button>
      <button class="res-portal-nav-btn nav-testimonials" onclick="goToSection('testimonials')">
        <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
        <span class="nav-label">Testimonials</span>
      </button>
      <button class="res-portal-nav-btn nav-about" onclick="goToSection('about-us')">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-label">About Us</span>
      </button>
      <button class="res-portal-nav-btn nav-contact" onclick="goToSection('contact')">
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
              <!-- Using dropdown for both desktop and mobile -->
              <div class="brands-dropdown">
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
                  /* Brand filter dropdown for both desktop and mobile */
                  .brands-dropdown { position: relative; width: 100%; overflow: visible; }
                  .brands-dropdown-btn {
                    width: 350px;
                    min-width: 100%;
                    background: linear-gradient(90deg, #2343ce 0%, #2a6aff 100%);
                    color: #fff;
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
                    background: #fff;
                    color: #2343ce;;
                  }
                  .brands-dropdown-list {
                    position: absolute;
                    top: 110%;
                    left: 0;
                    width: 100%;
                    min-width: 400px;
                    background: #fff;
                    border-radius: 14px;
                    box-shadow: 0 8px 32px rgba(35,67,206,0.13);
                    border: 1px solid #e5eafe;
                    z-index: 999999; /* ensure dropdown floats above other elements */
                    max-height: 220px;
                    overflow-y: auto;
                    margin-top: 4px;
                    padding: 4px 0;
                    animation: brandsDropdownIn .22s cubic-bezier(.22,1.19,.4,1);
                    will-change: transform, opacity; /* hint to browser and reduce layering issues */
                    -webkit-backface-visibility: hidden;
                    backface-visibility: hidden;
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
                  @media (max-width: 1160px) {
                    .brands-dropdown { width: calc(100vw - 50px); margin: 0; max-width: unset; }
                    .brands-dropdown-btn { width: 102%; }
                    .brands-dropdown-list { width: 100%; min-width: 0; }
                  }
              </style>
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
          @php $currentSort = $sort ?? 'popular'; @endphp
          @php $currentDirection = $sortDirection ?? 'desc'; @endphp
            <div class="res-portal-sorting-filter" aria-label="Sorting Options">
              <div class="sorting-label"><i class="fas fa-sort"></i> Sort:</div>
              <div class="sorting-options">
                <button type="button" class="sort-chip {{ $currentSort === 'alpha' ? 'active' : '' }}" data-sort="alpha">
                  Alphabetically
                  <i class="fas fa-sort-{{ $currentSort === 'alpha' ? ($currentDirection === 'asc' ? 'up' : 'down') : 'up' }}" id="alpha-icon"></i>
                </button>
                <button type="button" class="sort-chip {{ $currentSort === 'latest' ? 'active' : '' }}" data-sort="latest">
                  Added
                  <i class="fas fa-sort-{{ $currentSort === 'latest' ? ($currentDirection === 'asc' ? 'up' : 'down') : 'down' }}" id="latest-icon"></i>
                </button>
                <button type="button" class="sort-chip {{ $currentSort === 'popular' ? 'active' : '' }}" data-sort="popular">
                  Popular
                  <i class="fas fa-sort-{{ $currentSort === 'popular' ? ($currentDirection === 'asc' ? 'up' : 'down') : 'down' }}" id="popular-icon"></i>
                </button>
              </div>
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
          min-width: fit-content;
          max-width: 100%;
          display: flex;
          align-items: flex-start;
          flex-direction: column;
        }
        .res-portal-filter-right {
          flex: 2;
          min-width: 220px;
          max-width: 100%;
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          gap: 10px;
          margin-top: 20px;
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
          padding: 0;
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
        @media (max-width: 1160px) {
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

          .res-portal-filter-left {
          gap: 15px;
          }
          .res-portal-filter-right {
            padding: 0;
            margin-top: 0;
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

    @if(isset($paginationData) && $paginationData['last_page'] > 1)
    <div class="pagination-wrapper">
      <div class="sv-pagination">
        {{-- Previous button --}}
        <button class="sv-pagination-btn {{ $paginationData['current_page'] == 1 ? 'disabled' : '' }}" 
                onclick="window.location.href='{{ request()->fullUrlWithQuery(['page' => max(1, $paginationData['current_page'] - 1)]) }}'">
          <i class="fas fa-chevron-left"></i>
        </button>

        {{-- Page numbers --}}
        @for($i = 1; $i <= $paginationData['last_page']; $i++)
          @if($i == 1 || $i == $paginationData['last_page'] || ($i >= $paginationData['current_page'] - 2 && $i <= $paginationData['current_page'] + 2))
            <button class="sv-pagination-btn {{ $i == $paginationData['current_page'] ? 'active' : '' }}" 
                    onclick="window.location.href='{{ request()->fullUrlWithQuery(['page' => $i]) }}'">
              {{ $i }}
            </button>
          @elseif(($i == $paginationData['current_page'] - 3 && $i > 2) || ($i == $paginationData['current_page'] + 3 && $i < $paginationData['last_page'] - 1))
            <span class="sv-pagination-dots">...</span>
          @endif
        @endfor

        {{-- Next button --}}
        <button class="sv-pagination-btn {{ $paginationData['current_page'] == $paginationData['last_page'] ? 'disabled' : '' }}" 
                onclick="window.location.href='{{ request()->fullUrlWithQuery(['page' => min($paginationData['last_page'], $paginationData['current_page'] + 1)]) }}'">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
    @endif
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
        // Brand filter interactivity (dynamic filtering without page reload)
        document.addEventListener('DOMContentLoaded', function() {
          // Wait for the main reservation portal JS to load first
          setTimeout(function() {
            // Dropdown functionality
            var dropdownBtn = document.getElementById('brandsDropdownBtn');
            var dropdownList = document.getElementById('brandsDropdownList');
            var dropdownSelected = document.getElementById('brandsDropdownSelected');
            
            if (dropdownBtn && dropdownList) {
              // Create a portal container for the dropdown to avoid stacking context issues
              var portalContainer = null;
              var isOpen = false;

              function ensurePortal() {
                if (!portalContainer) {
                  portalContainer = document.createElement('div');
                  portalContainer.id = 'brandsDropdownPortal';
                  portalContainer.style.position = 'absolute';
                  portalContainer.style.top = '0';
                  portalContainer.style.left = '0';
                  portalContainer.style.zIndex = '999999';
                  portalContainer.style.pointerEvents = 'none';
                  document.body.appendChild(portalContainer);
                }
              }

              function openDropdown() {
                ensurePortal();
                // move the dropdownList into portal
                if (dropdownList.parentNode !== portalContainer) {
                  portalContainer.appendChild(dropdownList);
                }
                // Position it under the button
                var rect = dropdownBtn.getBoundingClientRect();
                dropdownList.style.display = 'block';
                dropdownList.style.position = 'absolute';
                dropdownList.style.left = (rect.left + window.scrollX) + 'px';
                dropdownList.style.top = (rect.bottom + window.scrollY + 6) + 'px';
                dropdownList.style.minWidth = Math.max(rect.width, 200) + 'px';
                dropdownList.style.pointerEvents = 'auto';
                isOpen = true;
                // attach reposition handlers
                window.addEventListener('scroll', repositionDropdown, true);
                window.addEventListener('resize', repositionDropdown);
              }

              function closeDropdown() {
                dropdownList.style.display = 'none';
                isOpen = false;
                window.removeEventListener('scroll', repositionDropdown, true);
                window.removeEventListener('resize', repositionDropdown);
              }

              function repositionDropdown() {
                if (!isOpen) return;
                var rect = dropdownBtn.getBoundingClientRect();
                dropdownList.style.left = (rect.left + window.scrollX) + 'px';
                dropdownList.style.top = (rect.bottom + window.scrollY + 6) + 'px';
                dropdownList.style.minWidth = Math.max(rect.width, 200) + 'px';
              }

              dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (isOpen) {
                  closeDropdown();
                } else {
                  openDropdown();
                }
              });

              // wire up options (they may be moved into portal, so delegate via portal)
              function onOptionClick(e) {
                var opt = e.target.closest('.brands-dropdown-option');
                if (!opt) return;
                e.stopPropagation();
                const brand = opt.getAttribute('data-brand');
                closeDropdown();
                if (dropdownSelected) dropdownSelected.textContent = opt.textContent;

                // Update active state
                document.querySelectorAll('.brands-dropdown-option').forEach(function(o) {
                  o.classList.remove('active');
                });
                opt.classList.add('active');

                // Filter products dynamically without page reload
                if (typeof window.filterProductsByBrand === 'function') {
                  window.filterProductsByBrand(brand === 'All' ? '' : brand);
                } else {
                  const url = new URL(window.location.href);
                  url.searchParams.set('brand', brand);
                  url.searchParams.delete('page');
                  window.location.href = url.toString();
                }
              }

              // Use event delegation on document so clicks work regardless of where the options live
              document.addEventListener('click', function(e) {
                // if click is inside dropdownList or button, ignore
                if (dropdownList.contains(e.target) || dropdownBtn.contains(e.target)) return;
                if (isOpen) closeDropdown();
              });

              document.addEventListener('click', onOptionClick, true);
            }
          }, 100);
        });
    window.customerData = @json(auth('customer')->user());
    window.IS_CUSTOMER_LOGGED_IN = {{ auth('customer')->check() ? 'true' : 'false' }};
    window.initialPaginationData = @json(isset($paginationData) ? $paginationData : null);
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

  <!-- Dynamic Sorting JavaScript -->
  <script>
    (function() {
      let currentSort = '{{ $sort ?? "popular" }}';
      let currentDirection = '{{ $sortDirection ?? "desc" }}';

      function updateSortIcon(sortType, direction) {
        const icons = {
          'alpha': document.getElementById('alpha-icon'),
          'latest': document.getElementById('latest-icon'),
          'popular': document.getElementById('popular-icon')
        };

        // Reset all icons to default
        Object.entries(icons).forEach(([type, icon]) => {
          if (icon) {
            if (type === sortType) {
              // Update active sort icon
              icon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'}`;
            } else {
              // Default icons for inactive sorts
              icon.className = type === 'alpha' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
          }
        });
      }

      function performSort(sortType) {
        // Toggle direction if clicking the same sort
        if (currentSort === sortType) {
          currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        } else {
          // Set default direction for new sort
          if (sortType === 'alpha') {
            currentDirection = 'asc';  // Alphabetically: A-Z (ascending)
          } else if (sortType === 'latest') {
            currentDirection = 'desc'; // Added: newest first (descending)
          } else if (sortType === 'popular') {
            currentDirection = 'desc'; // Popular: most popular first (descending)
          } else {
            currentDirection = 'desc'; // Default fallback
          }
        }
        
        currentSort = sortType;
        
        // Update active states
        document.querySelectorAll('.sort-chip').forEach(chip => {
          chip.classList.remove('active');
        });
        document.querySelector(`[data-sort="${sortType}"]`).classList.add('active');
        
        // Update icons
        updateSortIcon(sortType, currentDirection);
        
        // Add loading state
        const productsGrid = document.querySelector('.res-portal-products-grid');
        if (productsGrid) {
          productsGrid.style.opacity = '0.6';
          productsGrid.style.pointerEvents = 'none';
        }
        
        // Get current filters
        const urlParams = new URLSearchParams(window.location.search);
        
        // Detect current active category from button state
        const activeBtn = document.querySelector('.res-portal-category-btn.active');
        const currentCategory = activeBtn ? activeBtn.dataset.category : 'All';
        
        // Preserve current category in URL params
        if (currentCategory && currentCategory !== 'All') {
          urlParams.set('category', currentCategory);
        } else {
          urlParams.delete('category');
        }
        
        // If sort type changed (not just direction), reset to page 1
        if (currentSort !== sortType) {
          urlParams.set('page', '1');
        }
        
        urlParams.set('sort', sortType);
        urlParams.set('direction', currentDirection);
        
        // Make AJAX request to the filter API endpoint
        fetch('/api/products/filter?' + urlParams.toString(), {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          // Update the products grid with the returned HTML
          if (data.html) {
            const productsGrid = document.querySelector('.res-portal-products-grid');
            if (productsGrid) {
              productsGrid.innerHTML = data.html;
              
              // Restore grid state
              productsGrid.style.opacity = '1';
              productsGrid.style.pointerEvents = 'auto';
              
              // Update URL without page reload
              window.history.pushState({}, '', window.location.pathname + '?' + urlParams.toString());
              
              // Update pagination data and render pagination if available
              if (data.pagination) {
                window.paginationData = data.pagination;
                renderSortingPagination(sortType, currentDirection);
              }
              
              // Update category button states to reflect current selection
              updateCategoryButtonStates();
              
              // Trigger product card animations if anime.js is available
              if (typeof anime !== 'undefined') {
                anime({
                  targets: '.res-portal-product-card',
                  translateY: [20, 0],
                  opacity: [0, 1],
                  scale: [0.95, 1],
                  duration: 400,
                  delay: anime.stagger(30),
                  easing: 'easeOutExpo'
                });
              }
            }
          }
        })
        .catch(error => {
          console.error('Sorting failed:', error);
          // Restore grid state on error
          const productsGrid = document.querySelector('.res-portal-products-grid');
          if (productsGrid) {
            productsGrid.style.opacity = '1';
            productsGrid.style.pointerEvents = 'auto';
          }
        });
      }

      // Initialize sorting event listeners
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sort-chip').forEach(chip => {
          chip.addEventListener('click', function(e) {
            e.preventDefault();
            performSort(this.getAttribute('data-sort'));
          });
        });

        // Set initial icon states
        updateSortIcon(currentSort, currentDirection);
      });

      // Function to update category button states based on current URL
      function updateCategoryButtonStates() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentCategory = urlParams.get('category') || 'All';
        
        // Remove active class from all category buttons
        document.querySelectorAll('.res-portal-category-btn').forEach(btn => {
          btn.classList.remove('active');
        });
        
        // Add active class to current category button
        const activeBtn = document.querySelector(`[data-category="${currentCategory}"]`);
        if (activeBtn) {
          activeBtn.classList.add('active');
        }
      }

      // Function to render pagination for sorting results
      function renderSortingPagination(sortType, direction) {
        // Remove existing pagination wrapper
        const existingPaginationWrapper = document.querySelector('.pagination-wrapper');
        if (existingPaginationWrapper) existingPaginationWrapper.remove();
        
        if (!window.paginationData || window.paginationData.last_page <= 1) return;
        
        const paginationWrapper = document.createElement('div');
        paginationWrapper.className = 'pagination-wrapper';
        
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'sv-pagination';
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'sv-pagination-btn';
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = window.paginationData.current_page === 1;
        prevBtn.onclick = () => loadSortedPage(sortType, direction, window.paginationData.current_page - 1);
        paginationDiv.appendChild(prevBtn);
        
        // Page numbers (simplified version)
        const currentPage = window.paginationData.current_page;
        const lastPage = window.paginationData.last_page;
        
        for (let i = 1; i <= lastPage; i++) {
          if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'sv-pagination-btn' + (i === currentPage ? ' active' : '');
            pageBtn.textContent = i;
            pageBtn.onclick = () => loadSortedPage(sortType, direction, i);
            paginationDiv.appendChild(pageBtn);
          } else if ((i === currentPage - 3 && i > 2) || (i === currentPage + 3 && i < lastPage - 1)) {
            const dots = document.createElement('span');
            dots.className = 'sv-pagination-dots';
            dots.textContent = '...';
            paginationDiv.appendChild(dots);
          }
        }
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'sv-pagination-btn';
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === lastPage;
        nextBtn.onclick = () => loadSortedPage(sortType, direction, currentPage + 1);
        paginationDiv.appendChild(nextBtn);
        
        paginationWrapper.appendChild(paginationDiv);
        
        // Insert pagination after the products grid
        const productsGrid = document.querySelector('.res-portal-products-grid');
        if (productsGrid && productsGrid.parentNode) {
          productsGrid.parentNode.insertBefore(paginationWrapper, productsGrid.nextSibling);
        }
      }

      // Function to load a specific page with current sort
      function loadSortedPage(sortType, direction, page) {
        currentSort = sortType;
        currentDirection = direction;
        
        // Add loading state
        const productsGrid = document.querySelector('.res-portal-products-grid');
        if (productsGrid) {
          productsGrid.style.opacity = '0.6';
          productsGrid.style.pointerEvents = 'none';
        }
        
        // Get current filters and add pagination
        const urlParams = new URLSearchParams(window.location.search);
        
        // Detect current active category from button state
        const activeBtn = document.querySelector('.res-portal-category-btn.active');
        const currentCategory = activeBtn ? activeBtn.dataset.category : 'All';
        
        // Preserve current category in URL params
        if (currentCategory && currentCategory !== 'All') {
          urlParams.set('category', currentCategory);
        } else {
          urlParams.delete('category');
        }
        
        // If sort type changed (not just direction), reset to page 1
        const currentUrlSort = urlParams.get('sort') || 'popular';
        if (currentUrlSort !== sortType && page === 1) {
          // This ensures we start from page 1 when sort changes
        }
        
        urlParams.set('sort', sortType);
        urlParams.set('direction', direction);
        urlParams.set('page', page);
        
        // Make AJAX request to the filter API endpoint
        fetch('/api/products/filter?' + urlParams.toString(), {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          // Update the products grid with the returned HTML
          if (data.html) {
            const productsGrid = document.querySelector('.res-portal-products-grid');
            if (productsGrid) {
              productsGrid.innerHTML = data.html;
              
              // Restore grid state
              productsGrid.style.opacity = '1';
              productsGrid.style.pointerEvents = 'auto';
              
              // Update URL without page reload
              window.history.pushState({}, '', window.location.pathname + '?' + urlParams.toString());
              
              // Update pagination data and render pagination
              if (data.pagination) {
                window.paginationData = data.pagination;
                renderSortingPagination(sortType, direction);
              }
              
              // Update category button states to reflect current selection
              updateCategoryButtonStates();
              
              // Scroll to top of products grid smoothly
              const portalMain = document.querySelector('.res-portal-main');
              if (portalMain && page > 1) {
                portalMain.scrollIntoView({ behavior: 'smooth', block: 'start' });
              }
              
              // Trigger product card animations if anime.js is available
              if (typeof anime !== 'undefined') {
                anime({
                  targets: '.res-portal-product-card',
                  translateY: [20, 0],
                  opacity: [0, 1],
                  scale: [0.95, 1],
                  duration: 400,
                  delay: anime.stagger(30),
                  easing: 'easeOutExpo'
                });
              }
            }
          }
        })
        .catch(error => {
          console.error('Page loading failed:', error);
          // Restore grid state on error
          const productsGrid = document.querySelector('.res-portal-products-grid');
          if (productsGrid) {
            productsGrid.style.opacity = '1';
            productsGrid.style.pointerEvents = 'auto';
          }
        });
      }
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

        // 11a. Brand chips intro animation
        anime({
          targets: '.res-portal-brands-filter',
          translateY: [18, 0],
          opacity: [0, 1],
          scale: [0.96, 1],
          duration: 620,
          delay: anime.stagger(60, { start: 960 }),
          easing: 'easeOutExpo'
        });

        // 11b. Sorting filter intro (label + chips)
        anime({
          targets: '.sorting-label',
          translateX: [14, 0],
          opacity: [0, 1],
          duration: 520,
          delay: 1040,
          easing: 'easeOutQuad'
        });

        anime({
          targets: '.sorting-options .sort-chip',
          translateX: [12, 0],
          opacity: [0, 1],
          duration: 560,
          delay: anime.stagger(70, { start: 1100 }),
          easing: 'easeOutExpo'
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

    <!-- Smart section navigation: scroll when on portal, otherwise navigate to portal with hash -->
    <script>
      (function(){
        if (window.__svGoToSection) return; window.__svGoToSection = true;
        // Portal base URL
        var _portalUrl = @json(route('reservation.home'));

        function isSameOriginAndPath(url) {
          try {
            var u = new URL(url, window.location.origin);
            return (u.origin === window.location.origin) && (u.pathname.replace(/\/+$/,'') === window.location.pathname.replace(/\/+$/,''));
          } catch(e) { return false; }
        }

        window.goToSection = function(sectionId){
          if (!sectionId) return;
          // If already on the portal page, attempt smooth scroll to element
          try {
            if (isSameOriginAndPath(_portalUrl)) {
              var el = document.getElementById(sectionId);
              if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // update hash without causing jump
                history.replaceState(null, '', window.location.pathname + '#' + sectionId);
                return;
              }
              // if element not present, still set hash (no reload)
              window.location.hash = sectionId;
              return;
            }
          } catch(e) {
            // fallback to navigation below
          }

          // Not on portal page: navigate to portal URL with hash
          try {
            var base = _portalUrl.split('#')[0];
            window.location.href = base + '#' + sectionId;
          } catch(e) {
            window.location.href = _portalUrl + '#' + sectionId;
          }
        };
      })();
    </script>
</body>
</html>
