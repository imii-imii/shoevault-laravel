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
                <button class="btn btn-primary" onclick="openAddSupplierModal()">
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
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="supplier-tbody">
                        @foreach($suppliers ?? [] as $supplier)
                        <tr>
                            <td>{{ $supplier->id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>
                                @php
                                    $brandText = 'N/A';
                                    if (isset($supplier->brands) && is_array($supplier->brands)) {
                                        $brandText = implode(', ', array_filter($supplier->brands));
                                    } elseif (!empty($supplier->brand)) {
                                        $brandText = $supplier->brand;
                                    }
                                @endphp
                                {{ $brandText }}
                            </td>
                            <td>{{ $supplier->total_stock ?? 0 }}</td>
                            <td>{{ $supplier->country ?? 'N/A' }}</td>
                            <td>{{ $supplier->available_sizes ?? 'N/A' }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->phone }}</td>
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
<div id="add-supplier-modal" class="modal">
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
            <div class="form-group">
                <label for="supplier-phone">Phone</label>
                <input type="tel" id="supplier-phone" required>
            </div>
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

// Modal functions
function openAddSupplierModal() {
    document.getElementById('add-supplier-modal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
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

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
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

// --- Add Supplier: wire form to API and brand chips input ---
(function(){
    const form = document.getElementById('add-supplier-form');
    const tbody = document.getElementById('supplier-tbody');
    const chipBox = document.getElementById('brand-chip-input');
    const entry = document.getElementById('brand-entry');
    const brands = [];

    function renderChips(){
        if (!chipBox) return;
        // Keep the entry input as last element
        const inputs = Array.from(chipBox.querySelectorAll('.brand-chip, input'));
        inputs.forEach(el => { if (el.classList && el.classList.contains('brand-chip')) el.remove(); });
        brands.forEach((b, idx) => {
            const chip = document.createElement('span');
            chip.className = 'brand-chip';
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;background:#eef2ff;color:#1e3a8a;border-radius:999px;padding:6px 10px;font-weight:700;font-size:.85rem;';
            chip.innerHTML = `${b}<button type="button" aria-label="Remove ${b}" style="background:none;border:none;color:#1e3a8a;font-weight:800;cursor:pointer;line-height:1">&times;</button>`;
            chip.querySelector('button')?.addEventListener('click', ()=>{ brands.splice(idx,1); renderChips(); });
            entry.before(chip);
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

    form?.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const name = document.getElementById('supplier-name')?.value.trim();
        const contact = document.getElementById('supplier-contact')?.value.trim();
        const totalStock = Number(document.getElementById('supplier-stock')?.value || 0);
        const country = document.getElementById('supplier-country')?.value.trim();
        const sizes = document.getElementById('supplier-sizes')?.value.trim();
        const email = document.getElementById('supplier-email')?.value.trim();
        const phone = document.getElementById('supplier-phone')?.value.trim();

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
            phone: phone || null,
            status: 'active'
        };

        try {
            const res = await fetch("{{ route('inventory.suppliers.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || data.success === false) {
                const msg = data.message || 'Failed to add supplier';
                alert(msg);
                return;
            }
            const s = data.supplier;
            const brandText = Array.isArray(s.brands) ? s.brands.join(', ') : (s.brand || 'N/A');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${s.id}</td>
                <td>${s.name || ''}</td>
                <td>${s.contact_person || ''}</td>
                <td>${brandText}</td>
                <td>${Number(s.total_stock||0)}</td>
                <td>${s.country || 'N/A'}</td>
                <td>${s.available_sizes || 'N/A'}</td>
                <td>${s.email || ''}</td>
                <td>${s.phone || ''}</td>
                <td><span class="status-badge ${s.status||'active'}">${(s.status||'active').charAt(0).toUpperCase() + (s.status||'active').slice(1)}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editSupplier(${s.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${s.id})"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody?.prepend(tr);
            // Reset and close
            brands.splice(0, brands.length); renderChips(); entry.value = '';
            form.reset();
            closeModal('add-supplier-modal');
        } catch (err) {
            alert('Unable to add supplier right now.');
        }
    });
})();
</script>
@endpush
