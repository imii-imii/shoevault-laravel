@extends('layouts.app')

@section('title', 'Reservation Reports - ShoeVault Batangas')

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
        <li class="nav-item">
            <a href="{{ route('inventory.suppliers') }}" class="nav-link">
                <i class="fas fa-people-carry-box"></i>
                <span>Supplier</span>
            </a>
        </li>
        <li class="nav-item active">
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
            <h1 class="main-title">Reservation Management</h1>
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
        <!-- Reservation Reports Section -->
        <section id="reservation-reports" class="content-section active">
            <div class="section-header">
                <h2 style="display:flex;align-items:center;gap:12px;"><i class="fas fa-chart-line"></i> Reservation Management</h2>
            </div>
            
            <!-- Analytics Cards -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin: 20px;">
                <div style="background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Incomplete Reservations</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['incomplete'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Expiring Soon</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['expiring_soon'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #EF4444 0%, #991B1B 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Expiring Today</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['expiring_today'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- Search and Filter Bar -->
            <div style="display: flex; gap: 16px; margin: 20px; align-items: center;">
                <input type="text" id="reservation-search" placeholder="Search reservations..." style="flex: 1; padding: 8px 16px; border: 1px solid #E2E8F0; border-radius: 8px;">
                <select id="reservation-status-filter" style="padding: 8px 16px; border: 1px solid #E2E8F0; border-radius: 8px;">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="ready">Ready</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Reservation Cards -->
            <div style="margin: 20px; display: grid; gap: 16px;" id="reservations-container">
                @foreach($reservations ?? [] as $reservation)
                <!-- Reservation Card -->
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; align-items: center;">
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Reservation ID</p>
                            <p style="font-weight: 600;">{{ $reservation->reservation_id }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Customer Name</p>
                            <p style="font-weight: 600;">{{ $reservation->customer->name }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Email</p>
                            <p style="font-weight: 600;">{{ $reservation->customer->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Item Reserved</p>
                            <p style="font-weight: 600;">{{ $reservation->product->name }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Reservation Date</p>
                            <p style="font-weight: 600;">{{ $reservation->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Pickup Date</p>
                            <p style="font-weight: 600;">{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}</p>
                        </div>
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Status</p>
                            @php
                                $statusColors = [
                                    'pending' => 'background-color: #FEF3C7; color: #92400E;',
                                    'confirmed' => 'background-color: #DBEAFE; color: #1E40AF;',
                                    'ready' => 'background-color: #D1FAE5; color: #065F46;',
                                    'completed' => 'background-color: #DCFCE7; color: #166534;',
                                    'cancelled' => 'background-color: #FEE2E2; color: #991B1B;'
                                ];
                            @endphp
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; {{ $statusColors[$reservation->status] ?? $statusColors['pending'] }} font-weight: 500; font-size: 0.9rem;">
                                {{ ucfirst($reservation->status) }}
                            </span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            @if($reservation->status === 'pending')
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'confirmed')" style="min-width: 100px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 500;">Confirm</button>
                            @elseif($reservation->status === 'confirmed')
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'ready')" style="min-width: 100px; padding: 8px 16px; border-radius: 8px; background-color: #059669; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 500;">Ready</button>
                            @elseif($reservation->status === 'ready')
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'completed')" style="min-width: 100px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 500;">Complete</button>
                            @endif
                            
                            @if($reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'cancelled')" style="min-width: 100px; padding: 8px 16px; border-radius: 8px; background-color: #DC2626; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 500;">Cancel</button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </div>
</main>
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

// Reservation status update function
function updateReservationStatus(reservationId, status) {
    if (confirm(`Are you sure you want to change the status to ${status}?`)) {
        console.log('Update reservation:', reservationId, 'to status:', status);
        // Implement reservation status update functionality
        // This would typically make an AJAX request to update the status
    }
}

// Search functionality
document.getElementById('reservation-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const reservationCards = document.querySelectorAll('#reservations-container > div');
    
    reservationCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});

// Filter functionality
document.getElementById('reservation-status-filter').addEventListener('change', function(e) {
    const selectedStatus = e.target.value;
    const reservationCards = document.querySelectorAll('#reservations-container > div');
    
    reservationCards.forEach(card => {
        if (selectedStatus === 'all') {
            card.style.display = 'block';
        } else {
            const statusElement = card.querySelector('span');
            const cardStatus = statusElement ? statusElement.textContent.toLowerCase() : '';
            card.style.display = cardStatus.includes(selectedStatus) ? 'block' : 'none';
        }
    });
});
</script>
@endpush
