<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoe Vault Reservation Cancelled</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .cancellation-notice {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }
        .cancellation-notice h3 {
            margin: 0 0 10px 0;
            color: #dc2626;
            font-size: 18px;
        }
        .reason-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .reason-box h4 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }
        .reason-box p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            font-style: italic;
        }
        .receipt-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #6b7280;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #e5e7eb;
        }
        .business-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 5px;
        }
        .business-address {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
        }
        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 12px;
            color: #374151;
        }
        .receipt-id {
            font-weight: 600;
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #f3f4f6;
            padding: 12px 8px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .items-table td {
            padding: 10px 8px;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: center;
        }
        .total-row {
            font-weight: 700;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
        }
        .total-row td {
            padding: 15px 8px;
            font-size: 14px;
            color: #111827;
        }
        .customer-info {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .customer-info h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #111827;
        }
        .customer-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #374151;
        }
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #6b7280;
        }
        .contact-info {
            margin: 20px 0;
            font-size: 13px;
            color: #374151;
        }
        .apology-message {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .apology-message h3 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 16px;
        }
        .apology-message p {
            margin: 0;
            color: #451a03;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SHOE VAULT BATANGAS</div>
            <p class="subtitle">Reservation Cancellation Notice</p>
        </div>
        
        <div class="content">
            <div class="cancellation-notice">
                <h3>🚫 Reservation Cancelled</h3>
                <p>We regret to inform you that your reservation has been cancelled. Please see the details below.</p>
            </div>

            <div class="reason-box">
                <h4>Cancellation Reason:</h4>
                <p>"{{ $cancellationReason }}"</p>
            </div>

            <div class="receipt-section">
                <div class="receipt-header">
                    <div class="business-name">SHOE VAULT BATANGAS</div>
                    <div class="business-address">
                        Manghinao Proper Bauan, Batangas 4201<br>
                        Tel.: +63 936 382 0087
                    </div>
                </div>

                <div class="receipt-id">Reservation ID: {{ $reservation->reservation_id }}</div>
                
                <div class="receipt-meta">
                    <span>Reserved: {{ $reservation->created_at ? $reservation->created_at->format('M d, Y h:i A') : 'N/A' }}</span>
                    <span>Cancelled: {{ now()->format('M d, Y h:i A') }}</span>
                </div>

                @if($reservation->items && count($reservation->items) > 0)
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->items as $item)
                            @php
                                $quantity = intval($item['quantity'] ?? 1);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item['product_name'] ?? $item['name'] ?? 'Product' }}</strong><br>
                                    <small style="color: #6b7280;">
                                        {{ $item['product_brand'] ?? $item['brand'] ?? '' }}
                                        @if(!empty($item['product_color'] ?? $item['color']))
                                            • {{ $item['product_color'] ?? $item['color'] }}
                                        @endif
                                        @if(!empty($item['size']))
                                            • Size {{ $item['size'] }}
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <div class="customer-info">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $reservation->customer_name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $reservation->customer_email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $reservation->customer_phone ?? 'N/A' }}</p>
                @if($reservation->pickup_date)
                    <p><strong>Original Pickup Date:</strong> {{ $reservation->pickup_date->format('M d, Y') }}
                    @if($reservation->pickup_time)
                        at {{ \Carbon\Carbon::createFromFormat('H:i:s', $reservation->pickup_time)->format('g:i A') }}
                    @endif
                    </p>
                @endif
            </div>

            <div class="apology-message">
                <h3>We Apologize for the Inconvenience</h3>
                <p>We sincerely apologize for any inconvenience this cancellation may have caused. If you have any questions or would like to make a new reservation, please don't hesitate to contact us.</p>
            </div>

            <div class="contact-info">
                <p><strong>Need Help?</strong></p>
                <p>📞 Phone: +63 936 382 0087</p>
                <p>📧 Email: shoevault2020@gmail.com</p>
                <p>📍 Address: Manghinao Proper Bauan, Batangas 4201</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated message from Shoe Vault Batangas.</p>
            <p>© {{ date('Y') }} Shoe Vault Batangas. All rights reserved.</p>
        </div>
    </div>
</body>
</html>