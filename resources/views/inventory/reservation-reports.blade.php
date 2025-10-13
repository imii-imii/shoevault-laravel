@extends('layouts.app')

@section('title', 'Reservation Reports - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(239,68,68,.35)}
.logout-btn:hover{filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
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
            <h1 class="main-title">Reservation Management</h1>
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
                        <div style="padding:10px 14px;border-bottom:1px solid #f8fafc;">Low stock: Nike Air Max 270 size 9</div>
                        <div style="padding:10px 14px;border-bottom:1px solid #f8fafc;">Reservation REV-DEF456 confirmed</div>
                        <div style="padding:10px 14px;">New reservation REV-GHI789</div>
                    </div>
                </div>
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
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Pending Reservations</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['incomplete'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Completed</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['completed'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #EF4444 0%, #991B1B 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Cancelled</h3>
                    <p style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['cancelled'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- Search and Filter Bar -->
            <div style="display: flex; gap: 16px; margin: 20px; align-items: center;">
                <input type="text" id="reservation-search" placeholder="Search reservations..." style="flex: 1; padding: 8px 16px; border: 1px solid #E2E8F0; border-radius: 8px;">
                <select id="reservation-status-filter" style="padding: 8px 16px; border: 1px solid #E2E8F0; border-radius: 8px;">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
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
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'completed')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #059669; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Complete</button>
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'cancelled')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #DC2626; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Cancel</button>
                            @elseif($reservation->status === 'completed')
                                <button onclick="updateReservationStatus('{{ $reservation->id }}', 'pending')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Uncomplete</button>
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
    const allowed = ['pending','completed','cancelled'];
    if (!allowed.includes(status)) { alert('Invalid status'); return; }
    if (!confirm(`Are you sure you want to change the status to ${status}?`)) return;
    fetch(`{{ route('inventory.reservations.update-status', ['id' => 'RES_ID']) }}`.replace('RES_ID', reservationId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed');
        // Update UI optimistically
        const card = [...document.querySelectorAll('#reservations-container > div')]
            .find(div => div.textContent.includes(reservationId));
        if (card) {
            // Update status pill text
            const pill = card.querySelector('span');
            if (pill) pill.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            // Re-render action buttons container
            const actions = card.querySelector('div[style*="display: flex;"]');
            if (actions) {
                if (status === 'pending') {
                    actions.innerHTML = `
                        <button onclick="updateReservationStatus('${reservationId}', 'completed')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #059669; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Complete</button>
                        <button onclick="updateReservationStatus('${reservationId}', 'cancelled')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #DC2626; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Cancel</button>
                    `;
                } else if (status === 'completed') {
                    actions.innerHTML = `
                        <button onclick="updateReservationStatus('${reservationId}', 'pending')" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Uncomplete</button>
                    `;
                } else if (status === 'cancelled') {
                    actions.innerHTML = '';
                }
            }
        }
        alert('Reservation status updated');
    })
    .catch(err => {
        console.error(err);
        alert('Failed to update reservation status');
    });
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
</script>
@endpush
