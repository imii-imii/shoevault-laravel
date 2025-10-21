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
                @forelse($reservations ?? [] as $reservation)
                <!-- Reservation Card (4x2 grid) -->
                <div class="reservation-card" data-res-id="{{ $reservation->id }}" data-res-number="{{ $reservation->reservation_id }}" data-res-date="{{ $reservation->created_at ? $reservation->created_at->format('M d, Y h:i A') : 'N/A' }}" data-customer-name="{{ $reservation->customer_name }}" data-customer-email="{{ $reservation->customer_email }}" data-customer-phone="{{ $reservation->customer_phone }}" data-pickup-date="{{ $reservation->pickup_date ? $reservation->pickup_date->format('M d, Y') : 'TBD' }}" data-pickup-time="{{ $reservation->pickup_time ?? 'TBD' }}" data-status="{{ $reservation->status }}" style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
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
                                    'cancelled' => 'background-color: #FEE2E2; color: #991B1B;'
                                ];
                            @endphp
                            <span class="status-pill" style="display: inline-block; padding: 4px 12px; border-radius: 9999px; {{ $statusColors[$reservation->status] ?? $statusColors['pending'] }} font-weight: 500; font-size: 0.9rem;">
                                {{ ucfirst($reservation->status) }}
                            </span>
                            <div style="margin-top: 12px; display:flex; justify-content:flex-end;">
                                <button class="view-reservation-btn" data-id="{{ $reservation->id }}" style="min-width: 110px; padding: 8px 16px; border-radius: 8px; background-color: #2563EB; color: white; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;">View</button>
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
        // Update UI: card status pill, modal footer
        const card = document.querySelector(`.reservation-card[data-res-id="${reservationId}"]`);
        if (card) {
            const pill = card.querySelector('.status-pill');
            if (pill) {
                const label = status.charAt(0).toUpperCase() + status.slice(1);
                pill.textContent = label;
                const styleMap = {
                    pending: 'background-color: #FEF3C7; color: #92400E;',
                    completed: 'background-color: #DCFCE7; color: #166534;',
                    cancelled: 'background-color: #FEE2E2; color: #991B1B;'
                };
                pill.setAttribute('style', `display:inline-block;padding:4px 12px;border-radius:9999px;${styleMap[status] || styleMap.pending}font-weight:500;font-size:0.9rem;`);
            }
            card.dataset.status = status;
        }
        // Update modal actions and close modal if needed
        renderReservationModalActions(reservationId, status);
        alert('Reservation status updated');
    })
    .catch(err => {
        console.error(err);
        alert('Failed to update reservation status');
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
            list.innerHTML = data.items.map(item => `
                <div style=\"display:flex;justify-content:space-between;gap:12px;border:1px solid #cfd4ff;;border-radius:8px;padding:10px;\">
                    <div>
                        <div style=\"font-weight:600;\">${item.name}</div>
                        <div style=\"color:#6B7280;font-size:0.85rem;\">${item.brand || ''} ${item.color ? '• ' + item.color : ''} ${item.size ? '• Size ' + item.size : ''}</div>
                    </div>
                    <div style=\"text-align:right;\">x${item.quantity || 1}<br>₱${Number(item.price || 0).toLocaleString()}</div>
                </div>
            `).join('');
                const totalEl = document.getElementById('reservation-total');
                if (totalEl) {
                    const total = data.reservation && typeof data.reservation.total_amount !== 'undefined' ? Number(data.reservation.total_amount) : data.items.reduce((sum, i) => sum + (Number(i.price || 0) * (Number(i.quantity || 1))), 0);
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
        actions.innerHTML = `
              <button onclick=\"updateReservationStatus('${reservationId}','pending')\" style=\"min-width:120px;padding:10px 16px;border-radius:8px;background:#2563EB;color:#fff;border:none;font-weight:700;\">Revert</button>
        `;
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
            const total = data.reservation?.total_amount != null
                ? Number(data.reservation.total_amount)
                : items.reduce((s,i)=>s + (Number(i.price||0) * Number(i.quantity||1)),0);
            txContext.total = total;

            txBody.innerHTML = `
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;">
                    <div>
                        <h4 style=\"margin:0 0 8px 0;\">Products</h4>
                        <div style=\"display:grid;gap:8px;\">
                            ${items.length ? items.map(i => `
                                <div style=\"display:flex;justify-content:space-between;border:1px solid #f1f5f9;border-radius:8px;padding:10px;\">
                                    <div>
                                        <div style=\"font-weight:700;\">${i.name}</div>
                                        <div style=\"color:#6b7280;font-size:0.85rem;\">${i.brand||''} ${i.color?('• '+i.color):''} ${i.size?('• Size '+i.size):''}</div>
                                    </div>
                                    <div style=\"text-align:right;\">x${i.quantity||1}<br>₱${Number(i.price||0).toLocaleString()}</div>
                                </div>
                            `).join('') : '<div style=\"color:#6b7280;\">No items</div>'}
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

// Print POS-like receipt and mark as completed
if (txPay) txPay.addEventListener('click', async function(){
    const paidRaw = (txBody.querySelector('#tx-payment')?.value || '').trim();
    const paid = parseFloat(paidRaw || '0');
    if (!(paid >= txContext.total)) return;

    // Optional: mark reservation completed first
    try { await updateReservationStatus(txContext.reservationId, 'completed'); } catch (e) {}

    // Build a simple receipt content similar to POS
    const receiptWin = window.open('', '_blank', 'width=480,height=640');
    const now = new Date();
    const stamp = now.toLocaleString();
    const total = txContext.total;
    const change = Math.max(0, paid - total);

    // For items, reuse last fetched data in DOM
    const itemRows = Array.from(txBody.querySelectorAll('[style*="justify-content:space-between"][style*="padding:10px"]'))
        .map(row => {
            const name = row.querySelector('div div')?.textContent || 'Item';
            const meta = row.querySelector('div div + div')?.textContent || '';
            const qtyPrice = row.querySelector('div:last-child')?.innerHTML || '';
            return `<tr><td style="padding:4px 0;border-bottom:1px dotted #e5e7eb;">${name}<div style="color:#6b7280;font-size:10px;">${meta}</div></td><td style="text-align:right;white-space:nowrap;">${qtyPrice}</td></tr>`;
        }).join('');

    receiptWin.document.write(`
        <html><head><title>Receipt</title>
        <style>
            body{font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; padding:16px; color:#111827}
            .sep{border-top:1px dotted #9ca3af; margin:10px 0}
            table{width:100%; border-collapse:collapse;}
            td{font-size:12px}
        </style></head><body>
            <div style="text-align:center; margin-bottom:8px;">
                <h3 style="margin:0; font-size:16px; font-weight:800;">ShoeVault Batangas</h3>
                <div style="color:#6b7280; font-size:11px;">Reservation Payment Receipt</div>
                <div style="color:#6b7280; font-size:11px;">${stamp}</div>
            </div>
            <div class="sep"></div>
            <table>${itemRows}</table>
            <div class="sep"></div>
            <div style="display:flex; justify-content:space-between; font-size:13px;">
                <div>Total</div>
                <div>₱ ${total.toLocaleString()}</div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px;">
                <div>Cash</div>
                <div>₱ ${paid.toLocaleString()}</div>
            </div>
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:14px;">
                <div>Change</div>
                <div>₱ ${change.toLocaleString()}</div>
            </div>
            <script>window.onload = function(){ window.print(); setTimeout(()=>window.close(), 300); }<\/script>
        </body></html>
    `);
    receiptWin.document.close();
    closeTransactionModal();
});

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

// Ensure View buttons are bound after page load (in case SSR)
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.view-reservation-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const card = this.closest('.reservation-card');
            if (card) openReservationModalFromCard(card);
        });
    });
});
</script>
@endpush
