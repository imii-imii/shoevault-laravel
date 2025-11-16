<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Master Controls - ShoeVault Batangas</title>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
	<link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">

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
		.notification-count { position: absolute; top: 2px; right: 2px; background: rgb(239, 68, 68); color: rgb(255, 255, 255); border-radius: 999px; padding: 2px 6px; font-size: 11px; display: inline-block; }
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

		/* Segmented toggle for Employee vs Customer management */
		.seg-toggle { display:inline-flex; gap:6px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:999px; padding:6px; box-shadow: inset 0 1px 0 rgba(255,255,255,.6); }
		.seg-toggle .seg-btn { border:0; background:transparent; padding:8px 14px; border-radius:999px; font-size:12px; font-weight:800; color:#64748b; cursor:pointer; transition: all .18s ease; }
		.seg-toggle .seg-btn:hover { color:#111827; }
		.seg-toggle .seg-btn.active { border:1px solid transparent; color:#0b1220; background:
			linear-gradient(#ffffff,#f8fbff) padding-box,
			linear-gradient(135deg, #000e2e 0%, #2343ce 100%) border-box;
			box-shadow: 0 8px 22px -10px rgba(35,67,206,.28);
		}

		/* Customer card states */
		.odash-status-badge.locked { background:#fef3c7; color:#92400e; border-color:#fde68a; }
		.odash-status-badge.banned { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
		.odash-card.is-locked { filter: grayscale(.2) brightness(.98); }
		.odash-card.is-banned { filter: grayscale(.45); border-color:#fecaca !important; box-shadow:0 4px 14px rgba(185,28,28,.12) !important; }

		/* Loading + animations */
		@keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
		@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
		.section-loader { position: absolute; inset: 0; background: rgba(255,255,255,0.85); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 20000; }
		.loader { width: 48px; height: 48px; border-radius: 50%; border: 3px solid #bfdbfe; border-top-color: #3b82f6; animation: spin 0.8s linear infinite; }
		.content-section { position: relative; }
		@keyframes spin { to { transform: rotate(360deg); } }
		.skeleton { position: relative; overflow: hidden; background: #eef2f7; border-radius: 10px; }
		.skeleton::after { content: ""; position: absolute; inset: 0; background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.6) 40%, rgba(255,255,255,0) 80%); background-size: 1000px 100%; animation: shimmer 1.2s infinite; opacity: .7; }
		.skeleton.line { height: 12px; margin: 6px 0; }
		.animate-in { animation: fadeInUp .35s ease both; }

		/* User cards grid layout improvements */
		#user-list, #customer-list {
			width: 100%;
			box-sizing: border-box;
		}

		/* Settings panel overflow prevention */
		.settings-panel {
			width: 100%;
			max-width: 100%;
			overflow-x: hidden;
			box-sizing: border-box;
		}

		#employee-management, #customer-management {
			width: 100%;
			max-width: 100%;
			overflow-x: hidden;
			box-sizing: border-box;
		}

		/* Responsive grid adjustments */
		@media (max-width: 1400px) {
			#user-list, #customer-list {
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
			}
		}

		@media (max-width: 1200px) {
			#user-list, #customer-list {
				grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
			}
		}

		@media (max-width: 768px) {
			#user-list, #customer-list {
				grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
				gap: 12px !important;
			}
		}

		/* Reset password button styling */
		.reset-password-btn { 
			transition: all 0.2s ease; 
			box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
		}
		.reset-password-btn:hover { 
			background: #d97706 !important; 
			transform: translateY(-1px);
			box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
		}
		.reset-password-btn:active { 
			transform: translateY(0); 
			box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
		}

		/* Clear notifications button styling */
		#clear-notifications-btn {
			transition: all 0.2s ease;
		}
		#clear-notifications-btn:hover {
			background: #b91c1c !important;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
		}
		#clear-notifications-btn:active {
			transform: translateY(0);
		}
	</style>
</head>
<body>
<!-- section-scoped loaders are created dynamically inside the active section -->
<!-- Sidebar Navigation -->
<nav class="sidebar">
	<div class="logo">
		<img src="{{ asset('images/shoevault-logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
		<div class="logo-text">
			<h2>ShoeVault Batangas</h2>
		</div>
	</div>

	<ul class="sidebar-nav">
		<li class="nav-item">
			<a href="{{ route('owner.dashboard') }}" class="nav-link">
				<i class="fas fa-chart-pie"></i>
				<span>Dashboard</span>
			</a>
		</li>
		<li class="nav-item">
			<a href="{{ route('owner.reports') }}" class="nav-link">
				<i class="fas fa-chart-bar"></i>
				<span>Reports</span>
			</a>
		</li>
		<li class="nav-item active">
			<a href="{{ route('owner.settings') }}" class="nav-link">
				<i class="fas fa-cog"></i>
				<span>Master Controls</span>
			</a>
		</li>
	</ul>

	<div class="sidebar-footer">
		<div class="user-info">
			<div class="user-avatar">
				@php
					$currentEmployee = auth()->user()->employee;
					$sidebarProfilePicture = $currentEmployee && $currentEmployee->profile_picture && file_exists(public_path($currentEmployee->profile_picture)) 
						? asset($currentEmployee->profile_picture) 
						: asset('images/profile.png');
				@endphp
				<img src="{{ $sidebarProfilePicture }}" alt="Owner">
			</div>
			<div class="user-details">
				<h4>{{ $currentEmployee->fullname ?? auth()->user()->username ?? 'Owner' }}</h4>
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
						<!-- Trendy account type toggle -->
						<div style="display:flex; gap:12px; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap;">
							<div class="seg-toggle" id="account-type-toggle" role="tablist" aria-label="Account type">
								<button type="button" class="seg-btn active" data-type="employees" aria-selected="true">Employees</button>
								<button type="button" class="seg-btn" data-type="customers" aria-selected="false">Customers</button>
							</div>
							<div style="display:flex; gap:10px; align-items:center;">
								<!-- Right side can hold extra actions for active segment if needed -->
							</div>
						</div>

						<!-- Employees management (existing) -->
						<div id="employee-management">
							<div style="display:flex; gap:12px; align-items:center; margin-bottom:14px;">
								<input type="text" id="user-search" placeholder="Search employees..." class="search-input" style="flex:1; min-width:180px;">
								<button class="btn btn-primary" id="add-user-btn">
									<i class="fas fa-user-plus"></i> Add User
								</button>
							</div>
							<div id="user-list" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; align-items:stretch; max-width:100%; overflow:hidden;"></div>
						</div>

						<!-- Customers management -->
						<div id="customer-management" style="display:none;">
							<div style="display:flex; gap:12px; align-items:center; margin-bottom:14px;">
								<input type="text" id="customer-search" placeholder="Search customers..." class="search-input" style="flex:1; min-width:180px;">
							</div>
							<div id="customer-list" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; align-items:stretch; max-width:100%; overflow:hidden;"></div>
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
									@php
										$employee = auth()->user()->employee;
										$profilePicture = $employee && $employee->profile_picture && file_exists(public_path($employee->profile_picture)) 
											? asset($employee->profile_picture) 
											: asset('assets/images/profile.png');
									@endphp
									<img id="settings-avatar-preview" src="{{ $profilePicture }}" alt="Avatar" class="avatar-preview">
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
										<input id="settings-name" type="text" placeholder="Your name" value="{{ $employee->fullname ?? auth()->user()->username }}">
									</div>
									<div class="form-group">
										<label for="settings-username">Username</label>
										<input id="settings-username" type="text" placeholder="Username" value="{{ auth()->user()->username ?? '' }}">
									</div>
									<div class="form-group">
										<label for="settings-email">Email</label>
										<input id="settings-email" type="email" placeholder="name@example.com" value="{{ $employee->email ?? '' }}">
									</div>
									<div class="form-group">
										<label for="settings-phone">Phone</label>
										<input id="settings-phone" type="tel" placeholder="+63 900 000 0000" value="{{ $employee->phone_number ?? '' }}">
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
											<input id="current-password" type="password" placeholder="Enter current password" autocomplete="off">
										</div>
										<div class="form-group">
											<label for="new-password">New Password</label>
											<input id="new-password" type="password" placeholder="Enter new password" autocomplete="new-password">
										</div>
										<div class="form-group">
											<label for="confirm-password">Confirm Password</label>
											<input id="confirm-password" type="password" placeholder="Confirm new password" autocomplete="new-password">
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

							<!-- Operating Hours Section -->
							<div style="margin-top:24px;padding-top:20px;border-top:1px solid #e5e7eb;">
								<div style="margin-bottom:16px;">
									<h4 style="color:#2563eb;font-weight:700;font-size:0.95rem;margin:0;display:flex;align-items:center;gap:8px;">
										<i class="fas fa-clock"></i>
										Operating Hours Control
									</h4>
									<p style="color:#6b7280;font-size:0.85rem;margin:4px 0 0 0;">Control when managers and cashiers can access the system</p>
								</div>

								<!-- Operating Hours Settings -->
								<div style="display:grid;gap:16px;">
									<!-- Operating Hours Enable/Disable -->
									<div style="display:flex;justify-content:space-between;align-items:center;">
										<div>
											<label style="font-weight:600;color:#374151;font-size:0.9rem;">Enforce Operating Hours</label>
											<p style="color:#6b7280;font-size:0.8rem;margin:2px 0 0 0;">Restrict manager/cashier access to operating hours only</p>
										</div>
										<label class="odash-switch">
											<input type="checkbox" id="operating-hours-enabled" class="odash-switch-input">
											<span class="odash-switch-track"></span>
										</label>
									</div>

									<!-- Operating Hours Time Settings -->
									<div id="operating-hours-times" style="display:grid;gap:12px;grid-template-columns:1fr 1fr;">
										<div>
											<label style="font-weight:600;color:#374151;font-size:0.9rem;display:block;margin-bottom:6px;">Opening Time</label>
											<input type="time" id="operating-hours-start" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;width:100%;box-sizing:border-box;">
										</div>
										<div>
											<label style="font-weight:600;color:#374151;font-size:0.9rem;display:block;margin-bottom:6px;">Closing Time</label>
											<input type="time" id="operating-hours-end" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;width:100%;box-sizing:border-box;">
										</div>
									</div>
								</div>

								<!-- Emergency Access Section -->
								<div style="margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
									<div style="margin-bottom:16px;">
										<h5 style="color:#dc2626;font-weight:700;font-size:0.9rem;margin:0;display:flex;align-items:center;gap:8px;">
											<i class="fas fa-shield-alt"></i>
											Emergency Access
										</h5>
										<p style="color:#6b7280;font-size:0.8rem;margin:4px 0 0 0;">Grant temporary access outside operating hours</p>
									</div>

									<!-- Emergency Access Duration Setting -->
									<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
										<label style="font-weight:600;color:#374151;font-size:0.9rem;">Duration (minutes):</label>
										<input type="number" id="emergency-access-duration" min="1" max="480" value="30" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;width:80px;">
									</div>

									<!-- Emergency Access Control -->
									<div id="emergency-access-control">
										<!-- Will be populated by JavaScript -->
									</div>
								</div>
							</div>

							<!-- Dangerous Actions Section -->
							<div style="margin-top:24px;padding-top:20px;border-top:1px solid #e5e7eb;">
								<div style="margin-bottom:12px;">
									<h4 style="color:#dc2626;font-weight:700;font-size:0.95rem;margin:0;display:flex;align-items:center;gap:8px;">
										<i class="fas fa-exclamation-triangle"></i>
										Dangerous Actions
									</h4>
									<p style="color:#6b7280;font-size:0.85rem;margin:4px 0 0 0;">These actions are irreversible and will affect all users.</p>
								</div>
								<button id="clear-notifications-btn" class="btn" style="background:#dc2626;color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:0.9rem;">
									<i class="fas fa-trash-alt"></i>
									Clear All Notifications
								</button>
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
		usersToggle: '{{ route('owner.users.toggle') }}',
		profileUpdate: '{{ route('owner.profile.update') }}',
		profilePictureRemove: '{{ route('owner.profile.picture.remove') }}'
	});
</script>
<script src="{{ asset('js/owner.js') }}"></script>
<script>
// Time/date header
document.addEventListener('DOMContentLoaded', function() {
	// Section-scoped loader helpers (settings page)
	function getActiveSection() { return document.querySelector('.content-section.active') || document.querySelector('.content-section'); }
	function ensureSectionPosition(sec) { if (!sec) return; const pos = window.getComputedStyle(sec).position; if (!pos || pos === 'static') sec.style.position = 'relative'; }
	function showSectionLoader() { const sec = getActiveSection(); if (!sec) return; ensureSectionPosition(sec); if (sec.querySelector('.section-loader')) return; const loader = document.createElement('div'); loader.className='section-loader'; loader.style.position='absolute'; loader.style.inset='0'; loader.style.display='flex'; loader.style.alignItems='center'; loader.style.justifyContent='center'; loader.style.background='rgba(255,255,255,0.85)'; loader.style.backdropFilter='blur(2px)'; loader.style.zIndex=20000; loader.innerHTML = '<div class="loader" aria-label="Loading"></div>'; sec.appendChild(loader); }
	function hideSectionLoader() { const sec = getActiveSection(); if (!sec) return; const loader = sec.querySelector('.section-loader'); if (loader) loader.remove(); }
	showSectionLoader();
	function updateDateTime() {
		const now = new Date();
		document.getElementById('current-time').textContent = now.toLocaleTimeString();
		document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', {
			weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
		});
	}
	updateDateTime();
	setInterval(updateDateTime, 1000);
	// Expose hide loader for later
	window.__hideSettingsLoader = hideSectionLoader;
});
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
				<div style="margin-top:6px;font-size:0.85rem;color:#6b7280;">Default password: <strong>manager123</strong> or <strong>cashier123</strong> (users should change this after first login)</div>
			</div>
			<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
				<button type="button" id="au-cancel" style="padding:10px 14px;border-radius:8px;background:#6b7280;color:#fff;border:none;font-weight:700;">Cancel</button>
				<button type="submit" id="au-save" style="padding:10px 14px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Save User</button>
			</div>
		</form>
	</div>

	<!-- Clear Notifications Modal -->
	<div id="clear-notifications-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;z-index:15000;"></div>
	<div id="clear-notifications-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.3);width:min(480px,94vw);max-height:90vh;overflow:auto;display:none;z-index:15001;border:2px solid #dc2626;">
		<div style="padding:20px 24px;border-bottom:1px solid #fecaca;background:#fef2f2;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
			<h3 style="margin:0;font-size:1.1rem;font-weight:800;color:#dc2626;display:flex;align-items:center;gap:10px;">
				<i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i>
				Dangerous Action
			</h3>
			<button id="clear-notifications-close" style="background:none;border:none;font-size:1.3rem;cursor:pointer;line-height:1;color:#dc2626;">&times;</button>
		</div>
		<div style="padding:20px 24px;">
			<div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;padding:16px;margin-bottom:20px;">
				<div style="display:flex;align-items:flex-start;gap:12px;">
					<i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:1.2rem;margin-top:2px;"></i>
					<div>
						<h4 style="margin:0 0 8px 0;color:#92400e;font-weight:700;font-size:0.95rem;">Warning: This action is irreversible!</h4>
						<p style="margin:0;color:#92400e;font-size:0.9rem;line-height:1.4;">
							This will permanently delete <strong>ALL notifications</strong> for <strong>ALL users</strong> in the system. 
							This includes:
						</p>
						<ul style="margin:8px 0 0 0;color:#92400e;font-size:0.85rem;padding-left:16px;">
							<li>Low stock alerts</li>
							<li>New reservation notifications</li>
							<li>System notifications</li>
							<li>All notification read statuses</li>
						</ul>
					</div>
				</div>
			</div>
			
			<form id="clear-notifications-form" style="display:grid;gap:16px;">
				<div style="display:grid;gap:8px;">
					<label for="clear-notifications-password" style="font-weight:600;color:#374151;font-size:0.9rem;">
						Enter your password to confirm this action:
					</label>
					<input id="clear-notifications-password" type="password" placeholder="Your current password" required autocomplete="current-password" style="padding:12px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:0.95rem;">
					<small id="clear-notifications-password-error" style="color:#ef4444;display:none;font-weight:500;"></small>
				</div>
				
				<div style="background:#f3f4f6;border-radius:8px;padding:12px;">
					<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem;color:#374151;">
						<input type="checkbox" id="confirm-action" required style="margin:0;">
						<span>I understand this action cannot be undone</span>
					</label>
				</div>
				
				<div style="display:flex;justify-content:flex-end;gap:12px;margin-top:8px;">
					<button type="button" id="clear-notifications-cancel" style="padding:12px 20px;border-radius:8px;background:#6b7280;color:#fff;border:none;font-weight:600;cursor:pointer;">
						Cancel
					</button>
					<button type="submit" id="clear-notifications-confirm" style="padding:12px 20px;border-radius:8px;background:#dc2626;color:#fff;border:none;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;" disabled>
						<i class="fas fa-trash-alt"></i>
						Clear All Notifications
					</button>
				</div>
			</form>
		</div>
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
	const apiUrl = @json($userStoreRoute ?? '');
	const toggleUrl = @json($userToggleRoute ?? '');
	const indexUrl = @json($userIndexRoute ?? '');
	const resetPasswordUrl = @json(route('owner.users.reset-password'));
	const customerIndexUrl = @json($customerIndexRoute ?? '');
	const customerToggleUrl = @json($customerToggleRoute ?? '');

	// URLs configured for user and customer management
	console.log('User and customer management URLs loaded');

	// Global variables to ensure they're accessible everywhere
	window.customerIndexUrl = customerIndexUrl;
	window.customerToggleUrl = customerToggleUrl;
	
	// Fallback for showSection if not loaded yet
	window.showSection = window.showSection || function(sectionId) {
		console.warn('showSection called but not yet loaded:', sectionId);
	};

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
		function injectUserSkeletons(count = 8){
			if (!userList) return;
			userList.innerHTML = '';
			for (let i=0;i<count;i++){
				const sk = document.createElement('div');
				sk.className = 'odash-card skeleton';
				sk.style.cssText = 'width:100%;max-width:220px;min-width:180px;height:320px;border-radius:12px;';
				userList.appendChild(sk);
			}
		}
		function animateUserCards(){
			Array.from(userList.children||[]).forEach((el,i)=>{ el.classList.add('animate-in'); el.style.animationDelay = `${i*30}ms`; });
		}
		async function loadUsers(search = ''){
			if (!indexUrl || !userList) return;
			try{
				injectUserSkeletons(8);
				const url = new URL(indexUrl, window.location.origin);
				if (search) url.searchParams.set('search', search);
				const res = await fetch(url.toString(), { headers: { 'Accept':'application/json' } });
				const data = await res.json();
				if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to load users');
				cachedUsers.list = Array.isArray(data.users) ? data.users : [];
				renderUsers(cachedUsers.list);
				animateUserCards();
				window.__hideSettingsLoader && window.__hideSettingsLoader();
			} catch(err){
				console.error(err);
				userList.innerHTML = '<div style="color:#ef4444;">Failed to load users.</div>';
				window.__hideSettingsLoader && window.__hideSettingsLoader();
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
				'width:100%',
				'max-width:220px',
				'min-width:180px',
				'height:320px',
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
					<button class="reset-password-btn" data-user-id="${userId}" data-user-name="${payload.name}" style="background:#f59e0b;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:0.75rem;font-weight:600;cursor:pointer;margin-bottom:4px;transition:background 0.2s;"><i class="fas fa-key" style="margin-right:4px;"></i>Reset Password</button>
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

				if (toggleUrl && card.dataset.userId) {
					try {
						const resp = await fetch(toggleUrl, {
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

			// Add reset password functionality
			const resetPasswordBtn = card.querySelector('.reset-password-btn');
			resetPasswordBtn?.addEventListener('click', async () => {
				const userId = resetPasswordBtn.dataset.userId;
				const userName = resetPasswordBtn.dataset.userName;
				
				if (!userId) return;

				// Show confirmation dialog
				const confirmed = confirm(`Are you sure you want to reset the password for ${userName}?\n\nThis will reset their password to the default password based on their role and they will be logged out immediately.`);
				if (!confirmed) return;

				try {
					const resp = await fetch(resetPasswordUrl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
						body: JSON.stringify({ id: userId })
					});
					const result = await resp.json().catch(()=>({}));
					
					if (!resp.ok || result.success === false) {
						alert(result.message || 'Failed to reset password');
						return;
					}

					// Show success message with the default password
					alert(`Password reset successful!\n\nNew password for ${userName}: ${result.default_password}\n\nPlease inform the employee of their new password. They have been logged out and will need to login again.`);
					
				} catch (err) {
					console.error('Password reset error:', err);
					alert('Unable to reset password right now. Please try again.');
				}
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
			};
			
			// Only include password fields if they have values
			const passwordField = document.getElementById('au-password');
			const passwordConfirmField = document.getElementById('au-password-confirm');
			if (passwordField && passwordField.value.trim()) {
				payload.password = passwordField.value;
				payload.password_confirmation = passwordConfirmField ? passwordConfirmField.value : passwordField.value;
			}

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
					
					// Show success message with default password if one was generated
					if (data.default_password) {
						alert(`Employee created successfully!\n\nUsername: ${payload.username}\nDefault Password: ${data.default_password}\n\nPlease inform the employee of their login credentials. They will be required to change their password on first login.`);
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

	<script>
	// Employee/Customer account type toggle and real customer data
	(function(){
		const toggle = document.getElementById('account-type-toggle');
		const emp = document.getElementById('employee-management');
		const cust = document.getElementById('customer-management');
		const customerListEl = document.getElementById('customer-list');
		const customerSearchEl = document.getElementById('customer-search');
		
		let cachedCustomers = { list: [] };
		
		if (toggle && emp && cust) {
			toggle.addEventListener('click', (e)=>{
				const btn = e.target.closest('.seg-btn');
				if (!btn) return;
				toggle.querySelectorAll('.seg-btn').forEach(b=>{
					b.classList.remove('active');
					b.setAttribute('aria-selected','false');
				});
				btn.classList.add('active');
				btn.setAttribute('aria-selected','true');
				const type = btn.getAttribute('data-type');
				if (type === 'customers') { 
					emp.style.display = 'none'; 
					cust.style.display=''; 
					// Load customers when switching to customer tab
					if (cachedCustomers.list.length === 0) {
						loadCustomers();
					}
				}
				else { cust.style.display='none'; emp.style.display=''; }
			});
		}

		// Load customers from API
		async function loadCustomers(search = '') {
			const custIndexUrl = window.customerIndexUrl || customerIndexUrl;
			if (!custIndexUrl || !customerListEl) {
				console.error('Customer index URL not available:', custIndexUrl);
				return;
			}
			
			try {
				// Show loading skeleton
				customerListEl.innerHTML = Array.from({length:6}).map(()=>'<div class="odash-card skeleton" style="width:100%;max-width:220px;min-width:180px;height:320px;border-radius:12px;"></div>').join('');
				
				const url = new URL(custIndexUrl, window.location.origin);
				if (search) url.searchParams.set('search', search);
				
				const res = await fetch(url.toString(), { headers: { 'Accept':'application/json' } });
				const data = await res.json();
				
				if (!res.ok || data.success === false) throw new Error(data.message || 'Failed to load customers');
				
				cachedCustomers.list = Array.isArray(data.customers) ? data.customers : [];
				renderCustomers(cachedCustomers.list);
			} catch(err) {
				console.error(err);
				customerListEl.innerHTML = '<div style="color:#ef4444;">Failed to load customers.</div>';
			}
		}

		function renderCustomers(items){
			if (!customerListEl) return;
			customerListEl.innerHTML = '';
			if (!Array.isArray(items) || items.length === 0) {
				customerListEl.innerHTML = '<div style="color:#6b7280;">No customers found.</div>';
				return;
			}
			items.forEach(c => customerListEl.appendChild(buildCustomerCard(c)));
		}

		function buildCustomerCard(c){
			const card = document.createElement('div');
			card.className = 'odash-list-item odash-card';
			card.dataset.customerId = c.id;
			
			const isLocked = !c.is_active;
			const isBanned = c.status === 'banned'; // Future enhancement
			
			if (isLocked) card.classList.add('is-locked');
			if (isBanned) card.classList.add('is-banned');
			
			const badgeClass = isBanned ? 'banned' : (isLocked ? 'locked' : 'active');
			const badgeText = isBanned ? 'Banned' : (isLocked ? 'Locked' : 'Active');
			
			card.style.cssText = [
				'width:100%','max-width:220px','min-width:180px','height:320px','display:flex','flex-direction:column','align-items:center','justify-content:flex-start','padding:14px',
				'border:1px solid #e5e7eb','border-radius:12px','background:#fff','box-shadow:0 2px 8px rgba(0,0,0,0.04)','position:relative'
			].join(';');
			
			card.innerHTML = `
				<div class="odash-status-badge ${badgeClass}">${badgeText}</div>
				<div style="flex:0 0 auto;display:flex;justify-content:center;width:100%;margin-top:8px;">
					<img src="{{ asset('images/profile.png') }}" alt="Avatar" style="width:88px;height:88px;border-radius:9999px;object-fit:cover;border:3px solid #eef2ff;">
				</div>
				<div style="flex:0 0 auto;text-align:center;margin-top:12px;">
					<div class="name" style="font-weight:800;color:#111827;">${c.name}</div>
					<div class="sub" style="color:#6b7280;font-size:0.85rem;">@${c.username}</div>
				</div>
				<div style="flex:0 0 auto;text-align:center;margin-top:8px;color:#6b7280;font-size:0.85rem;">${c.email || 'N/A'}</div>
				<div style="flex:1 1 auto"></div>
				<div style="flex:0 0 auto;margin-top:8px;display:flex;flex-direction:column;align-items:center;gap:10px;">
					<div style="display:flex;align-items:center;gap:10px;">
						<label class="odash-switch" title="Lock account">
							<input type="checkbox" class="odash-switch-input cust-lock" ${isLocked ? 'checked' : ''} ${isBanned ? 'disabled' : ''}>
							<span class="odash-switch-track"></span>
						</label>
						<span style="font-size:0.8rem;color:#374151;font-weight:700;">Lock</span>
					</div>
				</div>
			`;

			const badge = card.querySelector('.odash-status-badge');
			const lockInput = card.querySelector('.cust-lock');

			lockInput?.addEventListener('change', async ()=>{
				const wantLocked = lockInput.checked;
				const custToggleUrl = window.customerToggleUrl || customerToggleUrl;
				
				if (custToggleUrl && card.dataset.customerId) {
					try {
						const resp = await fetch(custToggleUrl, {
							method: 'POST',
							headers: { 
								'Content-Type': 'application/json', 
								'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
							},
							body: JSON.stringify({ 
								id: card.dataset.customerId, 
								action: 'lock',
								enabled: wantLocked 
							})
						});
						
						const result = await resp.json().catch(()=>({}));
						if (!resp.ok || result.success === false) {
							throw new Error(result.message || 'Failed to update customer status');
						}
						
						// Update UI
						card.classList.toggle('is-locked', wantLocked);
						const state = wantLocked ? 'Locked' : 'Active';
						const cls = wantLocked ? 'locked' : 'active';
						badge.textContent = state;
						badge.className = 'odash-status-badge ' + cls;
						
					} catch(err) {
						console.error(err);
						// Revert checkbox on error
						lockInput.checked = !wantLocked;
						alert('Failed to update customer status: ' + err.message);
					}
				}
			});

			return card;
		}

		function applyCustomerSearch(){
			const q = (customerSearchEl?.value || '').toLowerCase();
			let rows = [...cachedCustomers.list];
			if (q) rows = rows.filter(x => (
				String(x.name).toLowerCase().includes(q) || 
				String(x.username).toLowerCase().includes(q) || 
				String(x.email).toLowerCase().includes(q)
			));
			renderCustomers(rows);
		}

		let ct;
		customerSearchEl?.addEventListener('input', ()=>{ 
			clearTimeout(ct); 
			ct = setTimeout(applyCustomerSearch, 200); 
		});
	})();
	</script>

	<script>
	// Image Compression Function
	async function compressImageIfNeeded(file) {
		const maxSize = 2048 * 1024; // 2MB
		
		if (file.size <= maxSize) {
			console.log('File size OK, no compression needed');
			return file;
		}
		
		console.log('Compressing file...');
		
		return new Promise((resolve) => {
			const canvas = document.createElement('canvas');
			const ctx = canvas.getContext('2d');
			const img = new Image();
			
			img.onload = function() {
				// Calculate new dimensions to maintain aspect ratio
				let { width, height } = img;
				const maxDimension = 1200; // Max width or height
				
				if (width > height) {
					if (width > maxDimension) {
						height = (height * maxDimension) / width;
						width = maxDimension;
					}
				} else {
					if (height > maxDimension) {
						width = (width * maxDimension) / height;
						height = maxDimension;
					}
				}
				
				canvas.width = width;
				canvas.height = height;
				
				// Draw and compress
				ctx.drawImage(img, 0, 0, width, height);
				
				// Try different quality levels until we get under 2MB
				let quality = 0.8;
				let compressedFile;
				
				const tryCompress = (q) => {
					canvas.toBlob((blob) => {
						console.log(`Compression quality: ${q}`);
						
						if (blob.size <= maxSize || q <= 0.1) {
							// Create a new File object
							compressedFile = new File([blob], file.name, {
								type: 'image/jpeg',
								lastModified: Date.now()
							});
							console.log('File compression completed');
							resolve(compressedFile);
						} else {
							// Try with lower quality
							tryCompress(q - 0.1);
						}
					}, 'image/jpeg', q);
				};
				
				tryCompress(quality);
			};
			
			img.src = URL.createObjectURL(file);
		});
	}

	// Profile Management Functionality
	(function initProfileManagement() {
		const profileForm = document.getElementById('settings-profile-save');
		const avatarBtn = document.getElementById('settings-avatar-btn');
		const avatarInput = document.getElementById('settings-avatar');
		const avatarPreview = document.getElementById('settings-avatar-preview');
		const avatarRemove = document.getElementById('settings-avatar-remove');
		
		// Load current user data when page loads
		async function loadCurrentProfile() {
			try {
				// Get current user info from auth user
				const nameInput = document.getElementById('settings-name');
				const usernameInput = document.getElementById('settings-username');
				const emailInput = document.getElementById('settings-email');
				const phoneInput = document.getElementById('settings-phone');
				
				// Values are already populated from Blade template, but we can refresh them
				console.log('Profile form loaded');
				
			} catch (err) {
				console.error('Failed to load profile:', err);
			}
		}
		
		// Avatar upload button handler
		avatarBtn?.addEventListener('click', () => {
			avatarInput?.click();
		});
		
		// Avatar file selection handler
		avatarInput?.addEventListener('change', (e) => {
			const file = e.target.files[0];
			if (file) {
				// Preview the selected image
				const reader = new FileReader();
				reader.onload = (e) => {
					if (avatarPreview) {
						avatarPreview.src = e.target.result;
					}
				};
				reader.readAsDataURL(file);
			}
		});
		
		// Avatar remove button handler
		avatarRemove?.addEventListener('click', async () => {
			if (!confirm('Remove profile picture?')) return;
			
			try {
				const response = await fetch('{{ route('owner.profile.picture.remove') }}', {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					}
				});
				
				const result = await response.json();
				
				if (result.success) {
					// Reset to default avatar
					if (avatarPreview) {
						avatarPreview.src = '{{ asset('assets/images/profile.png') }}';
					}
					alert('Profile picture removed successfully!');
				} else {
					alert(result.message || 'Failed to remove profile picture');
				}
			} catch (err) {
				console.error('Error removing profile picture:', err);
				alert('Failed to remove profile picture');
			}
		});
		
		// Profile save button handler
		profileForm?.addEventListener('click', async (e) => {
			e.preventDefault();
			
			const nameInput = document.getElementById('settings-name');
			const usernameInput = document.getElementById('settings-username');
			const emailInput = document.getElementById('settings-email');
			const phoneInput = document.getElementById('settings-phone');
			
			if (!nameInput?.value || !usernameInput?.value || !emailInput?.value) {
				alert('Please fill in all required fields');
				return;
			}
			
			try {
				const formData = new FormData();
				formData.append('name', nameInput.value);
				formData.append('username', usernameInput.value);
				formData.append('email', emailInput.value);
				formData.append('phone', phoneInput.value || '');
				
				console.log('Preparing profile update form data');
				
				// Add profile picture if selected
				if (avatarInput?.files[0]) {
					const file = avatarInput.files[0];
					console.log('Profile picture selected for upload');
					
					const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
					if (!allowedTypes.includes(file.type)) {
						alert('Invalid file type. Please select a JPEG, PNG, GIF, or WebP image.');
						return;
					}
					
					// Compress image if needed
					const processedFile = await compressImageIfNeeded(file);
					formData.append('profile_picture', processedFile);
					console.log('Profile picture added to form data');
				}
				
				console.log('Sending profile update request...');
				
				const response = await fetch('{{ route('owner.profile.update') }}', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: formData
				});
				
				console.log('Profile update response received with status:', response.status);
				
				const result = await response.json();
				console.log('Profile update:', result.success ? 'Success' : 'Failed');
				
				if (result.success) {
					alert('Profile updated successfully!');
					
					// Refresh the page immediately to show all changes
					window.location.reload();
					
				} else {
					// Show detailed error message
					let errorMsg = result.message || 'Failed to update profile';
					if (result.errors) {
						console.error('Validation errors:', result.errors);
						errorMsg += '\n\nDetails:\n';
						Object.entries(result.errors).forEach(([field, messages]) => {
							errorMsg += `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}\n`;
						});
					}
					alert(errorMsg);
				}
				
			} catch (err) {
				console.error('Error updating profile:', err);
				alert('Failed to update profile: ' + err.message);
			}
		});
		
		// Initialize profile on page load
		loadCurrentProfile();
	})();

	// Password Change Functionality
	(function initPasswordChange() {
		const passwordForm = document.getElementById('change-password-form');
		
		passwordForm?.addEventListener('submit', async (e) => {
			e.preventDefault();
			
			const currentPassword = document.getElementById('current-password').value;
			const newPassword = document.getElementById('new-password').value;
			const confirmPassword = document.getElementById('confirm-password').value;
			
			// Validation
			if (!currentPassword || !newPassword || !confirmPassword) {
				alert('Please fill in all password fields');
				return;
			}
			
			if (newPassword !== confirmPassword) {
				alert('New passwords do not match');
				return;
			}
			
			if (newPassword.length < 8) {
				alert('New password must be at least 8 characters long');
				return;
			}
			
			try {
				const response = await fetch('{{ route('owner.password.update') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: JSON.stringify({
						current_password: currentPassword,
						password: newPassword,
						password_confirmation: confirmPassword
					})
				});
				
				const result = await response.json();
				console.log('Password update:', result.success ? 'Success' : 'Failed');
				
				if (result.success) {
					alert('Password updated successfully!');
					passwordForm.reset();
				} else {
					// Show detailed error message
					let errorMsg = result.message || 'Failed to update password';
					if (result.errors) {
						console.error('Password validation errors:', result.errors);
						errorMsg += '\n\nDetails:\n';
						Object.entries(result.errors).forEach(([field, messages]) => {
							errorMsg += `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}\n`;
						});
					}
					alert(errorMsg);
				}
				
			} catch (err) {
				console.error('Error updating password:', err);
				alert('Failed to update password: ' + err.message);
			}
		});
	})();

	// Clear Notifications Modal functionality
	(function initClearNotifications() {
		const openBtn = document.getElementById('clear-notifications-btn');
		const overlay = document.getElementById('clear-notifications-modal-overlay');
		const modal = document.getElementById('clear-notifications-modal');
		const closeBtn = document.getElementById('clear-notifications-close');
		const cancelBtn = document.getElementById('clear-notifications-cancel');
		const form = document.getElementById('clear-notifications-form');
		const passwordInput = document.getElementById('clear-notifications-password');
		const confirmCheckbox = document.getElementById('confirm-action');
		const confirmBtn = document.getElementById('clear-notifications-confirm');
		const passwordError = document.getElementById('clear-notifications-password-error');

		function openModal() {
			overlay.style.display = 'block';
			modal.style.display = 'block';
			passwordInput.focus();
		}

		function closeModal() {
			modal.style.display = 'none';
			overlay.style.display = 'none';
			form.reset();
			clearErrors();
			updateConfirmButton();
		}

		function clearErrors() {
			passwordError.style.display = 'none';
			passwordError.textContent = '';
			passwordInput.style.borderColor = '#e5e7eb';
		}

		function showPasswordError(message) {
			passwordError.textContent = message;
			passwordError.style.display = 'block';
			passwordInput.style.borderColor = '#ef4444';
		}

		function updateConfirmButton() {
			const hasPassword = passwordInput && passwordInput.value.trim();
			const isChecked = confirmCheckbox && confirmCheckbox.checked;
			const isValid = hasPassword && isChecked;
			
			if (confirmBtn) {
				confirmBtn.disabled = !isValid;
				confirmBtn.style.opacity = isValid ? '1' : '0.6';
				confirmBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
			}
		}



		// Event listeners
		openBtn?.addEventListener('click', openModal);
		closeBtn?.addEventListener('click', closeModal);
		cancelBtn?.addEventListener('click', closeModal);
		overlay?.addEventListener('click', closeModal);
		
		// Update button state when inputs change
		passwordInput?.addEventListener('input', () => {
			clearErrors();
			updateConfirmButton();
		});
		
		confirmCheckbox?.addEventListener('change', updateConfirmButton);

		// Form submission
		form?.addEventListener('submit', async (e) => {
			e.preventDefault();
			clearErrors();

			const password = passwordInput.value.trim();
			if (!password) {
				showPasswordError('Password is required');
				return;
			}

			if (!confirmCheckbox.checked) {
				alert('Please confirm that you understand this action cannot be undone');
				return;
			}

			// Double confirmation
			const finalConfirm = confirm(
				'FINAL CONFIRMATION:\n\n' +
				'This will permanently delete ALL notifications for ALL users in the system.\n\n' +
				'Are you absolutely sure you want to proceed?\n\n' +
				'This action CANNOT be undone!'
			);

			if (!finalConfirm) {
				return;
			}

			// Disable button and show loading
			confirmBtn.disabled = true;
			confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';

			try {
				const response = await fetch('{{ route('owner.notifications.clear') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: JSON.stringify({
						password: password
					})
				});

				const result = await response.json();

				if (response.ok && result.success) {
					// Success
					closeModal();
					alert(
						'All notifications have been cleared successfully!\n\n' +
						'Notifications cleared: ' + (result.notifications_cleared || 0) + '\n' +
						'Read statuses cleared: ' + (result.reads_cleared || 0)
					);
					
					// Refresh the notification count in the header if present
					const notificationCount = document.querySelector('.notification-count');
					if (notificationCount) {
						notificationCount.textContent = '0';
						notificationCount.style.display = 'none';
					}

					// Clear notification dropdown if present
					const notificationList = document.querySelector('.notification-list');
					if (notificationList) {
						notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-inbox"></i> No new notifications</div>';
					}

				} else {
					// Handle errors
					if (result.error === 'invalid_password') {
						showPasswordError('Invalid password. Please try again.');
					} else {
						alert('Failed to clear notifications: ' + (result.message || 'Unknown error'));
					}
				}

			} catch (error) {
				alert('Failed to clear notifications. Please try again.');
			} finally {
				// Reset button
				confirmBtn.disabled = false;
				confirmBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear All Notifications';
				updateConfirmButton();
			}
		});

		// Initial button state
		updateConfirmButton();
	})();

	// Operating Hours Management
	(function() {
		let emergencyAccessTimer = null;
		
		// Load current settings
		async function loadOperatingHoursSettings() {
			try {
				const response = await fetch('{{ route("owner.operating-hours.get") }}', {
					headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
				});
				const data = await response.json();
				
				if (data.success) {
					const settings = data.settings;
					document.getElementById('operating-hours-enabled').checked = settings.operating_hours_enabled;
					document.getElementById('operating-hours-start').value = settings.operating_hours_start;
					document.getElementById('operating-hours-end').value = settings.operating_hours_end;
					document.getElementById('emergency-access-duration').value = settings.emergency_access_duration;
					
					updateOperatingHoursUI();
					updateEmergencyAccessUI(settings.emergency_access_enabled, settings.emergency_access_expires_at);
				}
			} catch (error) {
				console.error('Failed to load operating hours settings:', error);
			}
		}

		// Update operating hours settings
		async function updateOperatingHoursSetting(key, value) {
			try {
				const response = await fetch('{{ route("owner.operating-hours.update") }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: JSON.stringify({ key, value })
				});
				const data = await response.json();
				
				if (!data.success) {
					alert('Failed to update setting: ' + (data.message || 'Unknown error'));
				}
			} catch (error) {
				console.error('Failed to update operating hours setting:', error);
				alert('Failed to update setting. Please try again.');
			}
		}

		// Update UI based on operating hours enabled state
		function updateOperatingHoursUI() {
			const enabled = document.getElementById('operating-hours-enabled').checked;
			const timesDiv = document.getElementById('operating-hours-times');
			timesDiv.style.opacity = enabled ? '1' : '0.5';
			
			const timeInputs = timesDiv.querySelectorAll('input[type="time"]');
			timeInputs.forEach(input => input.disabled = !enabled);
			
			// Update emergency access button state
			showEmergencyAccessDisabled();
		}

		// Update emergency access UI
		function updateEmergencyAccessUI(isActive, expiresAt) {
			const control = document.getElementById('emergency-access-control');
			
			if (isActive && expiresAt) {
				const now = new Date();
				const expires = new Date(expiresAt);
				const remainingMs = expires - now;
				
				if (remainingMs > 0) {
					const remainingMin = Math.floor(remainingMs / 60000);
					const remainingSec = Math.floor((remainingMs % 60000) / 1000);
					control.innerHTML = `
						<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;">
							<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
								<span style="color:#dc2626;font-weight:600;font-size:0.9rem;">
									<i class="fas fa-shield-alt"></i> Emergency Access Active
								</span>
								<button id="disable-emergency-access" style="background:#dc2626;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8rem;cursor:pointer;">
									Disable
								</button>
							</div>
							<div id="emergency-timer" style="color:#991b1b;font-weight:600;font-size:0.85rem;">
								Time remaining: <span id="time-remaining">${remainingMin}:${remainingSec.toString().padStart(2, '0')}</span>
							</div>
						</div>
					`;
					
					// Start countdown timer
					startEmergencyTimer(expires);
					
					// Bind disable button
					document.getElementById('disable-emergency-access').addEventListener('click', disableEmergencyAccess);
				} else {
					// Expired, show as disabled
					showEmergencyAccessDisabled();
				}
			} else {
				showEmergencyAccessDisabled();
			}
		}

		// Show emergency access as disabled
		function showEmergencyAccessDisabled() {
			const control = document.getElementById('emergency-access-control');
			const operatingHoursEnabled = document.getElementById('operating-hours-enabled').checked;
			const isDisabled = !operatingHoursEnabled;
			
			control.innerHTML = `
				<button id="enable-emergency-access" style="background:${isDisabled ? '#9ca3af' : '#dc2626'};color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;cursor:${isDisabled ? 'not-allowed' : 'pointer'};transition:all 0.2s;display:flex;align-items:center;gap:8px;font-size:0.9rem;" ${isDisabled ? 'disabled' : ''}>
					<i class="fas fa-shield-alt"></i>
					Enable Emergency Access
				</button>
				${isDisabled ? '<p style="color:#6b7280;font-size:0.8rem;margin:8px 0 0 0;font-style:italic;">Emergency access is only available when operating hours are enforced</p>' : ''}
			`;
			
			if (!isDisabled) {
				document.getElementById('enable-emergency-access').addEventListener('click', enableEmergencyAccess);
			}
		}

		// Start emergency access countdown timer
		function startEmergencyTimer(expiresAt) {
			if (emergencyAccessTimer) {
				clearInterval(emergencyAccessTimer);
			}
			
			emergencyAccessTimer = setInterval(() => {
				const now = new Date();
				const remainingMs = expiresAt - now;
				
				if (remainingMs <= 0) {
					clearInterval(emergencyAccessTimer);
					showEmergencyAccessDisabled();
					showToast('Emergency access has expired', 'warning');
					return;
				}
				
				const remainingMin = Math.floor(remainingMs / 60000);
				const remainingSec = Math.floor((remainingMs % 60000) / 1000);
				const timeSpan = document.getElementById('time-remaining');
				if (timeSpan) {
					timeSpan.textContent = `${remainingMin}:${remainingSec.toString().padStart(2, '0')}`;
				}
			}, 1000);
		}

		// Enable emergency access
		async function enableEmergencyAccess() {
			const duration = parseInt(document.getElementById('emergency-access-duration').value);
			
			if (!duration || duration < 1) {
				showToast('Please enter a valid duration in minutes', 'error');
				return;
			}
			
			try {
				const response = await fetch('{{ route("owner.emergency-access.enable") }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: JSON.stringify({ duration })
				});
				const data = await response.json();
				
				if (data.success) {
					updateEmergencyAccessUI(true, data.expires_at);
					showToast(`Emergency access enabled for ${duration} minutes`, 'success');
				} else {
					showToast('Failed to enable emergency access: ' + (data.message || 'Unknown error'), 'error');
				}
			} catch (error) {
				console.error('Failed to enable emergency access:', error);
				showToast('Failed to enable emergency access. Please try again.', 'error');
			}
		}

		// Disable emergency access
		async function disableEmergencyAccess() {
			if (!confirm('Are you sure you want to disable emergency access?')) {
				return;
			}
			
			try {
				const response = await fetch('{{ route("owner.emergency-access.disable") }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					}
				});
				const data = await response.json();
				
				if (data.success) {
					if (emergencyAccessTimer) {
						clearInterval(emergencyAccessTimer);
					}
					showEmergencyAccessDisabled();
					showToast('Emergency access disabled', 'success');
				} else {
					showToast('Failed to disable emergency access: ' + (data.message || 'Unknown error'), 'error');
				}
			} catch (error) {
				console.error('Failed to disable emergency access:', error);
				showToast('Failed to disable emergency access. Please try again.', 'error');
			}
		}

		// Toast notification system
		function showToast(message, type = 'info') {
			// Remove existing toast
			const existingToast = document.getElementById('operating-hours-toast');
			if (existingToast) {
				existingToast.remove();
			}

			// Create toast
			const toast = document.createElement('div');
			toast.id = 'operating-hours-toast';
			toast.style.cssText = `
				position: fixed;
				top: 20px;
				right: 20px;
				z-index: 10000;
				padding: 12px 16px;
				border-radius: 8px;
				color: white;
				font-weight: 600;
				font-size: 0.9rem;
				box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
				transform: translateX(100%);
				transition: transform 0.3s ease;
				max-width: 300px;
				display: flex;
				align-items: center;
				gap: 8px;
			`;

			// Set colors based on type
			switch (type) {
				case 'success':
					toast.style.background = '#10b981';
					toast.innerHTML = '<i class="fas fa-check-circle"></i>' + message;
					break;
				case 'error':
					toast.style.background = '#dc2626';
					toast.innerHTML = '<i class="fas fa-exclamation-circle"></i>' + message;
					break;
				case 'warning':
					toast.style.background = '#f59e0b';
					toast.innerHTML = '<i class="fas fa-exclamation-triangle"></i>' + message;
					break;
				default:
					toast.style.background = '#3b82f6';
					toast.innerHTML = '<i class="fas fa-info-circle"></i>' + message;
			}

			document.body.appendChild(toast);

			// Animate in
			setTimeout(() => {
				toast.style.transform = 'translateX(0)';
			}, 10);

			// Auto remove after 3 seconds
			setTimeout(() => {
				toast.style.transform = 'translateX(100%)';
				setTimeout(() => {
					if (toast.parentNode) {
						toast.remove();
					}
				}, 300);
			}, 3000);
		}

		// Event listeners
		document.getElementById('operating-hours-enabled').addEventListener('change', function() {
			const enabled = this.checked;
			updateOperatingHoursSetting('operating_hours_enabled', enabled);
			updateOperatingHoursUI();
			showEmergencyAccessDisabled(); // Update emergency access button state
			showToast(enabled ? 'Operating hours enforcement enabled' : 'Operating hours enforcement disabled', 'success');
		});

		document.getElementById('operating-hours-start').addEventListener('change', function() {
			const time = this.value;
			updateOperatingHoursSetting('operating_hours_start', time);
			showToast(`Opening time updated to ${time}`, 'success');
		});

		document.getElementById('operating-hours-end').addEventListener('change', function() {
			const time = this.value;
			updateOperatingHoursSetting('operating_hours_end', time);
			showToast(`Closing time updated to ${time}`, 'success');
		});

		document.getElementById('emergency-access-duration').addEventListener('change', function() {
			updateOperatingHoursSetting('emergency_access_duration', parseInt(this.value));
		});

		// Initialize on page load
		document.addEventListener('DOMContentLoaded', loadOperatingHoursSettings);
	})();
	</script>
<script src="{{ asset('js/notifications.js') }}"></script>
<script>
// Initialize notifications for settings page
function initNotifications() {
    if (window.notificationManager) {
        console.log('Initializing notification manager...');
        try {
            window.notificationManager.init('{{ auth()->user()->role ?? "owner" }}');
            return true;
        } catch (e) {
            console.warn('notificationManager init failed:', e);
        }
    }
    
    // Fallback notification toggle
    console.log('Using fallback notification system');
    document.querySelectorAll('.notification-wrapper').forEach(wrapper => {
        const bell = wrapper.querySelector('.notification-bell');
        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                wrapper.classList.toggle('open');
            });
        }
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.notification-wrapper.open').forEach(w => w.classList.remove('open'));
    });
    return false;
}

// Try to initialize notifications with retries
document.addEventListener('DOMContentLoaded', function() {
    let attempts = 0;
    const maxAttempts = 10;
    const retryDelay = 100;
    
    function tryInit() {
        attempts++;
        if (initNotifications()) {
            console.log('Notifications initialized');
            return;
        }
        
        if (attempts < maxAttempts) {
            setTimeout(tryInit, retryDelay);
        } else {
            console.log('Max attempts reached, using fallback');
        }
    }
    
    tryInit();
});
</script>
@include('partials.mobile-blocker')

</body>
</html>