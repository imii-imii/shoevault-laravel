<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SHOE VAULT BATANGAS</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            /* Colors */
            --primary: #2d3748;
            --primary-dark: #1a202c;
            --primary-light: #4a5568;
            --secondary: #4a5568;
            --accent: #d1c4e9;
            --success: #4a5568;
            --warning: #a0aec0;
            --danger: #718096;
            --info: #a3bffa;
            /* Neutral Colors */
            --white: #ffffff;
            --gray-50: #f7fafc;
            --gray-100: #edf2f7;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-400: #a0aec0;
            --gray-500: #718096;
            --gray-600: #4a5568;
            --gray-700: #2d3748;
            --gray-800: #1a202c;
            --gray-900: #171923;
            /* Background Colors */
            --bg-primary: #f7fafc;
            --bg-secondary: #edf2f7;
            --bg-card: #ffffff;
            /* Typography */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-secondary: 'Roboto Slab', 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            /* Transitions */
            --transition-fast: 0.15s ease-in-out;
            --transition-normal: 0.3s ease-in-out;
            --transition-slow: 0.5s ease-in-out;
            /* Layout */
            --sidebar-width: 280px;
            --header-height: 80px;
        }

        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 0.9rem;
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-primary);
            font-weight: 300;
            background: var(--bg-primary);
            color: var(--gray-800);
            line-height: 1.6;
            overflow: hidden;
            height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR STYLES ===== */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(to top right, #112c70 0%, #2a6aff 100%);
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            border-radius: 0 25px 25px 0;
            box-shadow: 0 8px 32px rgba(30, 58, 138, 0.4);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            z-index: 1000;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            pointer-events: none;
        }

        /* Logo Section */
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-img {
            width: 100%;
            max-width: 170px;
            height: auto;
            object-fit: cover;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            padding: var(--spacing-lg) var(--spacing-lg) 0;
            list-style: none;
            font-size: 0.86rem;
        }

        .nav-item {
            margin-bottom: var(--spacing-sm);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md) var(--spacing-lg);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: var(--radius-lg);
            transition: all var(--transition-normal);
            font-family: var(--font-secondary);
            font-weight: 300;
            position: relative;
            overflow: hidden;
            margin-bottom: var(--spacing-sm);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left var(--transition-slow);
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateX(5px);
            border-left: 3px solid rgba(255, 255, 255, 0.7);
        }

        .nav-item.active .nav-link {
            color: var(--white);
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid var(--white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transform: translateX(5px);
        }

        .nav-link i {
            font-size: 1.125rem;
            transition: transform var(--transition-normal);
        }

        .nav-link:hover i {
            transform: scale(1.2);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: var(--spacing-lg);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-md);
            background: rgba(255, 255, 255, 0);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details h4 {
            color: var(--white);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .user-details span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
        }

        .logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
        .logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
        .logout-btn i{font-size:1rem}

        /* ===== MAIN CONTENT STYLES ===== */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: var(--bg-primary);
        }

        /* Header */
        .header {
            height: var(--header-height);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--spacing-xl);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .main-title {
            font-family: var(--font-secondary);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            margin-right: 380px;
        }

        .time-display,
        .date-display {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--gray-600);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Header notifications */
        .notification-wrapper {
            position: relative;
        }
        .notification-bell {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--gray-600);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .notification-bell:hover {
            background: var(--gray-100);
            color: var(--primary);
        }
        .notification-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            padding: 0 6px;
            height: 16px;
            min-width: 16px;
            line-height: 16px;
            font-size: 0.65rem;
            font-weight: 700;
            border: 2px solid #fff;
        }
        .notification-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 280px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            display: none;
            overflow: hidden;
            z-index: 200;
        }
        .notification-wrapper.open .notification-dropdown {
            display: block;
        }
        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .notification-empty {
            padding: 12px;
            color: var(--gray-500);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Main Content Wrapper */
        .main-content-wrapper {
            flex: 1;
            padding: var(--spacing-xl);
            overflow-y: auto;
            background: var(--bg-primary);
        }

        /* Products Section */
        .products-section {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-xl);
            height: 100%;
            display: flex;
            flex-direction: column;
            width: calc(100% - 340px); /* Adjust for compact cart width */
        }

        /* Category Filter */
        .category-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-lg);
            gap: var(--spacing-md);
            position: relative; /* allow search to overlap tabs */
        }

        .category-tabs {
            display: flex;
            gap: var(--spacing-xs);
        }

        .category-tab {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--gray-100);
            color: var(--gray-600);
            border: none;
            border-radius: var(--radius-lg);
            font-family: var(--font-secondary);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-normal);
        }

        .category-tab:hover {
            background: var(--gray-200);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .category-tab.actives {
            background: linear-gradient(135deg, #2a6aff 0%, #4a5568 100%);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }

        .category-tab i {
            font-size: 0.9rem;
        }

        /* Search Container */
        .search-container {
            flex: 1;
            max-width: 340px;
            transition: all var(--transition-normal);
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--white);
        }



        .search-box i {
            position: absolute;
            left: var(--spacing-md);
            color: var(--gray-500);
            font-size: 0.8rem;
        }

        .search-box input {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-sm) 2.25rem;
            /* ensure space for the clear (times) button */
            padding-right: 2.25rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.8rem;
            background: var(--white);
            color: var(--gray-800);
            transition: all var(--transition-fast);
        }

        .search-box input:focus {
            outline: none;
            border-color: #2a6aff;
            box-shadow: 0 0 0 3px rgba(42, 106, 255, 0.1);
        }

        .clear-search {
            position: absolute;
            right: var(--spacing-xs);
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .clear-search:hover {
            color: var(--danger);
            background: var(--gray-100);
        }

        /* Products Grid */
        .products-grid {
            flex: 1;
            display: grid;
            /* Limit to 4 columns on large screens for better sizing at 100% scale */
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: var(--spacing-md);
            overflow-y: auto;
            padding: var(--spacing-sm);
        }

        .loading-products {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            grid-column: 1 / -1;
            height: 200px;
            color: var(--gray-500);
        }

        .loading-products i {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
            border: none;
            display: flex;
            flex-direction: column;
            /* Increased height to avoid content overflow at 100% scale */
            height: 280px;
            padding: var(--spacing-md);
        }

        .product-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .product-image {
            display: none;
        }

        .stock-badge {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            background: rgba(74, 85, 104, 0.9);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stock-badge.low-stock {
            background: rgba(255, 193, 7, 0.9);
            color: var(--gray-800);
        }

        .stock-badge.out-of-stock {
            background: rgba(220, 38, 38, 0.9);
        }

        .out-of-stock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-md);
        }

        .product-details {
            flex: 1;
        }

        /* Keep only the top text clear of the category tag so rows below can use full width */
        .product-info h3,
        .product-info .brand {
            padding-right: 100px;
        }

        .category-tag {
            position: absolute;
            top: var(--spacing-md);
            right: var(--spacing-md);
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: var(--white);
            padding: 0.2rem 0.6rem;
            border-radius: var(--radius-xl);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-tag.men {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .category-tag.women {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .category-tag.children {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }

        .product-info h3 {
            font-family: var(--font-secondary);
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
            line-height: 1.25;
        }

        .product-info .brand {
            color: var(--gray-500);
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        /* Product color (e.g., Red, Blue) */
        .product-info .product-color {
            color: #4b5563; /* gray-600 */
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
            margin-top: -0.2rem;
            margin-bottom: 0.4rem;
        }

        /* Color row + image preview button aligned to the right */
        .color-row { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom: 0.4rem; }
        .preview-btn { width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--white); color: var(--gray-700); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; box-shadow: var(--shadow-sm); transition: all var(--transition-fast); }
        .preview-btn:hover { color:#1e40af; border-color:#2a6aff; box-shadow: 0 6px 16px rgba(42,106,255,0.18); }
        .preview-btn i { font-size: .9rem; }

        /* Floating image preview tooltip */
        .preview-tooltip { position: fixed; top:0; left:0; transform: translate(-9999px, -9999px); background:#fff; border:1px solid var(--gray-200); border-radius:12px; box-shadow:0 12px 30px rgba(2,6,23,.22); padding:8px; z-index:3000; max-width:260px; max-height:260px; display:none; }
        .preview-tooltip.open { display:block; }
        .preview-tooltip img { max-width:244px; max-height:244px; border-radius:8px; display:block; }

        .price-stock-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: var(--spacing-md);
        }

        .product-info .price {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }

        .product-stock {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            margin-left: auto; /* push stock to the right edge */
            text-align: right;
            white-space: nowrap;
        }

        .product-sizes {
            margin-top: auto;
        }

        /* Small invisible footer to reserve space at the bottom of product cards
           so internal scroll areas (sizes) don't butt directly against the card edge. */
        .product-footer {
            height: 4px;           /* larger so gap is clearly visible */
            width: 100%;
            display: block;
            pointer-events: none;
            flex: 0 0 4px;        /* do not allow footer to shrink */
        }

        .sizes-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.4rem;
        }

        .size-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            max-height: 84px; /* cap the area to prevent card overflow; leave room for footer */
            overflow-y: auto;  /* allow scrolling when many sizes */
            padding-right: 14px; /* room for scrollbar and to avoid touching card edge */
            padding-top: 4px; /* breathing room so scrollbar/content don't touch card bottom */
            margin-bottom: 6px; /* extra visual gap */
            -webkit-overflow-scrolling: touch;
        }

        /* Custom compact scrollbar so it doesn't visually butt to rounded card edge */
        .size-buttons::-webkit-scrollbar { width: 8px; }
        .size-buttons::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 6px; }
        .size-buttons::-webkit-scrollbar-track { background: transparent; }

        .size-btn {
            width: 38px;            /* consistent width for all size buttons */
            height: 30px;           /* smaller but still clickable */
            padding: 0;             /* keep content centered */
            border: 1px solid var(--gray-300);
            background: var(--white);
            color: var(--gray-700);
            border-radius: var(--radius-md);
            font-size: 0.75rem;     /* slightly smaller text */
            font-weight: 700;
            line-height: 1;         /* avoid vertical overflow */
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            touch-action: manipulation;
            user-select: none;
        }

        .size-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #1e40af 0%, #374151 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #2a6aff;
            background: #2a6aff;
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 106, 255, 0.3);
        }

        .size-btn:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
            background: var(--gray-100);
            color: var(--gray-400);
            cursor: not-allowed;
            border-color: var(--gray-200);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .men-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #2a6aff;
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-xl);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== CART SIDEBAR STYLES ===== */
        .cart-sidebar {
            width: 340px;
            height: 100vh;
            background: var(--white);
            position: fixed;
            right: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xl);
            border-left: 1px solid var(--gray-200);
            z-index: 1000;
        }

        .cart-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-header h2 {
            font-family: var(--font-secondary);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        .cart-header h2 i { color: #2a6aff; }

        .clear-cart-btn {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            border: 1px solid #dc2626;
            color: var(--white);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .clear-cart-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: var(--white);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
        }

        .cart-items {
            flex: 1;
            padding: var(--spacing-sm) var(--spacing-md); /* Compact padding */
            overflow-y: auto;
        }

        .empty-cart {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 140px;
            color: var(--gray-500);
            text-align: center;
        }

        .empty-cart i {
            font-size: 2rem;
            margin-bottom: var(--spacing-sm);
        }

        .empty-cart h3 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-sm);
            background: var(--gray-50);
        }

        .item-info {
            flex: 1;
        }

        .item-info h5 {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.15rem;
        }

        .item-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .item-controls button {
            width: 26px;
            height: 26px;
            border: 1px solid var(--gray-300);
            background: var(--white);
            color: var(--primary);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-controls button:hover {
            background: var(--primary);
            color: var(--white);
        }

        .item-total {
            font-weight: 600;
            color: #2a6aff;
            font-size: 0.9rem;
        }

        /* Right column for controls + total stacked vertically */
        .item-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 78px;
        }

        .cart-summary {
            padding: var(--spacing-sm); /* Compact summary */
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }

        .summary-row.total {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            /* Removed dividing line below subtotal */
        }

        .payment-section {
            padding: var(--spacing-sm); /* Compact */
            border-top: 1px solid var(--gray-200);
        }

        .payment-section h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .payment-input-group {
            margin-bottom: var(--spacing-sm);
        }

        .payment-input-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .payment-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .currency-symbol {
            position: absolute;
            left: var(--spacing-sm);
            color: var(--gray-600);
            font-weight: 600;
        }

        .payment-input-container input {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-sm) var(--spacing-sm) 1.6rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            background: var(--white);
        }

        /* Discount type select styling */
        .payment-input-group select {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            background: var(--white);
        }

        .change-display {
            display: flex;
            justify-content: space-between;
            padding: var(--spacing-sm);
            background: var(--gray-100);
            border-radius: 0; /* Remove corner radius */
            margin-bottom: var(--spacing-sm);
            font-weight: 600;
            color: var(--primary);
        }

        .quick-amounts {
            display: flex;
            gap: var(--spacing-xs);
            margin: 0 8px 8px; /* Side margins smaller */
        }

        /* Void pull-out panel (hidden behind cart, pull to reveal) */
        .void-pull {
            position: fixed;
            right: 340px; /* will be adjusted by JS to match cart width */
            bottom: 0;
            z-index: 0; /* ensure above cart and overlays */
            pointer-events: auto;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            overflow: visible;
        }

        .void-panel {
            transform: translateX(100%); /* hidden behind cart by default */
            transition: transform 0.28s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            will-change: transform;
            position: relative; /* so tab can be absolutely positioned inside */
            overflow: visible;
        }

        .void-panel.open { transform: translateX(0); }

        .void-inner {
            background: #fff;
            border-radius: 12px;
            padding: 6px;
            box-shadow: 0 12px 30px rgba(2, 6, 23, 0.43);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Tab is placed at the left edge of the panel so it moves with the panel */
        .void-tab {
            position: absolute;
            left: -40px; /* sits flush to the left edge of the panel */
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 44px;
            background: #fff;
            color: #010749ff;
            border-radius: 8px 0 0 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.44);
            transition: transform 0.28s ease;
        }

        .void-tab .arrow { transition: transform 0.18s ease; font-size: 18px; }
        /* rotate arrow when panel is open; tab is inside panel so rotate based on panel.open */
        .void-panel.open .void-tab .arrow { transform: rotate(180deg); }

        .void-button {
            background: linear-gradient(135deg,#9b1c1c 0%, #ef4444 100%);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 28px rgba(185,28,28,0.28);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* small description next to button */
        .void-desc { color: #374151; font-size: 0.82rem; font-weight:600; }


        .quick-amount-btn {
            flex: 1;
            padding: var(--spacing-sm);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        .cancel-quick-btn {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: var(--white);
        }

        .cancel-quick-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: var(--white);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
        }

        .print-quick-btn {
            background: linear-gradient(135deg, #2a6aff 0%, #4a5568 100%);
            color: var(--white);
        }

        .print-quick-btn:hover {
            background: linear-gradient(135deg, #1e40af 0%, #374151 100%);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Slide-in animation for new cart items */
        @keyframes slideInFromLeft {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .cart-item.slide-in { animation: slideInFromLeft 0.35s ease-out; }

        /* Receipt preview modal */
        .receipt-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .receipt-modal.open { display: flex; }
        .receipt-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            width: 340px;
            max-width: 92vw;
            max-height: 90vh;
            overflow: auto;
            padding: 12px;
        }
        .receipt-actions { display:flex; justify-content: flex-end; gap:8px; margin-top:8px; }
        .btn-print { background:#2a6aff; color:#fff; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; }
        .btn-close { background:#6b7280; color:#fff; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; }
        .receipt-paper {
            width: 280px;
            margin: 0 auto;
            color: #111827;
            font-size: 11px;
            font-weight: 400; /* thinner overall */
            line-height: 1.5; /* more airy */
            min-height: 420px; /* slightly longer */
            padding-bottom: 16px;
        }
        .receipt-header { text-align:center; margin-bottom:10px; }
        .receipt-header h3 { margin:0; font-size:15px; letter-spacing:0.5px; font-weight:700; }
        .receipt-address { text-align:center; color:#374151; font-size:10px; line-height:1.5; font-weight:400; margin-top:4px; }
        .receipt-meta { text-align:center; color:#6b7280; font-size:10px; margin:6px 0; font-weight:300; }
        .receipt-sep { border-top: 1px dotted #9ca3af; margin: 10px 0; }
        .receipt-info-row { display:flex; justify-content:space-between; font-size:10px; color:#374151; margin:4px 0; font-weight:400; }
        .receipt-items { width:100%; border-collapse: collapse; margin:8px 0; }
        .receipt-items th, .receipt-items td { padding:5px 0; border-bottom:1px dotted #e5e7eb; font-weight:400; }
        .receipt-items th { text-align:left; color:#374151; font-weight:600; font-size:10px; }
        .receipt-items td:last-child { text-align:right; }
    .receipt-totals { margin-top:8px; }
    .receipt-row { display:flex; justify-content:space-between; margin:4px 0; font-weight:400; }
    .receipt-row.total { font-weight:700; font-size:12px; }
    .receipt-footer { text-align:center; margin-top:12px; color:#6b7280; font-size:10px; line-height:1.6; }
    .receipt-barcode { height:44px; background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 4px); margin-top:12px; }

        /* Print only receipt */
        @media print {
            * { 
                visibility: hidden; 
                box-shadow: none !important;
                background: transparent !important;
            }
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            #receipt-modal, 
            #receipt-modal *, 
            .receipt-container,
            .receipt-container *,
            .receipt-paper,
            .receipt-paper * { 
                visibility: visible !important; 
                background: transparent !important;
            }
            #receipt-modal { 
                position: static !important; 
                background: transparent !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .receipt-container { 
                box-shadow: none !important; 
                border: none !important; 
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                display: block !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
            }
            .receipt-paper {
                margin: 0 !important;
                padding: 8px !important;
                background: white !important;
                border: 1px solid #ddd !important;
                width: 280px !important;
                max-width: 280px !important;
            }
            /* Hide action buttons when printing */
            .receipt-actions,
            #receipt-print,
            #receipt-close { 
                display: none !important; 
                visibility: hidden !important; 
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition-normal);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .cart-sidebar {
                width: 100%;
            }

            .header-right {
                margin-right: 0;
            }

            .category-filter {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .category-tabs {
                flex-wrap: wrap;
            }

            .products-grid {
                /* Step down to 2 columns on tablets */
                grid-template-columns: repeat(2, 1fr);
            }
        }
        /* Single column on small phones */
        @media (max-width: 600px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Restore previous layout specifically around 125% scale on desktops
           - Use resolution-based media queries to target OS/browser scaling ~1.25x
           - Keep width breakpoint so mobile/tablet rules still apply */
        @media (min-width: 769px) and (min-resolution: 1.2dppx) and (max-resolution: 1.3dppx),
               (min-width: 769px) and (min-resolution: 120dpi) and (max-resolution: 130dpi) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .product-card {
                height: 245px; /* previous height for 125% scale */
            }
        }
    </style>
</head>

<body data-user-role="{{ auth()->user()->role ?? 'guest' }}">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
            <div class="logo-text">
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item nav-pos active" onclick="showTab('pos')">
                <a href="#" class="nav-link">
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
                <a href="{{ route('pos.settings') }}" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="{{ auth()->user() && auth()->user()->profile_picture && file_exists(public_path(auth()->user()->profile_picture)) ? asset(auth()->user()->profile_picture) : asset('assets/images/profile.png') }}" 
                         alt="User" 
                         class="sidebar-avatar-img">
                </div>
                <div class="user-details">
                    <h4>{{ auth()->user()->name }}</h4>
                    <span>{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
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
                <div class="notification-wrapper" id="header-notifications">
                    <button class="notification-bell" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count" id="notification-count" style="display:none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-list" id="notification-list">
                            <div class="notification-empty"><i class="fas fa-inbox"></i> No new notifications</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- POS Tab -->
        <div class="main-content-wrapper" id="tab-pos">
            <!-- Products Section -->
            <section class="products-section">
                <!-- Category Filter -->
                <div class="category-filter">
                    <div class="category-tabs">
                        <button class="category-tab actives" data-category="all">
                            <i class="fas fa-th-large"></i>
                            <span>All Products</span>
                        </button>
                        <button class="category-tab men" data-category="men">
                            <i class="fas fa-male"></i>
                            <span>Men</span>
                        </button>
                        <button class="category-tab women" data-category="women">
                            <i class="fas fa-female"></i>
                            <span>Women</span>
                        </button>
                        <button class="category-tab accessories" data-category="accessories">
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

        <!-- Cart Sidebar -->
        <aside class="cart-sidebar" id="cart-sidebar">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart"></i> Order Summary</h2>
                <button class="clear-cart-btn" id="clear-cart">
                    <i class="fas fa-trash"></i>
                    <span>Clear All</span>
                </button>
            </div>
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some shoes to get started!</p>
                </div>
            </div>
            <div class="cart-summary">
                <div class="summary-row" id="subtotal-row">
                    <span>Subtotal</span>
                    <span id="subtotal-amount">₱ 0.00</span>
                </div>
                <div class="summary-row" id="discount-row" style="display:none;">
                    <span>Discount</span>
                    <span id="discount-amount">- ₱ 0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total-amount">₱ 0.00</span>
                </div>
            </div>
            <div class="payment-section">
                <h3><i class="fas fa-tags"></i> Discount</h3>
                <div class="payment-input-group">
                    <label for="discount-type">Type:</label>
                    <select id="discount-type">
                        <option value="amount">Amount (₱)</option>
                        <option value="percent">Percent (%)</option>
                    </select>
                </div>
                <div class="payment-input-group">
                    <label for="discount-value">Value:</label>
                    <div class="payment-input-container">
                        <input type="text" id="discount-value" placeholder="0.00">
                    </div>
                </div>

                <h3><i class="fas fa-credit-card"></i> Payment</h3>
                <div class="payment-input-group">
                    <label for="payment-amount">Cash Received:</label>
                    <div class="payment-input-container">
                        <span class="currency-symbol">₱</span>
                        <input type="text" id="payment-amount" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="change-display" id="change-display">
                <span>Change:</span>
                <span id="change-amount">₱ 0.00</span>
            </div>
            <div class="quick-amounts">
                <button class="quick-amount-btn cancel-quick-btn" id="quick-cancel">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </button>
                <button class="quick-amount-btn print-quick-btn" id="quick-print">
                    <i class="fas fa-print"></i>
                    <span>Print Receipt</span>
                </button>
            </div>
        </aside>
    </main>

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
            console.log('Loaded products:', allProducts);
            console.log('First product ID type:', typeof allProducts[0]?.id, allProducts[0]?.id);
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
            
            grid.innerHTML = filteredProducts.map(product => {
                const totalStock = product.total_stock ?? (product.sizes ? product.sizes.reduce((s, x) => s + (x.stock || 0), 0) : 0);
                const category = (product.category || 'men').toLowerCase();
                const categoryLabel = category.toUpperCase();
                const imageUrl = product.image_url || product.image || '';
                const sizeButtons = (product.sizes || []).map(sz => {
                    const disabled = !sz.is_available || sz.stock <= 0;
                    console.log(`Creating size button for product ${product.id}, size ${sz.size}, disabled: ${disabled}`);
                    // Use data attributes instead of onclick for safer event handling
                    return `<button class="size-btn" ${disabled ? 'disabled' : ''} data-product-id="${product.id}" data-size="${sz.size}">${sz.size}</button>`;
                }).join('');

                return `
                <div class="product-card ${totalStock <= 0 ? 'out-of-stock' : totalStock <= 5 ? 'low-stock' : ''}" data-id="${product.id}">
                    <div class="category-tag ${category}">${categoryLabel}</div>
                    <div class="product-info">
                        <div class="product-details">
                            <h3>${product.name}</h3>
                            <p class="brand">${product.brand || ''}</p>
                            <div class="color-row">
                                ${product.color ? `<p class=\"product-color\">${product.color}</p>` : '<span></span>'}
                                ${imageUrl ? `<button class=\"preview-btn\" data-image=\"${imageUrl}\" title=\"Preview image\"><i class=\"fas fa-eye\"></i></button>` : ''}
                            </div>
                            <div class="price-stock-row">
                                <p class="price">₱${parseFloat(product.price).toLocaleString()}</p>
                                <p class="product-stock">${totalStock} in stock</p>
                            </div>
                        </div>
                        <div class="product-sizes">
                            <div class="sizes-label">Sizes:</div>
                            <div class="size-buttons">${sizeButtons}</div>
                        </div>
                    </div>
                    <div class="product-footer" aria-hidden="true"></div>
                </div>`;
            }).join('');

            // Ensure a single global preview tooltip exists
            if (!document.getElementById('image-preview-tooltip')) {
                const tip = document.createElement('div');
                tip.id = 'image-preview-tooltip';
                tip.className = 'preview-tooltip';
                tip.innerHTML = '<img alt="Product preview" />';
                document.body.appendChild(tip);
            }

            // Bind preview events (hover and click)
            grid.querySelectorAll('.preview-btn').forEach(btn => {
                const show = (ev) => {
                    const url = btn.getAttribute('data-image');
                    if (!url) return;
                    const tip = document.getElementById('image-preview-tooltip');
                    const img = tip.querySelector('img');
                    img.src = url;
                    const rect = btn.getBoundingClientRect();
                    const margin = 10;
                    let top = rect.top + window.scrollY + rect.height + margin;
                    let left = rect.left + window.scrollX - 232; // bias toward right edge above stock
                    // Keep within viewport
                    const tipWidth = 260;
                    const tipHeight = 260;
                    if (left + tipWidth > window.scrollX + window.innerWidth) left = window.scrollX + window.innerWidth - tipWidth - margin;
                    if (left < window.scrollX) left = window.scrollX + margin;
                    if (top + tipHeight > window.scrollY + window.innerHeight) top = rect.top + window.scrollY - tipHeight - margin;
                    tip.style.transform = `translate(${left}px, ${top}px)`;
                    tip.classList.add('open');
                };
                const hide = () => {
                    const tip = document.getElementById('image-preview-tooltip');
                    tip.classList.remove('open');
                    tip.style.transform = 'translate(-9999px, -9999px)';
                };
                btn.addEventListener('mouseenter', show);
                btn.addEventListener('mouseleave', hide);
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tip = document.getElementById('image-preview-tooltip');
                    if (tip.classList.contains('open')) hide(); else show(e);
                });
            });

            // Bind size button events using event delegation
            grid.querySelectorAll('.size-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    const size = this.getAttribute('data-size');
                    console.log('Size button clicked:', { productId, size });
                    addToCartWithSize(productId, size);
                });
            });
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
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('actives'));
        this.classList.add('actives');
        loadProducts(this.dataset.category);
    });
});

// Add to cart function
// Add to cart with selected size
function addToCartWithSize(productId, size) {
    console.log('🛒 addToCartWithSize called with:', { productId, size, productIdType: typeof productId });
    console.log('📦 allProducts length:', allProducts.length);
    
    if (!productId) {
        console.error('No product ID provided');
        alert('Invalid product ID');
        return;
    }
    
    // Convert both to strings for reliable comparison
    const product = allProducts.find(p => String(p.id) === String(productId));
    console.log('Found product:', product);
    console.log('Searching for ID:', String(productId), 'in products with IDs:', allProducts.map(p => String(p.id)));
    console.log('Available products:', allProducts.map(p => ({ id: p.id, name: p.name, idType: typeof p.id })));
    
    if (!product) { 
        console.error('Product not found with ID:', productId);
        alert('Product not found'); 
        return; 
    }
    
    // Convert both to strings for reliable comparison  
    const sizeInfo = (product.sizes || []).find(s => String(s.size) === String(size));
    console.log('Found size info:', sizeInfo);
    console.log('Searching for size:', String(size), 'in sizes:', product.sizes?.map(s => String(s.size)));
    
    if (!sizeInfo) { 
        console.error('Size not found:', size, 'Available sizes:', product.sizes);
        alert('Selected size not available for this product'); 
        return; 
    }
    
    if (!sizeInfo.is_available || sizeInfo.stock <= 0) { 
        console.error('Size out of stock:', sizeInfo);
        alert('This size is out of stock'); 
        return; 
    }

    // Find if same product + size already in cart
    const key = `${productId}::${size}`;
    let item = cart.find(ci => ci.key === key);
    if (item) {
        if (item.quantity >= sizeInfo.stock) {
            alert(`Cannot add more. Only ${sizeInfo.stock} available in stock for size ${size}.`);
            return;
        }
        item.quantity += 1;
    } else {
        const price = sizeInfo.effective_price ?? parseFloat(product.price);
        // Product IDs are strings like "SV-MEN-ABC123"
        let productId = product.id;
        
        if (!productId || typeof productId !== 'string') {
            console.error('Invalid product ID type:', typeof product.id, product.id);
            alert('Invalid product ID');
            return;
        }
        
        // Validate string format
        if (!productId.match(/^[A-Z0-9\-]+$/)) {
            console.error('Invalid product ID format:', product.id);
            alert('Invalid product ID format');
            return;
        }
        
        const productStock = parseInt(sizeInfo.stock, 10);
        
        if (!Number.isInteger(productStock) || productStock < 0) {
            console.error('Invalid stock value:', sizeInfo.stock);
            alert('Invalid stock information');
            return;
        }
        
        item = {
            key,
            id: productId, // Already converted to integer
            name: product.name,
            brand: product.brand,
            size: String(size), // Ensure size is stored as string
            price: parseFloat(price),
            stock: productStock, // Already converted to integer
            quantity: 1
        };
        
        console.log('Adding item to cart:', item);
        console.log('Item ID type:', typeof item.id, 'Value:', item.id);
        cart.push(item);
    }
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const printBtn = document.getElementById('quick-print');
    
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-bag"></i>
                <h3>Your cart is empty</h3>
                <p>Add some shoes to get started!</p>
            </div>
        `;
        printBtn.disabled = true;
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item slide-in">
                <div class="item-info">
                    <h5>${item.name} <span style="color:#718096; font-weight:500">• Size ${item.size || ''}</span></h5>
                    <p>₱${item.price.toLocaleString()}</p>
                </div>
                <div class="item-right">
                    <div class="item-controls">
                        <button onclick="updateQuantityByKey('${item.key}', -1)">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="updateQuantityByKey('${item.key}', 1)">+</button>
                    </div>
                    <div class="item-total">
                        ₱${(item.price * item.quantity).toLocaleString()}
                    </div>
                </div>
            </div>
        `).join('');
        printBtn.disabled = false;
    }
    
    updateCartSummary();
}

// Update quantity
function updateQuantityByKey(key, change) {
    const item = cart.find(ci => ci.key === key);
    if (!item) return;

    const product = allProducts.find(p => parseInt(p.id) === parseInt(item.id));
    const sizeInfo = (product?.sizes || []).find(s => String(s.size) === String(item.size));

    item.quantity = parseInt(item.quantity) + parseInt(change);

    const maxStock = parseInt(sizeInfo ? sizeInfo.stock : item.stock);
    if (item.quantity > maxStock) {
        alert(`Cannot add more. Only ${maxStock} available in stock${item.size ? ' for size ' + item.size : ''}.`);
        item.quantity = maxStock;
        return;
    }

    if (item.quantity <= 0) {
        cart = cart.filter(ci => ci.key !== key);
    }

    updateCartDisplay();
}

// Update cart summary
function updateCartSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = 0;

    // Read discount inputs
    const typeEl = document.getElementById('discount-type');
    const valEl = document.getElementById('discount-value');
    let discountType = typeEl ? typeEl.value : 'amount';
    let raw = valEl ? (valEl.value || '').trim().replace(/[^0-9.]/g, '') : '0';
    let discountValue = parseFloat(raw || '0') || 0;

    let discountAmount = 0;
    if (discountType === 'percent') {
        discountValue = Math.min(100, Math.max(0, discountValue));
        discountAmount = subtotal * (discountValue / 100);
    } else {
        discountValue = Math.max(0, discountValue);
        discountAmount = Math.min(subtotal, discountValue);
    }
    discountAmount = Math.round(discountAmount * 100) / 100;

    const total = Math.max(0, subtotal - discountAmount);

    // Update UI
    const discountRow = document.getElementById('discount-row');
    const discountAmtEl = document.getElementById('discount-amount');
    if (discountRow && discountAmtEl) {
        if (discountAmount > 0) {
            discountRow.style.display = 'flex';
            discountAmtEl.textContent = `- ₱ ${discountAmount.toLocaleString()}`;
        } else {
            discountRow.style.display = 'none';
            discountAmtEl.textContent = '- ₱ 0.00';
        }
    }

    // Update subtotal display
    const subtotalEl = document.getElementById('subtotal-amount');
    if (subtotalEl) subtotalEl.textContent = `₱ ${subtotal.toLocaleString()}`;

    document.getElementById('total-amount').textContent = `₱ ${total.toLocaleString()}`;
    
    return { subtotal, tax, total, discountAmount, discountType, discountValue };
}

// Clear cart
document.getElementById('clear-cart').addEventListener('click', function() {
    if (cart.length === 0) return;
    
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCartDisplay();
    }
});

// Process sale when print button is clicked
document.getElementById('quick-print').addEventListener('click', async function() {
    if (cart.length === 0) return;
    
    const summary = updateCartSummary();
    const raw = document.getElementById('payment-amount').value.trim();
    const paymentAmount = parseFloat(raw.replace(/[^0-9.]/g, '')) || 0;
    
    if (paymentAmount < summary.total) {
        alert('Insufficient payment amount');
        return;
    }
    
    // Show receipt preview before final print/submit
    openReceiptPreview({ summary, paymentAmount });
});

// Cancel transaction
document.getElementById('quick-cancel').addEventListener('click', function() {
    if (cart.length === 0) return;
    
    if (confirm('Are you sure you want to cancel this transaction?')) {
        cart = [];
        updateCartDisplay();
        document.getElementById('payment-amount').value = '';
        const dt = document.getElementById('discount-type');
        const dv = document.getElementById('discount-value');
        if (dt) dt.value = 'amount';
        if (dv) dv.value = '';
        updatePaymentUi();
    }
});

// Search functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const brand = card.querySelector('.brand').textContent.toLowerCase();
        const color = (card.querySelector('.product-color')?.textContent || '').toLowerCase();
        
        if (name.includes(searchTerm) || brand.includes(searchTerm) || color.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Clear search
document.getElementById('clear-search').addEventListener('click', function() {
    document.getElementById('search-input').value = '';
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = 'block';
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    bindPaymentInput();
    bindDiscountInputs();
    // Expand search to overlap tabs on focus for a seamless visual transition
    const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        searchContainer.addEventListener('focusin', () => {
            searchContainer.classList.add('expanded');
        });
        searchContainer.addEventListener('focusout', () => {
            setTimeout(() => {
                const active = document.activeElement;
                if (!searchContainer.contains(active)) {
                    searchContainer.classList.remove('expanded');
                }
            }, 100);
        });
    }

    // Initialize notification system
    if (typeof NotificationManager !== 'undefined') {
        const notificationManager = new NotificationManager();
        notificationManager.init('{{ auth()->user()->role ?? "guest" }}');
        window.notificationManager = notificationManager; // Make it globally accessible
    }
});

// ===== Payment input wiring =====
function bindPaymentInput() {
    const input = document.getElementById('payment-amount');
    input.addEventListener('input', () => {
        // Keep only numbers and dot; limit to two decimals
        const cleaned = input.value.replace(/[^0-9.]/g, '');
        const parts = cleaned.split('.');
        const normalized = parts.length > 1 ? parts[0] + '.' + parts[1].slice(0, 2) : parts[0];
        if (input.value !== normalized) input.value = normalized;
        updatePaymentUi();
    });
    updatePaymentUi();
}

// ===== Discount input wiring =====
function bindDiscountInputs() {
    const typeEl = document.getElementById('discount-type');
    const valEl = document.getElementById('discount-value');
    if (!typeEl || !valEl) return;

    typeEl.addEventListener('change', () => {
        // Reset value when switching types for clarity
        valEl.value = '';
        valEl.placeholder = typeEl.value === 'percent' ? '0 - 100' : '0.00';
        updatePaymentUi();
    });

    valEl.addEventListener('input', () => {
        // Sanitize input
        const cleaned = valEl.value.replace(/[^0-9.]/g, '');
        const parts = cleaned.split('.');
        let normalized = parts.length > 1 ? parts[0] + '.' + parts[1].slice(0, 2) : parts[0];
        // Clamp percent to 0..100
        if ((document.getElementById('discount-type')?.value) === 'percent') {
            const num = parseFloat(normalized || '0') || 0;
            normalized = Math.max(0, Math.min(100, num)).toString();
        }
        if (valEl.value !== normalized) valEl.value = normalized;
        updatePaymentUi();
    });
}

function updatePaymentUi() {
    const { total } = updateCartSummary();
    const raw = document.getElementById('payment-amount').value.trim();
    const paymentAmount = parseFloat(raw || '0');
    const change = Math.max(0, paymentAmount - total);
    document.getElementById('change-amount').textContent = `₱ ${change.toLocaleString()}`;

    const printBtn = document.getElementById('quick-print');
    // Enable print only if payment covers total and there's something in cart
    printBtn.disabled = !(cart.length > 0 && paymentAmount >= total);
}

// ===== Receipt Preview =====
function openReceiptPreview({ summary, paymentAmount }) {
    let modal = document.getElementById('receipt-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'receipt-modal';
        modal.className = 'receipt-modal';
        modal.innerHTML = `
        <div class="receipt-container">
            <div class="receipt-paper" id="receipt-paper">
                <div class="receipt-header">
                    <h3>SHOE VAULT BATANGAS</h3>
                    <div class="receipt-address">Manghinao Proper Bauan, Batangas 4201<br>Tel.: +63 936 382 0087</div>
                    <div class="receipt-meta">{{ date('F d, Y h:i A') }}</div>
                </div>
                <div class="receipt-sep"></div>
                <div class="receipt-info-row"><span>Cashier</span><span>{{ auth()->user()->name ?? '—' }}</span></div>
                <div class="receipt-info-row"><span>Register</span><span>#1</span></div>
                <div class="receipt-sep"></div>
                <table class="receipt-items">
                    <thead>
                        <tr><th>Name</th><th>Qty</th><th>Price</th></tr>
                    </thead>
                    <tbody id="receipt-items-body"></tbody>
                </table>
                <div class="receipt-sep"></div>
                <div class="receipt-totals">
                    <div class="receipt-row"><span>Subtotal</span><span>₱ ${summary.subtotal.toLocaleString()}</span></div>
                    ${summary.discountAmount && summary.discountAmount > 0 ? `<div class=\"receipt-row\"><span>Discount</span><span>- ₱ ${summary.discountAmount.toLocaleString()}</span></div>` : ''}
                    <div class="receipt-row total"><span>Total</span><span>₱ ${summary.total.toLocaleString()}</span></div>
                    <div class="receipt-row"><span>Cash</span><span>₱ ${paymentAmount.toLocaleString()}</span></div>
                    <div class="receipt-row"><span>Change</span><span>₱ ${(paymentAmount - summary.total).toLocaleString()}</span></div>
                </div>
                <div class="receipt-barcode"></div>
                <div class="receipt-footer">THANK YOU!<br>Glad to see you again!</div>
            </div>
            <div class="receipt-actions">
                <button class="btn-close" id="receipt-close">Close</button>
                <button class="btn-print" id="receipt-print">Print</button>
            </div>
        </div>`;
        document.body.appendChild(modal);
    }
    // Populate items
    const tbody = modal.querySelector('#receipt-items-body');
    tbody.innerHTML = cart.map(item => `
        <tr>
            <td>${item.name} ${item.size ? `(Size ${item.size})` : ''}</td>
            <td style="text-align:center">${item.quantity}</td>
            <td style="text-align:right">₱ ${(item.price * item.quantity).toLocaleString()}</td>
        </tr>
    `).join('');

    modal.classList.add('open');
    modal.querySelector('#receipt-close').onclick = () => modal.classList.remove('open');
    modal.querySelector('#receipt-print').onclick = async () => {
        // Proceed to process sale then trigger print
        await processSaleAndPrint(summary, paymentAmount);
    };
}

async function processSaleAndPrint(summary, paymentAmount) {
    // First, validate cart items before processing
    console.log('Raw cart items before processing:', cart);
    
    const processedItems = cart.map((item, index) => {
        console.log(`Processing cart item ${index}:`, item);
        
        // Product IDs are strings like "SV-MEN-ABC123"
        let itemId = item.id;
        
        if (!itemId || typeof itemId !== 'string') {
            throw new Error(`Invalid product ID at cart item ${index}: ${item.id}`);
        }
        
        // Validate string format (should be alphanumeric with dashes)
        if (!itemId.match(/^[A-Z0-9\-]+$/)) {
            throw new Error(`Invalid product ID format at cart item ${index}: ${item.id}`);
        }
        
        // More robust quantity parsing
        let quantity = item.quantity;
        if (typeof quantity === 'string') {
            quantity = parseInt(quantity, 10);
        }
        if (!Number.isInteger(quantity) || quantity <= 0) {
            throw new Error(`Invalid quantity at cart item ${index}: ${item.quantity}`);
        }
        
        // Ensure size is a string
        let size = String(item.size || '');
        if (!size.trim()) {
            throw new Error(`Invalid size at cart item ${index}: ${item.size}`);
        }
        
        return {
            id: itemId,
            size: size,
            quantity: quantity
        };
    });
    
    const saleData = {
        items: processedItems,
        subtotal: parseFloat(summary.subtotal),
        tax: parseFloat(summary.tax || 0),
        // Backend expects 'discount' (numeric) — include it so discount is persisted
        discount: parseFloat(summary.discountAmount || 0),
        // Keep more detailed fields for extensibility
        discount_type: summary.discountType,
        discount_value: parseFloat(summary.discountValue || 0),
        discount_amount: parseFloat(summary.discountAmount || 0),
        total: parseFloat(summary.total),
        amount_paid: parseFloat(paymentAmount),
        payment_method: 'cash' // Can be enhanced to allow selection
    };
    
    try {
        console.log('Processing sale with data:', saleData);
        console.log('Cart items types after processing:', processedItems.map(item => ({
            id: typeof item.id,
            idValue: item.id,
            quantity: typeof item.quantity,
            quantityValue: item.quantity,
            size: typeof item.size,
            sizeValue: item.size
        })));
        
        const response = await fetch('{{ route("pos.process-sale") }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(saleData)
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error:', response.status, errorText);
            alert(`HTTP Error ${response.status}: ${errorText}`);
            return;
        }
        
        const result = await response.json();
        console.log('Sale response:', result);
        console.log('Response status:', response.status);
        
        if (!result.success) { 
            let errorMessage = 'Error processing sale: ' + result.message;
            if (result.errors) {
                errorMessage += '\n\nValidation errors:';
                for (const [field, messages] of Object.entries(result.errors)) {
                    errorMessage += `\n- ${field}: ${messages.join(', ')}`;
                }
            }
            console.error('Sale processing failed:', result);
            alert(errorMessage); 
            return; 
        }
        
        // Show success message with transaction details
        alert(`Sale processed successfully!\nTransaction ID: ${result.transaction_id}`);
        
        // Print the receipt using our custom print function
        if (typeof printReceipt === 'function') {
            printReceipt();
        } else {
            window.print();
        }
        
        // After print: clear cart and close modal
        cart = [];
        updateCartDisplay();
        loadProducts(); // Refresh products to update stock
        document.getElementById('payment-amount').value = '';
        updatePaymentUi();
        
        const modal = document.getElementById('receipt-modal');
        if (modal) modal.classList.remove('open');
        
    } catch (e) {
        console.error('Sale processing error:', e);
        alert('Error processing sale. Please check your connection and try again.');
    }
}

// Void pull-out panel: create and wire UI (visual only)
(function(){
    function createVoidPull() {
        const quick = document.querySelector('.quick-amounts');
        const cart = document.querySelector('.cart-sidebar');
        const rightOffset = cart ? cart.offsetWidth : 340;

        // wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'void-pull';
        wrapper.style.right = rightOffset + 'px';

        // panel
        const panel = document.createElement('div');
        panel.className = 'void-panel';
        panel.id = 'void-panel';

        // inner content
        const inner = document.createElement('div');
        inner.className = 'void-inner';

        const desc = document.createElement('div');
        desc.className = 'void-desc';
        desc.textContent = 'Hidden Actions';

        const btn = document.createElement('button');
        btn.id = 'void-btn';
        btn.className = 'void-button';
        btn.innerHTML = '<i class="fas fa-ban"></i><span>Void Last Transaction</span>';

        inner.appendChild(desc);
        inner.appendChild(btn);
        panel.appendChild(inner);

    // tab (visible edge to pull) - append inside panel so it moves with it
    const tab = document.createElement('div');
    tab.className = 'void-tab';
    tab.id = 'void-tab';
    tab.innerHTML = '<i class="fas fa-angle-left arrow"></i>';

    panel.appendChild(tab); // tab lives inside panel
    wrapper.appendChild(panel);
    document.body.appendChild(wrapper);

        // size panel to match quick-amounts width/height
        function sizePanel(){
            const rect = quick ? quick.getBoundingClientRect() : { width: 320, height: 48 };
            panel.style.width = rect.width + 'px';
            panel.style.height = rect.height + 'px';
            // align vertically with quick-amounts position relative to viewport bottom
            const bottomGap = 16; // same as CSS bottom
            panel.style.bottom = bottomGap + 'px';
            wrapper.style.right = (cart ? cart.offsetWidth : 340) + 'px';
        }
        sizePanel();
        window.addEventListener('resize', sizePanel);

        // toggle
        tab.addEventListener('click', (e)=>{
            e.stopPropagation();
            panel.classList.toggle('open');
            tab.setAttribute('aria-expanded', panel.classList.contains('open'));
        });

        // close when clicking outside
        document.addEventListener('click', (e)=>{
            if (!panel.classList.contains('open')) return;
            if (!wrapper.contains(e.target)) {
                panel.classList.remove('open');
                tab.setAttribute('aria-expanded', 'false');
            }
        });

        // void transaction functionality
        btn.addEventListener('click', ()=>{
            startVoidProcess();
            // close panel
            panel.classList.remove('open');
            tab.setAttribute('aria-expanded', 'false');
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', createVoidPull);
    else createVoidPull();
})();

// Void Transaction Functionality
let currentVoidTransactions = [];
let selectedTransactionId = null;
let managerAuth = null;

function startVoidProcess() {
    // Fetch recent transactions
    fetch('{{ route("pos.void.recent-transactions") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentVoidTransactions = data.transactions;
            if (currentVoidTransactions.length === 0) {
                alert('No transactions found in the last 10 minutes.');
                return;
            } else if (currentVoidTransactions.length === 1) {
                selectedTransactionId = currentVoidTransactions[0].transaction_id;
                showManagerAuth();
            } else {
                showTransactionSelection();
            }
        } else {
            alert('Failed to retrieve transactions: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to retrieve transactions. Please try again.');
    });
}

function showTransactionSelection() {
    const modal = createModal('Select Transaction to Void', '');
    
    let content = '<div style="max-height: 400px; overflow-y: auto;">';
    content += '<p style="margin-bottom: 15px; color: #666;">Select the transaction you want to void:</p>';
    
    currentVoidTransactions.forEach(transaction => {
        // Calculate minutes passed since transaction
        const transactionTime = new Date(transaction.created_at);
        const now = new Date();
        const minutesPassed = Math.floor((now - transactionTime) / (1000 * 60));
        
        content += `
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 10px; cursor: pointer; transition: background-color 0.2s;" 
                 onclick="selectTransaction('${transaction.transaction_id}')" 
                 onmouseover="this.style.backgroundColor='#f5f5f5'" 
                 onmouseout="this.style.backgroundColor='white'">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong>Transaction ID: ${transaction.transaction_id}</strong>
                    <span style="color: #059669; font-weight: bold;">₱${parseFloat(transaction.total_amount).toLocaleString()}</span>
                </div>
                <div style="font-size: 14px; color: #666; margin-bottom: 5px;">
                    Cashier: ${transaction.cashier_name}
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-size: 14px; color: #666;">
                        Time: ${transaction.created_at_human}
                    </div>
                    <div style="font-size: 13px; color: #16a34a; font-weight: bold;">
                        ${minutesPassed} minute${minutesPassed !== 1 ? 's' : ''} ago
                    </div>
                </div>
                <div style="font-size: 13px; color: #888;">
                    ${transaction.items_count} item(s)
                </div>
            </div>
        `;
    });
    
    content += '</div>';
    
    modal.querySelector('.modal-body').innerHTML = content;
    
    const footer = modal.querySelector('.modal-footer');
    footer.innerHTML = `
        <button onclick="closeModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
    `;
}

function selectTransaction(transactionId) {
    selectedTransactionId = transactionId;
    closeModal();
    showManagerAuth();
}

function showManagerAuth() {
    const modal = createModal('Manager Authentication Required', '');
    
    const content = `
        <p style="margin-bottom: 20px; color: #666;">Manager credentials required to void transaction.</p>
        <form id="manager-auth-form" onsubmit="authenticateManager(event)">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Manager Username:</label>
                <input type="text" id="manager-username" required 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
                <input type="password" id="manager-password" required 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </form>
    `;
    
    modal.querySelector('.modal-body').innerHTML = content;
    
    const footer = modal.querySelector('.modal-footer');
    footer.innerHTML = `
        <button onclick="closeModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; margin-right: 10px;">Cancel</button>
        <button onclick="submitManagerAuth()" 
                style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Authenticate</button>
    `;
}

function submitManagerAuth() {
    const username = document.getElementById('manager-username').value;
    const password = document.getElementById('manager-password').value;
    
    if (!username || !password) {
        alert('Please enter both username and password.');
        return;
    }
    
    // Disable button to prevent double submission
    const button = event.target;
    button.disabled = true;
    button.textContent = 'Authenticating...';
    
    fetch('{{ route("pos.void.authenticate-manager") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            managerAuth = data.manager;
            closeModal();
            showTransactionConfirmation();
        } else {
            alert('Authentication failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Authentication failed. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Authenticate';
    });
}

function authenticateManager(event) {
    event.preventDefault();
    submitManagerAuth();
}

function showTransactionConfirmation() {
    const transaction = currentVoidTransactions.find(t => t.transaction_id === selectedTransactionId);
    
    const modal = createModal('Confirm Void Transaction', '');
    
    let content = `
        <div style="margin-bottom: 20px; padding: 15px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;">
            <p style="color: #dc2626; font-weight: bold; margin: 0;">⚠️ This action cannot be undone!</p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #333;">Transaction Details:</h4>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                <div style="margin-bottom: 10px;"><strong>Transaction ID:</strong> ${transaction.transaction_id}</div>
                <div style="margin-bottom: 10px;"><strong>Total Amount:</strong> ₱${parseFloat(transaction.total_amount).toLocaleString()}</div>
                <div style="margin-bottom: 10px;"><strong>Cashier:</strong> ${transaction.cashier_name}</div>
                <div style="margin-bottom: 15px;"><strong>Time:</strong> ${transaction.created_at_human}</div>
                
                <h5 style="margin: 15px 0 10px 0;">Items:</h5>
                <div style="max-height: 200px; overflow-y: auto;">
    `;
    
    transaction.items.forEach(item => {
        content += `
            <div style="border-bottom: 1px solid #eee; padding: 8px 0; display: flex; justify-content: space-between;">
                <div>
                    <div style="font-weight: bold;">${item.product_name}</div>
                    <div style="font-size: 13px; color: #666;">
                        Brand: ${item.product_brand || 'N/A'} | Size: ${item.size || 'N/A'} | Color: ${item.product_color || 'N/A'}
                    </div>
                    <div style="font-size: 13px; color: #666;">Qty: ${item.quantity} × ₱${parseFloat(item.unit_price).toLocaleString()}</div>
                </div>
                <div style="font-weight: bold;">₱${parseFloat(item.subtotal).toLocaleString()}</div>
            </div>
        `;
    });
    
    content += `
                </div>
            </div>
        </div>
        
        <div style="margin-bottom: 20px; padding: 15px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
            <p style="margin: 0; color: #0369a1;"><strong>Manager:</strong> ${managerAuth.full_name} (${managerAuth.email})</p>
        </div>
    `;
    
    modal.querySelector('.modal-body').innerHTML = content;
    
    const footer = modal.querySelector('.modal-footer');
    footer.innerHTML = `
        <button onclick="closeModal()" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; margin-right: 10px;">Cancel</button>
        <button onclick="confirmVoidTransaction()" 
                style="padding: 10px 20px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Void Transaction</button>
    `;
}

function confirmVoidTransaction() {
    const button = event.target;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch(`{{ route("pos.void.transaction", ":id") }}`.replace(':id', selectedTransactionId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            alert('Transaction voided successfully!');
            // Reset void process variables
            currentVoidTransactions = [];
            selectedTransactionId = null;
            managerAuth = null;
        } else {
            alert('Failed to void transaction: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to void transaction. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Void Transaction';
    });
}

function createModal(title, bodyContent) {
    // Remove existing modal if any
    const existingModal = document.getElementById('void-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.id = 'void-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 style="margin: 0; color: #333;">${title}</h3>
            </div>
            <div class="modal-body" style="padding: 20px;">
                ${bodyContent}
            </div>
            <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #eee; text-align: right;">
                <!-- Footer content will be added by specific functions -->
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on outside click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    return modal;
}

function closeModal() {
    const modal = document.getElementById('void-modal');
    if (modal) {
        modal.remove();
    }
}
</script>

<script src="{{ asset('js/notifications.js') }}"></script>
@include('partials.mobile-blocker')
</body>
</html>