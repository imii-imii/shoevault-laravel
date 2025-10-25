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
		/* Gradient pill logout button (copied from inventory dashboard) */
		.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
		.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
		.logout-btn i{font-size:1rem}
		.notification-wrapper { position: relative; }
		.notification-bell { width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:none; border:none; color:#6b7280; border-radius:10px; cursor:pointer; transition: all .2s ease; }
		.notification-bell:hover { background:#f3f4f6; color:#1f2937; }
		.notification-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; padding:0 6px; height:16px; min-width:16px; line-height:16px; font-size:0.65rem; font-weight:700; border:2px solid #fff; }
		.notification-dropdown { position:absolute; top:calc(100% + 8px); right:0; width:280px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); display:none; overflow:hidden; z-index:200; }
		.notification-wrapper.open .notification-dropdown { display:block; }
		.notification-list { max-height:300px; overflow-y:auto; }
		.notification-empty { padding:12px; color:#6b7280; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; }

		/* Owner user card styles and switch */
		.odash-card { transition: transform .2s ease, box-shadow .2s ease; will-change: transform; }
		.odash-card:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 10px 28px rgba(0,0,0,0.08); }
		.odash-card.is-disabled { opacity: .65; filter: grayscale(.15); }
		.odash-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; user-select:none; }
		.odash-switch-input { position:absolute; opacity:0; width:0; height:0; }
		.odash-switch-track { width: 44px; height: 24px; background:#e5e7eb; border-radius:999px; position: relative; transition: background .2s ease; display:inline-block; }
		.odash-switch-track::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:999px; box-shadow:0 1px 2px rgba(0,0,0,.15); transition: transform .2s ease; }
		.odash-switch-input:checked + .odash-switch-track { background:#22c55e; }
		.odash-switch-input:checked + .odash-switch-track::after { transform: translateX(20px); }
		/* Status badge on user cards */
		.odash-status-badge { position:absolute; top:10px; right:10px; padding:4px 8px; border-radius:999px; font-size:0.7rem; font-weight:800; letter-spacing:.02em; border:1px solid transparent; transition: transform .2s ease, background .2s ease, color .2s ease; }
		.odash-status-badge.active { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
		.odash-status-badge.inactive { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
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
						<div id="user-list" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:16px; align-items:stretch;">
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
	// Provide routes object to avoid undefined access in owner.js and settings scripts
	window.laravelData = window.laravelData || { routes: {} };
	window.laravelData.routes = Object.assign({}, window.laravelData.routes, {
		settings: '{{ route('owner.settings') }}',
		usersIndex: '{{ route('owner.users.index') }}',
		usersStore: '{{ route('owner.users.store') }}',
		usersToggle: '{{ route('owner.users.toggle') }}'
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
	@php 
		$userIndexRoute = \Illuminate\Support\Facades\Route::has('owner.users.index') ? route('owner.users.index') : null; 
		$userStoreRoute = \Illuminate\Support\Facades\Route::has('owner.users.store') ? route('owner.users.store') : null; 
		$userToggleRoute = \Illuminate\Support\Facades\Route::has('owner.users.toggle') ? route('owner.users.toggle') : null; 
	@endphp
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
				<div style="margin-top:6px;font-size:0.85rem;color:#6b7280;">Default password: <strong>password</strong> (users should change this after first login)</div>
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
	const toggleUrl = '{{ $userToggleRoute ?? '' }}';
	const indexUrl = '{{ $userIndexRoute ?? '' }}';

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
			// Password fields are optional in the modal; default password will be used when absent
			const passEl = document.getElementById('au-password');
			const pass = passEl ? passEl.value : 'password';
			const pass2El = document.getElementById('au-password-confirm');
			const pass2 = pass2El ? pass2El.value : 'password';

			if(!name){ showError('au-fullname','Full name is required'); ok=false; }
			if(!username){ showError('au-username','Username is required'); ok=false; }
			if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ showError('au-email','Valid email is required'); ok=false; }
			if(!role){ showError('au-role','Please select a role'); ok=false; }

			// If password fields are present, validate them; otherwise we'll set a safe default
			if(passEl){
				if(!pass || pass.length < 8){ showError('au-password','Min 8 characters'); ok=false; }
				if(pass2El && pass2 !== pass){ showError('au-password-confirm','Passwords do not match'); ok=false; }
			}
			return ok;
		}

		if(openBtn){ openBtn.addEventListener('click', open); }
		if(closeBtn){ closeBtn.addEventListener('click', close); }
		if(cancelBtn){ cancelBtn.addEventListener('click', close); }
		if(overlay){ overlay.addEventListener('click', close); }

		// Load users from API and render cards
		const cachedUsers = { list: [] };
		async function loadUsers(search = ''){
			if (!indexUrl || !userList) return;
			try{
				const url = new URL(indexUrl, window.location.origin);
				if (search) url.searchParams.set('search', search);
				const res = await fetch(url.toString(), { headers: { 'Accept':'application/json' } });
				const data = await res.json();
				if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to load users');
				cachedUsers.list = Array.isArray(data.users) ? data.users : [];
				renderUsers(cachedUsers.list);
			} catch(err){
				console.error(err);
				userList.innerHTML = '<div style="color:#ef4444;">Failed to load users.</div>';
			}
		}

		function renderUsers(items){
			userList.innerHTML = '';
			if (!Array.isArray(items) || items.length === 0) {
				userList.innerHTML = '<div style="color:#6b7280;">No users found.</div>';
				return;
			}
			items.forEach(u => {
				const payload = { name: u.name, username: u.username, email: u.email, role: u.role, phone: u.phone };
				const card = buildUserCard(payload, String(u.id), Boolean(u.is_active));
				userList.appendChild(card);
			});
		}

		// Initial load
		loadUsers();

		// Helper: Build a user card element with toggle and status badge
		function buildUserCard(payload, userId = '', enabled = true){
			const card = document.createElement('div');
			card.className = 'odash-list-item odash-card';
			card.style.cssText = [
				'width:200px',
				'height:300px',
				'display:flex',
				'flex-direction:column',
				'align-items:center',
				'justify-content:flex-start',
				'padding:14px',
				'border:1px solid #e5e7eb',
				'border-radius:12px',
				'background:#fff',
				'box-shadow:0 2px 8px rgba(0,0,0,0.04)',
				'position:relative'
			].join(';');
			if (userId) card.dataset.userId = userId;
			card.innerHTML = `
				<div class="odash-status-badge ${enabled ? 'active' : 'inactive'}">${enabled ? 'Active' : 'Inactive'}</div>
				<div style="flex:0 0 auto;display:flex;justify-content:center;width:100%;margin-top:8px;">
					<img src="{{ asset('images/profile.png') }}" alt="Avatar" style="width:88px;height:88px;border-radius:9999px;object-fit:cover;border:3px solid #eef2ff;">
				</div>
				<div style="flex:0 0 auto;text-align:center;margin-top:12px;">
					<div class="name" style="font-weight:800;color:#111827;">${payload.name}</div>
					<div class="sub" style="color:#6b7280;font-size:0.85rem;">@${payload.username}</div>
				</div>
				<div style="flex:0 0 auto;text-align:center;margin-top:8px;color:#6b7280;font-size:0.85rem;">${payload.email || 'N/A'}</div>
				<div style="flex:0 0 auto;text-align:center;margin-top:4px;color:#6b7280;font-size:0.85rem;">Phone: ${payload.phone || 'N/A'}</div>
				<div style="flex:1 1 auto"></div>
				<div style="flex:0 0 auto;margin-top:8px;display:flex;flex-direction:column;align-items:center;gap:8px;">
					<span class="odash-badge" style="background:#eef2ff;color:#1e3a8a;padding:6px 12px;border-radius:999px;font-weight:700;font-size:0.75rem;text-transform:capitalize;">${payload.role}</span>
					<div style="display:flex;align-items:center;gap:8px;">
						<label class="odash-switch">
							<input type="checkbox" class="odash-switch-input" ${enabled ? 'checked' : ''}>
							<span class="odash-switch-track"></span>
						</label>
						<span class="odash-switch-state" style="font-size:0.8rem;color:#374151;font-weight:700;">${enabled ? 'Enabled' : 'Disabled'}</span>
					</div>
				</div>
			`;

			const switchInput = card.querySelector('.odash-switch-input');
			const stateLabel = card.querySelector('.odash-switch-state');
			const badge = card.querySelector('.odash-status-badge');
			const setDisabledUI = (disabled) => {
				card.classList.toggle('is-disabled', disabled);
				if (stateLabel) stateLabel.textContent = disabled ? 'Disabled' : 'Enabled';
				if (badge) {
					badge.textContent = disabled ? 'Inactive' : 'Active';
					badge.classList.toggle('inactive', disabled);
					badge.classList.toggle('active', !disabled);
				}
			};
			setDisabledUI(!enabled);
			switchInput?.addEventListener('change', async () => {
				const wantEnabled = switchInput.checked;
				if (!wantEnabled) {
					const ok = confirm('Disable this account? Are you sure?');
					if (!ok) { switchInput.checked = true; return; }
				}

				if ('{{ $userToggleRoute ?? '' }}' && card.dataset.userId) {
					try {
						const resp = await fetch('{{ $userToggleRoute ?? '' }}', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
							body: JSON.stringify({ id: card.dataset.userId, enabled: wantEnabled })
						});
						const j = await resp.json().catch(()=>({}));
						if (!resp.ok || j.success === false) {
							alert(j.message || 'Failed to update account status');
							switchInput.checked = !wantEnabled;
							return;
						}
					} catch (err) {
						alert('Unable to update account status right now.');
						switchInput.checked = !wantEnabled;
						return;
					}
				}

				setDisabledUI(!wantEnabled);
			});

			return card;
		}

	form?.addEventListener('submit', async (e)=>{
			e.preventDefault();
			if(!validate()) return;

			const payload = {
				name: document.getElementById('au-fullname').value.trim(),
				username: document.getElementById('au-username').value.trim(),
				email: document.getElementById('au-email').value.trim(),
				role: document.getElementById('au-role').value,
				// Use provided password fields when present; otherwise default to 'password'
				password: (document.getElementById('au-password') ? document.getElementById('au-password').value : 'password'),
				password_confirmation: (document.getElementById('au-password-confirm') ? document.getElementById('au-password-confirm').value : 'password')
			};

			let serverData = null;

			// If an API route exists, attempt to POST; otherwise simulate UI update
			if(apiUrl){
				try{
					const res = await fetch(apiUrl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
						body: JSON.stringify(payload)
					});
					const data = await res.json().catch(()=>({}));
					serverData = data;
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
				const userId = (serverData && (serverData.user?.id || serverData.id)) ? (serverData.user?.id || serverData.id) : '';
				const card = buildUserCard(payload, userId, true);
				userList.prepend(card);
				// refresh cache
				loadUsers();
			}

			close();
		});
		// Wire up search input
		const searchEl = document.getElementById('user-search');
		let st;
		searchEl?.addEventListener('input', ()=>{
			clearTimeout(st);
			const val = searchEl.value.trim();
			st = setTimeout(()=> loadUsers(val), 250);
		});

	})();
	</script>

</body>
</html>
