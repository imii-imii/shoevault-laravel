<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - SHOE VAULT BATANGAS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse POS/Reservations layout styles for consistent sidebar/header */
        :root {
            --primary: #2d3748; --primary-dark: #1a202c; --primary-light: #4a5568; --secondary: #4a5568;
            --white: #ffffff; --gray-50: #f7fafc; --gray-100: #edf2f7; --gray-200: #e2e8f0; --gray-300: #cbd5e0;
            --gray-400: #a0aec0; --gray-500: #718096; --gray-600: #4a5568; --gray-700: #2d3748; --gray-800: #1a202c;
            --bg-primary: #f7fafc; --bg-card: #ffffff; --radius-lg: 0.75rem; --radius-xl: 1rem; --radius-2xl: 1.5rem;
            --spacing-sm: 0.5rem; --spacing-md: 1rem; --spacing-lg: 1.5rem; --spacing-xl: 2rem; --header-height: 80px; --sidebar-width: 280px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05); --shadow-md: 0 4px 6px rgba(0,0,0,0.1); --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }
        *{margin:0;padding:0;box-sizing:border-box}
        html{font-size:0.9rem}
        body{font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:var(--bg-primary); color:var(--gray-800); height:100vh; overflow:hidden; display:flex}
        .sidebar{width:var(--sidebar-width);height:100vh;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);position:fixed;left:0;top:0;display:flex;flex-direction:column;border-radius:0 25px 25px 0;box-shadow:0 8px 32px rgba(30,58,138,.4);border:1px solid rgba(255,255,255,.15);z-index:1000}
        .logo{display:flex;align-items:center;justify-content:center;border-bottom:1px solid rgba(255,255,255,.1)}
        .logo-img{width:100%;max-width:170px;height:auto;object-fit:cover}
        .sidebar-nav{flex:1;padding:var(--spacing-lg);list-style:none}
        .nav-item{margin-bottom:0.5rem}
        .nav-link{display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md) var(--spacing-lg);color:rgba(255,255,255,.9);text-decoration:none;border-radius:12px;transition:.3s}
        .nav-item.active .nav-link{background:rgba(255,255,255,.15);border-left:3px solid #fff}
        .nav-link:hover{background:rgba(255,255,255,.1);color:#fff}
        .sidebar-footer{padding:var(--spacing-lg);border-top:1px solid rgba(255,255,255,.1)}
        .user-info{display:flex;align-items:center;gap:var(--spacing-md);margin-bottom:var(--spacing-md);padding:var(--spacing-md);background:rgba(255,255,255,.1);border-radius:12px}
        .user-avatar{width:40px;height:40px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,.3)}
        .user-avatar img{width:100%;height:100%;object-fit:cover}
        .user-details h4{color:#fff;font-size:.85rem;font-weight:600;margin-bottom:.25rem}
        .user-details span{color:rgba(255,255,255,.8);font-size:.75rem}
        .logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem;background:rgba(220,38,38,.2);color:#fff;border:1px solid rgba(220,38,38,.4);border-radius:12px;cursor:pointer}

        .main-content{flex:1;margin-left:var(--sidebar-width);display:flex;flex-direction:column;height:100vh}
        .header{height:var(--header-height);background:#fff;box-shadow:var(--shadow-sm);border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;padding:0 var(--spacing-xl);position:sticky;top:0;z-index:100}
        .main-title{font-family:'Roboto Slab', serif;font-size:1.5rem;font-weight:700;color:var(--primary)}
        .header-right{display:flex;align-items:center;gap:var(--spacing-lg)}
        .time-display,.date-display{display:flex;align-items:center;gap:.5rem;color:var(--gray-600);font-size:.85rem}
        .main-content-wrapper{flex:1;padding:var(--spacing-xl);overflow:auto}

    /* Header notifications */
    .notification-wrapper{position:relative}
    .notification-bell{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:none;border:none;color:var(--gray-600);border-radius:8px;cursor:pointer;transition:.15s}
    .notification-bell:hover{background:var(--gray-100);color:var(--primary)}
    .notification-count{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border-radius:999px;padding:0 6px;height:16px;min-width:16px;line-height:16px;font-size:.65rem;font-weight:700;border:2px solid #fff}
    .notification-dropdown{position:absolute;top:calc(100% + 8px);right:0;width:280px;background:#fff;border:1px solid var(--gray-200);border-radius:12px;box-shadow:var(--shadow-lg);display:none;overflow:hidden;z-index:200}
    .notification-wrapper.open .notification-dropdown{display:block}
    .notification-list{max-height:300px;overflow-y:auto}
    .notification-empty{padding:12px;color:var(--gray-500);text-align:center;display:flex;align-items:center;justify-content:center;gap:8px}

        /* Settings-specific styles (ported) */
        .settings-container{max-width:1200px;margin:0 auto}
        .settings-tabs{display:flex;gap:.5rem;margin-bottom:1rem;border-bottom:2px solid #e2e8f0}
        .settings-tab{padding:.75rem 1rem;border:none;background:transparent;color:#64748b;cursor:pointer;border-bottom:3px solid transparent;transition:.2s;font-weight:500;display:flex;align-items:center;gap:.5rem}
        .settings-tab:hover{color:#1e293b;background:#f1f5f9}
        .settings-tab.active{color:#3b82f6;border-bottom-color:#3b82f6;background:#eff6ff}
        .settings-panels{background:#fff;border-radius:12px;box-shadow:var(--shadow-md);padding:1.25rem}
        .settings-panel{display:none}
        .settings-panel.active{display:block}
        .settings-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1rem;margin-bottom:1rem}
        .card-title{font-size:1.05rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
        .form-group{display:flex;flex-direction:column}
        .form-group.full{grid-column:1 / -1}
        .form-group label{font-weight:500;color:#374151;margin-bottom:.5rem}
        .form-group input,.form-group textarea,.form-group select{padding:.65rem;border:1px solid #d1d5db;border-radius:8px;font-size:.875rem}
        .form-actions{margin-top:1rem;display:flex;gap:.75rem}
        .btn{padding:.6rem 1rem;border:none;border-radius:8px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem}
        .btn-primary{background:#3b82f6;color:#fff}
        .btn-primary:hover{background:#2563eb}
        .btn-secondary{background:#6b7280;color:#fff}
        .btn-secondary:hover{background:#4b5563}
        .avatar-row{display:flex;align-items:center;gap:1rem;margin-bottom:1rem}
        .avatar-preview{width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb}
        .avatar-actions{display:flex;gap:.5rem}
        .btn-sm{padding:.45rem .8rem;font-size:.85rem}
        .back-button{background:#4a5568;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;margin-bottom:1rem}
        .back-button:hover{background:#2d3748}
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
            <div class="logo-text"></div>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item nav-pos">
                <a href="{{ route('pos.dashboard') }}" class="nav-link">
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
            <li class="nav-item active">
                <a href="#" class="nav-link">
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
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
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
        <header class="header">
            <div class="header-left">
                <h1 class="main-title">Settings</h1>
            </div>
            <div class="header-right">
                <div class="time-display"><i class="fas fa-clock"></i><span id="current-time">Loading...</span></div>
                <div class="date-display"><i class="fas fa-calendar"></i><span id="current-date">Loading...</span></div>
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

        <div class="main-content-wrapper">
            <div class="settings-container">
                <div style="margin-bottom:1rem;">
                    <h2 style="color:#2d3748;margin:0;">Account & System Settings</h2>
                </div>

                <div class="settings-tabs" role="tablist">
                    <button class="settings-tab active" data-tab="profile" role="tab"><i class="fas fa-user"></i> Profile</button>
                    <button class="settings-tab" data-tab="security" role="tab"><i class="fas fa-shield-alt"></i> Security</button>
                </div>

                <div class="settings-panels">
                    <!-- Profile -->
                    <div class="settings-panel active" id="settings-panel-profile">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-id-card"></i> User Information</div>
                            <div class="avatar-row">
                                <img id="settings-avatar-preview" src="{{ asset('assets/images/profile.png') }}" alt="Avatar" class="avatar-preview">
                                <div class="avatar-actions">
                                    <input type="file" id="settings-avatar" accept="image/*" hidden>
                                    <button class="btn btn-secondary btn-sm" id="settings-avatar-btn"><i class="fas fa-upload"></i> Upload</button>
                                    <button class="btn btn-secondary btn-sm" id="settings-avatar-remove"><i class="fas fa-trash"></i> Remove</button>
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
                                <button class="btn btn-primary" id="settings-profile-save"><i class="fas fa-save"></i> Save Profile</button>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="settings-panel" id="settings-panel-security">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-key"></i> Change Password</div>
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
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </main>

    <script>
    // Time and date display
    function updateDateTime(){
        const now = new Date();
        const timeOptions = {hour:'2-digit', minute:'2-digit', hour12:true};
        document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        const dateOptions = {weekday:'long', year:'numeric', month:'long', day:'numeric'};
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
    }
    setInterval(updateDateTime, 1000); updateDateTime();
    initNotifications();

    // Settings tab switching
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('settings-panel-' + this.dataset.tab).classList.add('active');
        });
    });

    // Avatar upload
    document.getElementById('settings-avatar-btn').addEventListener('click', () => document.getElementById('settings-avatar').click());
    document.getElementById('settings-avatar').addEventListener('change', function(e){
        if (e.target.files && e.target.files[0]){
            const reader = new FileReader();
            reader.onload = ev => document.getElementById('settings-avatar-preview').src = ev.target.result;
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    document.getElementById('settings-avatar-remove').addEventListener('click', function(){
        if (confirm('Are you sure you want to remove your avatar?')){
            document.getElementById('settings-avatar-preview').src = '{{ asset('assets/images/profile.png') }}';
            document.getElementById('settings-avatar').value = '';
        }
    });

    // Profile save
    document.getElementById('settings-profile-save').addEventListener('click', function(){
        alert('Profile settings saved successfully!');
    });

    // Password change
    document.getElementById('change-password-form').addEventListener('submit', function(e){
        e.preventDefault();
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        if (!currentPassword || !newPassword || !confirmPassword){ alert('Please fill in all password fields.'); return; }
        if (newPassword !== confirmPassword){ alert('New password and confirmation do not match.'); return; }
        if (newPassword.length < 8){ alert('New password must be at least 8 characters long.'); return; }
        alert('Password updated successfully!'); this.reset();
    });

    // ===== Header notifications wiring =====
    function initNotifications(){
        const wrappers = document.querySelectorAll('.notification-wrapper');
        wrappers.forEach(wrapper => {
            const bell = wrapper.querySelector('.notification-bell');
            if(!bell) return;
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