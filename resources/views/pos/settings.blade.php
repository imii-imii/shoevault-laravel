<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - SHOE VAULT BATANGAS</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">
    
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
        .user-info{display:flex;align-items:center;gap:var(--spacing-md);margin-bottom:var(--spacing-md);padding:var(--spacing-md);background:rgba(255, 255, 255, 0);border-radius:12px}
        .user-avatar{width:40px;height:40px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,.3)}
        .user-avatar img{width:100%;height:100%;object-fit:cover}
        .user-details h4{color:#fff;font-size:.85rem;font-weight:600;margin-bottom:.25rem}
        .user-details span{color:rgba(255,255,255,.8);font-size:.75rem}
    .logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
    .logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
    .logout-btn i{font-size:1rem}

        .main-content{flex:1;margin-left:var(--sidebar-width);display:flex;flex-direction:column;height:100vh}
        .header{height:var(--header-height);background:#fff;box-shadow:var(--shadow-sm);border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;padding:0 var(--spacing-xl);position:sticky;top:0;z-index:100}
        .main-title{font-family:'Roboto Slab', serif;font-size:1.5rem;font-weight:700;color:var(--primary)}
        .header-right{display:flex;align-items:center;gap:var(--spacing-lg)}
        .time-display,.date-display{display:flex;align-items:center;gap:.5rem;color:var(--gray-600);font-size:.85rem}
    .main-content-wrapper{flex:1;padding:var(--spacing-lg);overflow:auto}

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
        .settings-tabs{display:flex;gap:.25rem;margin-bottom:.5rem;border-bottom:1px solid #e2e8f0}
        .settings-tab{padding:.5rem .75rem;border:none;background:transparent;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;transition:.2s;font-weight:500;display:flex;align-items:center;gap:.5rem;font-size:.9rem}
        .settings-tab:hover{color:#1e293b;background:#f1f5f9}
        .settings-tab.active{color:#3b82f6;border-bottom-color:#3b82f6;background:#eff6ff}
        .settings-panels{background:#fff;border-radius:12px;box-shadow:var(--shadow-md);padding:.75rem}
        .settings-panel{display:none}
        .settings-panel.active{display:block}
        .settings-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.75rem;margin-bottom:.75rem}
        .card-title{font-size:1rem;font-weight:600;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem}
        .form-group{display:flex;flex-direction:column}
        .form-group.full{grid-column:1 / -1}
        .form-group label{font-weight:500;color:#374151;margin-bottom:.35rem}
        .form-group input,.form-group textarea,.form-group select{padding:.5rem;border:1px solid #d1d5db;border-radius:8px;font-size:.85rem}
        .form-actions{margin-top:.75rem;display:flex;gap:.5rem}
        .btn{padding:.5rem .8rem;border:none;border-radius:8px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem}
        .btn-primary{background:#3b82f6;color:#fff}
        .btn-primary:hover{background:#2563eb}
        .btn-secondary{background:#6b7280;color:#fff}
        .btn-secondary:hover{background:#4b5563}
        .avatar-row{display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem}
        .avatar-preview{width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb}
        .avatar-actions{display:flex;gap:.5rem}
        .btn-sm{padding:.4rem .7rem;font-size:.85rem}
        .back-button{background:#4a5568;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;margin-bottom:1rem}
        .back-button:hover{background:#2d3748}
        
        /* Loading state */
        .loading-settings {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            color: var(--gray-500);
        }

        .loading-settings i {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }
        /* --- Section skeleton & entry animations --- */
        .section-loading-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.95));
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
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
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <img src="{{ asset('assets/images/shoevault-logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
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
                    <img src="{{ $employee && $employee->profile_picture && file_exists(public_path($employee->profile_picture)) ? asset($employee->profile_picture) : asset('assets/images/profile.png') }}" 
                         alt="User" 
                         class="sidebar-avatar-img">
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
                    <button class="settings-tab" data-tab="system" role="tab"><i class="fas fa-tools"></i> System</button>
                </div>

                <div class="settings-panels">
                    <!-- Profile -->
                    <div class="settings-panel active" id="settings-panel-profile">
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
                                    <button class="btn btn-secondary btn-sm" id="settings-avatar-remove"><i class="fas fa-trash"></i> Remove</button>
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

                    <!-- Security -->
                    <div class="settings-panel" id="settings-panel-security">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-key"></i> Change Password</div>
                            <div class="form-grid">
                                <div class="form-group full">
                                    <label for="settings-current-password">Current Password</label>
                                    <input id="settings-current-password" type="password" placeholder="Enter current password" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label for="settings-new-password">New Password</label>
                                    <input id="settings-new-password" type="password" placeholder="Enter new password" autocomplete="new-password">
                                </div>
                                <div class="form-group">
                                    <label for="settings-confirm-password">Confirm Password</label>
                                    <input id="settings-confirm-password" type="password" placeholder="Confirm new password" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-primary" id="settings-password-save"><i class="fas fa-save"></i> Update Password</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    
                    <!-- System -->
                    <div class="settings-panel" id="settings-panel-system">
                        <div class="settings-card">
                            <div class="card-title"><i class="fas fa-info-circle"></i> System</div>
                            <div class="system-list">
                                <div><strong>App:</strong> ShoeVault POS</div>
                                <div><strong>Version:</strong> 1.0.0</div>
                                <div><strong>Framework:</strong> Laravel {{ app()->version() }}</div>
                                <div><strong>PHP Version:</strong> {{ PHP_VERSION }}</div>
                                <div><strong>Environment:</strong> {{ app()->environment() }}</div>
                            </div>
                            
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
    document.getElementById('settings-avatar-remove').addEventListener('click', async function(){
        if (confirm('Are you sure you want to remove your avatar?')){
            const removeBtn = this;
            const originalText = removeBtn.innerHTML;
            
            // Show loading state
            removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
            removeBtn.disabled = true;
            
            try {
                // Determine the correct route based on current URL
                let removeRoute;
                if (window.location.pathname.includes('/pos/')) {
                    removeRoute = '{{ route("pos.profile.picture.remove") }}';
                } else if (window.location.pathname.includes('/owner/')) {
                    removeRoute = '{{ route("owner.profile.picture.remove") }}';
                } else {
                    // Default fallback
                    removeRoute = '{{ route("pos.profile.picture.remove") }}';
                }
                
                const response = await fetch(removeRoute, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update preview image
                    document.getElementById('settings-avatar-preview').src = '{{ asset('assets/images/profile.png') }}';
                    // Update sidebar avatar
                    document.querySelector('.sidebar-avatar-img').src = '{{ asset('assets/images/profile.png') }}';
                    // Clear file input
                    document.getElementById('settings-avatar').value = '';
                    alert('Profile picture removed successfully!');
                } else {
                    alert(data.message || 'Failed to remove profile picture');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while removing profile picture');
            } finally {
                // Restore button state
                removeBtn.innerHTML = originalText;
                removeBtn.disabled = false;
            }
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

    // Profile save
    document.getElementById('settings-profile-save').addEventListener('click', async function(){
        const saveBtn = this;
        const originalText = saveBtn.innerHTML;
        
        // Validate required fields
        const name = document.getElementById('settings-name').value.trim();
        const username = document.getElementById('settings-username').value.trim();
        const email = document.getElementById('settings-email').value.trim();
        
        if (!name || !username || !email) {
            alert('Please fill in all required fields (Name, Username, Email)');
            return;
        }
        
        // Show loading state
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
        
        try {
            // Prepare form data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('username', username);
            formData.append('email', email);
            formData.append('phone', document.getElementById('settings-phone').value.trim());
            
            // Handle profile picture if selected
            const profilePictureInput = document.getElementById('settings-avatar');
            if (profilePictureInput.files && profilePictureInput.files[0]) {
                const originalFile = profilePictureInput.files[0];
                console.log('Original file size:', originalFile.size, 'bytes');
                
                // Compress image if needed
                const compressedFile = await compressImageIfNeeded(originalFile);
                console.log('Compressed file size:', compressedFile.size, 'bytes');
                
                formData.append('profile_picture', compressedFile);
            }
            
            // Send request - determine the correct route based on current URL
            let updateRoute;
            if (window.location.pathname.includes('/pos/')) {
                updateRoute = '{{ route("pos.profile.update") }}';
            } else if (window.location.pathname.includes('/owner/')) {
                updateRoute = '{{ route("owner.profile.update") }}';
            } else {
                // Default fallback
                updateRoute = '{{ route("pos.profile.update") }}';
            }
            
            const response = await fetch(updateRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Profile updated successfully!');
                
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
                
                // Refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating profile');
        } finally {
            // Restore button state
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    });

    // Password change
    document.getElementById('settings-password-save').addEventListener('click', function() {
        const currentPassword = document.getElementById('settings-current-password').value;
        const newPassword = document.getElementById('settings-new-password').value;
        const confirmPassword = document.getElementById('settings-confirm-password').value;
        
        // Validate required fields
        if (!currentPassword || !newPassword || !confirmPassword) {
            alert('Please fill in all password fields');
            return;
        }
        
        // Check password length
        if (newPassword.length < 8) {
            alert('New password must be at least 8 characters long');
            return;
        }
        
        // Check password confirmation
        if (newPassword !== confirmPassword) {
            alert('New password and confirmation do not match');
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
        
        // Determine the correct route based on current URL
        let passwordRoute;
        if (window.location.pathname.includes('/pos/')) {
            passwordRoute = '{{ route("pos.password.update") }}';
        } else if (window.location.pathname.includes('/owner/')) {
            passwordRoute = '{{ route("owner.password.update") }}';
        } else {
            // Default fallback
            passwordRoute = '{{ route("pos.password.update") }}';
        }
        
        // Send request
        fetch(passwordRoute, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password updated successfully!');
                
                // Clear password fields
                document.getElementById('settings-current-password').value = '';
                document.getElementById('settings-new-password').value = '';
                document.getElementById('settings-confirm-password').value = '';
            } else {
                alert(data.message || 'Failed to update password');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating password');
        })
        .finally(() => {
            // Restore button state
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    });

    // System reset button removed per request

    // ===== Header notifications wiring =====
    function initNotifications(){
        console.log('🎯 POS Settings: Initializing notifications...');
        
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
                            if(!bell) return;
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

    // --- Section loading & slow entry animation (visual only) for settings ---
    (function(){
        function makeSkeleton(section) {
            const overlay = document.createElement('div');
            overlay.className = 'section-loading-overlay';
            overlay.setAttribute('aria-hidden','true');
            const t = document.createElement('div'); t.className = 'skeleton title'; overlay.appendChild(t);
            for (let i=0;i<2;i++){ const l=document.createElement('div'); l.className='skeleton line'; overlay.appendChild(l); }
            const cardCount = 2;
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
            const delay = 450 + Math.random()*400;
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
            document.querySelectorAll('.settings-panel, .settings-card, .settings-tabs').forEach(playSection);
        });
        else document.querySelectorAll('.settings-panel, .settings-card, .settings-tabs').forEach(playSection);
    })();
    </script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @include('partials.mobile-blocker')
</body>
</html>