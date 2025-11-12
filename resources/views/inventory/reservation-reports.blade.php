@extends('layouts.app')

@section('title', 'Reservation Reports - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>   
.logout-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem 1rem;background:linear-gradient(to top right,#112c70 0%,#2a6aff 100%);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9999px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .2s ease;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 6px 20px rgba(42,106,255,.35)}
.logout-btn:hover{background:linear-gradient(135deg,#ef4444,#b91c1c);filter:brightness(1.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.15),0 10px 24px rgba(185,28,28,.45)}
.logout-btn i{font-size:1rem}
/* Reservations: loading overlay & entry animation */
@keyframes slideFadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0);} }
#reservations-container { position: relative; }
#reservations-container .loading-overlay{ position:absolute; inset:0; display:grid; place-items:center; gap:10px; background:linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.94)); backdrop-filter: blur(6px); z-index:20; border-radius:8px; }
#reservations-container .loading-overlay i{ font-size:20px; color:#64748b }
#reservations-container.animate-entry > .reservation-card { animation: slideFadeIn 420ms ease both; }
            
              .receipt-paper{ width:320px; margin:0 auto; border:1px dashed #e5e7eb; padding:12px; border-radius:8px; background:#fff; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
              .receipt-sep{ border-top:1px dotted #9ca3af; margin:8px 0; }
              .receipt-paper table{ width:100%; border-collapse:collapse; }
              .receipt-paper th, .receipt-paper td{ font-size:12px; padding:2px 0; }
              .receipt-paper th:last-child, .receipt-paper td:last-child{text-align:right}
              .receipt-paper th:nth-child(2), .receipt-paper td:nth-child(2){ text-align:center }
            
#reservations-container.animate-entry > .reservation-card:nth-child(1){ animation-delay: 40ms }
#reservations-container.animate-entry > .reservation-card:nth-child(2){ animation-delay: 80ms }
#reservations-container.animate-entry > .reservation-card:nth-child(3){ animation-delay: 120ms }
#reservations-container.animate-entry > .reservation-card:nth-child(4){ animation-delay: 160ms }
#reservations-container.animate-entry > .reservation-card:nth-child(5){ animation-delay: 200ms }
#reservations-container.animate-entry > .reservation-card:nth-child(6){ animation-delay: 240ms }
#reservations-container.animate-entry > .reservation-card:nth-child(7){ animation-delay: 280ms }
#reservations-container.animate-entry > .reservation-card:nth-child(8){ animation-delay: 320ms }

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

                    <div class=\"receipt-sep\"></div>
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
        <!-- Reservation Reports Section -->
        <section id="reservation-reports" class="content-section active">
            <div class="section-header">
                <h2 style="display:flex;align-items:center;gap:12px;"><i class="fas fa-chart-line"></i> Reservation Management</h2>
            </div>
            
            <!-- Analytics Cards -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin: 20px;">
                <div style="background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Pending Reservations</h3>
                    <p id="stat-pending-count" style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['incomplete'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Completed</h3>
                    <p id="stat-completed-count" style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['completed'] ?? 0 }}</p>
                </div>
                <div style="background: linear-gradient(135deg, #EF4444 0%, #991B1B 100%); padding: 24px; border-radius: 12px; color: white;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Cancelled</h3>
                    <p id="stat-cancelled-count" style="font-size: 2rem; font-weight: bold;">{{ $reservationStats['cancelled'] ?? 0 }}</p>
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
                    <option value="for_cancellation">For Cancellation</option>
                </select>
            </div>

            <!-- Reservation Cards -->
            <div style="margin: 20px; display: grid; gap: 16px;" id="reservations-container">
                @forelse($reservations ?? [] as $reservation)
                <!-- Reservation Card (4x2 grid) -->
                <div class="reservation-card" data-res-id="{{ $reservation->reservation_id }}" data-res-number="{{ $reservation->reservation_id }}" data-res-date="{{ $reservation->created_at ? $reservation->created_at->format('M d, Y h:i A') : 'N/A' }}" data-res-ts="{{ $reservation->created_at ? $reservation->created_at->timestamp : '' }}" data-customer-name="{{ $reservation->customer_name }}" data-customer-email="{{ $reservation->customer_email }}" data-customer-phone="{{ $reservation->customer_phone }}" data-pickup-date="{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}" data-pickup-time="{{ $reservation->pickup_time ?? 'TBD' }}" data-status="{{ $reservation->status }}" style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; align-items: start;">
                        <!-- Col 1 -->
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Reservation ID</p>
                            <p style="font-weight: 600;">{{ $reservation->reservation_id ?? 'N/A' }}</p>
                            <p style="color: #6B7280; font-size: 0.9rem; margin-top: 12px;">Reservation Date</p>
                            <p style="font-weight: 600;">{{ $reservation->created_at ? $reservation->created_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <!-- Col 2 -->
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Customer Name</p>
                            <p style="font-weight: 600;">{{ $reservation->customer_name ?? 'N/A' }}</p>
                            <p style="color: #6B7280; font-size: 0.9rem; margin-top: 12px;">Pickup Date</p>
                            <p style="font-weight: 600;">{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}</p>
                        </div>
                        <!-- Col 3 -->
                        <div>
                            <p style="color: #6B7280; font-size: 0.9rem;">Email</p>
                            <p style="font-weight: 600;">{{ $reservation->customer_email ?? 'N/A' }}</p>
                            <p style="color: #6B7280; font-size: 0.9rem; margin-top: 12px;">Phone Number</p>
                            <p style="font-weight: 600;">{{ $reservation->customer_phone ?? 'N/A' }}</p>
                        </div>
                        <!-- Col 4 -->
                        <div style="text-align:right;">
                            <p style="color: #6B7280; font-size: 0.9rem;">Status</p>
                            @php
                                $statusColors = [
                                    'pending' => 'background-color: #FEF3C7; color: #92400E;',
                                    'completed' => 'background-color: #DCFCE7; color: #166534;',
                                    'cancelled' => 'background-color: #FEE2E2; color: #991B1B;',
                                    'for_cancellation' => 'background-color: #FED7AA; color: #C2410C;'
                                ];
                            @endphp
                            <span class="status-pill" style="display: inline-block; padding: 4px 12px; border-radius: 9999px; {{ $statusColors[$reservation->status] ?? $statusColors['pending'] }} font-weight: 500; font-size: 0.9rem;">
                                @php
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        'for_cancellation' => 'For Cancellation'
                                    ];
                                @endphp
                                {{ $statusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                            </span>
                            <div style="margin-top: 12px; display:flex; justify-content:flex-end;">
                                <button class="view-reservation-btn" data-id="{{ $reservation->reservation_id }}" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">View</button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <!-- Empty State -->
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="font-size: 3rem; color: #E5E7EB; margin-bottom: 16px;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3 style="color: #6B7280; margin-bottom: 8px;">No Reservations Yet</h3>
                    <p style="color: #9CA3AF;">Reservations will appear here once customers start making reservations through the portal.</p>
                </div>
                @endforelse
            </div>
            <!-- Pagination Bar -->
            <div id="inv-reservation-pagination" style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin: 0 20px 20px 20px;">
                <button id="inv-res-prev" class="paginate-btn" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Prev</button>
                <span id="inv-res-page-info" style="color:#6b7280;font-weight:700;">Page 1 of 1</span>
                <button id="inv-res-next" class="paginate-btn" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#111827;font-weight:700;cursor:pointer;">Next</button>
            </div>
        </section>
    </div>
</main>
<!-- Reservation Details Modal -->
<div id="reservation-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;z-index:9998;"></div>
<div id="reservation-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(920px,94vw);max-height:90vh;overflow:auto;display:none;z-index:9999;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:1.1rem;font-weight:800;">Reservation Details</h3>
        <button id="reservation-modal-close" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
    </div>
    <div id="reservation-modal-body" style="padding:20px 24px;">
        <!-- Populated via JS -->
    </div>
    <div id="reservation-modal-actions" style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:12px;justify-content:flex-end;">
        <!-- Buttons injected via JS depending on status -->
    </div>
 </div>

<!-- Transaction Modal (Instant POS) -->
<div id="transaction-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;z-index:10010;"></div>
<div id="transaction-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(900px,94vw);max-height:90vh;overflow:auto;display:none;z-index:10011;">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:1.05rem;font-weight:800;">Complete Reservation</h3>
        <button id="transaction-modal-close" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
    </div>
    <div id="transaction-modal-body" style="padding:16px 20px;">
        <!-- Filled dynamically -->
    </div>
    <div style="padding:14px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;">
        <button id="transaction-cancel-btn" style="padding:10px 14px;border-radius:8px;background:#6b7280;color:#fff;border:none;font-weight:700;">Cancel</button>
        <button id="transaction-pay-btn" disabled style="padding:10px 14px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Pay & Receipt</button>
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

// Ensure Pending reservations appear first (then Completed, then Cancelled), tie-breaker by newest date
function sortReservations() {
    const container = document.getElementById('reservations-container');
    if (!container) return;
    const priority = { pending: 0, completed: 1, cancelled: 2 };
    const cards = Array.from(container.querySelectorAll('.reservation-card'));
    if (!cards.length) return;
    cards.sort((a, b) => {
        const pa = priority[a.dataset.status] ?? 99;
        const pb = priority[b.dataset.status] ?? 99;
        if (pa !== pb) return pa - pb;
        const ta = Number(a.dataset.resTs || 0);
        const tb = Number(b.dataset.resTs || 0);
        return tb - ta; // newest first
    });
    cards.forEach(card => container.appendChild(card));
}

// Update analytics counters in the header cards when a reservation changes status
function updateAnalyticsCounts(oldStatus, newStatus) {
    const map = { pending: 'stat-pending-count', completed: 'stat-completed-count', cancelled: 'stat-cancelled-count' };
    if (!oldStatus && !newStatus) return;
    if (oldStatus === newStatus) return;

    // decrement old
    if (oldStatus && map[oldStatus]) {
        const elOld = document.getElementById(map[oldStatus]);
        if (elOld) {
            let v = parseInt((elOld.textContent || '').replace(/[^0-9-]/g, '')) || 0;
            v = Math.max(0, v - 1);
            elOld.textContent = v;
        }
    }

    // increment new
    if (newStatus && map[newStatus]) {
        const elNew = document.getElementById(map[newStatus]);
        if (elNew) {
            let v = parseInt((elNew.textContent || '').replace(/[^0-9-]/g, '')) || 0;
            v = v + 1;
            elNew.textContent = v;
        }
    }
}

// Reservation status update function
function updateReservationStatus(reservationId, status, options = { reload: true }) {
    const allowed = ['pending','completed','cancelled'];
    if (!allowed.includes(status)) { alert('Invalid status'); return Promise.reject(new Error('Invalid status')); }
    if (!confirm(`Are you sure you want to change the status to ${status}?`)) return Promise.reject(new Error('User cancelled'));
    
    // Prepare request body with status and optional payment data
    const requestBody = { status };
    if (options.amount_paid !== undefined) {
        requestBody.amount_paid = options.amount_paid;
    }
    if (options.change_given !== undefined) {
        requestBody.change_given = options.change_given;
    }
    
    return fetch(`{{ route('inventory.reservations.update-status', ['id' => 'RES_ID']) }}`.replace('RES_ID', reservationId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestBody)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed');
        // Update UI: card status pill, modal footer
        const card = document.querySelector(`.reservation-card[data-res-id="${reservationId}"]`);
        const oldStatus = card ? card.dataset.status : null;
        if (card) {
            const pill = card.querySelector('.status-pill');
            if (pill) {
                const labelMap = {
                    'pending': 'Pending',
                    'completed': 'Completed',
                    'cancelled': 'Cancelled',
                    'for_cancellation': 'For Cancellation'
                };
                const label = labelMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
                pill.textContent = label;
                const styleMap = {
                    pending: 'background-color: #FEF3C7; color: #92400E;',
                    completed: 'background-color: #DCFCE7; color: #166534;',
                    cancelled: 'background-color: #FEE2E2; color: #991B1B;',
                    for_cancellation: 'background-color: #FED7AA; color: #C2410C;'
                };
                pill.setAttribute('style', `display:inline-block;padding:4px 12px;border-radius:9999px;${styleMap[status] || styleMap.pending}font-weight:500;font-size:0.9rem;`);
            }
            // update analytics counts based on old/new status
            updateAnalyticsCounts(oldStatus, status);
            card.dataset.status = status;
        } else {
            // if card not in DOM, still try to update analytics if possible
            updateAnalyticsCounts(oldStatus, status);
        }
        // Update modal actions and close modal if needed
        renderReservationModalActions(reservationId, status);
        // Re-sort list so Pending stays on top
        sortReservations();

        // Optionally reload the page to fetch fresh data from server
        if (options.reload) {
            // show short notice then reload so user sees the change
            setTimeout(() => { location.reload(); }, 700);
        }

        return data;
    })
    .catch(err => {
        console.error(err);
        alert('Failed to update reservation status');
        throw err;
    });
}

// Modal: open/close and populate
const modalEl = document.getElementById('reservation-modal');
const modalOverlayEl = document.getElementById('reservation-modal-overlay');
const modalBodyEl = document.getElementById('reservation-modal-body');
const modalCloseEl = document.getElementById('reservation-modal-close');

function openReservationModalFromCard(card) {
    const id = card.dataset.resId;
    const number = card.dataset.resNumber;
    const resDate = card.dataset.resDate;
    const name = card.dataset.customerName || 'N/A';
    const email = card.dataset.customerEmail || 'N/A';
    const phone = card.dataset.customerPhone || 'N/A';
    const pickupDate = card.dataset.pickupDate || 'TBD';
    const pickupTime = card.dataset.pickupTime || 'TBD';
    const status = card.dataset.status || 'pending';

    // Build modal body; products list fetched from API or placeholder
    modalBodyEl.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Reservation ID</p>
                <p style=\"font-weight:600;\">${number}</p>
            </div>
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Reservation Date</p>
                <p style=\"font-weight:600;\">${resDate}</p>
            </div>
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Customer Name</p>
                <p style=\"font-weight:600;\">${name}</p>
            </div>
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Email</p>
                <p style=\"font-weight:600;\">${email}</p>
            </div>
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Phone</p>
                <p style=\"font-weight:600;\">${phone}</p>
            </div>
            <div>
                <p style=\"color:#6B7280;font-size:0.9rem;\">Pickup</p>
                <p style=\"font-weight:600;\">${pickupDate} ${pickupTime !== 'TBD' ? ('• ' + pickupTime) : ''}</p>
            </div>
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb;">
            <h4 style="margin:0 0 8px 0;">Products</h4>
            <div id="reservation-products" style="display:grid;gap:8px;">
                <div style=\"color:#6B7280;font-size:0.9rem;\">Loading products…</div>
            </div>
                <div id="reservation-total" style="margin-top:12px;text-align:right;font-weight:800;font-size:1rem;"></div>
        </div>
    `;

    // Try fetching products via API if available
        fetch(`{{ route('inventory.api.reservations.show', ['id' => 'RES_ID']) }}`.replace('RES_ID', id))
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            const list = document.getElementById('reservation-products');
            if (!data || !Array.isArray(data.items) || data.items.length === 0) {
                list.innerHTML = '<div style="color:#6B7280;font-size:0.9rem;">No products recorded for this reservation.</div>';
                return;
            }
            
            // Debug: Log the actual item structure to see what properties are available
            console.log('Reservation items data:', data.items);
            data.items.forEach((item, index) => {
                console.log(`Item ${index}:`, item);
                console.log(`Available properties:`, Object.keys(item));
            });
            
            list.innerHTML = data.items.map(item => {
                // The backend returns 'price' property, so use that first
                const price = item.price || 0;
                
                console.log(`Item "${item.name}" price debug:`, {
                    item_price: item.price,
                    final_price_used: price,
                    quantity: item.quantity,
                    all_item_properties: Object.keys(item)
                });
                
                return `
                <div style=\"display:flex;justify-content:space-between;gap:12px;border:1px solid #cfd4ff;;border-radius:8px;padding:10px;\">
                    <div>
                        <div style=\"font-weight:600;\">${item.name}</div>
                        <div style=\"color:#6B7280;font-size:0.85rem;\">${item.brand || ''} ${item.color ? '• ' + item.color : ''} ${item.size ? '• Size ' + item.size : ''}</div>
                    </div>
                    <div style=\"text-align:right;\">x${item.quantity || 1}<br>₱${Number(price || 0).toLocaleString()}</div>
                </div>
                `;
            }).join('');
                const totalEl = document.getElementById('reservation-total');
                if (totalEl) {
                    // Use reservation total if available, otherwise calculate from items
                    let total = 0;
                    if (data.reservation && typeof data.reservation.total_amount !== 'undefined') {
                        total = Number(data.reservation.total_amount);
                    } else {
                        // Calculate total using item prices
                        total = data.items.reduce((sum, i) => {
                            const itemPrice = i.price || 0;
                            return sum + (Number(itemPrice) * (Number(i.quantity || 1)));
                        }, 0);
                    }
                    
                    console.log('Total calculation:', {
                        reservation_total: data.reservation?.total_amount,
                        calculated_total: total,
                        items_used_for_calc: data.items.map(i => ({
                            name: i.name,
                            price: i.price,
                            quantity: i.quantity
                        }))
                    });
                    
                    totalEl.textContent = `Total: ₱${total.toLocaleString()}`;
                }
        })
        .catch(() => {
            const list = document.getElementById('reservation-products');
            list.innerHTML = '<div style="color:#6B7280;font-size:0.9rem;">Products API not available. Ensure backend endpoint exists.</div>';
        });

    renderReservationModalActions(id, status);
    modalOverlayEl.style.display = 'block';
    modalEl.style.display = 'block';
}

function renderReservationModalActions(reservationId, status) {
    const actions = document.getElementById('reservation-modal-actions');
    if (!actions) return;
    if (status === 'pending') {
        actions.innerHTML = `
            <button onclick=\"openTransactionModal('${reservationId}')\" style=\"min-width:120px;padding:10px 16px;border-radius:8px;background:#059669;color:#fff;border:none;font-weight:700;\">Complete</button>
            <button onclick=\"updateReservationStatus('${reservationId}','cancelled')\" style=\"min-width:120px;padding:10px 16px;border-radius:8px;background:#DC2626;color:#fff;border:none;font-weight:700;\">Cancel</button>
        `;
    } else if (status === 'completed') {
        // Revert action removed: no modal actions for already completed reservations
        actions.innerHTML = '';
    } else {
        actions.innerHTML = '';
    }
}

// Transaction Modal logic
const txModal = document.getElementById('transaction-modal');
const txOverlay = document.getElementById('transaction-modal-overlay');
const txBody = document.getElementById('transaction-modal-body');
const txClose = document.getElementById('transaction-modal-close');
const txCancel = document.getElementById('transaction-cancel-btn');
const txPay = document.getElementById('transaction-pay-btn');

let txContext = { reservationId: null, total: 0 };

function openTransactionModal(reservationId) {
    txContext = { reservationId, total: 0 };
    // Reuse the details endpoint to fetch items and total
    fetch(`{{ route('inventory.api.reservations.show', ['id' => 'RES_ID']) }}`.replace('RES_ID', reservationId))
        .then(r => r.json())
        .then(data => {
            const items = data.items || [];
            console.log('Transaction modal items data:', items);
            
            const total = data.reservation?.total_amount != null
                ? Number(data.reservation.total_amount)
                : items.reduce((s,i)=> {
                    const itemPrice = i.price || 0;
                    return s + (Number(itemPrice) * Number(i.quantity||1));
                }, 0);
            txContext.total = total;

            txBody.innerHTML = `
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;">
                    <div>
                        <h4 style=\"margin:0 0 8px 0;\">Products</h4>
                        <div style=\"display:grid;gap:8px;\">
                            ${items.length ? items.map(i => {
                                const itemPrice = i.price || 0;
                                return `
                                <div style=\"display:flex;justify-content:space-between;border:1px solid #f1f5f9;border-radius:8px;padding:10px;\">
                                    <div>
                                        <div style=\"font-weight:700;\">${i.name}</div>
                                        <div style=\"color:#6b7280;font-size:0.85rem;\">${i.brand||''} ${i.color?('• '+i.color):''} ${i.size?('• Size '+i.size):''}</div>
                                    </div>
                                    <div style=\"text-align:right;\">x${i.quantity||1}<br>₱${Number(itemPrice||0).toLocaleString()}</div>
                                </div>
                                `;
                            }).join('') : '<div style=\"color:#6b7280;\">No items</div>'}
                        </div>
                    </div>
                    <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
                        <div style="display:flex;justify-content:space-between;font-weight:800;margin-bottom:10px;">
                            <span>Total</span>
                            <span id="tx-total">₱ ${total.toLocaleString()}</span>
                        </div>
                        <label for="tx-payment" style="font-size:0.9rem;color:#374151;">Amount Paid</label>
                        <input id="tx-payment" type="text" placeholder="0.00" style="width:100%;margin:6px 0 8px 0;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">
                        <div style="display:flex;justify-content:space-between;font-weight:700;">
                            <span>Change</span>
                            <span id="tx-change">₱ 0</span>
                        </div>
                        <small style="display:block;color:#6b7280;margin-top:8px;">Enter amount equal or greater than total to enable Pay.</small>
                    </div>
                </div>
            `;

            // Bind payment input
            const input = txBody.querySelector('#tx-payment');
            txPay.disabled = true;
            input.addEventListener('input', () => {
                const cleaned = input.value.replace(/[^0-9.]/g, '');
                const parts = cleaned.split('.');
                const normalized = parts.length > 1 ? parts[0] + '.' + parts[1].slice(0, 2) : parts[0];
                if (input.value !== normalized) input.value = normalized;
                const paid = parseFloat(normalized || '0');
                const change = Math.max(0, paid - txContext.total);
                txBody.querySelector('#tx-change').textContent = `₱ ${change.toLocaleString()}`;
                txPay.disabled = !(paid >= txContext.total);
            });

            txOverlay.style.display = 'block';
            txModal.style.display = 'block';
        })
        .catch(() => {
            alert('Unable to load reservation details.');
        });
}

function closeTransactionModal(){
    txModal.style.display = 'none';
    txOverlay.style.display = 'none';
}

if (txClose) txClose.addEventListener('click', closeTransactionModal);
if (txCancel) txCancel.addEventListener('click', closeTransactionModal);
if (txOverlay) txOverlay.addEventListener('click', closeTransactionModal);

// Enhanced: receipt preview modal then print & complete
if (txPay) txPay.addEventListener('click', function(){
    const paidRaw = (txBody.querySelector('#tx-payment')?.value || '').trim();
    const paid = parseFloat(paidRaw || '0');
    if (!(paid >= txContext.total)) return;

    openInventoryReservationReceiptPreview({
        items: Array.from(txBody.querySelectorAll('[style*="justify-content:space-between"][style*="padding:10px"]')).map(row => ({
            name: row.querySelector('div div')?.textContent || 'Item',
            meta: row.querySelector('div div + div')?.textContent || '',
            quantity: parseInt((row.querySelector('div:last-child')?.textContent || '').match(/x(\d+)/)?.[1] || '1', 10),
            price: Number((row.querySelector('div:last-child')?.textContent || '').replace(/[^0-9.]/g, '')) || 0
        })),
        summary: { subtotal: txContext.total, discountAmount: 0, total: txContext.total },
        paymentAmount: paid,
        onConfirm: async () => {
            const change = Math.max(0, paid - txContext.total);
            try {
                await updateReservationStatus(txContext.reservationId, 'completed', { reload: false, amount_paid: paid, change_given: change });
            } catch (e) {
                alert('Failed to complete reservation.');
                return;
            }
            const paper = document.getElementById('inv-res-receipt-paper');
            if (paper) {
                // Print the preview directly, with @media print styles hiding non-receipt elements
                window.print();
            }
            const modal = document.getElementById('inv-res-receipt-modal');
            if (modal) modal.remove();
            closeTransactionModal();
            setTimeout(()=> { try { location.reload(); } catch(_) {} }, 600);
        }
    });
});

function openInventoryReservationReceiptPreview({ items = [], summary = { subtotal:0, discountAmount:0, total:0 }, paymentAmount = 0, onConfirm }) {
    let modal = document.getElementById('inv-res-receipt-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'inv-res-receipt-modal';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.45)';
        modal.style.zIndex = '10050';
        modal.style.display = 'grid';
        modal.style.placeItems = 'center';
        modal.innerHTML = `
            <style>
                /* Print only the receipt paper, hide controls */
                @media print {
                    body * { visibility: hidden !important; }
                    #inv-res-receipt-paper, #inv-res-receipt-paper * { visibility: visible !important; }
                    #inv-res-receipt-paper { position: absolute; inset: 0 auto auto 0; margin: 0 !important; box-shadow: none !important; width: auto !important; }
                    .no-print { display: none !important; }
                }
            </style>
            <div style="background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(460px,94vw);padding:14px;">
                <div id="inv-res-receipt-paper" class="receipt-paper">
                    <div style="text-align:center;margin-bottom:6px;">
                        <div style="font-weight:800;">SHOE VAULT BATANGAS</div>
                        <div style="color:#6b7280;font-size:12px;">Manghinao Proper Bauan, Batangas 4201<br>Tel.: +63 936 382 0087</div>
                        <div style="color:#6b7280;font-size:11px;">${new Date().toLocaleString()}</div>
                    </div>
                    <div class="receipt-sep"></div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;"><span>Cashier</span><span>{{ auth()->user()->name ?? '—' }}</span></div>
                    <div class="receipt-sep"></div>
                    <table>
                        <thead><tr><th>Name</th><th>Qty</th><th>Price</th></tr></thead>
                        <tbody id="inv-res-receipt-items"></tbody>
                    </table>
                    <div class="receipt-sep"></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Subtotal</span><span>₱ ${Number(summary.subtotal||0).toLocaleString()}</span></div>
                    ${Number(summary.discountAmount||0) > 0 ? `<div style=\"display:flex;justify-content:space-between;font-size:13px;\"><span>Discount</span><span>- ₱ ${Number(summary.discountAmount).toLocaleString()}</span></div>` : ''}
                    <div style="display:flex;justify-content:space-between;font-weight:800;font-size:14px;"><span>Total</span><span>₱ ${Number(summary.total||0).toLocaleString()}</span></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Cash</span><span>₱ ${Number(paymentAmount||0).toLocaleString()}</span></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Change</span><span>₱ ${Math.max(0, Number(paymentAmount||0) - Number(summary.total||0)).toLocaleString()}</span></div>
                </div>
                <div class="no-print" style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;">
                    <button id="inv-res-receipt-cancel" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;font-weight:700;">Cancel</button>
                    <button id="inv-res-receipt-print" style="padding:8px 12px;border-radius:8px;background:#2a6aff;color:#fff;border:none;font-weight:700;">Print</button>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }
    const tbody = modal.querySelector('#inv-res-receipt-items');
    tbody.innerHTML = (items||[]).map(it => `<tr><td>${it.name || ''} ${it.size ? `(Size ${it.size})` : ''}</td><td style=\"text-align:center;\">${Number(it.quantity||1)}</td><td style=\"text-align:right;\">₱ ${(Number(it.price||0)*Number(it.quantity||1)).toLocaleString()}</td></tr>`).join('');
    modal.querySelector('#inv-res-receipt-cancel').onclick = ()=> modal.remove();
    modal.querySelector('#inv-res-receipt-print').onclick = ()=> { if (typeof onConfirm === 'function') onConfirm(); };
}

function closeReservationModal() {
    modalEl.style.display = 'none';
    modalOverlayEl.style.display = 'none';
}

if (modalCloseEl) modalCloseEl.addEventListener('click', closeReservationModal);
if (modalOverlayEl) modalOverlayEl.addEventListener('click', closeReservationModal);

// Bind View buttons
document.querySelectorAll('.view-reservation-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        const card = this.closest('.reservation-card');
        if (card) openReservationModalFromCard(card);
    });
});

// Filter functionality
document.getElementById('reservation-status-filter').addEventListener('change', function(e) {
    const newStatus = e.target.value || 'all';
    console.log('Status filter changed to:', newStatus);
    
    __invResPageState.status = newStatus;
    __invResPageState.page = 1;
    
    // Reset any inline styles that might be interfering
    document.querySelectorAll('#reservations-container > .reservation-card').forEach(card => {
        card.style.display = '';
    });
    
    applyInvReservationPagination();
});

// Initialize dynamic notification system
if (typeof NotificationManager !== 'undefined') {
    const notificationManager = new NotificationManager();
    notificationManager.init('{{ auth()->user()->role ?? "guest" }}');
} else {
    console.warn('NotificationManager not found. Make sure notifications.js is loaded.');
}

// Handle URL parameters for notification clicks
const urlParams = new URLSearchParams(window.location.search);
const showReservationId = urlParams.get('show_reservation');
if (showReservationId) {
    // Wait a bit for the page to load completely, then show the reservation modal
    setTimeout(() => {
        const reservationCard = document.querySelector(`[data-res-id="${showReservationId}"]`);
        if (reservationCard && typeof openReservationModalFromCard === 'function') {
            openReservationModalFromCard(reservationCard);
            // Clean up the URL parameter
            const cleanUrl = new URL(window.location);
            cleanUrl.searchParams.delete('show_reservation');
            window.history.replaceState({}, document.title, cleanUrl);
        } else {
            console.warn(`Reservation with ID ${showReservationId} not found or modal function not available`);
        }
    }, 1000);
}

// Ensure View buttons are bound after page load (in case SSR)
document.addEventListener('DOMContentLoaded', function(){
    // Initial sort on load
    sortReservations();
    applyInvReservationPagination();
    document.querySelectorAll('.view-reservation-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const card = this.closest('.reservation-card');
            if (card) openReservationModalFromCard(card);
        });
    });
    // brief loading overlay + entry animation for reservations container
    const resContainer = document.getElementById('reservations-container');
    if (resContainer) {
        let lo = resContainer.querySelector('.loading-overlay');
        if (!lo) {
            lo = document.createElement('div');
            lo.className = 'loading-overlay';
            lo.innerHTML = '<i class="fas fa-spinner fa-spin"></i><div style="color:#475569;font-weight:700;">Loading reservations…</div>';
            resContainer.appendChild(lo);
        }
        lo.style.display = 'grid';
        setTimeout(() => {
            resContainer.classList.add('animate-entry');
            lo.style.display = 'none';
        }, 360);
    }
});

// Pagination state + helpers (Inventory Reservation Reports)
const __invResPageState = { page: 1, perPage: 10, search: '', status: 'all' };

function getFilteredInvReservationCards() {
    const term = (__invResPageState.search || '').toLowerCase();
    const status = (__invResPageState.status || 'all');
    const nodes = Array.from(document.querySelectorAll('#reservations-container > .reservation-card'));
    
    console.log('Filtering reservations:', {
        totalCards: nodes.length,
        searchTerm: term,
        statusFilter: status,
        cardStatuses: nodes.map(card => card.dataset.status)
    });
    
    return nodes.filter(card => {
        const matchText = !term || card.textContent.toLowerCase().includes(term);
        const cardStatus = (card.dataset.status || '').toLowerCase();
        const matchStatus = status === 'all' || cardStatus === status;
        
        const matches = matchText && matchStatus;
        if (!matches) {
            console.log('Card filtered out:', {
                cardId: card.dataset.resId,
                cardStatus: cardStatus,
                expectedStatus: status,
                matchText: matchText,
                matchStatus: matchStatus
            });
        }
        
        return matches;
    });
}

function applyInvReservationPagination() {
    // Keep current sort order, then slice
    const all = getFilteredInvReservationCards();
    const total = all.length;
    const totalPages = Math.max(1, Math.ceil(total / __invResPageState.perPage));
    if (__invResPageState.page > totalPages) __invResPageState.page = totalPages;
    const start = (__invResPageState.page - 1) * __invResPageState.perPage;
    const end = start + __invResPageState.perPage;

    console.log('Applying pagination:', {
        totalCards: all.length,
        currentPage: __invResPageState.page,
        totalPages: totalPages,
        showingRange: `${start + 1}-${Math.min(end, total)}`
    });

    // Hide all cards first
    document.querySelectorAll('#reservations-container > .reservation-card').forEach(c => c.style.display = 'none');
    
    // Show only the cards for current page
    const pageCards = all.slice(start, end);
    pageCards.forEach(c => c.style.display = 'block');
    
    console.log('Cards shown on page:', pageCards.map(c => ({
        id: c.dataset.resId,
        status: c.dataset.status
    })));

    const info = document.getElementById('inv-res-page-info');
    if (info) info.textContent = `Page ${__invResPageState.page} of ${totalPages}`;
    const prev = document.getElementById('inv-res-prev');
    const next = document.getElementById('inv-res-next');
    if (prev) prev.disabled = __invResPageState.page <= 1;
    if (next) next.disabled = __invResPageState.page >= totalPages;
}

document.getElementById('inv-res-prev')?.addEventListener('click', function(){
    if (__invResPageState.page > 1) { __invResPageState.page -= 1; applyInvReservationPagination(); }
});
document.getElementById('inv-res-next')?.addEventListener('click', function(){
    __invResPageState.page += 1; applyInvReservationPagination();
});

// Override search to work with pagination
document.getElementById('reservation-search').addEventListener('input', function(e) {
    __invResPageState.search = e.target.value || '';
    __invResPageState.page = 1;
    applyInvReservationPagination();
});
</script>
@endpush

@include('partials.mobile-blocker')