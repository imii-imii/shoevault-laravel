<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Master Controls - ShoeVault Batangas</title>

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">

	<!-- Font Awesome -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

	<!-- Owner CSS -->
	<link href="{{ asset('css/owner.css') }}" rel="stylesheet">
	<style>
		.notification-wrapper { position: relative; }
		.notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
		.notification-bell:hover { background:#f3f4f6; color:#1f2937; }
		.notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
		.notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:200; }
		.notification-wrapper.open .notification-dropdown { display:block; }
		.notification-list { max-height:300px; overflow-y:auto; }
		.notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }
	</style>
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
		<li class="nav-item" data-section="inventory-dashboard">
			<a href="{{ route('owner.dashboard') }}" class="nav-link">
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
		<li class="nav-item active" data-section="settings">
			<a href="{{ route('owner.settings') }}" class="nav-link">
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
			<h1 class="main-title" id="page-title">Master Controls</h1>
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

	<div class="content-grid">
		<!-- Master Controls Content -->
		<section id="settings" class="content-section active">
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
                    

					<div class="settings-panel" id="settings-panel-system">
						<div class="settings-card">
							<div class="card-title">
								<i class="fas fa-info-circle"></i> System
							</div>
							<div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;font-size:0.95rem;">
								@php
									// Prefer explicit APP_NAME from env, then config('app.name').
									// If the value is the Laravel default, normalize to a more descriptive name.
									$envAppName = env('APP_NAME');
									$cfgAppName = config('app.name', 'ShoeVault System');
									$appName = $envAppName ?: $cfgAppName;
									if (!$appName || strtolower(trim($appName)) === 'laravel') {
										$appName = 'ShoeVault System';
									}
									$appVersion = config('app.version', '1.0.0');
									// Get Laravel framework version safely
									$laravelVersion = defined('\Illuminate\\Foundation\\Application::VERSION')
										? \Illuminate\Foundation\Application::VERSION
										: (\Illuminate\Foundation\Application::VERSION ?? 'unknown');
									$phpVersion = PHP_VERSION;
									$env = app()->environment();
								@endphp

								<div style="display:flex;gap:8px;align-items:flex-start;">
									<strong style="width:120px;color:#374151;">App:</strong>
									<span style="color:#111827;font-weight:700;">{{ $appName }}</span>
								</div>
								<div style="display:flex;gap:8px;align-items:flex-start;">
									<strong style="width:120px;color:#374151;">Version:</strong>
									<span style="color:#111827;font-weight:700;">{{ $appVersion }}</span>
								</div>
								<div style="display:flex;gap:8px;align-items:flex-start;">
									<strong style="width:120px;color:#374151;">Framework:</strong>
									<span style="color:#111827;font-weight:700;">Laravel {{ $laravelVersion }}</span>
								</div>
								<div style="display:flex;gap:8px;align-items:flex-start;">
									<strong style="width:120px;color:#374151;">PHP Version:</strong>
									<span style="color:#111827;font-weight:700;">{{ $phpVersion }}</span>
								</div>
								<div style="display:flex;gap:8px;align-items:flex-start;">
									<strong style="width:120px;color:#374151;">Environment:</strong>
									<span style="color:#111827;font-weight:700;">{{ $env }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<!-- Include Laravel's CSRF token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
	// Make sure Owner JS shows the correct section on this page
	window.initialOwnerSection = 'settings';
	// Provide routes object to avoid undefined access in owner.js
	window.laravelData = window.laravelData || { routes: {} };
	window.laravelData.routes = Object.assign({}, window.laravelData.routes, {
		settings: '{{ route('owner.settings') }}'
	});
</script>
<script src="{{ asset('js/owner.js') }}"></script>
<script>
// Time/date header
document.addEventListener('DOMContentLoaded', function() {
	function updateDateTime() {
		const now = new Date();
		document.getElementById('current-time').textContent = now.toLocaleTimeString();
		document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
			weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
		});
	}
	updateDateTime();
	setInterval(updateDateTime, 1000);
});
</script>

	<script>
	// Notifications toggle for settings page
	(function initNotifications(){
		document.querySelectorAll('.notification-wrapper').forEach(wrapper => {
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
	})();
	</script>

	<!-- Add User Modal -->
	@php $userStoreRoute = \Illuminate\Support\Facades\Route::has('owner.users.store') ? route('owner.users.store') : null; @endphp
	<div id="add-user-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;z-index:10000;"></div>
	<div id="add-user-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(520px,94vw);max-height:90vh;overflow:auto;display:none;z-index:10001;">
		<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
			<h3 style="margin:0;font-size:1.05rem;font-weight:800;">Add User</h3>
			<button id="add-user-close" style="background:none;border:none;font-size:1.2rem;cursor:pointer;line-height:1;">&times;</button>
		</div>
		<form id="add-user-form" method="post" action="{{ $userStoreRoute ?? '#' }}" novalidate style="padding:14px 20px;display:grid;gap:10px;">
			@csrf
			<div style="display:grid;gap:8px;">
				<label for="au-fullname" style="font-weight:600;color:#374151;font-size:0.9rem;">Full Name</label>
				<input id="au-fullname" name="name" type="text" placeholder="Full Name" required style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">
				<small class="au-error" data-for="au-fullname" style="color:#ef4444;display:none;"></small>
			</div>
			<div style="display:grid;gap:8px;">
				<label for="au-username" style="font-weight:600;color:#374151;font-size:0.9rem;">Username</label>
				<input id="au-username" name="username" type="text" placeholder="Username" required style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">
				<small class="au-error" data-for="au-username" style="color:#ef4444;display:none;"></small>
			</div>
			<div style="display:grid;gap:8px;">
				<label for="au-email" style="font-weight:600;color:#374151;font-size:0.9rem;">Email</label>
				<input id="au-email" name="email" type="email" placeholder="name@example.com" required style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">
				<small class="au-error" data-for="au-email" style="color:#ef4444;display:none;"></small>
			</div>
			<div style="display:grid;gap:8px;">
				<label for="au-role" style="font-weight:600;color:#374151;font-size:0.9rem;">Role</label>
				<select id="au-role" name="role" required style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
					<option value="">Select role</option>
					<option value="manager">Manager</option>
					<option value="cashier">Cashier</option>
				</select>
				<small class="au-error" data-for="au-role" style="color:#ef4444;display:none;"></small>
			</div>
			<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
				<button type="button" id="au-cancel" style="padding:10px 14px;border-radius:8px;background:#6b7280;color:#fff;border:none;font-weight:700;">Cancel</button>
				<button type="submit" id="au-save" style="padding:10px 14px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Save User</button>
			</div>
		</form>
	</div>

	<script>
	// Add User Modal interactions and client-side validation
	(function(){
		const openBtn = document.getElementById('add-user-btn');
		const overlay = document.getElementById('add-user-modal-overlay');
		const modal = document.getElementById('add-user-modal');
		const closeBtn = document.getElementById('add-user-close');
		const cancelBtn = document.getElementById('au-cancel');
		const form = document.getElementById('add-user-form');
		const userList = document.getElementById('user-list');
		const apiUrl = '{{ $userStoreRoute ?? '' }}';

		function open(){ overlay.style.display='block'; modal.style.display='block'; }
		function close(){ modal.style.display='none'; overlay.style.display='none'; form.reset(); clearErrors(); }
		function showError(id, message){ const el = document.querySelector(`.au-error[data-for="${id}"]`); if(el){ el.textContent = message; el.style.display = message? 'block':'none'; }}
		function clearErrors(){ document.querySelectorAll('.au-error').forEach(e=>{e.textContent=''; e.style.display='none';}); }

		function validate(){
			clearErrors();
			let ok = true;
			const name = document.getElementById('au-fullname').value.trim();
			const username = document.getElementById('au-username').value.trim();
			const email = document.getElementById('au-email').value.trim();
			const role = document.getElementById('au-role').value;
			const pass = document.getElementById('au-password').value;
			const pass2 = document.getElementById('au-password-confirm').value;

			if(!name){ showError('au-fullname','Full name is required'); ok=false; }
			if(!username){ showError('au-username','Username is required'); ok=false; }
			if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ showError('au-email','Valid email is required'); ok=false; }
			if(!role){ showError('au-role','Please select a role'); ok=false; }
			if(!pass || pass.length < 8){ showError('au-password','Min 8 characters'); ok=false; }
			if(pass2 !== pass){ showError('au-password-confirm','Passwords do not match'); ok=false; }
			return ok;
		}

		if(openBtn){ openBtn.addEventListener('click', open); }
		if(closeBtn){ closeBtn.addEventListener('click', close); }
		if(cancelBtn){ cancelBtn.addEventListener('click', close); }
		if(overlay){ overlay.addEventListener('click', close); }

		form?.addEventListener('submit', async (e)=>{
			e.preventDefault();
			if(!validate()) return;

			const payload = {
				name: document.getElementById('au-fullname').value.trim(),
				username: document.getElementById('au-username').value.trim(),
				email: document.getElementById('au-email').value.trim(),
				role: document.getElementById('au-role').value,
				password: document.getElementById('au-password').value,
				password_confirmation: document.getElementById('au-password-confirm').value
			};

			// If an API route exists, attempt to POST; otherwise simulate UI update
			if(apiUrl){
				try{
					const res = await fetch(apiUrl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
						body: JSON.stringify(payload)
					});
					const data = await res.json().catch(()=>({}));
					if(!res.ok || data.success === false){
						const msg = data.message || 'Failed to add user';
						alert(msg);
						return;
					}
				}catch(err){
					alert('Unable to add user right now.');
					return;
				}
			}

			// Reflect immediately in UI
			if(userList){
				const card = document.createElement('div');
				card.className = 'odash-list-item';
				card.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:10px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;';
				card.innerHTML = `
					<div>
						<div class="name" style="font-weight:700;">${payload.name} <span style="color:#6b7280;font-weight:600;">(@${payload.username})</span></div>
						<div class="sub" style="color:#6b7280;font-size:0.85rem;">${payload.email}</div>
					</div>
					<span class="odash-badge" style="background:#eef2ff;color:#1e3a8a;padding:4px 10px;border-radius:999px;font-weight:700;font-size:0.75rem;text-transform:capitalize;">${payload.role}</span>
				`;
				userList.prepend(card);
			}

			close();
		});
	})();
	</script>

</body>
</html>
