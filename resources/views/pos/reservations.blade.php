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
            background: rgba(255, 255, 255, 0.1);
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

        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md);
            background: rgba(220, 38, 38, 0.2);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(220, 38, 38, 0.4);
            border-radius: var(--radius-lg);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-decoration: none;
        }

        .logout-btn:hover {
            background: rgba(220, 38, 38, 0.4);
            color: var(--white);
            border-color: rgba(220, 38, 38, 0.6);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

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
            width: calc(100vw - var(--sidebar-width));
            max-width: 100vw;
        }
        .reservation-full .reservation-section {
            margin: 20px;
            width: 98vw;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .reservation-full .reservation-cards {
            width: 100%;
            justify-content: center;
        }
        .reservation-full .reservation-table-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
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
            margin: 20px;
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
                        <div class="card-value" id="card-expiring-soon">5</div>
                    </div>
                    <div class="reservation-card expiring-today">
                        <div class="card-title">Expiring Today</div>
                        <div class="card-value" id="card-expiring-today">2</div>
                    </div>
                    <div class="reservation-card total-reservations">
                        <div class="card-title">Total Reservations</div>
                        <div class="card-value" id="card-total-reservations">18</div>
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
                <!-- Reservation Table -->
                <div class="reservation-table-wrapper">
                    <table class="reservation-table">
                        <thead>
                            <tr>
                                <th>ReservationID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Reserved Product(s)</th>
                                <th>Date Reserved</th>
                                <th>Pickup Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="reservation-table-body">
                            <!-- Pending Reservations Only -->
                            <tr>
                                <td>R-1001</td>
                                <td>John Doe</td>
                                <td>john@example.com</td>
                                <td>09171234567</td>
                                <td>Nike Air Max 270, Adidas Ultraboost 22</td>
                                <td>2025-09-01</td>
                                <td>2025-09-06</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="complete-btn" onclick="markAsComplete('R-1001', this)" title="Mark as Complete">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>R-1004</td>
                                <td>Sarah Johnson</td>
                                <td>sarah@example.com</td>
                                <td>09201234567</td>
                                <td>Vans Old Skool</td>
                                <td>2025-10-10</td>
                                <td>2025-10-15</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="complete-btn" onclick="markAsComplete('R-1004', this)" title="Mark as Complete">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>R-1005</td>
                                <td>Michael Brown</td>
                                <td>michael@example.com</td>
                                <td>09211234567</td>
                                <td>Adidas Stan Smith</td>
                                <td>2025-10-11</td>
                                <td>2025-10-16</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="complete-btn" onclick="markAsComplete('R-1005', this)" title="Mark as Complete">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
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



        // Search functionality
        document.getElementById('reservation-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#reservation-table-body tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        match = true;
                    }
                });
                
                if (match || searchTerm === '') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Clear search
        document.getElementById('clear-reservation-search').addEventListener('click', function() {
            document.getElementById('reservation-search').value = '';
            document.querySelectorAll('#reservation-table-body tr').forEach(row => {
                row.style.display = '';
            });
        });

        // Mark reservation as complete
        function markAsComplete(reservationId, button) {
            if (confirm('Are you sure you want to mark this reservation as complete?')) {
                // Disable the button immediately to prevent double clicks
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Here you would typically make an AJAX call to your Laravel backend
                // For now, we'll simulate the process
                setTimeout(() => {
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
                }, 1000);
                
                // In a real application, you would make an AJAX call like this:
                /*
                fetch('/pos/reservations/' + reservationId + '/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        reservation_id: reservationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI as above
                    } else {
                        alert('Error: ' + data.message);
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-check"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the reservation.');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i>';
                });
                */
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
            initNotifications();
        });

        // ===== Header notifications wiring =====
        function initNotifications() {
            const wrappers = document.querySelectorAll('.notification-wrapper');
            wrappers.forEach(wrapper => {
                const bell = wrapper.querySelector('.notification-bell');
                if (!bell) return;
                bell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    wrapper.classList.toggle('open');
                });
            });
            document.addEventListener('click', () => {
                document.querySelectorAll('.notification-wrapper.open').forEach(w => w.classList.remove('open'));
            });
        }
    </script>
</body>
</html>
