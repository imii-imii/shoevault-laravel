<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Owner Dashboard - ShoeVault Batangas</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Owner CSS -->
    <link href="{{ asset('css/owner.css') }}" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text">
            <h2>ShoeVault Batangas</h2>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item active" data-section="inventory-dashboard">
            <a href="#" class="nav-link" onclick="showSection('inventory-dashboard'); return false;">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item" data-section="reports">
            <a href="#" class="nav-link" onclick="showSection('reports-sales-history'); return false;">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="nav-item" data-section="settings">
            <a href="#" class="nav-link" onclick="showSection('settings'); return false;">
                <i class="fas fa-cog"></i>
                <span>Master Controls</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <img src="{{ asset('images/profile.png') }}" alt="Owner">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name ?? 'Owner' }}</h4>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
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
            <h1 class="main-title" id="page-title">Dashboard</h1>
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
        </div>
    </header>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Reports: Sales History -->
        <section id="reports-sales-history" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-receipt"></i> Sales History</h2>
                <div style="margin-left:auto;">
                    <select id="reports-nav-dropdown-1" class="filter-select filter-sm" style="min-width: 180px;">
                        <option value="reports-sales-history" selected>Sales History</option>
                        <option value="reports-reservation-logs">Reservation Logs</option>
                        <option value="reports-supply-logs">Supply Logs</option>
                        <option value="reports-inventory-overview">Inventory Overview</option>
                    </select>
                </div>
            </div>
            
            <!-- KPI Cards Row -->
            <div class="dashboard-kpi-row gradient-row" style="display: flex; gap: 24px; margin-bottom: 32px;">
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="sales-kpi-transactions">{{ $dashboardData['totalSales'] ?? 0 }}</div>
                            <div class="kpi-label">Total No. of Transactions</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="sales-kpi-revenue">₱{{ number_format($dashboardData['todaySales'] ?? 0, 2) }}</div>
                            <div class="kpi-label">Total Revenue</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="sales-kpi-quantity">{{ $dashboardData['totalQuantitySold'] ?? 0 }}</div>
                            <div class="kpi-label">Total Quantity Sold</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="dashboard-charts-row" style="display: flex; gap: 24px;">
                <div class="card-lg" style="flex:2; display:flex; flex-direction:column; min-height: 340px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div class="card-title">Sales Report</div>
                        <select id="sales-report-filter" class="filter-select filter-sm" style="min-width: 140px;">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <canvas id="sales-report-chart" style="margin-top:18px;"></canvas>
                </div>
                <div class="card-lg" style="flex:1; overflow-y:auto;">
                    <div class="card-title">Top Selling Products</div>
                    <div id="top-selling-products-list" class="most-sold-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="sales-search" placeholder="Search sales..." class="search-input" style="flex:1; min-width:180px;">
                <select id="sales-period-filter" class="filter-select">
                    <option value="">All Periods</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
                <select id="sales-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date (Newest)</option>
                    <option value="date-asc">Date (Oldest)</option>
                    <option value="amount-desc">Amount (High-Low)</option>
                    <option value="amount-asc">Amount (Low-High)</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table id="sales-history-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer Name</th>
                            <th>Products</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="sales-history-tbody">
                        <!-- Sales data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Reports: Reservation Logs -->
        <section id="reports-reservation-logs" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-calendar-alt"></i> Reservation Logs</h2>
                <div style="margin-left:auto;">
                    <select id="reports-nav-dropdown-2" class="filter-select filter-sm" style="min-width: 180px;">
                        <option value="reports-sales-history">Sales History</option>
                        <option value="reports-reservation-logs" selected>Reservation Logs</option>
                        <option value="reports-supply-logs">Supply Logs</option>
                        <option value="reports-inventory-overview">Inventory Overview</option>
                    </select>
                </div>
            </div>

            <!-- KPI Cards Row -->
            <div class="dashboard-kpi-row gradient-row" style="display: flex; gap: 24px; margin-bottom: 32px;">
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="reservation-kpi-total">{{ $dashboardData['activeReservations'] ?? 0 }}</div>
                            <div class="kpi-label">Total Reservations</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="reservation-kpi-completed">0</div>
                            <div class="kpi-label">Completed Reservations</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="reservation-kpi-cancelled">0</div>
                            <div class="kpi-label">Cancelled Reservations</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="dashboard-charts-row" style="display: flex; gap: 24px;">
                <div class="card-lg" style="flex:2; display:flex; flex-direction:column; min-height: 340px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div class="card-title">Reservation Report</div>
                        <select id="reservation-report-filter" class="filter-select filter-sm" style="min-width: 140px;">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <canvas id="reservation-report-chart" style="margin-top:18px;"></canvas>
                </div>
                <div class="card-lg" style="flex:1; overflow-y:auto;">
                    <div class="card-title">Popular Reserved Products</div>
                    <div id="popular-reserved-products-list" class="most-sold-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="reservation-search" placeholder="Search reservations..." class="search-input" style="flex:1; min-width:180px;">
                <select id="reservation-status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="reservation-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date Modified (Newest)</option>
                    <option value="date-asc">Date Modified (Oldest)</option>
                    <option value="id-desc">Reservation ID (Descending)</option>
                    <option value="id-asc">Reservation ID (Ascending)</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table id="reservation-logs-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Reserved Product(s)</th>
                            <th>Date Reserved</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="reservation-logs-tbody">
                        <!-- Reservation logs will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Reports: Supply Logs -->
        <section id="reports-supply-logs" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-truck"></i> Supply Logs</h2>
                <div style="margin-left:auto;">
                    <select id="reports-nav-dropdown-3" class="filter-select filter-sm" style="min-width: 180px;">
                        <option value="reports-sales-history">Sales History</option>
                        <option value="reports-reservation-logs">Reservation Logs</option>
                        <option value="reports-supply-logs" selected>Supply Logs</option>
                        <option value="reports-inventory-overview">Inventory Overview</option>
                    </select>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="supply-search" placeholder="Search supply logs..." class="search-input" style="flex:1; min-width:180px;">
                <select id="supply-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="date-desc">Date Modified (Newest)</option>
                    <option value="date-asc">Date Modified (Oldest)</option>
                    <option value="id-desc">Log ID (Descending)</option>
                    <option value="id-asc">Log ID (Ascending)</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table id="supply-logs-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Supplier Name</th>
                            <th>Contact</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Country</th>
                            <th>Last Update</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="supply-logs-tbody">
                        <!-- Supply logs will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Reports: Inventory Overview -->
        <section id="reports-inventory-overview" class="content-section" style="display:none;">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-boxes"></i> Inventory Overview</h2>
                <div style="margin-left:auto;">
                    <select id="reports-nav-dropdown-4" class="filter-select filter-sm" style="min-width: 180px;">
                        <option value="reports-sales-history">Sales History</option>
                        <option value="reports-reservation-logs">Reservation Logs</option>
                        <option value="reports-supply-logs">Supply Logs</option>
                        <option value="reports-inventory-overview" selected>Inventory Overview</option>
                    </select>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="filters" style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                <input type="text" id="inventory-search" placeholder="Search inventory..." class="search-input" style="flex:1; min-width:180px;">
                <select id="inventory-brand-filter" class="filter-select">
                    <option value="">All Brands</option>
                    <option value="Nike">Nike</option>
                    <option value="Adidas">Adidas</option>
                    <option value="Puma">Puma</option>
                    <option value="Converse">Converse</option>
                </select>
                <select id="inventory-category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="men">Men's Shoes</option>
                    <option value="women">Women's Shoes</option>
                    <option value="accessories">Accessories</option>
                </select>
                <select id="inventory-sort-filter" class="filter-select" style="min-width:180px;">
                    <option value="name-asc">Product Name (A-Z)</option>
                    <option value="name-desc">Product Name (Z-A)</option>
                    <option value="brand-asc">Brand (A-Z)</option>
                    <option value="brand-desc">Brand (Z-A)</option>
                    <option value="stock-desc">Stock (High-Low)</option>
                    <option value="stock-asc">Stock (Low-High)</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table id="inventory-overview-table" class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Colors</th>
                            <th>Sizes</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-overview-tbody">
                        <!-- Inventory overview will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Dashboard Section -->
        <section id="inventory-dashboard" class="content-section active">
            <div class="section-header with-left-actions">
                <h2><i class="fas fa-warehouse"></i> Inventory Dashboard</h2>
                <div style="display:flex; gap:10px; align-items:center; justify-content:space-between; width:100%;">
                    <div style="margin-left:auto;">
                        <select id="dashboard-scope" class="filter-select filter-sm" style="min-width: 40px;">
                            <option value="inventory" selected>Inventory</option>
                            <option value="pos">POS</option>
                            <option value="reservation">Reservations</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Top KPI Cards Row -->
            <div class="dashboard-kpi-row gradient-row" style="display: flex; gap: 24px; margin-bottom: 32px;">
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="kpi-total-stocks">{{ $dashboardData['totalStock'] ?? 0 }}</div>
                            <div class="kpi-label">Total Stocks</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="kpi-inventory-items">{{ $dashboardData['totalProducts'] ?? 0 }}</div>
                            <div class="kpi-label">No. of Inventory Items</div>
                        </div>
                    </div>
                </div>
                <div class="kpi-card gradient-card" style="flex:1;">
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-value" id="kpi-products-sold">{{ $dashboardData['todaySales'] ?? 0 }}</div>
                            <div class="kpi-label">Today's Sales</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="dashboard-charts-row" style="display: flex; gap: 24px;">
                <div class="card-lg" style="flex:2;">
                    <div class="card-title">Product Stocks by Brand</div>
                    <canvas id="chart-stocks-brand"></canvas>
                </div>
                <div class="card-lg" style="flex:1;">
                    <div class="card-title">Products Sold by Category</div>
                    <canvas id="chart-sold-category"></canvas>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-cog"></i> Master Controls</h2>
            </div>
            <div class="settings-container">
                <div class="settings-tabs" role="tablist">
                    <button class="settings-tab active" data-tab="user-accounts" role="tab">
                        <i class="fas fa-users"></i> User Account Management
                    </button>
                    <button class="settings-tab" data-tab="profile" role="tab">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <button class="settings-tab" data-tab="security" role="tab">
                        <i class="fas fa-shield-alt"></i> Security
                    </button>
                    <button class="settings-tab" data-tab="preferences" role="tab">
                        <i class="fas fa-sliders-h"></i> Preferences
                    </button>
                    <button class="settings-tab" data-tab="notifications" role="tab">
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                    <button class="settings-tab" data-tab="system" role="tab">
                        <i class="fas fa-tools"></i> System
                    </button>
                </div>

                <div class="settings-panels">
                    <!-- User Account Management -->
                    <div class="settings-panel active" id="settings-panel-user-accounts">
                        <div style="display:flex; gap:12px; align-items:center; margin-bottom:18px;">
                            <input type="text" id="user-search" placeholder="Search users..." class="search-input" style="flex:1; min-width:180px;">
                            <button class="btn btn-primary" id="add-user-btn">
                                <i class="fas fa-user-plus"></i> Add User
                            </button>
                        </div>
                        <div id="user-list" style="display:flex; flex-direction:column; gap:16px;">
                            <!-- User cards will be rendered here by JavaScript -->
                        </div>
                    </div>

                    <!-- Profile -->
                    <div class="settings-panel" id="settings-panel-profile">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="card-title">
                                    <i class="fas fa-id-card"></i> User Information
                                </div>
                                <div class="avatar-row">
                                    <img id="settings-avatar-preview" src="{{ asset('images/profile.png') }}" alt="Avatar" class="avatar-preview">
                                    <div class="avatar-actions">
                                        <input type="file" id="settings-avatar" accept="image/*" hidden>
                                        <button class="btn btn-secondary btn-sm" id="settings-avatar-btn">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                        <button class="btn btn-danger btn-sm" id="settings-avatar-remove">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="settings-name">Full Name</label>
                                        <input id="settings-name" type="text" placeholder="Your name" value="{{ auth()->user()->name ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-username">Username</label>
                                        <input id="settings-username" type="text" placeholder="Username" value="{{ auth()->user()->username ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-email">Email</label>
                                        <input id="settings-email" type="email" placeholder="name@example.com" value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-phone">Phone</label>
                                        <input id="settings-phone" type="tel" placeholder="+63 900 000 0000">
                                    </div>
                                    <div class="form-group full">
                                        <label for="settings-bio">Bio</label>
                                        <textarea id="settings-bio" rows="3" placeholder="Tell us about yourself..."></textarea>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn btn-primary" id="settings-profile-save">
                                        <i class="fas fa-save"></i> Save Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="settings-panel" id="settings-panel-security">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="card-title">
                                    <i class="fas fa-key"></i> Change Password
                                </div>
                                <form id="change-password-form">
                                    @csrf
                                    <div class="form-grid">
                                        <div class="form-group full">
                                            <label for="current-password">Current Password</label>
                                            <input id="current-password" type="password" placeholder="Enter current password">
                                        </div>
                                        <div class="form-group">
                                            <label for="new-password">New Password</label>
                                            <input id="new-password" type="password" placeholder="Enter new password">
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm-password">Confirm Password</label>
                                            <input id="confirm-password" type="password" placeholder="Confirm new password">
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Other settings panels can be added here -->
                    <div class="settings-panel" id="settings-panel-preferences">
                        <div class="settings-card">
                            <div class="card-title">
                                <i class="fas fa-sliders-h"></i> System Preferences
                            </div>
                            <p>Preferences settings will be implemented here.</p>
                        </div>
                    </div>

                    <div class="settings-panel" id="settings-panel-notifications">
                        <div class="settings-card">
                            <div class="card-title">
                                <i class="fas fa-bell"></i> Notification Settings
                            </div>
                            <p>Notification settings will be implemented here.</p>
                        </div>
                    </div>

                    <div class="settings-panel" id="settings-panel-system">
                        <div class="settings-card">
                            <div class="card-title">
                                <i class="fas fa-tools"></i> System Configuration
                            </div>
                            <p>System configuration options will be implemented here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Add User Modal -->
<div id="add-user-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-add-user-modal">&times;</span>
        <h3>Add New User</h3>
        <form id="add-user-form">
            @csrf
            <input type="text" id="add-user-fullname" placeholder="Full Name" required>
            <input type="text" id="add-user-username" placeholder="Username" required>
            <input type="email" id="add-user-email" placeholder="Email" required>
            <select id="add-user-role" required>
                <option value="">Select Role</option>
                <option value="manager">Manager</option>
                <option value="cashier">Cashier</option>
                <option value="owner">Owner</option>
            </select>
            <input type="password" id="add-user-password" placeholder="Password" required>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Include Laravel's CSRF token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="{{ asset('js/owner.js') }}"></script>
<script>
// Pass Laravel data to JavaScript
window.laravelData = {
    dashboardData: @json($dashboardData ?? []),
    user: @json(auth()->user()),
    routes: {
        salesHistory: '{{ route("owner.sales-history") }}',
        reservationLogs: '{{ route("owner.reservation-logs") }}',
        supplyLogs: '{{ route("owner.supply-logs") }}',
        inventoryOverview: '{{ route("owner.inventory-overview") }}',
        settings: '{{ route("owner.settings") }}'
    }
};

// Initialize dashboard when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    updateDateTime();
    setInterval(updateDateTime, 1000); // Update time every second
});

function updateDateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString();
    document.getElementById('current-date').textContent = now.toLocaleDateString();
}
</script>

</body>
</html>
