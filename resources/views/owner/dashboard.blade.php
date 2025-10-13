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
            <a href="{{ route('owner.reports') }}" class="nav-link">
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
        

        <!-- Dashboard Section -->
        <section id="inventory-dashboard" class="content-section active">
            <div class="odash-wrap">
                <!-- Top 4 KPI Cards -->
                <div class="odash-row-top">
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon"><i class="fas fa-peso-sign"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-revenue">₱0.00</div>
                                <div class="odash-kpi-label">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#2563eb,#60a5fa)"><i class="fas fa-cube"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-sold">0</div>
                                <div class="odash-kpi-label">Total Products Sold</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#16a34a,#34d399)"><i class="fas fa-check-circle"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-resv-completed">0</div>
                                <div class="odash-kpi-label">Completed Reservations</div>
                            </div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-kpi">
                            <div class="odash-kpi-icon" style="background: linear-gradient(135deg,#ef4444,#f97316)"><i class="fas fa-ban"></i></div>
                            <div class="odash-kpi-meta">
                                <div class="odash-kpi-value" id="odash-kpi-resv-cancelled">0</div>
                                <div class="odash-kpi-label">Cancelled Reservations</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Row: Line Chart + Latest POS Transactions -->
                <div class="odash-row-mid">
                    <div class="odash-card odash-line-card">
                        <div class="odash-card-header">
                            <div class="odash-title">Sales Trend</div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <div class="odash-legend">
                                    <span class="dot" style="background:#2563eb"></span><span class="sub">Physical</span>
                                    <span class="dot" style="background:#10b981"></span><span class="sub">Reservation</span>
                                </div>
                                <select id="odash-line-range" class="odash-select">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Annually</option>
                                </select>
                            </div>
                        </div>
                        <div class="odash-chart-shell">
                            <canvas id="odash-line"></canvas>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-card-header">
                            <div class="odash-title">Latest POS Transactions</div>
                        </div>
                        <div id="odash-latest-pos" class="odash-list"></div>
                    </div>
                </div>

                <!-- Bottom Row: Bar Chart + Stock Level list, and Low Stock Ranking -->
                <div class="odash-row-bottom">
                    <div class="odash-bottom-left">
                        <div class="odash-card">
                            <div class="odash-card-header">
                                <div class="odash-title">Popular Products</div>
                                <select id="odash-bar-filter" class="odash-select">
                                    <option value="men">Men</option>
                                    <option value="women">Women</option>
                                    <option value="accessories">Accessories</option>
                                </select>
                            </div>
                            <div class="odash-chart-shell" style="min-height:260px;">
                                <canvas id="odash-bar"></canvas>
                            </div>
                        </div>
                        <div class="odash-card">
                            <div class="odash-card-header">
                                <div class="odash-title">Stock Levels</div>
                            </div>
                            <div id="odash-stock-levels" class="odash-list"></div>
                        </div>
                    </div>
                    <div class="odash-card">
                        <div class="odash-card-header">
                            <div class="odash-title">Lowest Stock (%)</div>
                        </div>
                        <div id="odash-lowest-stock" class="odash-list"></div>
                    </div>
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
    document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

</body>
</html>
