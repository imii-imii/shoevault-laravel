@extends('layouts.app')

@section('title', 'Supplier Management - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
.logout-btn i{font-size:1rem}
/* Scoped modal styles to avoid global .modal conflicts */


#add-supplier-modal.show{ display:flex !important; }
#add-supplier-modal .modal-content{max-width:100%; display:flex; flex-direction:column; justify-content:flex-start;}

#add-supplier-modal{background: none; border: none; box-shadow: none;}
.modal-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.modal-header h3{ margin:0; font-size:1.05rem; font-weight:800; }
.close-btn{ background:transparent; border:none; font-size:1.4rem; line-height:1; padding:6px 8px; cursor:pointer; color: #374151; border-radius:8px; transition: background .12s, transform .08s; }
.close-btn:hover{ background: rgba(0,0,0,0.04); transform: translateY(-1px); }
.modal-form .form-group { margin-bottom:10px; }
.modal-form .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.brand-chip { margin-right:8px; }
/* Supplier cards */
.cards-container{ display:grid; gap:12px; }
.supplier-card{ background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.04); padding:14px; }
.supplier-card .card-main{ display:flex; align-items:center; justify-content:space-between; gap:14px; }
.supplier-card .info{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.supplier-card .info .name{ font-weight:800; font-size:1rem; color:#111827; }
.supplier-card .meta{ display:flex; gap:14px; color:#374151; font-size:.9rem; flex-wrap:wrap; }
.supplier-card .actions{ display:flex; gap:8px; }
.supplier-card .logs-panel{ margin-top:12px; display:none; border:1px solid rgba(15,23,42,0.06); border-radius:14px; background:linear-gradient(180deg, rgba(255,255,255,0.8), rgba(250,250,255,0.75)); backdrop-filter: blur(10px) saturate(110%); -webkit-backdrop-filter: blur(10px) saturate(110%); box-shadow: 0 16px 40px rgba(2,8,23,0.08), inset 0 1px 0 rgba(255,255,255,0.5); overflow:hidden; }
.supplier-card .logs-header{ display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:linear-gradient(135deg, rgba(59,130,246,.12), rgba(59,130,246,.06)); border-bottom:1px solid rgba(15,23,42,0.06); }
.supplier-card .logs-title{ font-weight:800; font-size:.95rem; color:#0f172a; display:flex; align-items:center; gap:8px; }
.supplier-card .logs-body{ padding:12px; display:grid; gap:12px; }
.supplier-card .log-form{ display:grid; grid-template-columns:1.1fr .8fr .7fr 1fr auto; gap:10px; align-items:end; background:rgba(255,255,255,.6); border:1px solid rgba(15,23,42,0.06); border-radius:12px; padding:10px; box-shadow: inset 0 1px 0 rgba(255,255,255,.6); }
.supplier-card .log-form label{ font-size:.8rem; color:#475569; font-weight:600; }
.supplier-card .log-form input{ border:1px solid #e5e7eb; border-radius:10px; padding:8px 10px; }
.supplier-card .logs-list{ display:grid; gap:10px; }
.log-item{ display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid rgba(15,23,42,0.08); border-radius:12px; background:linear-gradient(180deg,#ffffff,#f8fafc); box-shadow:0 6px 18px rgba(2,8,23,0.06); transition: transform .12s ease, box-shadow .12s ease; }
.log-item:hover{ transform: translateY(-1px); box-shadow:0 10px 24px rgba(2,8,23,0.08); }
.log-item .left{ display:flex; gap:10px; align-items:center; }
.badge{ display:inline-block; padding:6px 10px; border-radius:9999px; font-size:.75rem; font-weight:800; letter-spacing:.2px; box-shadow: inset 0 1px 0 rgba(255,255,255,.9); }
.badge.brand{ background:linear-gradient(135deg,#e0e7ff,#c7d2fe); color:#1e3a8a; }
.badge.size{ background:linear-gradient(135deg,#cffafe,#a5f3fc); color:#0e7490; }
.badge.qty{ background:linear-gradient(135deg,#d9f99d,#bbf7d0); color:#3f6212; }
.toggle-btn{ background:linear-gradient(135deg,#eef2ff,#e0e7ff); color:#1e3a8a; border:1px solid rgba(30,58,138,.15); border-radius:10px; padding:8px 12px; cursor:pointer; transition: transform .08s ease, filter .12s ease; box-shadow: 0 6px 16px rgba(30,58,138,.12); }
.toggle-btn:hover{ filter: brightness(1.03); transform: translateY(-1px); }

/* Edit Supplier Modal styles (separate from Add) */
#edit-supplier-modal{background: none; border: none; box-shadow: none}
#edit-supplier-modal.show{ display:flex !important; }
#edit-supplier-modal .modal-content{max-width:100%; min-height:560px; display:flex; flex-direction:column; justify-content:flex-start; align-items:stretch;}
@keyframes slideFadeIn { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform: translateY(0);} }
.supplier-card .logs-panel.open .logs-body{ animation: slideFadeIn .18s ease both; }
.supplier-card .logs-panel.open .logs-header{ animation: slideFadeIn .18s ease both; }
/* Modal zoom animations (open = zoom-in, close = zoom-out)
    Added so supplier modals (which use the `.show` class) have the same
    smooth zoom behavior as other inventory modals. */
@keyframes modalZoomIn { from { opacity: 0; transform: translateY(-8px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes modalZoomOut { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(-8px) scale(.96); } }
.modal.show .modal-content { animation: modalZoomIn 260ms cubic-bezier(.2,.9,.3,1) both; }
.modal .modal-content.zoom-out { animation: modalZoomOut 220ms cubic-bezier(.4,.0,.2,1) both; }
/* Card container loading + entry animation */
.cards-container { position: relative; }
.cards-container .loading-overlay{ position:absolute; inset:0; display:grid; place-items:center; gap:8px; background:linear-gradient(180deg, rgba(255,255,255,0.92), rgba(248,250,252,0.88)); backdrop-filter: blur(6px) saturate(110%); -webkit-backdrop-filter: blur(6px) saturate(110%); border-radius:12px; z-index:30; }
.cards-container .loading-overlay i{ font-size:18px; color:#6b7280 }
.cards-container.animate-entry > .supplier-card { animation: slideFadeIn 420ms ease both; }
.cards-container.animate-entry > .supplier-card:nth-child(1){ animation-delay: 40ms }
.cards-container.animate-entry > .supplier-card:nth-child(2){ animation-delay: 80ms }
.cards-container.animate-entry > .supplier-card:nth-child(3){ animation-delay: 120ms }
.cards-container.animate-entry > .supplier-card:nth-child(4){ animation-delay: 160ms }
.cards-container.animate-entry > .supplier-card:nth-child(5){ animation-delay: 200ms }
.cards-container.animate-entry > .supplier-card:nth-child(6){ animation-delay: 240ms }
.cards-container.animate-entry > .supplier-card:nth-child(7){ animation-delay: 280ms }
.cards-container.animate-entry > .supplier-card:nth-child(8){ animation-delay: 320ms }

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
        <li class="nav-item active">
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
        <li class="nav-item">
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
            <h1 class="main-title">Supplier Management</h1>
        </div>
        <div class="header-right" style="position:relative;">
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
        <!-- Supplier Section -->
        <section id="supplier" class="content-section active">
            <div class="section-header">
                <h2 style="display:flex;align-items:center;gap:12px;"><i class="fas fa-people-carry-box"></i>Supplier Management</h2>
                <button class="btn btn-primary" onclick="openAddSupplierModal()">
                    <i class="fas fa-plus"></i> Add Supplier
                </button>
            </div>
            <div class="filters">
                <input type="text" id="search-supplier" placeholder="Search suppliers..." class="search-input">
            </div>
            <div class="cards-container" id="supplier-cards">
                @foreach($suppliers ?? [] as $supplier)
                <div class="supplier-card" data-id="{{ $supplier->id }}"
                    data-name="{{ $supplier->name }}"
                    data-contact_person="{{ $supplier->contact_person }}"
                    data-country="{{ $supplier->country ?? 'N/A' }}"
                    data-mobile="{{ $supplier->email }}"
                    data-status="{{ $supplier->status ?? 'active' }}">
                    <div class="card-main">
                        <div class="info">
                            <div class="name">#{{ $supplier->id }} • {{ $supplier->name }}</div>
                            <div class="meta"><span><i class="fas fa-user"></i> {{ $supplier->contact_person }}</span></div>
                            <div class="meta"><span><i class="fas fa-earth-asia"></i> {{ $supplier->country ?? 'N/A' }}</span></div>
                            <div class="meta"><span><i class="fas fa-mobile-alt"></i> {{ $supplier->email }}</span></div>
                            <div><span class="status-badge {{ $supplier->status ?? 'active' }}">{{ ucfirst($supplier->status ?? 'active') }}</span></div>
                        </div>
                        <div class="actions">
                            <button class="btn btn-sm btn-primary" onclick="editSupplier({{ $supplier->id }})" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="toggle-btn" onclick="toggleSupplyLogs({{ $supplier->id }})" id="toggle-logs-{{ $supplier->id }}"><i class="fas fa-clipboard-list"></i> Logs</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="logs-panel" id="logs-{{ $supplier->id }}" data-loaded="false">
                        <div class="logs-header">
                            <div class="logs-title"><i class="fas fa-clipboard-list"></i> Supply Logs</div>
                            <div class="logs-cta" style="font-size:.8rem;color:#475569;">Add a new log below</div>
                        </div>
                        <div class="logs-body">
                            <form class="log-form" data-supplier-id="{{ $supplier->id }}">
                                <div>
                                    <label>Brand</label>
                                    <input type="text" name="brand" required>
                                </div>
                                <div>
                                    <label>Size</label>
                                    <input type="text" name="size" placeholder="e.g., 8 or 42">
                                </div>
                                <div>
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" min="1" required>
                                </div>
                                <div>
                                    <label>Received At</label>
                                    <input type="datetime-local" name="received_at">
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </form>
                            <div class="logs-list"><div class="logs-empty" style="color:#6b7280;">Logs are hidden. Click "Logs" to load.</div></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </div>
</main>

<!-- Modal overlay for supplier modals -->
<div id="modal-overlay" class="modal-overlay active" style="display: block;"></div>

<!-- Add Supplier Modal -->
<div id="add-supplier-modal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Supplier</h3>
            <button class="close-btn" onclick="closeModal('add-supplier-modal')">&times;</button>
        </div>
        <form id="add-supplier-form" class="modal-form">
            <div class="form-group">
                <label for="supplier-name">Supplier Name</label>
                <input type="text" id="supplier-name" required>
            </div>
            <div class="form-group">
                <label for="supplier-contact">Contact Person</label>
                <input type="text" id="supplier-contact" required>
            </div>
            <div class="form-group">
                <label for="supplier-country">Country</label>
                <input type="text" id="supplier-country" placeholder="e.g., Philippines" required>
            </div>
            <div class="form-group">
                <label for="supplier-mobile">Mobile Number</label>
                <input type="tel" id="supplier-mobile" placeholder="e.g., +63 912 345 6789" required>
            </div>
            <!-- Email input replaced with mobile -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-supplier-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Supplier Modal (separate) -->
<div id="edit-supplier-modal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Supplier</h3>
            <button class="close-btn" onclick="closeModal('edit-supplier-modal')">&times;</button>
        </div>
        <form id="edit-supplier-form" class="modal-form">
            <input type="hidden" id="edit-supplier-id" value="" />
            <div class="form-group">
                <label for="edit-supplier-name">Supplier Name</label>
                <input type="text" id="edit-supplier-name" required>
            </div>
            <div class="form-group">
                <label for="edit-supplier-contact">Contact Person</label>
                <input type="text" id="edit-supplier-contact" required>
            </div>
            <div class="form-group">
                <label for="edit-supplier-country">Country</label>
                <input type="text" id="edit-supplier-country" placeholder="e.g., Philippines" required>
            </div>
            <div class="form-group">
                <label for="edit-supplier-mobile">Mobile Number</label>
                <input type="tel" id="edit-supplier-mobile" placeholder="e.g., +63 912 345 6789" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('edit-supplier-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    
</div>

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

// Modal functions — use class toggles and append to body to avoid clipping
function openAddSupplierModal() {
    const modal = document.getElementById('add-supplier-modal');
    if (!modal) return;
    if (modal.parentElement !== document.body) document.body.appendChild(modal);
    // reset form before opening
    const form = document.getElementById('add-supplier-form');
    if (form) form.reset();
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    // show overlay
    const overlay = document.getElementById('modal-overlay');
    if (overlay) { overlay.style.display = 'block'; overlay.classList.add('active'); }
    document.body.style.overflow = 'hidden';
    // autofocus first form element
    setTimeout(()=>{
        const first = modal.querySelector('input, select, textarea, button');
        if (first) first.focus();
    }, 40);
}

function openEditSupplierModal() {
    const modal = document.getElementById('edit-supplier-modal');
    if (!modal) return;
    if (modal.parentElement !== document.body) document.body.appendChild(modal);
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    // show overlay
    const overlay = document.getElementById('modal-overlay');
    if (overlay) { overlay.style.display = 'block'; overlay.classList.add('active'); }
    document.body.style.overflow = 'hidden';
    setTimeout(()=>{
        const first = modal.querySelector('input, select, textarea, button');
        if (first) first.focus();
    }, 40);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    // play zoom-out on modal content (if present) then hide
    const content = modal.querySelector('.modal-content');
    const duration = 200; // ms - should match CSS animation timing
    if (content) {
        content.classList.add('zoom-out');
        setTimeout(() => {
            content.classList.remove('zoom-out');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.style.display = 'none';
        }, duration);
    } else {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.style.display = 'none';
    }

    // hide overlay after animation
    const overlay = document.getElementById('modal-overlay');
    if (overlay) { setTimeout(() => { overlay.style.display = 'none'; overlay.classList.remove('active'); }, duration); }
    document.body.style.overflow = 'auto';
}

// editSupplier implemented below (opens the dedicated edit modal)

function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        console.log('Deleting supplier...');
        // Implement delete supplier functionality
    }
}

// Close modal when clicking outside (overlay)
window.addEventListener('click', function(e) {
    if (e.target && e.target.matches && e.target.matches('.modal')) {
        // use closeModal to ensure zoom-out animation plays
        if (e.target.id) closeModal(e.target.id);
        else {
            e.target.classList.remove('show');
            e.target.setAttribute('aria-hidden', 'true');
            e.target.style.display = 'none';
        }
    }
});

// Ensure the modal is attached to document.body early so it can't be clipped by transformed/overflowing ancestors
document.addEventListener('DOMContentLoaded', function(){
    const addModal = document.getElementById('add-supplier-modal');
    if (addModal && addModal.parentElement !== document.body) document.body.appendChild(addModal);
    const editModal = document.getElementById('edit-supplier-modal');
    if (editModal && editModal.parentElement !== document.body) document.body.appendChild(editModal);
    const overlay = document.getElementById('modal-overlay');
    if (overlay) { overlay.style.display = 'none'; overlay.classList.remove('active'); }
    // show brief loading overlay for supplier cards and animate entry
    const cards = document.querySelector('.cards-container');
    if (cards) {
        let lo = cards.querySelector('.loading-overlay');
        if (!lo) {
            lo = document.createElement('div');
            lo.className = 'loading-overlay';
            lo.innerHTML = '<i class="fas fa-spinner fa-spin"></i><div style="color:#475569;font-weight:700;">Loading suppliers…</div>';
            cards.appendChild(lo);
        }
        lo.style.display = 'grid';
        // after a short delay, play entry animation and remove overlay
        setTimeout(() => {
            cards.classList.add('animate-entry');
            lo.style.display = 'none';
        }, 320);
    }
});

// Initialize dynamic notification system
if (typeof NotificationManager !== 'undefined') {
    const notificationManager = new NotificationManager();
    notificationManager.init('{{ auth()->user()->role ?? "manager" }}');
} else {
    console.warn('NotificationManager not found. Make sure notifications.js is loaded.');
}

// --- Logs pagination state ---
const __supplierLogsCache = {}; // supplierId -> full logs array
const __supplierLogsPage = {};  // supplierId -> { page, perPage }

function ensureSupplierLogsState(supplierId){
    if (!__supplierLogsPage[supplierId]) __supplierLogsPage[supplierId] = { page: 1, perPage: 8 };
    if (!__supplierLogsCache[supplierId]) __supplierLogsCache[supplierId] = [];
    return __supplierLogsPage[supplierId];
}

function renderSupplierLogs(supplierId){
    const panel = document.getElementById(`logs-${supplierId}`);
    if (!panel) return;
    const list = panel.querySelector('.logs-list');
    const state = ensureSupplierLogsState(supplierId);
    const all = __supplierLogsCache[supplierId] || [];
    const total = all.length;
    const totalPages = Math.max(1, Math.ceil(total / state.perPage));
    if (state.page > totalPages) state.page = totalPages;
    const start = (state.page - 1) * state.perPage;
    const end = start + state.perPage;
    const pageItems = all.slice(start, end);
    if (!pageItems.length){
        list.innerHTML = '<div class="logs-empty" style="color:#475569;background:rgba(241,245,249,.7);border:1px dashed #cbd5e1;padding:14px;border-radius:12px;text-align:center;">No logs yet. Add the first one above.</div>';
    } else {
        list.innerHTML = pageItems.map(l => logItemHtml(l)).join('');
    }
    // pagination bar
    let bar = panel.querySelector('.logs-pagination');
    if (!bar){
        bar = document.createElement('div');
        bar.className = 'logs-pagination';
        bar.style.display = 'flex';
        bar.style.alignItems = 'center';
        bar.style.justifyContent = 'flex-end';
        bar.style.gap = '10px';
        bar.style.marginTop = '6px';
        bar.innerHTML = `
            <button class="logs-prev" style="padding:6px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Prev</button>
            <span class="logs-page-info" style="color:#6b7280;font-weight:700;">Page 1 of 1</span>
            <button class="logs-next" style="padding:6px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Next</button>`;
        const body = panel.querySelector('.logs-body');
        body.appendChild(bar);
        bar.querySelector('.logs-prev').addEventListener('click', function(){ if (state.page > 1){ state.page -= 1; renderSupplierLogs(supplierId); } });
        bar.querySelector('.logs-next').addEventListener('click', function(){ state.page += 1; renderSupplierLogs(supplierId); });
    }
    const info = bar.querySelector('.logs-page-info');
    if (info) info.textContent = `Page ${state.page} of ${totalPages}`;
    const prev = bar.querySelector('.logs-prev');
    const next = bar.querySelector('.logs-next');
    if (prev) prev.disabled = state.page <= 1;
    if (next) next.disabled = state.page >= totalPages;
}

// --- Add Supplier module (create only) ---
const baseSuppliersUrl = "{{ url('inventory/suppliers') }}";

const addSupplierForm = (function(){
    const form = document.getElementById('add-supplier-form');
    const cards = document.getElementById('supplier-cards');

    async function submitHandler(e){
        e.preventDefault();
        const name = document.getElementById('supplier-name')?.value.trim();
        const contact = document.getElementById('supplier-contact')?.value.trim();
        const country = document.getElementById('supplier-country')?.value.trim();
        const mobile = document.getElementById('supplier-mobile')?.value.trim();
        // email field replaced with mobile
        if (!name) { alert('Supplier name is required'); return; }
        if (!mobile) { alert('Mobile number is required'); return; }

        const payload = {
            name,
            contact_person: contact || null,
            country: country || null,
            email: mobile || null,
            status: 'active'
        };

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // Create
            const res = await fetch("{{ route('inventory.suppliers.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || data.success === false) {
                const msg = data.message || 'Failed to add supplier';
                alert(msg);
                return;
            }
            const s = data.supplier || data;
            const card = document.createElement('div');
            card.className = 'supplier-card';
            card.setAttribute('data-id', s.id);
            card.setAttribute('data-name', s.name || '');
            card.setAttribute('data-contact_person', s.contact_person || '');
            card.setAttribute('data-country', s.country || 'N/A');
            card.setAttribute('data-mobile', s.email || '');
            card.setAttribute('data-status', s.status || 'active');
            card.innerHTML = `
                <div class="card-main">
                    <div class="info">
                        <div class="name">#${s.id} • ${s.name || ''}</div>
                        <div class="meta"><span><i class="fas fa-user"></i> ${s.contact_person || ''}</span></div>
                        <div class="meta"><span><i class="fas fa-earth-asia"></i> ${s.country || 'N/A'}</span></div>
                        <div class="meta"><span><i class="fas fa-mobile-alt"></i> ${s.email || ''}</span></div>
                        <div><span class="status-badge ${s.status||'active'}">${(s.status||'active').charAt(0).toUpperCase() + (s.status||'active').slice(1)}</span></div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-sm btn-primary" onclick="editSupplier(${s.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="toggle-btn" onclick="toggleSupplyLogs(${s.id})" id="toggle-logs-${s.id}"><i class="fas fa-clipboard-list"></i> Logs</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${s.id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="logs-panel" id="logs-${s.id}" data-loaded="false">
                    <div class="logs-header">
                        <div class="logs-title"><i class="fas fa-clipboard-list"></i> Supply Logs</div>
                        <div class="logs-cta" style="font-size:.8rem;color:#475569;">Add a new log below</div>
                    </div>
                    <div class="logs-body">
                        <form class="log-form" data-supplier-id="${s.id}">
                            <div>
                                <label>Brand</label>
                                <input type="text" name="brand" required>
                            </div>
                            <div>
                                <label>Size</label>
                                <input type="text" name="size" placeholder="e.g., 8 or 42">
                            </div>
                            <div>
                                <label>Quantity</label>
                                <input type="number" name="quantity" min="1" required>
                            </div>
                            <div>
                                <label>Received At</label>
                                <input type="datetime-local" name="received_at">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                            </div>
                        </form>
                        <div class="logs-list"><div class="logs-empty" style=\"color:#6b7280;\">Logs are hidden. Click \"Logs\" to load.</div></div>
                    </div>
                </div>
            `;
            cards?.prepend(card);

            // Reset and close
            form.reset();
            closeModal('add-supplier-modal');
        } catch (err) {
            console.error(err);
            alert('Unable to add supplier right now.');
        }
    }

    function init(){
        form?.addEventListener('submit', submitHandler);
        return {};
    }

    // initialize and return API
    return init();
})();

// --- Edit Supplier module (update only) ---
const editSupplierForm = (function(){
    const form = document.getElementById('edit-supplier-form');
    async function submitHandler(e){
        e.preventDefault();
        const id = document.getElementById('edit-supplier-id').value;
        const name = document.getElementById('edit-supplier-name')?.value.trim();
        const contact = document.getElementById('edit-supplier-contact')?.value.trim();
        const country = document.getElementById('edit-supplier-country')?.value.trim();
        const mobile = document.getElementById('edit-supplier-mobile')?.value.trim();
        if (!id) { alert('Missing supplier ID.'); return; }
        if (!name) { alert('Supplier name is required'); return; }
        if (!mobile) { alert('Mobile number is required'); return; }
        const payload = { name, contact_person: contact || null, country: country || null, email: mobile || null };
        try{
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(`${baseSuppliersUrl}/${id}`, {
                method: 'PUT', headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || data.success === false){ alert(data.message || 'Failed to update supplier'); return; }
            const s = data.supplier || data;
            const card = document.querySelector(`.supplier-card[data-id='${id}']`);
            if (card){
                card.dataset.name = s.name || '';
                card.dataset.contact_person = s.contact_person || '';
                card.dataset.country = s.country || 'N/A';
                card.dataset.mobile = s.email || '';
                card.dataset.status = s.status || 'active';
                const info = card.querySelector('.info');
                if (info){
                    info.innerHTML = `
                        <div class="name">#${s.id} • ${s.name || ''}</div>
                        <div class="meta"><span><i class="fas fa-user"></i> ${s.contact_person || ''}</span></div>
                        <div class="meta"><span><i class="fas fa-earth-asia"></i> ${s.country || 'N/A'}</span></div>
                        <div class="meta"><span><i class="fas fa-mobile-alt"></i> ${s.email || ''}</span></div>
                        <div><span class="status-badge ${s.status||'active'}">${(s.status||'active').charAt(0).toUpperCase() + (s.status||'active').slice(1)}</span></div>
                    `;
                }
            }
            closeModal('edit-supplier-modal');
        }catch(err){ console.error(err); alert('Unable to update supplier right now.'); }
    }
    function init(){ form?.addEventListener('submit', submitHandler); return {}; }
    return init();
})();

// Called when clicking Edit button on a card
function editSupplier(supplierId){
    const row = document.querySelector(`.supplier-card[data-id='${supplierId}']`);
    if (!row) return;
    document.getElementById('edit-supplier-id').value = row.dataset.id || '';
    document.getElementById('edit-supplier-name').value = row.dataset.name || '';
    document.getElementById('edit-supplier-contact').value = row.dataset.contact_person || '';
    document.getElementById('edit-supplier-country').value = row.dataset.country || '';
    document.getElementById('edit-supplier-mobile').value = row.dataset.mobile || '';
    openEditSupplierModal();
}

// Delete supplier
async function deleteSupplier(supplierId){
    if (!confirm('Are you sure you want to delete this supplier?')) return;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(`${baseSuppliersUrl}/${supplierId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        const data = await res.json().catch(()=>({}));
        if (!res.ok) {
            alert(data.message || 'Failed to delete supplier');
            return;
        }
    const card = document.querySelector(`.supplier-card[data-id='${supplierId}']`);
    if (card) card.remove();
    } catch (err) {
        console.error(err);
        alert('Unable to delete supplier right now.');
    }
}

// --- Supply Logs UI: inline toggled panel ---
async function toggleSupplyLogs(supplierId){
    const panel = document.getElementById(`logs-${supplierId}`);
    if (!panel) return;
    const list = panel.querySelector('.logs-list');
    const toggleBtn = document.getElementById(`toggle-logs-${supplierId}`);
    const isOpen = panel.style.display === 'block';
    if (isOpen){ panel.style.display = 'none'; panel.classList.remove('open'); if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-clipboard-list"></i> Logs'; return; }
    panel.style.display = 'block'; panel.classList.add('open'); if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Logs';
    if (panel.dataset.loaded === 'true'){ renderSupplierLogs(supplierId); return; }
    list.innerHTML = '<div style="color:#6b7280;"><i class="fas fa-spinner fa-spin"></i> Loading logs…</div>';
    try{
        const res = await fetch(`${baseSuppliersUrl}/${supplierId}/logs`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const logs = (data && data.logs) || [];
        __supplierLogsCache[supplierId] = logs;
        ensureSupplierLogsState(supplierId).page = 1;
        renderSupplierLogs(supplierId);
        panel.dataset.loaded = 'true';
    } catch(e){
        console.error(e);
        list.innerHTML = '<div style="color:#b91c1c;">Failed to load logs.</div>';
    }
}

function logItemHtml(l){
    const dt = l.received_at ? new Date(l.received_at).toLocaleString() : '';
    return `<div class="log-item">
        <div class="left">
            <span class="badge brand">${(l.brand || '').toString().trim()}</span>
            ${l.size ? `<span class="badge size">Size ${l.size}</span>` : ''}
            <span class="badge qty">Qty ${l.quantity || 0}</span>
        </div>
        <div style="color:#6b7280; font-size:.85rem;">${dt}</div>
    </div>`;
}

// Delegate submit for all inline log forms
document.addEventListener('submit', async function(ev){
    const form = ev.target.closest('.log-form');
    if (!form) return;
    ev.preventDefault();
    const supplierId = form.getAttribute('data-supplier-id');
    const brand = (form.querySelector('[name="brand"]')?.value || '').trim();
    const size = (form.querySelector('[name="size"]')?.value || '').trim();
    const qty = parseInt(form.querySelector('[name="quantity"]').value || '0', 10);
    const received = form.querySelector('[name="received_at"]').value;
    if (!brand || !qty || qty < 1){ alert('Brand and a positive quantity are required'); return; }
    try{
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(`${baseSuppliersUrl}/${supplierId}/logs`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ brand, size: size || null, quantity: qty, received_at: received || null })
        });
        const data = await res.json();
        if (!res.ok || data.success === false){ alert(data.message || 'Failed to add log'); return; }
        // update cache and re-render first page
        __supplierLogsCache[supplierId] = [data.log, ...(__supplierLogsCache[supplierId] || [])];
        ensureSupplierLogsState(supplierId).page = 1;
        const panel = document.getElementById(`logs-${supplierId}`);
        if (panel) { panel.dataset.loaded = 'true'; renderSupplierLogs(supplierId); }
        form.reset();
        form.querySelector('[name="brand"]').focus();
    } catch(e){ console.error(e); alert('Unable to add log right now.'); }
});
</script>
@endpush

@include('partials.mobile-blocker')