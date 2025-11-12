@extends('layouts.app')

@section('title', 'Settings - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
.logout-btn i{font-size:1rem}
/* Settings panels: loading overlay & entry animation */
@keyframes slideFadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0);} }
.settings-panels { position: relative; }
.settings-panel { position: relative; }
.settings-panel .loading-overlay{ position:absolute; inset:0; display:grid; place-items:center; gap:8px; background:linear-gradient(180deg, rgba(255,255,255,0.94), rgba(248,250,252,0.92)); backdrop-filter: blur(6px); z-index:20; border-radius:8px; }
.settings-panel .loading-overlay i{ font-size:18px; color:#64748b }
.settings-panel.animate-entry { animation: slideFadeIn 360ms ease both; }

/* Notification Styles - Match Dashboard */
.notification-wrapper { position: relative; }
.notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
.notification-bell:hover { background:#f3f4f6; color:#1f2937; }
.notification-count { position: absolute; top: 2px; right: 2px; background: rgb(239, 68, 68); color: rgb(255, 255, 255); border-radius: 999px; padding: 2px 6px; font-size: 11px; display: inline-block; }
.notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:9999; }
.notification-wrapper.open .notification-dropdown { display:block; }
.notification-list { max-height:300px; overflow-y:auto; }
.notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }
</style>
@endpush

@section('content')
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('assets/images/shoevault-logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
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
                <img src="{{ $employee && $employee->profile_picture && file_exists(public_path($employee->profile_picture)) ? asset($employee->profile_picture) : asset('assets/images/profile.png') }}" 
                     alt="Manager" 
                     class="sidebar-avatar-img">
            </div>
            <div class="user-details">
                <h4>{{ $employee->fullname ?? auth()->user()->name }}</h4>
                <span>{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: inline;">
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
            <h1 class="main-title">Settings</h1>
        </div>
        <div class="header-right">
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span id="current-time">Loading...</span>
            </div>
            <div class="date-display" style="display:flex;align-items:center;gap:12px;">
                <i class="fas fa-calendar"></i>
                <span id="current-date">Loading...</span>
                <!-- Notification System - Dashboard Style -->
                <div class="notification-wrapper">
                    <button class="notification-bell" aria-label="Notifications">
                        <i class="fas fa-bell" style="font-size: 1.5rem;"></i>
                        <span class="notification-count" style="display:none;">0</span>
                    </button>
                    <div class="notification-dropdown">
                        <div class="notification-list">
                            <div class="notification-empty"><i class="fas fa-inbox"></i> No new notifications</div>
                        </div>
                    </div>
                </div>
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
                    
                    <button class="settings-tab" data-tab="system" role="tab"><i class="fas fa-tools"></i> System</button>
                </div>

                <div class="settings-panels">
                    <!-- Profile -->
                    <div class="settings-panel active" id="settings-panel-profile">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="card-title"><i class="fas fa-id-card"></i> User Information</div>
                                <div class="avatar-row">
                                    <img id="settings-avatar-preview" 
                                         src="{{ $employee && $employee->profile_picture && file_exists(public_path($employee->profile_picture)) ? asset($employee->profile_picture) : asset('assets/images/profile.png') }}" 
                                         alt="Avatar" 
                                         class="avatar-preview settings-avatar-img">
                                    <div class="avatar-actions">
                                        <input type="file" id="settings-avatar" accept="image/*" hidden>
                                        <button class="btn btn-secondary btn-sm" id="settings-avatar-btn"><i class="fas fa-upload"></i> Upload</button>
                                        <button class="btn btn-danger btn-sm" id="settings-avatar-remove"><i class="fas fa-trash"></i> Remove</button>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="settings-name">Full Name</label>
                                        <input id="settings-name" type="text" placeholder="Your name" value="{{ $employee->fullname ?? auth()->user()->name ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-username">Username</label>
                                        <input id="settings-username" type="text" placeholder="Username" value="{{ auth()->user()->username ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-email">Email</label>
                                        <input id="settings-email" type="email" placeholder="name@example.com" value="{{ $employee->email ?? auth()->user()->email ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="settings-phone">Phone</label>
                                        <input id="settings-phone" type="tel" placeholder="+63 900 000 0000" value="{{ $employee->phone_number ?? '' }}">
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
                                    <input id="settings-current-password" type="password" placeholder="Current password" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label for="settings-new-password">New Password</label>
                                    <input id="settings-new-password" type="password" placeholder="New password" autocomplete="new-password">
                                </div>
                                <div class="form-group">
                                    <label for="settings-confirm-password">Confirm Password</label>
                                    <input id="settings-confirm-password" type="password" placeholder="Confirm new password" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-primary" id="settings-password-save"><i class="fas fa-key"></i> Update Password</button>
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
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/notifications.js') }}"></script>
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
            // Remove active class from all tabs
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show a small loading overlay on the target panel, then activate it with an entry animation
            const panel = document.getElementById(`settings-panel-${tabId}`);
            if (panel) {
                // hide any currently active panels and remove animation classes
                document.querySelectorAll('.settings-panel').forEach(p => { p.classList.remove('active'); p.classList.remove('animate-entry'); });

                let lo = panel.querySelector('.loading-overlay');
                if (!lo) {
                    lo = document.createElement('div');
                    lo.className = 'loading-overlay';
                    lo.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    panel.appendChild(lo);
                }
                lo.style.display = 'grid';

                // small delay to simulate load and allow CSS animations
                setTimeout(() => {
                    lo.style.display = 'none';
                    panel.classList.add('active');
                    panel.classList.add('animate-entry');
                }, 260);
            }
        });
    });
}

initializeSettings();

// Handle profile picture errors - fallback to default image
document.addEventListener('DOMContentLoaded', function() {
    // Handle sidebar avatar errors
    const sidebarAvatar = document.querySelector('.sidebar-avatar-img');
    if (sidebarAvatar) {
        sidebarAvatar.addEventListener('error', function() {
            this.src = '{{ asset("assets/images/profile.png") }}';
        });
    }
    
    // Handle settings avatar errors
    const settingsAvatar = document.querySelector('.settings-avatar-img');
    if (settingsAvatar) {
        settingsAvatar.addEventListener('error', function() {
            this.src = '{{ asset("assets/images/profile.png") }}';
        });
    }
});

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
    if (confirm('Are you sure you want to remove your profile picture?')) {
        fetch('{{ route("inventory.profile.picture.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const defaultProfilePic = '{{ asset("assets/images/profile.png") }}';
                document.getElementById('settings-avatar-preview').src = defaultProfilePic;
                document.getElementById('settings-avatar').value = '';
                
                // Also update sidebar avatar
                const sidebarAvatar = document.querySelector('.sidebar .user-avatar img');
                if (sidebarAvatar) {
                    sidebarAvatar.src = defaultProfilePic;
                }
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while removing profile picture', 'error');
        });
    }
});

// Image compression function
async function compressImageIfNeeded(file, maxSizeKB = 2048) {
    if (file.size <= maxSizeKB * 1024) {
        return file;
    }

    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = function() {
            const maxWidth = 1200;
            const maxHeight = 1200;
            let { width, height } = img;
            
            if (width > height) {
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
            } else {
                if (height > maxHeight) {
                    width = (width * maxHeight) / height;
                    height = maxHeight;
                }
            }
            
            canvas.width = width;
            canvas.height = height;
            
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob(resolve, 'image/jpeg', 0.8);
        };
        
        img.src = URL.createObjectURL(file);
    });
}

// Save functions
document.getElementById('settings-profile-save').addEventListener('click', async function() {
    // Get form values
    const name = document.getElementById('settings-name').value;
    const username = document.getElementById('settings-username').value;
    const email = document.getElementById('settings-email').value;
    const phone = document.getElementById('settings-phone').value;
    const profilePictureInput = document.getElementById('settings-avatar');
    
    // Validate required fields
    if (!name || !username || !email) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Show loading state
    const saveBtn = document.getElementById('settings-profile-save');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('name', name);
        formData.append('username', username);
        formData.append('email', email);
        formData.append('phone', phone);
        
        // Handle profile picture if selected
        if (profilePictureInput.files && profilePictureInput.files[0]) {
            const originalFile = profilePictureInput.files[0];
            console.log('Original file size:', originalFile.size, 'bytes');
            
            // Compress image if needed
            const compressedFile = await compressImageIfNeeded(originalFile);
            console.log('Compressed file size:', compressedFile.size, 'bytes');
            
            formData.append('profile_picture', compressedFile);
        }
        
        // Send request
        const response = await fetch('{{ route("inventory.profile.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Update UI with new data
            if (data.user) {
                document.getElementById('settings-name').value = data.user.name;
                document.getElementById('settings-username').value = data.user.username;
                document.getElementById('settings-email').value = data.user.email;
                document.getElementById('settings-phone').value = data.user.phone || '';
                
                // Update profile picture if changed
                if (data.user.profile_picture) {
                    document.getElementById('settings-avatar-preview').src = data.user.profile_picture;
                    // Update sidebar avatar too
                    document.querySelector('.sidebar-avatar-img').src = data.user.profile_picture;
                }
            }
            
            // Clear file input
            profilePictureInput.value = '';
            
            // Update sidebar user name
            const sidebarName = document.querySelector('.sidebar .user-details h4');
            if (sidebarName) {
                sidebarName.textContent = data.user.name;
            }
            
            // Refresh page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                showNotification(errorMessages, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred while updating profile', 'error');
    } finally {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
});

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    switch(type) {
        case 'success':
            notification.style.background = '#28a745';
            break;
        case 'error':
            notification.style.background = '#dc3545';
            break;
        default:
            notification.style.background = '#17a2b8';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

document.getElementById('settings-password-save').addEventListener('click', function() {
    const currentPassword = document.getElementById('settings-current-password').value;
    const newPassword = document.getElementById('settings-new-password').value;
    const confirmPassword = document.getElementById('settings-confirm-password').value;
    
    // Validate required fields
    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('Please fill in all password fields', 'error');
        return;
    }
    
    // Check password length
    if (newPassword.length < 8) {
        showNotification('New password must be at least 8 characters long', 'error');
        return;
    }
    
    // Check password confirmation
    if (newPassword !== confirmPassword) {
        showNotification('New password and confirmation do not match', 'error');
        return;
    }
    
    // Show loading state
    const saveBtn = document.getElementById('settings-password-save');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    saveBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('new_password_confirmation', confirmPassword);
    
    // Send request
    fetch('{{ route("inventory.password.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Clear password fields
            document.getElementById('settings-current-password').value = '';
            document.getElementById('settings-new-password').value = '';
            document.getElementById('settings-confirm-password').value = '';
        } else {
            showNotification(data.message, 'error');
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                showNotification(errorMessages, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating password', 'error');
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
});

// Initialize dynamic notification system
if (typeof NotificationManager !== 'undefined') {
    const notificationManager = new NotificationManager();
    notificationManager.init('{{ auth()->user()->role ?? "manager" }}');
} else {
    console.warn('NotificationManager not found. Make sure notifications.js is loaded.');
}
</script>
@endpush

@include('partials.mobile-blocker')