@extends('layouts.app')

@section('title', 'Supplier Management - ShoeVault Batangas')

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
            <h1 class="main-title">Supplier Management</h1>
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
                            <th>Brand</th>
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
                            <td>{{ $supplier->brand ?? 'N/A' }}</td>
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
                <label for="supplier-brand">Brand</label>
                <input type="text" id="supplier-brand" placeholder="e.g., Nike" required>
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
</script>
@endpush
