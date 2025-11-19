<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard - ShoeVault</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/reservation-portal.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <style>
    body {
      background: #f8fafc;
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      color: #1a202c;
    }

    /* Dashboard-specific styles that complement the portal design */
    .dashboard-main {
      min-height: 100vh;
      padding-top: 2rem;
      padding-bottom: 2rem;
    }

    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
    }
    
    .dashboard-header {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 50%, #2d3748 100%);
      color: white;
      padding: 3rem 2rem;
      border-radius: 20px;
      margin-bottom: 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(45, 55, 72, 0.3);
    }

    .dashboard-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .dashboard-header h1 {
      font-size: 2.5rem;
      font-weight: 800;
      margin: 0 0 0.5rem 0;
      position: relative;
      z-index: 1;
    }

    .dashboard-header p {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0;
      position: relative;
      z-index: 1;
    }
    
    .dashboard-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      transition: all 0.3s ease;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .dashboard-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
      background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
      color: white;
      padding: 2rem;
      font-weight: 700;
      font-size: 1.25rem;
      position: relative;
      overflow: hidden;
    }

    .card-header::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
      border-radius: 50%;
      transform: translate(30px, -30px);
    }
    
    .card-content {
      padding: 2rem;
    }
    
    .tab-navigation {
      display: flex;
      background: #f7fafc;
      border-radius: 15px;
      padding: 0.5rem;
      margin-bottom: 2rem;
      gap: 0.5rem;
    }
    
    .tab-btn {
      flex: 1;
      padding: 1rem 1.5rem;
      border: none;
      background: transparent;
      color: #718096;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border-radius: 12px;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .tab-btn:hover {
      color: #4299e1;
      background: rgba(66, 153, 225, 0.1);
    }
    
    .tab-btn.active {
      color: white;
      background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
      box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .reservation-item, .transaction-item {
      background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 6px solid #4299e1;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .reservation-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent 0%, rgba(66, 153, 225, 0.5) 50%, transparent 100%);
    }
    
    .reservation-item:hover {
      transform: translateX(5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .reservation-item.cancelled {
      border-left-color: #e53e3e;
      background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
    }
    
    .reservation-item.completed {
      border-left-color: #38a169;
      background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-pending {
      background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
      color: #d69e2e;
      border: 1px solid #d69e2e;
    }
    
    .status-confirmed {
      background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
      color: #319795;
      border: 1px solid #319795;
    }
    
    .status-completed {
      background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
      color: #38a169;
      border: 1px solid #38a169;
    }
    
    .status-cancelled {
      background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
      color: #e53e3e;
      border: 1px solid #e53e3e;
    }
    
    .status-for_cancellation {
      background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
      color: #d63031;
      border: 1px solid #d63031;
    }
    
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(66, 153, 225, 0.4);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(66, 153, 225, 0.6);
    }
    
    .btn-danger {
      background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(229, 62, 62, 0.4);
    }
    
    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(229, 62, 62, 0.6);
    }
    
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #718096;
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1.5rem;
      opacity: 0.3;
      background: linear-gradient(135deg, #4299e1, #9f7aea);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #2d3748;
    }

    .empty-state p {
      font-size: 1rem;
      margin-bottom: 2rem;
      opacity: 0.7;
    }
    
    @media (max-width: 768px) {
      .dashboard-container {
        padding: 0 1rem;
      }

      .dashboard-header {
        padding: 2rem 1.5rem;
        margin-bottom: 1.5rem;
      }

      .dashboard-header h1 {
        font-size: 2rem;
      }
      
      .tab-navigation {
        flex-wrap: wrap;
        gap: 0.25rem;
        padding: 0.25rem;
      }
      
      .tab-btn {
        flex: 1 1 calc(50% - 0.125rem);
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
      }

      .card-content {
        padding: 1.5rem;
      }

      .reservation-item, .transaction-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
      }

      .action-buttons {
        justify-content: center;
        gap: 0.75rem;
      }

      .btn {
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .tab-btn {
        flex: 1 1 100%;
        margin-bottom: 0.25rem;
      }
    }

    /* Additional sv-user styles to ensure dropdown works */
    .sv-user { position: relative; }
    .sv-user-menu.open { display: block !important; animation: sv-fade-in-up 0.16s ease; }
    
    @keyframes sv-fade-in-up {
      0% {
        opacity: 0;
        transform: translateY(10px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Cancel Confirmation Modal */
    .cancel-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }

    .cancel-modal.show {
      display: flex;
    }

    .cancel-modal-content {
      background: white;
      border-radius: 20px;
      padding: 2rem;
      max-width: 450px;
      width: 90%;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
      position: relative;
      animation: slideIn 0.3s ease;
    }

    .cancel-modal-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .cancel-modal-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e53e3e;
      font-size: 1.5rem;
    }

    .cancel-modal-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2d3748;
      margin: 0;
    }

    .cancel-modal-text {
      color: #4a5568;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .cancel-modal-reservation {
      background: #f7fafc;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #e53e3e;
    }

    .cancel-modal-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
    }

    .modal-btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-btn-cancel {
      background: #f7fafc;
      color: #4a5568;
      border: 1px solid #e2e8f0;
    }

    .modal-btn-cancel:hover {
      background: #edf2f7;
      transform: translateY(-1px);
    }

    .modal-btn-confirm {
      background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(229, 62, 62, 0.4);
    }

    .modal-btn-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(229, 62, 62, 0.6);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Collapsible Items Styles */
    .reservation-items-toggle {
      background: rgba(66, 153, 225, 0.1);
      border: 1px solid rgba(66, 153, 225, 0.3);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-top: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.875rem;
      font-weight: 600;
      color: #3182ce;
    }

    .reservation-items-toggle:hover {
      background: rgba(66, 153, 225, 0.15);
      border-color: rgba(66, 153, 225, 0.5);
    }

    .reservation-items-toggle i {
      transition: transform 0.3s ease;
    }

    .reservation-items-toggle.expanded i {
      transform: rotate(180deg);
    }

    .reservation-items-list {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;
      background: #f8fafc;
      border-radius: 0 0 8px 8px;
      margin-top: -1px;
      border: 1px solid rgba(66, 153, 225, 0.2);
      border-top: none;
    }

    .reservation-items-list.expanded {
      max-height: 500px;
      padding: 1rem;
    }

    .reservation-single-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .reservation-single-item:last-child {
      border-bottom: none;
    }

    .item-image {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      background: #edf2f7;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .item-image-placeholder {
      color: #a0aec0;
      font-size: 1.25rem;
    }

    .item-details {
      flex: 1;
      min-width: 0;
    }

    .item-name {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }

    .item-specs {
      font-size: 0.75rem;
      color: #718096;
      margin-bottom: 0.25rem;
    }

    .item-price-qty {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8rem;
    }

    .item-price {
      font-weight: 600;
      color: #3182ce;
    }

    .item-qty {
      color: #718096;
    }

    /* Image Preview Modal */
    .image-preview-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      animation: fadeIn 0.3s ease;
    }

    .image-preview-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .image-preview-content {
      position: relative;
      max-width: 90%;
      max-height: 90%;
      animation: zoomIn 0.3s ease;
    }

    .image-preview-content img {
      width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .image-preview-close {
      position: absolute;
      top: -40px;
      right: 0;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #333;
      transition: all 0.2s ease;
    }

    .image-preview-close:hover {
      background: #fff;
      transform: scale(1.1);
    }

    .item-image img {
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .item-image img:hover {
      transform: scale(1.05);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes zoomIn {
      from { transform: scale(0.5); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Navigation matching portal style -->
  <nav class="res-portal-navbar">
    <a href="{{ route('reservation.home') }}" class="res-portal-logo-link">
      <img src="{{ asset('reservation-assets/shoevault-logo.png') }}" alt="ShoeVault Logo" class="res-portal-logo">
    </a>
    <div class="cart-container" style="display:flex;align-items:center;gap:10px;">
      <a href="{{ route('reservation.portal') }}" class="res-portal-nav-btn desktop-only" style="text-decoration: none;margin-right:10px;">
        <span class="nav-icon"><i class="fas fa-store"></i></span>
        <span class="nav-label">Browse Products</span>
      </a>
      <div class="user-status-container">
        <div class="sv-user">
          <button class="sv-user-btn" type="button" aria-expanded="false" aria-haspopup="menu">
            <span class="sv-user-avatar">{{ strtoupper(substr(($customer->username ?? $customer->fullname ?? $customer->name ?? $customer->email ?? 'C'), 0, 1)) }}</span>
            <span class="sv-user-name">{{ $customer->username ?? $customer->first_name ?? $customer->name ?? $customer->fullname ?? 'Customer' }}</span>
            <i class="fas fa-chevron-down" style="font-size:0.75rem;"></i>
          </button>
          <div class="sv-user-menu" role="menu">
            <div class="sv-user-meta">
              <div class="sv-user-email">{{ $customer->email }}</div>
            </div>
            <a href="{{ route('reservation.portal') }}" class="sv-user-item" role="menuitem">
              <i class="fas fa-store"></i>
              <span>Browse Products</span>
            </a>
            <div class="sv-user-divider" style="height: 1px; background-color: #e2e8f0; margin: 8px 0;"></div>
            <form method="POST" action="{{ route('customer.logout') }}">
              @csrf
              <button type="submit" class="sv-user-item danger" role="menuitem">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-main" style="padding-top: 100px;">
    <div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <h1><i class="fas fa-tachometer-alt"></i> Welcome, {{ $customer->fullname }}!</h1>
      <p>Track your reservations and manage your account</p>
    </div>

    <!-- Reservations Card with Tabs -->
    <div class="dashboard-card">
      <div class="card-header">
        <i class="fas fa-calendar-alt"></i> My Reservations
      </div>
      <div class="card-content">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <button class="tab-btn active" onclick="switchTab('pending')" id="pending-tab">
            <i class="fas fa-clock"></i> Pending ({{ count($pendingReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('completed')" id="completed-tab">
            <i class="fas fa-check-circle"></i> Completed ({{ count($completedReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('cancelled')" id="cancelled-tab">
            <i class="fas fa-times-circle"></i> Cancelled ({{ count($cancelledReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('all')" id="all-tab">
            <i class="fas fa-list"></i> All ({{ count($allReservations) }})
          </button>
        </div>

        <!-- Pending Reservations Tab Content -->
        <div class="tab-content active" id="pending-content">
          @if(count($pendingReservations) > 0)
            @foreach($pendingReservations as $reservation)
              <div class="reservation-item {{ $reservation->status }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-{{ $reservation->status }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-clock"></i> Pickup: {{ $reservation->pickup_date->format('M d, Y') }} at {{ \Carbon\Carbon::createFromFormat('H:i:s', $reservation->pickup_time)->format('g:i A') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}')"
                     id="toggle-{{ $reservation->reservation_id }}">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Reserved Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
                
                @if(in_array($reservation->status, ['pending', 'for_cancellation']))
                  <div class="action-buttons">
                    <button class="btn btn-danger" onclick="cancelReservation('{{ $reservation->reservation_id }}')">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </div>
                @endif
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-clock"></i>
              <h3>No Pending Reservations</h3>
              <p>You don't have any pending reservations.</p>
              <a href="{{ route('reservation.portal') }}" class="btn btn-primary">Make a Reservation</a>
            </div>
          @endif
        </div>

        <!-- All Reservations Tab Content -->
        <div class="tab-content" id="all-content">
          @if(count($allReservations) > 0)
            @foreach($allReservations as $reservation)
              <div class="reservation-item">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-{{ $reservation->status }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-clock"></i> Pickup: {{ $reservation->pickup_date->format('M d, Y') }} at {{ \Carbon\Carbon::createFromFormat('H:i:s', $reservation->pickup_time)->format('g:i A') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-pending')"
                     id="toggle-{{ $reservation->reservation_id }}-pending">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Reserved Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-pending">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
                
                <div class="action-buttons">
                  <button class="btn btn-danger" onclick="cancelReservation('{{ $reservation->reservation_id }}')">
                    <i class="fas fa-times"></i> Cancel
                  </button>
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-list"></i>
              <h3>No Reservations</h3>
              <p>You haven't made any reservations yet.</p>
              <a href="{{ route('reservation.portal') }}" class="btn btn-primary">Make a Reservation</a>
            </div>
          @endif
        </div>

        <!-- Completed Reservations Tab Content -->
        <div class="tab-content" id="completed-content">
          @if(count($completedReservations) > 0)
            @foreach($completedReservations as $reservation)
              <div class="reservation-item completed">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-completed">Completed</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-check"></i> Completed: {{ $reservation->pickup_date->format('M d, Y') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-completed')"
                     id="toggle-{{ $reservation->reservation_id }}-completed">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Items Purchased ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-completed">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-check-circle"></i>
              <h3>No Completed Reservations</h3>
              <p>Your completed reservations will appear here.</p>
            </div>
          @endif
        </div>

        <!-- Cancelled Reservations Tab Content -->
        <div class="tab-content" id="cancelled-content">
          @if(count($cancelledReservations) > 0)
            @foreach($cancelledReservations as $reservation)
              <div class="reservation-item cancelled">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-cancelled">Cancelled</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-times"></i> Was scheduled for: {{ $reservation->pickup_date->format('M d, Y') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-cancelled')"
                     id="toggle-{{ $reservation->reservation_id }}-cancelled">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Cancelled Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-cancelled">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-times-circle"></i>
              <h3>No Cancelled Reservations</h3>
              <p>Your cancelled reservations will appear here.</p>
            </div>
          @endif
        </div>
      </div>
    </div>


    </div>
  </div>

  <!-- Image Preview Modal -->
  <div class="image-preview-modal" id="imagePreviewModal">
    <div class="image-preview-content">
      <button class="image-preview-close" onclick="closeImagePreview()">&times;</button>
      <img id="previewImage" src="" alt="Product Preview">
    </div>
  </div>

  <!-- Cancel Confirmation Modal -->
  <div class="cancel-modal" id="cancelModal">
    <div class="cancel-modal-content">
      <div class="cancel-modal-header">
        <div class="cancel-modal-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="cancel-modal-title">Cancel Reservation</h3>
      </div>
      <p class="cancel-modal-text">
        Are you sure you want to cancel this reservation? This action cannot be undone.
      </p>
      <div class="cancel-modal-reservation" id="modalReservationInfo">
        <!-- Reservation details will be inserted here -->
      </div>
      <div class="cancel-modal-actions">
        <button class="modal-btn modal-btn-cancel" onclick="closeCancelModal()">
          <i class="fas fa-times"></i> Keep Reservation
        </button>
        <button class="modal-btn modal-btn-confirm" id="confirmCancelBtn">
          <i class="fas fa-trash"></i> Yes, Cancel It
        </button>
      </div>
    </div>
  </div>

  <script>
    let currentReservationId = null;

    function cancelReservation(reservationId) {
      currentReservationId = reservationId;
      
      // Find the reservation element to get details
      const reservationElements = document.querySelectorAll('.reservation-item');
      let reservationInfo = '';
      
      for (const element of reservationElements) {
        const idElement = element.querySelector('strong');
        if (idElement && idElement.textContent === reservationId) {
          const totalElement = element.querySelector('.fa-peso-sign').parentElement;
          const dateElement = element.querySelector('.fa-calendar').parentElement;
          
          reservationInfo = `
            <strong>${reservationId}</strong><br>
            ${dateElement.textContent}<br>
            ${totalElement.textContent}
          `;
          break;
        }
      }
      
      // Update modal content
      document.getElementById('modalReservationInfo').innerHTML = reservationInfo;
      
      // Show modal
      document.getElementById('cancelModal').classList.add('show');
    }

    function closeCancelModal() {
      document.getElementById('cancelModal').classList.remove('show');
      currentReservationId = null;
    }

    async function confirmCancellation() {
      if (!currentReservationId) return;
      
      const confirmBtn = document.getElementById('confirmCancelBtn');
      const originalText = confirmBtn.innerHTML;
      
      // Show loading state
      confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
      confirmBtn.disabled = true;
      
      try {
        const response = await fetch(`/customer/reservations/${currentReservationId}/cancel`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Show success state briefly
          confirmBtn.innerHTML = '<i class="fas fa-check"></i> Cancelled!';
          confirmBtn.style.background = 'linear-gradient(135deg, #38a169 0%, #2f855a 100%)';
          
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          throw new Error(data.message || 'Failed to cancel reservation.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the reservation: ' + error.message);
        
        // Reset button
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
      }
    }
    
    function switchTab(tabName) {
      // Remove active class from all tabs and content
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
      
      // Add active class to clicked tab and corresponding content
      document.getElementById(tabName + '-tab').classList.add('active');
      document.getElementById(tabName + '-content').classList.add('active');
    }

    function toggleReservationItems(reservationId) {
      const toggle = document.getElementById('toggle-' + reservationId);
      const itemsList = document.getElementById('items-' + reservationId);
      
      if (toggle && itemsList) {
        const isExpanded = toggle.classList.contains('expanded');
        
        if (isExpanded) {
          // Collapse
          toggle.classList.remove('expanded');
          itemsList.classList.remove('expanded');
        } else {
          // Expand
          toggle.classList.add('expanded');
          itemsList.classList.add('expanded');
        }
      }
    }

    // Image Preview Functions
    function showImagePreview(src, alt) {
      const modal = document.getElementById('imagePreviewModal');
      const img = document.getElementById('previewImage');
      img.src = src;
      img.alt = alt;
      modal.classList.add('show');
    }

    function closeImagePreview() {
      const modal = document.getElementById('imagePreviewModal');
      modal.classList.remove('show');
    }

    // Modal event listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Cancel modal functionality
      const cancelModal = document.getElementById('cancelModal');
      const imageModal = document.getElementById('imagePreviewModal');
      const confirmBtn = document.getElementById('confirmCancelBtn');
      
      // Close cancel modal on backdrop click
      cancelModal.addEventListener('click', function(e) {
        if (e.target === cancelModal) {
          closeCancelModal();
        }
      });
      
      // Close image modal on backdrop click
      imageModal.addEventListener('click', function(e) {
        if (e.target === imageModal) {
          closeImagePreview();
        }
      });
      
      // Close modals on Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          if (cancelModal.classList.contains('show')) {
            closeCancelModal();
          }
          if (imageModal.classList.contains('show')) {
            closeImagePreview();
          }
        }
      });
      
      // Confirm cancellation
      confirmBtn.addEventListener('click', confirmCancellation);
    });

    // User menu dropdown functionality (matching portal behavior)
    document.addEventListener('DOMContentLoaded', function() {
      const svUser = document.querySelector('.sv-user');
      if (svUser) {
        const btn = svUser.querySelector('.sv-user-btn');
        const menu = svUser.querySelector('.sv-user-menu');
        
        if (btn && menu) {
          btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('open');
            const isOpen = menu.classList.contains('open');
            btn.setAttribute('aria-expanded', isOpen);
          });

          // Close dropdown when clicking outside
          document.addEventListener('click', function() {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
          });

          // Prevent menu from closing when clicking inside
          menu.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        }
      }
    });

  </script>
</body>
</html>





