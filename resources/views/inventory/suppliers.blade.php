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
./* Scoped modal styles to avoid global .modal conflicts */
#add-supplier-modal{
    position: fixed !important; /* ensure fixed and not overridden */
    top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important;
    inset: 0 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(rgba(6,10,18,0.64), rgba(6,10,18,0.64)) !important;
    backdrop-filter: blur(12px) saturate(120%) !important;
    -webkit-backdrop-filter: blur(12px) saturate(120%) !important;
    z-index: 999999 !important; /* ensure above sidebars */
    padding: 28px !important;
    box-sizing: border-box !important;
}


#add-supplier-modal.show{ display:flex !important; }
#add-supplier-modal .modal-content{
    width: min(880px, 96vw) !important;
    max-height: calc(100vh - 120px) !important;
    overflow:auto !important;
    background: linear-gradient(180deg, rgba(255,255,255,0.995), rgba(250,250,255,0.99)) !important;
    border-radius:14px !important;
    box-shadow: 0 34px 80px rgba(3,10,40,0.32), inset 0 1px 0 rgba(255,255,255,0.6) !important;
    padding: 22px 24px 18px 24px !important;
    border: 1px solid rgba(10,16,40,0.06) !important;
    position: relative !important;
}
.modal-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.modal-header h3{ margin:0; font-size:1.05rem; font-weight:800; }
.close-btn{ background:transparent; border:none; font-size:1.4rem; line-height:1; padding:6px 8px; cursor:pointer; color: #374151; border-radius:8px; transition: background .12s, transform .08s; }
.close-btn:hover{ background: rgba(0,0,0,0.04); transform: translateY(-1px); }
.modal-form .form-group { margin-bottom:10px; }
.modal-form .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.brand-chip { margin-right:8px; }
</style>
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
                <button id="notif-bell" title="Notifications" style="background:none;border:none;cursor:pointer;position:relative;">
                    <i class="fas fa-bell" style="font-size:1.5rem;"></i>
                    <span id="notif-badge" style="position:absolute;top:-4px;right:-8px;background:#ef4444;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;display:none;">3</span>
                </button>
                <div id="notif-dropdown" style="display:none;position:absolute;right:0;top:48px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.08);width:320px;z-index:1000;">
                    <div style="padding:12px 14px;border-bottom:1px solid #f1f5f9;font-weight:700;">Notifications</div>
                    <div style="max-height:280px;overflow:auto;">
                        <div style="padding:10px 14px;border-bottom:1px solid #f8fafc;">Low stock: Adidas Ultraboost size 10</div>
                        <div style="padding:10px 14px;border-bottom:1px solid #f8fafc;">Supplier contract update pending</div>
                        <div style="padding:10px 14px;">New supplier inquiry received</div>
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
                <button class="btn btn-primary" onclick="(window.supplierForm && supplierForm.openForAdd) ? supplierForm.openForAdd() : openAddSupplierModal()">
                    <i class="fas fa-plus"></i> Add Supplier
                </button>
            </div>
            <div class="filters">
                <input type="text" id="search-supplier" placeholder="Search suppliers..." class="search-input">
            </div>
            <div class="table-container">
                <table id="supplier-table" class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Contact</th>
                            <th>Brand(s)</th>
                            <th>Stock</th>
                            <th>Country</th>
                            <th>Sizes</th>
                            <th>Email</th>
                            <!-- Phone column removed -->
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="supplier-tbody">
                        @foreach($suppliers ?? [] as $supplier)
                        @php
                            $brandText = 'N/A';
                            $brandsJson = '[]';
                            if (isset($supplier->brands) && is_array($supplier->brands)) {
                                $brandText = implode(', ', array_filter($supplier->brands));
                                $brandsJson = json_encode(array_values(array_filter($supplier->brands)));
                            } elseif (!empty($supplier->brand)) {
                                $brandText = $supplier->brand;
                                $brandsJson = json_encode([$supplier->brand]);
                            }
                        @endphp
                        <tr data-id="{{ $supplier->id }}"
                            data-name="{{ $supplier->name }}"
                            data-contact_person="{{ $supplier->contact_person }}"
                            data-brands='{{ $brandsJson }}'
                            data-total_stock="{{ $supplier->total_stock ?? 0 }}"
                            data-country="{{ $supplier->country ?? 'N/A' }}"
                            data-available_sizes="{{ $supplier->available_sizes ?? 'N/A' }}"
                            data-email="{{ $supplier->email }}"
                            data-status="{{ $supplier->status ?? 'active' }}"
                        >
                            <td>{{ $supplier->id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $brandText }}</td>
                            <td>{{ $supplier->total_stock ?? 0 }}</td>
                            <td>{{ $supplier->country ?? 'N/A' }}</td>
                            <td>{{ $supplier->available_sizes ?? 'N/A' }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>
                                <span class="status-badge {{ $supplier->status ?? 'active' }}">
                                    {{ ucfirst($supplier->status ?? 'active') }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editSupplier({{ $supplier->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- Add Supplier Modal -->
<div id="add-supplier-modal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Supplier</h3>
            <button class="close-btn" onclick="closeModal('add-supplier-modal')">&times;</button>
        </div>
        <form id="add-supplier-form" class="modal-form">
            <input type="hidden" id="supplier-id" value="" />
            <div class="form-group">
                <label for="supplier-name">Supplier Name</label>
                <input type="text" id="supplier-name" required>
            </div>
            <div class="form-group">
                <label for="supplier-contact">Contact Person</label>
                <input type="text" id="supplier-contact" required>
            </div>
            <div class="form-group">
                <label>Brands</label>
                <div id="brand-chip-input" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-height:42px;cursor:text;">
                    <input type="text" id="brand-entry" placeholder="Type a brand and press Enter" style="border:none;outline:none;flex:1;min-width:160px;" />
                </div>
                <small style="color:#6b7280;">Add multiple brands. Press Enter or comma to add a brand.</small>
            </div>
            <div class="form-group">
                <label for="supplier-stock">Stock</label>
                <input type="number" id="supplier-stock" min="0" placeholder="e.g., 100" required>
            </div>
            <div class="form-group">
                <label for="supplier-country">Country</label>
                <input type="text" id="supplier-country" placeholder="e.g., Philippines" required>
            </div>
            <div class="form-group">
                <label for="supplier-sizes">Sizes</label>
                <input type="text" id="supplier-sizes" placeholder="e.g., 7, 8, 9">
            </div>
            <div class="form-group">
                <label for="supplier-email">Email</label>
                <input type="email" id="supplier-email" required>
            </div>
            <!-- Phone input removed -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-supplier-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Supplier</button>
            </div>
        </form>
    </div>
</div>
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

// Modal functions — use class toggles and append to body to avoid clipping
function openAddSupplierModal() {
    const modal = document.getElementById('add-supplier-modal');
    if (!modal) return;
    if (modal.parentElement !== document.body) document.body.appendChild(modal);
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    // autofocus first form element
    setTimeout(()=>{
        const first = modal.querySelector('input, select, textarea, button');
        if (first) first.focus();
    }, 40);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
}

function editSupplier(supplierId) {
    console.log('Edit supplier:', supplierId);
    // Implement edit supplier functionality
}

function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        console.log('Delete supplier:', supplierId);
        // Implement delete supplier functionality
    }
}

// Close modal when clicking outside (overlay)
window.addEventListener('click', function(e) {
    if (e.target && e.target.matches && e.target.matches('.modal')) {
        e.target.classList.remove('show');
        e.target.setAttribute('aria-hidden', 'true');
    }
});

// Ensure the modal is attached to document.body early so it can't be clipped by transformed/overflowing ancestors
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('add-supplier-modal');
    if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
});

// Notification bell toggle
const bell = document.getElementById('notif-bell');
const dd = document.getElementById('notif-dropdown');
const badge = document.getElementById('notif-badge');
if (bell) {
    bell.addEventListener('click', function(e){
        e.stopPropagation();
        dd.style.display = dd.style.display === 'none' || dd.style.display === '' ? 'block' : 'none';
        badge.style.display = 'none';
    });
    document.addEventListener('click', function(){
        dd.style.display = 'none';
    });
    badge.style.display = 'inline-block';
}

// --- Add / Edit Supplier module: wire form to API and brand chips input ---
const baseSuppliersUrl = "{{ url('inventory/suppliers') }}";

const supplierForm = (function(){
    const form = document.getElementById('add-supplier-form');
    const tbody = document.getElementById('supplier-tbody');
    const chipBox = document.getElementById('brand-chip-input');
    const entry = document.getElementById('brand-entry');
    const hiddenId = document.getElementById('supplier-id');
    const modal = document.getElementById('add-supplier-modal');
    const header = modal.querySelector('.modal-header h3');
    const submitBtn = form.querySelector('button[type="submit"]');

    let brands = [];
    let editingId = null;

    function renderChips(){
        if (!chipBox) return;
        // Remove existing chips
        Array.from(chipBox.querySelectorAll('.brand-chip')).forEach(el => el.remove());
        brands.forEach((b, idx) => {
            const chip = document.createElement('span');
            chip.className = 'brand-chip';
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;background:#eef2ff;color:#1e3a8a;border-radius:999px;padding:6px 10px;font-weight:700;font-size:.85rem;';
            chip.innerHTML = `${b}<button type="button" aria-label="Remove ${b}" style="background:none;border:none;color:#1e3a8a;font-weight:800;cursor:pointer;line-height:1">&times;</button>`;
            chip.querySelector('button')?.addEventListener('click', ()=>{ brands.splice(idx,1); renderChips(); });
            // Insert before the entry input
            if (entry && entry.parentElement) entry.parentElement.insertBefore(chip, entry);
        });
    }

    chipBox?.addEventListener('click', ()=> entry?.focus());
    entry?.addEventListener('keydown', (e)=>{
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = entry.value.trim();
            if (val && !brands.includes(val)) { brands.push(val); renderChips(); }
            entry.value = '';
        } else if (e.key === 'Backspace' && !entry.value) {
            brands.pop();
            renderChips();
        }
    });

    async function submitHandler(e){
        e.preventDefault();
        const name = document.getElementById('supplier-name')?.value.trim();
        const contact = document.getElementById('supplier-contact')?.value.trim();
        const totalStock = Number(document.getElementById('supplier-stock')?.value || 0);
        const country = document.getElementById('supplier-country')?.value.trim();
        const sizes = document.getElementById('supplier-sizes')?.value.trim();
        const email = document.getElementById('supplier-email')?.value.trim();
        // phone field removed
        if (!name) { alert('Supplier name is required'); return; }
        if (!email) { alert('Email is required'); return; }

        const payload = {
            name,
            contact_person: contact || null,
            brands: brands.slice(0),
            total_stock: isNaN(totalStock) ? 0 : totalStock,
            country: country || null,
            available_sizes: sizes || null,
            email: email || null,
            status: 'active'
        };

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let res, data;
            if (editingId) {
                // Update
                res = await fetch(`${baseSuppliersUrl}/${editingId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });
                data = await res.json();
                if (!res.ok || data.success === false) {
                    alert(data.message || 'Failed to update supplier');
                    return;
                }
                const s = data.supplier || data;
                // Update table row (find by data-id)
                const row = document.querySelector(`#supplier-tbody tr[data-id='${editingId}']`);
                if (row) {
                    const brandText = Array.isArray(s.brands) ? s.brands.join(', ') : (s.brand || 'N/A');
                    row.dataset.name = s.name || '';
                    row.dataset.contact_person = s.contact_person || '';
                    row.dataset.brands = JSON.stringify(s.brands || (s.brand ? [s.brand] : []));
                    row.dataset.total_stock = s.total_stock || 0;
                    row.dataset.country = s.country || 'N/A';
                    row.dataset.available_sizes = s.available_sizes || 'N/A';
                    row.dataset.email = s.email || '';
                    row.dataset.status = s.status || 'active';
                    row.innerHTML = `
                        <td>${s.id}</td>
                        <td>${s.name || ''}</td>
                        <td>${s.contact_person || ''}</td>
                        <td>${brandText}</td>
                        <td>${Number(s.total_stock||0)}</td>
                        <td>${s.country || 'N/A'}</td>
                        <td>${s.available_sizes || 'N/A'}</td>
                        <td>${s.email || ''}</td>
                        <!-- phone column removed -->
                        <td><span class="status-badge ${s.status||'active'}">${(s.status||'active').charAt(0).toUpperCase() + (s.status||'active').slice(1)}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editSupplier(${s.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${s.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                }
            } else {
                // Create
                res = await fetch("{{ route('inventory.suppliers.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });
                data = await res.json();
                if (!res.ok || data.success === false) {
                    const msg = data.message || 'Failed to add supplier';
                    alert(msg);
                    return;
                }
                const s = data.supplier || data;
                const brandText = Array.isArray(s.brands) ? s.brands.join(', ') : (s.brand || 'N/A');
                const tr = document.createElement('tr');
                tr.setAttribute('data-id', s.id);
                tr.setAttribute('data-name', s.name || '');
                tr.setAttribute('data-contact_person', s.contact_person || '');
                tr.setAttribute('data-brands', JSON.stringify(s.brands || (s.brand ? [s.brand] : [])));
                tr.setAttribute('data-total_stock', s.total_stock || 0);
                tr.setAttribute('data-country', s.country || 'N/A');
                tr.setAttribute('data-available_sizes', s.available_sizes || 'N/A');
                tr.setAttribute('data-email', s.email || '');
                tr.setAttribute('data-status', s.status || 'active');
                tr.innerHTML = `
                    <td>${s.id}</td>
                    <td>${s.name || ''}</td>
                    <td>${s.contact_person || ''}</td>
                    <td>${brandText}</td>
                    <td>${Number(s.total_stock||0)}</td>
                    <td>${s.country || 'N/A'}</td>
                    <td>${s.available_sizes || 'N/A'}</td>
                    <td>${s.email || ''}</td>
                    <!-- phone column removed -->
                    <td><span class="status-badge ${s.status||'active'}">${(s.status||'active').charAt(0).toUpperCase() + (s.status||'active').slice(1)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editSupplier(${s.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${s.id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody?.prepend(tr);
            }

            // Reset and close
            brands.splice(0, brands.length); renderChips(); entry.value = '';
            form.reset();
            editingId = null;
            hiddenId.value = '';
            closeModal('add-supplier-modal');
        } catch (err) {
            console.error(err);
            alert('Unable to add/update supplier right now.');
        }
    }

    function openForAdd(){
        editingId = null;
        hiddenId.value = '';
        header.textContent = 'Add New Supplier';
        submitBtn.textContent = 'Add Supplier';
        form.reset();
        brands.splice(0, brands.length); renderChips();
        openAddSupplierModal();
    }

    function openForEdit(supplier){
        editingId = supplier.id;
        hiddenId.value = supplier.id;
        header.textContent = 'Edit Supplier';
        submitBtn.textContent = 'Save Changes';
        // populate fields
        document.getElementById('supplier-name').value = supplier.name || '';
        document.getElementById('supplier-contact').value = supplier.contact_person || '';
        document.getElementById('supplier-stock').value = supplier.total_stock || '';
        document.getElementById('supplier-country').value = supplier.country || '';
        document.getElementById('supplier-sizes').value = supplier.available_sizes || '';
    document.getElementById('supplier-email').value = supplier.email || '';
        brands = Array.isArray(supplier.brands) ? supplier.brands.slice(0) : (supplier.brands ? (Array.isArray(supplier.brands) ? supplier.brands.slice(0) : []) : []);
        // fallback: if supplier.brands isn't present but supplier.brandsJSON exists, try parsing
        if (!brands.length && supplier.brandsJSON) {
            try { brands = JSON.parse(supplier.brandsJSON) || []; } catch(e){}
        }
        renderChips();
        openAddSupplierModal();
        setTimeout(()=>{ const first = modal.querySelector('input, select, textarea, button'); if (first) first.focus(); }, 60);
    }

    function init(){
        form?.addEventListener('submit', submitHandler);
        // Expose small helpers if needed
        return {
            openForAdd,
            openForEdit
        };
    }

    // initialize and return API
    const api = init();
    return api;
})();

// Called when clicking Edit button on a row
function editSupplier(supplierId){
    const row = document.querySelector(`#supplier-tbody tr[data-id='${supplierId}']`);
    if (!row) return;
    const supplier = {
        id: row.dataset.id,
        name: row.dataset.name,
        contact_person: row.dataset.contact_person,
        brands: (()=>{ try { return JSON.parse(row.dataset.brands || '[]'); } catch(e){ return []; } })(),
        brandsJSON: row.dataset.brands,
        total_stock: row.dataset.total_stock,
        country: row.dataset.country,
        available_sizes: row.dataset.available_sizes,
    email: row.dataset.email,
        status: row.dataset.status
    };
    supplierForm.openForEdit(supplier);
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
        const row = document.querySelector(`#supplier-tbody tr[data-id='${supplierId}']`);
        if (row) row.remove();
    } catch (err) {
        console.error(err);
        alert('Unable to delete supplier right now.');
    }
}
</script>
@endpush