@extends('layouts.app')

@section('title', 'Settings - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
@endpush

@section('content')
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
        <div class="logo-text">
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="{{ route('inventory.dashboard') }}" class="nav-link">
                <i class="fas fa-box-open"></i>
                <span>Add Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.suppliers') }}" class="nav-link">
                <i class="fas fa-people-carry-box"></i>
                <span>Supplier</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.reservation-reports') }}" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Reservation Reports</span>
            </a>
        </li>
        <li class="nav-item active">
            <a href="{{ route('inventory.settings') }}" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <img src="{{ asset('assets/images/profile.png') }}" alt="Manager">
            </div>
            <div class="user-details">
                <h4>{{ auth()->user()->name }}</h4>
                <span>{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn" style="background: none; border: none; color: inherit; width: 100%; text-align: left;">
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
            <h1 class="main-title">Settings</h1>
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
        <!-- Settings Section -->
        <section id="settings" class="content-section active">
            <div class="section-header">
                <h2><i class="fas fa-cog"></i> Settings</h2>
            </div>
            <div class="settings-container">
                <div class="settings-tabs" role="tablist">
                    <button class="settings-tab active" data-tab="profile" role="tab"><i class="fas fa-user"></i> Profile</button>
                    <button class="settings-tab" data-tab="security" role="tab"><i class="fas fa-shield-alt"></i> Security</button>
                    <button class="settings-tab" data-tab="preferences" role="tab"><i class="fas fa-sliders-h"></i> Preferences</button>
                    <button class="settings-tab" data-tab="notifications" role="tab"><i class="fas fa-bell"></i> Notifications</button>
                    <button class="settings-tab" data-tab="system" role="tab"><i class="fas fa-tools"></i> System</button>
                </div>

                <div class="settings-panels">
                    <!-- Profile -->
                    <div class="settings-panel active" id="settings-panel-profile">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="card-title"><i class="fas fa-id-card"></i> User Information</div>
                                <div class="avatar-row">
                                    <img id="settings-avatar-preview" src="{{ asset('assets/images/profile.png') }}" alt="Avatar" class="avatar-preview">
                                    <div class="avatar-actions">
                                        <input type="file" id="settings-avatar" accept="image/*" hidden>
                                        <button class="btn btn-secondary btn-sm" id="settings-avatar-btn"><i class="fas fa-upload"></i> Upload</button>
                                        <button class="btn btn-danger btn-sm" id="settings-avatar-remove"><i class="fas fa-trash"></i> Remove</button>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="settings-name">Full Name</label>
                                        <input id="settings-name" type="text" placeholder="Your name" value="{{ auth()->user()->name }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-username">Username</label>
                                        <input id="settings-username" type="text" placeholder="Username" value="{{ auth()->user()->username }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-email">Email</label>
                                        <input id="settings-email" type="email" placeholder="name@example.com" value="{{ auth()->user()->email }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-phone">Phone</label>
                                        <input id="settings-phone" type="tel" placeholder="+63 900 000 0000" value="{{ auth()->user()->phone ?? '' }}">
                                    </div>
                                    <div class="form-group full">
                                        <label for="settings-bio">Bio</label>
                                        <textarea id="settings-bio" rows="3" placeholder="Tell us about yourself...">{{ auth()->user()->bio ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn btn-primary" id="settings-profile-save"><i class="fas fa-save"></i> Save Profile</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="settings-panel" id="settings-panel-security">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-lock"></i> Change Password</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="settings-current-password">Current Password</label>
                                    <input id="settings-current-password" type="password" placeholder="Current password">
                                </div>
                                <div class="form-group">
                                    <label for="settings-new-password">New Password</label>
                                    <input id="settings-new-password" type="password" placeholder="New password">
                                </div>
                                <div class="form-group">
                                    <label for="settings-confirm-password">Confirm Password</label>
                                    <input id="settings-confirm-password" type="password" placeholder="Confirm new password">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-primary" id="settings-password-save"><i class="fas fa-key"></i> Update Password</button>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <div class="settings-panel" id="settings-panel-preferences">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="card-title"><i class="fas fa-palette"></i> Appearance</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="settings-theme">Theme</label>
                                        <select id="settings-theme">
                                            <option value="light">Light</option>
                                            <option value="dark">Dark</option>
                                            <option value="system">System</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-accent">Accent Color</label>
                                        <input id="settings-accent" type="color" value="#2a6aff">
                                    </div>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="card-title"><i class="fas fa-language"></i> Localization</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="settings-language">Language</label>
                                        <select id="settings-language">
                                            <option value="en">English</option>
                                            <option value="tl">Tagalog</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-currency">Currency</label>
                                        <select id="settings-currency">
                                            <option value="PHP">PHP - Philippine Peso</option>
                                            <option value="USD">USD - US Dollar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary" id="settings-preferences-save"><i class="fas fa-save"></i> Save Preferences</button>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="settings-panel" id="settings-panel-notifications">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-bell"></i> Notifications</div>
                            <div class="toggle-list">
                                <label class="toggle-item">
                                    <input type="checkbox" id="notif-sales" checked>
                                    <span>Sales notifications</span>
                                </label>
                                <label class="toggle-item">
                                    <input type="checkbox" id="notif-inventory" checked>
                                    <span>Low inventory alerts</span>
                                </label>
                                <label class="toggle-item">
                                    <input type="checkbox" id="notif-reservations" checked>
                                    <span>Reservation updates</span>
                                </label>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-primary" id="settings-notifications-save"><i class="fas fa-save"></i> Save Notifications</button>
                            </div>
                        </div>
                    </div>

                    <!-- System -->
                    <div class="settings-panel" id="settings-panel-system">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-info-circle"></i> System</div>
                            <div class="system-list">
                                <div><strong>App:</strong> ShoeVault Inventory</div>
                                <div><strong>Version:</strong> 1.0.0</div>
                                <div><strong>Framework:</strong> Laravel {{ app()->version() }}</div>
                                <div><strong>PHP Version:</strong> {{ PHP_VERSION }}</div>
                                <div><strong>Environment:</strong> {{ app()->environment() }}</div>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-danger" id="settings-reset"><i class="fas fa-trash-alt"></i> Reset All Data</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
// Time and date display
function updateDateTime() {
    const now = new Date();
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true 
    };
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    
    document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
    document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
}

setInterval(updateDateTime, 1000);
updateDateTime();

// Settings tab functionality
function initializeSettings() {
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panels
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding panel
            this.classList.add('active');
            const panel = document.getElementById(`settings-panel-${tabId}`);
            if (panel) {
                panel.classList.add('active');
            }
        });
    });
}

initializeSettings();

// Avatar upload functionality
document.getElementById('settings-avatar-btn').addEventListener('click', function() {
    document.getElementById('settings-avatar').click();
});

document.getElementById('settings-avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('settings-avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('settings-avatar-remove').addEventListener('click', function() {
    document.getElementById('settings-avatar-preview').src = '{{ asset("assets/images/profile.png") }}';
    document.getElementById('settings-avatar').value = '';
});

// Save functions
document.getElementById('settings-profile-save').addEventListener('click', function() {
    alert('Profile settings saved!');
});

document.getElementById('settings-password-save').addEventListener('click', function() {
    alert('Password updated!');
});

document.getElementById('settings-preferences-save').addEventListener('click', function() {
    alert('Preferences saved!');
});

document.getElementById('settings-notifications-save').addEventListener('click', function() {
    alert('Notification settings saved!');
});

document.getElementById('settings-reset').addEventListener('click', function() {
    if (confirm('Are you sure you want to reset all data? This action cannot be undone.')) {
        alert('Data reset functionality would be implemented here.');
    }
});
</script>
@endpush
