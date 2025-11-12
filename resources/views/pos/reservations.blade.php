<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reservations - SHOE VAULT BATANGAS</title>
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
            /* Enhanced text colors for stronger contrast */
            --text-base: #1f2937; /* slate-800 */
            --text-weak: #4b5563; /* slate-600 */
            --text-strong: #111827; /* slate-900 */
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
            font-weight: 400; /* increased from 300 for clearer rendering */
            background: var(--bg-primary);
            color: var(--text-base);
            line-height: 1.55;
            overflow: hidden;
            height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased; /* mac/webkit */
            -moz-osx-font-smoothing: grayscale;  /* firefox mac */
            text-rendering: optimizeLegibility;
        }

        /* Generic text clarity helpers */
        h1,h2,h3,h4,h5,h6 { color: var(--text-strong); font-weight: 700; letter-spacing: -.015em; }
        

        /* Strengthen key UI label contrast without overpowering */
        .reservation-card .card-title { color: var(--bg-primary); }
        .reservation-card .card-value { color: var(--bg-primary); }
        .header .time-display span, .header .date-display span { color: var(--gray-900); }
        

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
        .notification-wrapper { position: relative; }
        .notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:var(--gray-600); border-radius:var(--radius-md); cursor:pointer; transition: all var(--transition-fast); }
        .notification-bell:hover { background: var(--gray-100); color: var(--primary); }
        .notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
        .notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-lg); box-shadow: var(--shadow-lg); display:none; overflow:hidden; z-index:200; }
        .notification-wrapper.open .notification-dropdown { display:block; }
        .notification-list { max-height:300px; overflow-y:auto; }
        .notification-empty { padding:12px; color:var(--gray-500); text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }

        /* ===== RESERVATION TAB STYLES ===== */
        .reservation-full {
            /* Fill the available main-content width end-to-end */
            width: 100%;
            max-width: 100%;
        }
        .reservation-full .reservation-section {
              /* Apply a modest outer margin while keeping full-width layout inside */
              margin: 20px;
              width: calc(100% - 40px); /* account for the 20px left/right margin */
              max-width: 100%;
              display: flex;
              flex-direction: column;
              align-items: stretch;
        }
        .reservation-full .reservation-cards {
            width: 100%;
            justify-content: center;
        }
        .reservation-full .reservation-table-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0;
        }
        .reservation-full .reservation-filter {
            display: flex;
            width: 100%;
            justify-content:space-between;
            flex-wrap: wrap;
        }
        .reservation-section {
            padding: 24px 32px;
            background: var(--bg-card);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            /* No outer margin for end-to-end */
            margin: 20;
            min-height: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .reservation-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .reservation-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2a6aff 100%);
            color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-height: 88px;
            justify-content: center;
        }
        .reservation-card.expiring-soon {
            background: linear-gradient(135deg, #2a6aff 0%, #4a5568 100%);
        }
        .reservation-card.expiring-today {
            background: linear-gradient(135deg, #1e3a8a 0%, #a3bffa 100%);
        }
        .reservation-card.total-reservations {
            background: linear-gradient(135deg, #4a5568 0%, #2a6aff 100%);
        }
        .reservation-card .card-title {
            font-size: 0.875rem;
            font-family: var(--font-secondary);
            margin-bottom: 6px;
            font-weight: 600;
        }
        .reservation-card .card-value {
            font-size: 1.3rem;
            font-family: var(--font-primary);
            font-weight: 700;
        }
        .reservation-filter {
            display: flex;
            align-items: center;
            gap: 52px;
            margin-bottom: 24px;
        }
        .reservation-tabs {
            display: flex;
            gap: 24px;
        }
        .reservation-tab {
            background: var(--gray-200);
            color: var(--primary);
            border: none;
            border-radius: var(--radius-lg);
            font-family: var(--font-secondary);
            font-size: 0.86rem;
            font-weight: 500;
            padding: 8px 24px;
            cursor: pointer;
            transition: background var(--transition-fast), color var(--transition-fast);
        }
        .reservation-tab:hover {
            background: var(--gray-300);
        }
        .reservation-tab.active {
            background: linear-gradient(135deg, #2a6aff 0%, #4a5568 100%);
            color: var(--white);
        }

        /* Search Container for Reservations */
        .search-container {
            width: 100%;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box i {
            position: absolute;
            left: var(--spacing-md);
            color: var(--gray-500);
            font-size: 0.875rem;
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

        /* Reservation Table */
        .reservation-table-wrapper {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            flex: 1;
        }

        .reservation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reservation-table thead {
            background: linear-gradient(135deg, #2a6aff 0%, #4a5568 100%);
            color: var(--white);
        }

        .reservation-table th,
        .reservation-table td {
            padding: 8px 12px; /* tighter rows */
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .reservation-table th {
            font-weight: 600;
            font-size: 0.8rem; /* smaller header text */
            font-family: var(--font-secondary);
            line-height: 1.2;
        }

        .reservation-table td {
            font-size: 0.8rem; /* smaller cell text */
            line-height: 1.25;
        }

        .reservation-table tbody tr:hover {
            background: var(--gray-50);
        }

        .reservation-table tbody tr:nth-child(even) {
            background: var(--gray-50);
        }

        .reservation-table tbody tr:nth-child(even):hover {
            background: var(--gray-100);
        }

        /* Status badges */
        .status-badge {
            padding: 0.15rem 0.5rem; /* smaller badge */
            border-radius: var(--radius-md);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Action Button Styles */
        .complete-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            padding: 0.35rem; /* smaller tap area */
            cursor: pointer;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px; /* smaller button */
            height: 30px;
            box-shadow: var(--shadow-sm);
        }

        .complete-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .complete-btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        .complete-btn i {
            font-size: 0.8rem; /* smaller icon */
        }

        .complete-btn:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

            .reservation-filter {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .reservation-cards {
                grid-template-columns: 1fr;
            }

            .reservation-table-wrapper {
                overflow-x: auto;
            }
        }

        /* Custom scrollbar styles for reservations container */
        #reservations-container::-webkit-scrollbar {
            width: 8px;
        }

        #reservations-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        #reservations-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        #reservations-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* For Firefox */
        #reservations-container {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        /* Loading state */
        .loading-reservations {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            color: var(--gray-500);
        }

        .loading-reservations i {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }
        /* Loading products (used inside reservation modal) */
        .loading-products {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px;
            gap: 8px;
            color: var(--gray-600);
        }
        .loading-products i { font-size: 1.6rem; color: #2a6aff; }
        /* --- Section skeleton & entry animations --- */
        .section-loading-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.95));
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px;
            z-index: 9999;
            align-items: stretch;
            justify-content: flex-start;
            border-radius: inherit;
        }
        .skeleton { background: linear-gradient(90deg, #f3f4f6 25%, #e6eefc 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 1.4s linear infinite; border-radius: 6px; }
        .skeleton.title { height: 20px; width: 40%; }
        .skeleton.line { height: 12px; width: 100%; }
        .skeleton.card { height: 78px; width: 100%; border-radius: 10px; }

        @keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

        .animate-entry { opacity: 0; transform: translateY(14px); }
        @keyframes entryUp { to { opacity: 1; transform: translateY(0); } }
        .animate-entry.play { animation: entryUp 900ms cubic-bezier(.2,.9,.2,1) forwards; }
        /* Modal zoom / overlay fade animations */
        #reservation-modal.zoom-enter, #transaction-modal.zoom-enter {
            animation: modalZoomIn 320ms cubic-bezier(.2,.9,.2,1) forwards;
            transform-origin: center center;
        }
        #reservation-modal.zoom-exit, #transaction-modal.zoom-exit {
            animation: modalZoomOut 220ms cubic-bezier(.4,.0,.2,1) forwards;
            transform-origin: center center;
        }
        @keyframes modalZoomIn {
            from { transform: translate(-50%, -50%) scale(0.92); opacity: 0; }
            to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        @keyframes modalZoomOut {
            from { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            to   { transform: translate(-50%, -50%) scale(0.96); opacity: 0; }
        }
        #reservation-modal-overlay.fade-in, #transaction-modal-overlay.fade-in {
            animation: overlayFadeIn 240ms ease forwards;
        }
        #reservation-modal-overlay.fade-out, #transaction-modal-overlay.fade-out {
            animation: overlayFadeOut 200ms ease forwards;
        }
        @keyframes overlayFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes overlayFadeOut { from { opacity: 1; } to { opacity: 0; } }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
            <div class="logo-text">
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item nav-pos">
                <a href="{{ route('pos.dashboard') }}" class="nav-link">
                    <i class="fas fa-cash-register"></i>
                    <span>POS</span>
                </a>
            </li>
            <li class="nav-item nav-reservation active">
                <a href="#" class="nav-link">
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
                    <img src="{{ asset('assets/images/profile.png') }}" alt="User">
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
                <h1 class="main-title">Reservations</h1>
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
                <div class="notification-wrapper">
                    <button class="notification-bell" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count" style="display:none;">0</span>
                    </button>
                    <div class="notification-dropdown">
                        <div class="notification-list">
                            <div class="notification-empty"><i class="fas fa-inbox"></i> No new notifications</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Reservation Tab -->
        <div class="main-content-wrapper reservation-full" id="tab-reservation">
            <section class="reservation-section">
                <!-- Top Cards -->
                <div class="reservation-cards">
                    <div class="reservation-card expiring-soon">
                        <div class="card-title">Expiring Soon</div>
                        <div class="card-value" id="card-expiring-soon">{{ $reservationCards['expiring_soon'] ?? 0 }}</div>
                    </div>
                    <div class="reservation-card expiring-today">
                        <div class="card-title">Expiring Today</div>
                        <div class="card-value" id="card-expiring-today">{{ $reservationCards['expiring_today'] ?? 0 }}</div>
                    </div>
                    <div class="reservation-card total-reservations">
                        <div class="card-title">Total Pending Reservations</div>
                        <div class="card-value" id="card-total-reservations">{{ $reservationCards['pending_total'] ?? collect($reservations ?? [])->filter(function($r){ $status = is_array($r) ? ($r['status'] ?? '') : ($r->status ?? ''); return strtolower((string)$status) === 'pending'; })->count() }}</div>
                    </div>
                </div>
                <!-- Search -->
                <div class="reservation-filter">
                    <div class="search-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="reservation-search" placeholder="Search reservations by name, email, or product...">
                            <button class="clear-search" id="clear-reservation-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Reservation List (from Inventory Reservation Reports) -->
                <div style="width: 100%; display: grid; gap: 12px; max-height: calc(100vh - 320px); overflow-y: auto; padding-right: 8px;" id="reservations-container">
                    @php
                        // Ensure we only render reservations with status 'pending'
                        $pendingReservations = collect($reservations ?? [])->filter(function($r) {
                            // support both arrays and objects
                            $status = null;
                            if (is_array($r) && array_key_exists('status', $r)) $status = $r['status'];
                            elseif (is_object($r) && isset($r->status)) $status = $r->status;
                            return strtolower((string)($status ?? '')) === 'pending';
                        });
                    @endphp
                    @forelse($pendingReservations as $reservation)
                    <!-- Reservation Card - Compact Layout -->
                    <div class="reservation-card" data-res-id="{{ $reservation->id }}" data-res-number="{{ $reservation->reservation_id }}" data-res-date="{{ $reservation->created_at ? $reservation->created_at->format('M d, Y h:i A') : 'N/A' }}" data-customer-name="{{ $reservation->customer_name }}" data-customer-email="{{ $reservation->customer_email }}" data-customer-phone="{{ $reservation->customer_phone }}" data-pickup-date="{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}" data-pickup-time="{{ $reservation->pickup_time ?? 'TBD' }}" data-status="{{ $reservation->status }}" style="background: #F9FAFB; padding: 16px 20px; border-radius: 8px; border: 1px solid #E5E7EB;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 24px; align-items: center; width: 100%;">
                            <!-- Col 1: Reservation ID -->
                            <div style="min-width: 200px;">
                                <p style="color: #6B7280; font-size: 0.75rem; margin-bottom: 2px;">Reservation ID</p>
                                <p style="font-weight: 700; font-size: 0.875rem; color: #111827;">{{ $reservation->reservation_id ?? 'N/A' }}</p>
                                <p style="color: #6B7280; font-size: 0.75rem; margin-top: 8px; margin-bottom: 2px;">Reservation Date</p>
                                <p style="font-weight: 600; font-size: 0.8rem; color: #374151;">{{ $reservation->created_at ? $reservation->created_at->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <!-- Col 2: Name + Pickup Date -->
                            <div style="min-width: 160px;">
                                <p style="color: #6B7280; font-size: 0.75rem; margin-bottom: 2px;">Customer Name</p>
                                <p style="font-weight: 600; font-size: 0.8rem; color: #111827; margin-bottom: 4px;">{{ $reservation->customer_name ?? 'N/A' }}</p>
                                <p style="color: #6B7280; font-size: 0.75rem; margin-bottom: 2px;">Pickup Date</p>
                                <p style="font-weight: 600; font-size: 0.8rem; color: #111827;">{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}</p>
                            </div>
                            <!-- Col 3: Email + Phone -->
                            <div style="min-width: 180px;">
                                <p style="color: #6B7280; font-size: 0.75rem; margin-bottom: 2px;">Email</p>
                                <p style="font-weight: 600; font-size: 0.8rem; color: #111827; margin-bottom: 4px;">{{ $reservation->customer_email ?? 'N/A' }}</p>
                                <p style="color: #6B7280; font-size: 0.75rem; margin-bottom: 2px;">Phone Number</p>
                                <p style="font-weight: 600; font-size: 0.8rem; color: #111827;">{{ $reservation->customer_phone ?? 'N/A' }}</p>
                            </div>
                            <!-- Col 4: Status & Action -->
                            <div style="text-align: right; min-width: 150px;">
                                @php
                                    $statusColors = [
                                        'pending' => 'background-color: #FEF3C7; color: #92400E;',
                                        'completed' => 'background-color: #DCFCE7; color: #166534;',
                                        'cancelled' => 'background-color: #FEE2E2; color: #991B1B;'
                                    ];
                                @endphp
                                <span class="status-pill" style="display: inline-block; padding: 3px 10px; border-radius: 9999px; {{ $statusColors[$reservation->status] ?? $statusColors['pending'] }} font-weight: 600; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 8px;">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                                <div>
                                    <button class="view-reservation-btn" data-id="{{ $reservation->id }}" style="width: 50%; padding: 7px 14px; border-radius: 6px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: background-color 0.2s;">View</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <!-- Empty State -->
                    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="font-size: 3rem; color: #E5E7EB; margin-bottom: 16px;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 style="color: #6B7280; margin-bottom: 8px;">No Reservations Yet</h3>
                        <p style="color: #9CA3AF;">Reservations will appear here once customers start making reservations through the portal.</p>
                    </div>
                    @endforelse
                </div>
                <!-- Pagination Bar -->
                <div id="reservation-pagination" style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:10px;">
                    <button id="reservation-prev" class="paginate-btn" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Prev</button>
                    <span id="reservation-page-info" style="color:#6b7280;font-weight:700;">Page 1 of 1</span>
                    <button id="reservation-next" class="paginate-btn" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Next</button>
                </div>
            </section>
        </div>
    </main>

    <!-- Reservation Details Modal -->
    <div id="reservation-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;z-index:9998;"></div>
    <div id="reservation-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(920px,94vw);max-height:90vh;overflow:auto;display:none;z-index:9999;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1.1rem;font-weight:800;">Reservation Details</h3>
            <button id="reservation-modal-close" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <div id="reservation-modal-body" style="padding:20px 24px;"><!-- Populated via JS --></div>
        <div id="reservation-modal-actions" style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:12px;justify-content:flex-end;"></div>
    </div>

    <!-- Transaction Modal (Instant POS) -->
    <div id="transaction-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;z-index:10010;"></div>
    <div id="transaction-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(900px,94vw);max-height:90vh;overflow:auto;display:none;z-index:10011;">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:800;">Complete Reservation</h3>
            <button id="transaction-modal-close" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <div id="transaction-modal-body" style="padding:16px 20px;"><!-- Filled dynamically --></div>
        <div style="padding:14px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;">
            <button id="transaction-cancel-btn" style="padding:10px 14px;border-radius:8px;background:#6b7280;color:#fff;border:none;font-weight:700;">Cancel</button>
            <button id="transaction-pay-btn" disabled style="padding:10px 14px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Pay & Receipt</button>
        </div>
    </div>

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



        // Pagination state and helpers
        const __posResPageState = { page: 1, perPage: 10, search: '' };

        function getFilteredReservationCards() {
            const term = (__posResPageState.search || '').toLowerCase();
            const nodes = Array.from(document.querySelectorAll('#reservations-container > .reservation-card'));
            if (!term) return nodes;
            return nodes.filter(card => card.textContent.toLowerCase().includes(term));
        }

        function applyReservationPagination() {
            const all = getFilteredReservationCards();
            const total = all.length;
            const totalPages = Math.max(1, Math.ceil(total / __posResPageState.perPage));
            if (__posResPageState.page > totalPages) __posResPageState.page = totalPages;
            const start = (__posResPageState.page - 1) * __posResPageState.perPage;
            const end = start + __posResPageState.perPage;

            // First hide all cards
            document.querySelectorAll('#reservations-container > .reservation-card').forEach(c => c.style.display = 'none');
            // Show only the current slice
            all.slice(start, end).forEach(c => c.style.display = 'block');

            // Update pagination UI
            const info = document.getElementById('reservation-page-info');
            if (info) info.textContent = `Page ${__posResPageState.page} of ${totalPages}`;
            const prev = document.getElementById('reservation-prev');
            const next = document.getElementById('reservation-next');
            if (prev) prev.disabled = __posResPageState.page <= 1;
            if (next) next.disabled = __posResPageState.page >= totalPages;
        }

        // Hook up pagination buttons
        document.getElementById('reservation-prev')?.addEventListener('click', function() {
            if (__posResPageState.page > 1) { __posResPageState.page -= 1; applyReservationPagination(); }
        });
        document.getElementById('reservation-next')?.addEventListener('click', function() {
            __posResPageState.page += 1; applyReservationPagination();
        });

        // Search functionality (works with pagination)
        document.getElementById('reservation-search').addEventListener('input', function(e) {
            __posResPageState.search = e.target.value || '';
            __posResPageState.page = 1;
            applyReservationPagination();
        });

        // Clear search (reset and re-paginate)
        document.getElementById('clear-reservation-search').addEventListener('click', function() {
            document.getElementById('reservation-search').value = '';
            __posResPageState.search = '';
            __posResPageState.page = 1;
            applyReservationPagination();
        });

        // ==== Reservation Reports logic (ported) ====
        function updateReservationStatus(reservationId, status, options = { reload: true }) {
            const allowed = ['pending','completed','cancelled'];
            if (!allowed.includes(status)) { alert('Invalid status'); return Promise.reject(new Error('Invalid status')); }
            if (status !== 'completed' && !confirm(`Are you sure you want to change the status to ${status}?`)) return Promise.resolve({ cancelled: true });
            
            // Prepare the request body
            const requestBody = { status };
            
            // Add payment information if provided for completed status
            if (status === 'completed' && options.amount_paid !== undefined) {
                requestBody.amount_paid = options.amount_paid;
                requestBody.change_given = options.change_given || 0;
            }
            
            return fetch(`{{ route('pos.reservations.update-status', ['id' => 'RES_ID']) }}`.replace('RES_ID', reservationId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(requestBody)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                const card = document.querySelector(`.reservation-card[data-res-id="${reservationId}"]`);
                if (card) {
                    const pill = card.querySelector('.status-pill');
                    if (pill) {
                        const label = status.charAt(0).toUpperCase() + status.slice(1);
                        pill.textContent = label;
                        const styleMap = {
                            pending: 'background-color: #FEF3C7; color: #92400E;',
                            completed: 'background-color: #DCFCE7; color: #166534;',
                            cancelled: 'background-color: #FEE2E2; color: #991B1B;'
                        };
                        pill.setAttribute('style', `display:inline-block;padding:4px 12px;border-radius:9999px;${styleMap[status] || styleMap.pending}font-weight:500;font-size:0.9rem;`);
                    }
                    card.dataset.status = status;
                }
                renderReservationModalActions(reservationId, status);
                showNotification('Reservation status updated', 'success');
                if (options && options.reload !== false) {
                    setTimeout(() => { try { location.reload(); } catch(_) {} }, 500);
                }
                return data;
            })
            .catch(err => {
                console.error(err);
                showNotification('Failed to update reservation status', 'error');
                throw err;
            });
        }

        const modalEl = document.getElementById('reservation-modal');
        const modalOverlayEl = document.getElementById('reservation-modal-overlay');
        const modalBodyEl = document.getElementById('reservation-modal-body');
        const modalCloseEl = document.getElementById('reservation-modal-close');

        // Helper to show/hide modals with zoom + overlay fade animations
        function showModal(modal, overlay) {
            if (!modal || !overlay) return;
            // ensure overlay visible and play fade-in
            overlay.style.display = 'block';
            overlay.classList.remove('fade-out');
            // trigger reflow then add fade-in
            void overlay.offsetWidth;
            overlay.classList.add('fade-in');

            // show modal and animate zoom in
            modal.style.display = 'block';
            modal.classList.remove('zoom-exit');
            void modal.offsetWidth;
            modal.classList.add('zoom-enter');
        }

        function hideModal(modal, overlay) {
            if (!modal || !overlay) return;
            // play modal exit animation
            modal.classList.remove('zoom-enter');
            modal.classList.add('zoom-exit');

            // play overlay fade out
            overlay.classList.remove('fade-in');
            overlay.classList.add('fade-out');

            // after animations end, hide from layout
            const cleanup = (e) => {
                if (e && e.target !== modal && e.target !== overlay) return;
                try { modal.style.display = 'none'; } catch(_) {}
                try { overlay.style.display = 'none'; } catch(_) {}
                modal.classList.remove('zoom-exit');
                overlay.classList.remove('fade-out');
                modal.removeEventListener('animationend', cleanup);
                overlay.removeEventListener('animationend', cleanup);
            };
            modal.addEventListener('animationend', cleanup);
            overlay.addEventListener('animationend', cleanup);
            // fallback in case animationend doesn't fire
            setTimeout(()=> cleanup(), 420);
        }

        function openReservationModalFromCard(card) {
            const id = card.dataset.resNumber; // Use resNumber (reservation_id) instead of resId (internal id)
            const number = card.dataset.resNumber;
            const resDate = card.dataset.resDate;
            const name = card.dataset.customerName || 'N/A';
            const email = card.dataset.customerEmail || 'N/A';
            const phone = card.dataset.customerPhone || 'N/A';
            const pickupDate = card.dataset.pickupDate || 'TBD';
            const pickupTime = card.dataset.pickupTime || 'TBD';
            const status = card.dataset.status || 'pending';

            modalBodyEl.innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Reservation ID</p>
                        <p style=\"font-weight:600;\">${number}</p>
                    </div>
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Reservation Date</p>
                        <p style=\"font-weight:600;\">${resDate}</p>
                    </div>
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Customer Name</p>
                        <p style=\"font-weight:600;\">${name}</p>
                    </div>
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Email</p>
                        <p style=\"font-weight:600;\">${email}</p>
                    </div>
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Phone</p>
                        <p style=\"font-weight:600;\">${phone}</p>
                    </div>
                    <div>
                        <p style=\"color:#6B7280;font-size:0.9rem;\">Pickup</p>
                        <p style=\"font-weight:600;\">${pickupDate} ${pickupTime !== 'TBD' ? ('• ' + pickupTime) : ''}</p>
                    </div>
                </div>
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb;">
                    <h4 style="margin:0 0 8px 0;">Products</h4>
                    <div id="reservation-products" style="display:grid;gap:8px;">
                        <div class="loading-products" aria-live="polite">
                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                            <div style="font-size:0.95rem;color:#6B7280;">Loading products…</div>
                        </div>
                    </div>
                    <div id="reservation-total" style="margin-top:12px;text-align:right;font-weight:800;font-size:1rem;"></div>
                </div>
            `;

            const apiUrl = `{{ route('pos.api.reservations.show', ['id' => 'RES_ID']) }}`.replace('RES_ID', id);
            
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
                .then(r => {
                    if (!r.ok) {
                        return Promise.reject(new Error(`HTTP ${r.status}: ${r.statusText}`));
                    }
                    return r.json();
                })
                .then(data => {
                    const list = document.getElementById('reservation-products');
                    if (!list) {
                        return;
                    }
                    if (!data || !Array.isArray(data.items) || data.items.length === 0) {
                        list.innerHTML = '<div style="color:#6B7280;font-size:0.9rem;">No products recorded for this reservation.</div>';
                        return;
                    }
                    const itemsHtml = data.items.map(item => `
                        <div style=\"display:flex;justify-content:space-between;gap:12px;border:1px solid #cfd4ff;;border-radius:8px;padding:10px;\">
                            <div>
                                <div style=\"font-weight:600;\">${item.name}</div>
                                <div style=\"color:#6B7280;font-size:0.85rem;\">${item.brand || ''} ${item.color ? '• ' + item.color : ''} ${item.size ? '• Size ' + item.size : ''}</div>
                            </div>
                            <div style=\"text-align:right;\">x${item.quantity || 1}<br>₱${Number(item.price || 0).toLocaleString()}</div>
                        </div>
                    `).join('');
                    list.innerHTML = itemsHtml;
                    const totalEl = document.getElementById('reservation-total');
                    if (totalEl) {
                        const total = data.reservation && typeof data.reservation.total_amount !== 'undefined'
                            ? Number(data.reservation.total_amount)
                            : data.items.reduce((sum, i) => sum + (Number(i.price || 0) * (Number(i.quantity || 1))), 0);
                        totalEl.textContent = `Total: ₱${total.toLocaleString()}`;
                    }
                })
                .catch((error) => {
                    const list = document.getElementById('reservation-products');
                    list.innerHTML = `<div style="color:#DC2626;font-size:0.9rem;">Error loading products: ${error.message || 'Unknown error'}</div>`;
                });

            renderReservationModalActions(id, status);
            showModal(modalEl, modalOverlayEl);
        }

        function renderReservationModalActions(reservationId, status) {
            const actions = document.getElementById('reservation-modal-actions');
            if (!actions) return;
            if (status === 'pending') {
                actions.innerHTML = `
                    <button onclick=\"openTransactionModal('${reservationId}')\" style=\"min-width:120px;padding:10px 16px;border-radius:8px;background:#059669;color:#fff;border:none;font-weight:700;\">Complete</button>
                `;
            } else {
                actions.innerHTML = '';
            }
        }

        // Transaction Modal logic
        const txModal = document.getElementById('transaction-modal');
        const txOverlay = document.getElementById('transaction-modal-overlay');
        const txBody = document.getElementById('transaction-modal-body');
        const txClose = document.getElementById('transaction-modal-close');
        const txCancel = document.getElementById('transaction-cancel-btn');
        const txPay = document.getElementById('transaction-pay-btn');

        let txContext = { reservationId: null, total: 0 };

        function openTransactionModal(reservationId) {
            txContext = { reservationId, total: 0 };
            const txApiUrl = `{{ route('pos.api.reservations.show', ['id' => 'RES_ID']) }}`.replace('RES_ID', reservationId);
            
            fetch(txApiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => {
                    const items = data.items || [];
                    const total = data.reservation?.total_amount != null
                        ? Number(data.reservation.total_amount)
                        : items.reduce((s,i)=>s + (Number(i.price||0) * Number(i.quantity||1)),0);
                    txContext.total = total;

                    txBody.innerHTML = `
                        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;">
                            <div>
                                <h4 style=\"margin:0 0 8px 0;\">Products</h4>
                                <div style=\"display:grid;gap:8px;\">
                                    ${items.length ? items.map(i => `
                                        <div style=\"display:flex;justify-content:space-between;border:1px solid #f1f5f9;border-radius:8px;padding:10px;\">
                                            <div>
                                                <div style=\"font-weight:700;\">${i.name}</div>
                                                <div style=\"color:#6b7280;font-size:0.85rem;\">${i.brand||''} ${i.color?('• '+i.color):''} ${i.size?('• Size '+i.size):''}</div>
                                            </div>
                                            <div style=\"text-align:right;\">x${i.quantity||1}<br>₱${Number(i.price||0).toLocaleString()}</div>
                                        </div>
                                    `).join('') : '<div style=\"color:#6b7280;\">No items</div>'}
                                </div>
                            </div>
                            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
                                <div style="display:flex;justify-content:space-between;font-weight:800;margin-bottom:10px;">
                                    <span>Total</span>
                                    <span id="tx-total">₱ ${total.toLocaleString()}</span>
                                </div>
                                <label for="tx-payment" style="font-size:0.9rem;color:#374151;">Amount Paid</label>
                                <input id="tx-payment" type="text" placeholder="0.00" style="width:100%;margin:6px 0 8px 0;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">
                                <div style="display:flex;justify-content:space-between;font-weight:700;">
                                    <span>Change</span>
                                    <span id="tx-change">₱ 0</span>
                                </div>
                                <small style="display:block;color:#6b7280;margin-top:8px;">Enter amount equal or greater than total to enable Pay.</small>
                            </div>
                        </div>
                    `;

                    const input = txBody.querySelector('#tx-payment');
                    txPay.disabled = true;
                    input.addEventListener('input', () => {
                        const cleaned = input.value.replace(/[^0-9.]/g, '');
                        const parts = cleaned.split('.');
                        const normalized = parts.length > 1 ? parts[0] + '.' + parts[1].slice(0, 2) : parts[0];
                        if (input.value !== normalized) input.value = normalized;
                        const paid = parseFloat(normalized || '0');
                        const change = Math.max(0, paid - txContext.total);
                        txBody.querySelector('#tx-change').textContent = `₱ ${change.toLocaleString()}`;
                        txPay.disabled = !(paid >= txContext.total);
                    });

                    showModal(txModal, txOverlay);
                })
                .catch(() => {
                    alert('Unable to load reservation details.');
                });
        }

        function closeTransactionModal(){
            hideModal(txModal, txOverlay);
        }

        if (txClose) txClose.addEventListener('click', closeTransactionModal);
        if (txCancel) txCancel.addEventListener('click', closeTransactionModal);
        if (txOverlay) txOverlay.addEventListener('click', closeTransactionModal);

        if (txPay) txPay.addEventListener('click', function(){
            const paidRaw = (txBody.querySelector('#tx-payment')?.value || '').trim();
            const paid = parseFloat(paidRaw || '0');
            if (!(paid >= txContext.total)) return;

            // Build receipt preview similar to POS openReceiptPreview
            openReservationReceiptPreview({
                items: Array.isArray(txContext.items) ? txContext.items : Array.from(txBody.querySelectorAll('[style*="justify-content:space-between"][style*="padding:10px"]')).map(row => ({
                    name: row.querySelector('div div')?.textContent || 'Item',
                    meta: row.querySelector('div div + div')?.textContent || '',
                    quantity: parseInt((row.querySelector('div:last-child')?.textContent || '').match(/x(\d+)/)?.[1] || '1', 10),
                    price: Number((row.querySelector('div:last-child')?.textContent || '').replace(/[^0-9.]/g, '')) || 0
                })),
                summary: {
                    subtotal: (Array.isArray(txContext.items) ? txContext.items : []).reduce((s,i)=> s + (Number(i.price||0) * Number(i.quantity||1)), 0),
                    discountAmount: 0,
                    total: txContext.total
                },
                paymentAmount: paid,
                onConfirm: async () => {
                    const changeAmount = Math.max(0, paid - txContext.total);
                    try { 
                        await updateReservationStatus(txContext.reservationId, 'completed', { 
                            reload: false,
                            amount_paid: paid,
                            change_given: changeAmount
                        }); 
                    } catch (e) {
                        alert('Failed to complete reservation. Please try again.');
                        return;
                    }
                    // Print the preview content
                    const paper = document.getElementById('res-receipt-paper');
                    if (paper) {
                        const w = window.open('', '_blank', 'width=480,height=640');
                        w.document.write('<html><head><title>Receipt</title><style>body{font-family:-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif; padding:8px;} .receipt-paper{width:320px;margin:0 auto;border:1px dashed #e5e7eb;padding:12px;border-radius:8px;} .receipt-sep{border-top:1px dotted #9ca3af;margin:8px 0} table{width:100%;border-collapse:collapse} th,td{font-size:12px;text-align:left;padding:2px 0} th:last-child, td:last-child{text-align:right} th:nth-child(2), td:nth-child(2){text-align:center}</style></head><body>' + paper.outerHTML + '<script>window.onload=function(){window.print(); setTimeout(()=>window.close(), 300);}<\/script></body></html>');
                        w.document.close();
                    }
                    // Close modals and refresh
                    const modal = document.getElementById('res-receipt-modal');
                    if (modal) modal.remove();
                    closeTransactionModal();
                    setTimeout(() => { try { location.reload(); } catch(_) {} }, 600);
                }
            });
        });

        // Preview modal builder for reservation receipts
        function openReservationReceiptPreview({ items = [], summary = { subtotal: 0, discountAmount: 0, total: 0 }, paymentAmount = 0, onConfirm }) {
            let modal = document.getElementById('res-receipt-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'res-receipt-modal';
                modal.style.position = 'fixed';
                modal.style.inset = '0';
                modal.style.background = 'rgba(0,0,0,0.45)';
                modal.style.zIndex = '10050';
                modal.style.display = 'grid';
                modal.style.placeItems = 'center';
                modal.innerHTML = `
                    <style>
                        /* Receipt look & feel */
                        .receipt-paper{ width:320px; margin:0 auto; border:1px dashed #e5e7eb; padding:12px; border-radius:8px; background:#fff; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
                        .receipt-sep{ border-top:1px dotted #9ca3af; margin:8px 0; }
                        .receipt-paper table{ width:100%; border-collapse:collapse; }
                        .receipt-paper th, .receipt-paper td{ font-size:12px; padding:2px 0; }
                        .receipt-paper th:last-child, .receipt-paper td:last-child{ text-align:right; }
                        .receipt-paper th:nth-child(2), .receipt-paper td:nth-child(2){ text-align:center; }
                        .receipt-info-row{ display:flex; justify-content:space-between; font-size:12px; }
                        .receipt-row{ display:flex; justify-content:space-between; }
                        .receipt-totals .total{ font-weight:800; }
                    </style>
                    <div style="background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(460px,94vw);padding:14px;">
                        <div id="res-receipt-paper" class="receipt-paper">
                            <div style="text-align:center;margin-bottom:6px;">
                                <div style="font-weight:800;">SHOE VAULT BATANGAS</div>
                                <div style="color:#6b7280;font-size:12px;">Manghinao Proper Bauan, Batangas 4201<br>Tel.: +63 936 382 0087</div>
                                <div style="color:#6b7280;font-size:11px;">${new Date().toLocaleString()}</div>
                            </div>
                            <div class="receipt-sep"></div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;"><span>Cashier</span><span>{{ auth()->user()->name ?? '—' }}</span></div>
                            <div class="receipt-sep"></div>
                            <table>
                                <thead><tr><th>Name</th><th>Qty</th><th>Price</th></tr></thead>
                                <tbody id="res-receipt-items"></tbody>
                            </table>
                            <div class="receipt-sep"></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Subtotal</span><span>₱ ${Number(summary.subtotal||0).toLocaleString()}</span></div>
                            ${Number(summary.discountAmount||0) > 0 ? `<div style="display:flex;justify-content:space-between;font-size:13px;"><span>Discount</span><span>- ₱ ${Number(summary.discountAmount).toLocaleString()}</span></div>` : ''}
                            <div style="display:flex;justify-content:space-between;font-weight:800;font-size:14px;"><span>Total</span><span>₱ ${Number(summary.total||0).toLocaleString()}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Cash</span><span>₱ ${Number(paymentAmount||0).toLocaleString()}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Change</span><span>₱ ${Math.max(0, Number(paymentAmount||0) - Number(summary.total||0)).toLocaleString()}</span></div>
                            <div class="receipt-sep"></div>
                            <div style="text-align:center;color:#6b7280;font-size:11px;">THANK YOU! — Glad to see you again!</div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;">
                            <button id="res-receipt-cancel" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;font-weight:700;">Cancel</button>
                            <button id="res-receipt-print" style="padding:8px 12px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Print</button>
                        </div>
                    </div>`;
                document.body.appendChild(modal);
            }
            const tbody = modal.querySelector('#res-receipt-items');
            tbody.innerHTML = (items||[]).map(it => `<tr><td>${it.name || ''} ${it.size ? `(Size ${it.size})` : ''}</td><td style="text-align:center;">${Number(it.quantity||1)}</td><td style="text-align:right;">₱ ${(Number(it.price||0)*Number(it.quantity||1)).toLocaleString()}</td></tr>`).join('');
            modal.querySelector('#res-receipt-cancel').onclick = ()=> modal.remove();
            modal.querySelector('#res-receipt-print').onclick = ()=> { if (typeof onConfirm === 'function') onConfirm(); };
        }

        function closeReservationModal() {
            hideModal(modalEl, modalOverlayEl);
        }
        if (modalCloseEl) modalCloseEl.addEventListener('click', closeReservationModal);
        if (modalOverlayEl) modalOverlayEl.addEventListener('click', closeReservationModal);

        // Bind View buttons
        document.querySelectorAll('.view-reservation-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const card = this.closest('.reservation-card');
                if (card) openReservationModalFromCard(card);
            });
        });

        // Mark reservation as complete
        function markAsComplete(reservationId, button) {
            if (confirm('Are you sure you want to mark this reservation as complete?')) {
                // Disable the button immediately to prevent double clicks
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                console.log('Attempting to complete reservation:', reservationId);
                
                // Make AJAX call to update reservation status
                fetch('/pos/reservations/' + reservationId + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: 'completed'
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text().then(text => {
                        console.log('Raw response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse JSON:', e);
                            throw new Error('Invalid JSON response: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed response data:', data);
                    if (data.success) {
                        // Find the status badge in the same row
                        const row = button.closest('tr');
                        const statusBadge = row.querySelector('.status-badge');
                        
                        // Update status to completed
                        statusBadge.className = 'status-badge status-completed';
                        statusBadge.textContent = 'Completed';
                        
                        // Replace button with completed indicator
                        button.outerHTML = '<span class="completed-indicator" title="Completed"><i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i></span>';
                        
                        // Show success message
                        showNotification('Reservation ' + reservationId + ' marked as complete!', 'success');
                        
                        // Remove auto-refresh to see any errors - commented out for debugging
                        // setTimeout(() => {
                        //     location.reload();
                        // }, 2000);
                    } else {
                        console.error('Server returned error:', data);
                        alert('Error: ' + (data.message || 'Unknown error'));
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-check"></i>';
                    }
                })
                .catch(error => {
                    console.error('Complete error details:', error);
                    alert('An error occurred while updating the reservation: ' + error.message);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i>';
                });
            }
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Add notification styles
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.75rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                gap: 0.5rem;
                z-index: 9999;
                animation: slideInRight 0.3s ease-out;
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            // Rebind view buttons on load
            document.querySelectorAll('.view-reservation-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    const card = this.closest('.reservation-card');
                    if (card) openReservationModalFromCard(card);
                });
            });
            initNotifications();
        });

        // ===== Header notifications wiring =====
        function initNotifications() {
            console.log('🎯 POS Reservations: Initializing notifications...');
            
            // Initialize NotificationManager after script loads - let it handle all notification functionality
            setTimeout(() => {
                console.log('🔍 Checking for NotificationManager...', typeof NotificationManager);
                if (typeof NotificationManager !== 'undefined') {
                    console.log('✅ NotificationManager found, initializing...');
                    window.notificationManager = new NotificationManager();
                    window.notificationManager.init('{{ auth()->user()->role ?? "cashier" }}').catch(error => {
                        console.error('❌ NotificationManager init failed:', error);
                    });
                } else {
                    console.log('⏳ NotificationManager not ready, retrying...');
                    // Retry after a short delay
                    setTimeout(() => {
                        console.log('🔍 Retry: Checking for NotificationManager...', typeof NotificationManager);
                        if (typeof NotificationManager !== 'undefined') {
                            console.log('✅ NotificationManager found on retry, initializing...');
                            window.notificationManager = new NotificationManager();
                            window.notificationManager.init('{{ auth()->user()->role ?? "cashier" }}').catch(error => {
                                console.error('❌ NotificationManager init failed on retry:', error);
                            });
                        } else {
                            // Fallback: basic dropdown toggle if NotificationManager fails to load
                            console.warn('⚠️ NotificationManager not found after retry, using fallback dropdown');
                            const wrappers = document.querySelectorAll('.notification-wrapper');
                            console.log('🔍 Found notification wrappers:', wrappers.length);
                            wrappers.forEach(wrapper => {
                                const bell = wrapper.querySelector('.notification-bell');
                                if (!bell) return;
                                bell.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    console.log('🔔 Fallback: Bell clicked, toggling dropdown');
                                    wrapper.classList.toggle('open');
                                });
                            });
                            document.addEventListener('click', () => {
                                document.querySelectorAll('.notification-wrapper.open').forEach(w => {
                                    console.log('🚪 Fallback: Closing dropdown on outside click');
                                    w.classList.remove('open');
                                });
                            });
                        }
                    }, 500);
                }
            }, 200);
        }

        // --- Section loading & slow entry animation (visual only) ---
        (function(){
            function makeSkeleton(section) {
                const overlay = document.createElement('div');
                overlay.className = 'section-loading-overlay';
                overlay.setAttribute('aria-hidden','true');
                const t = document.createElement('div'); t.className = 'skeleton title'; overlay.appendChild(t);
                for (let i=0;i<2;i++){ const l=document.createElement('div'); l.className='skeleton line'; overlay.appendChild(l); }
                const cardCount = 3;
                for (let i=0;i<cardCount;i++){ const c=document.createElement('div'); c.className='skeleton card'; overlay.appendChild(c); }
                overlay.style.pointerEvents = 'none';
                overlay.style.opacity = '1';
                return overlay;
            }

            function playSection(section){
                if (!section) return;
                if (getComputedStyle(section).position === 'static') section.style.position = 'relative';
                const overlay = makeSkeleton(section);
                section.appendChild(overlay);
                const delay = 550 + Math.random()*300;
                setTimeout(()=>{
                    overlay.style.transition = 'opacity 280ms ease';
                    overlay.style.opacity = '0';
                    setTimeout(()=> overlay.remove(), 320);
                    const children = Array.from(section.querySelectorAll(':scope > *'))
                        .filter(el => !el.classList.contains('section-loading-overlay'));
                    children.forEach((el, i)=>{
                        el.classList.add('animate-entry');
                        setTimeout(()=> el.classList.add('play'), 120*i + 40);
                    });
                }, delay);
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ()=>{
                document.querySelectorAll('.reservation-section, .reservation-cards, .reservation-table-wrapper').forEach(playSection);
            });
            else document.querySelectorAll('.reservation-section, .reservation-cards, .reservation-table-wrapper').forEach(playSection);
        })();
    </script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @include('partials.mobile-blocker')
</body>
</html>