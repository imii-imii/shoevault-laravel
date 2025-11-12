<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoe Vault Reservation Confirmation</title>
    
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
            background: linear-gradient(135deg, #000e2e 0%, #2343ce 100%);
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
        .receipt-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #2343ce;
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
            color: #2343ce;
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
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
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
        .highlight-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .highlight-box p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 10px 0;
        }
        .contact-info {
            font-size: 12px;
            color: #9ca3af;
            margin: 10px 0 0 0;
        }
        .separator {
            border-top: 1px dashed #d1d5db;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SHOE VAULT BATANGAS</div>
            <p class="subtitle">Reservation Confirmation</p>
        </div>
        
        <div class="content">
            <h2 style="color: #111827; margin-bottom: 10px;">Thank you for your reservation!</h2>
            <p style="color: #6b7280; margin-bottom: 30px;">Your reservation has been confirmed. Please find your receipt details below:</p>
            
            <div class="receipt-section">
                <div class="receipt-header">
                    <div class="business-name">SHOE VAULT BATANGAS</div>
                    <div class="business-address">
                        Manghinao Proper Bauan, Batangas 4201<br>
                        Tel.: +63 936 382 0087
                    </div>
                    <div class="receipt-id">Receipt #{{ $receiptNumber }}</div>
                </div>
                
                <div class="receipt-meta">
                    <span>{{ now()->format('F d, Y') }}</span>
                    <span>{{ now()->format('h:i A') }}</span>
                </div>
                
                <div class="separator"></div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservationData['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }} @if(!empty($item['size']))(Size {{ $item['size'] }})@endif</td>
                            <td style="text-align: center;">{{ $item['qty'] ?? $item['quantity'] ?? 1 }}</td>
                            <td style="text-align: right;">₱ {{ number_format($item['priceNumber'] ?? $item['price'] ?? 0, 2) }}</td>
                            <td style="text-align: right;">₱ {{ number_format(($item['priceNumber'] ?? $item['price'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td style="text-align: right;"><strong>₱ {{ number_format($reservationData['total'], 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="customer-info">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $reservationData['customer']['fullName'] ?? $reservationData['customer']['name'] ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $reservationData['customer']['phone'] ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $reservationData['customer']['email'] ?? 'N/A' }}</p>
                @if(!empty($reservationData['customer']['pickupDate']))
                <p><strong>Pickup Date:</strong> {{ \Carbon\Carbon::parse($reservationData['customer']['pickupDate'])->format('F d, Y') }}</p>
                @endif
                @if(!empty($reservationData['customer']['pickupTime']))
                <p><strong>Pickup Time:</strong> {{ \Carbon\Carbon::parse($reservationData['customer']['pickupTime'])->format('g:i A') }}</p>
                @endif
            </div>
            
            <div class="highlight-box">
                <p><strong>Important:</strong> Please bring this confirmation receipt when you visit our store. Your reserved items will be held until your scheduled pickup time. Items not collected on the specified date and time will be released for other customers.</p>
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text">Thank you for choosing Shoe Vault Batangas!</p>
            <p class="footer-text">We look forward to serving you.</p>
            <p class="contact-info">
                Manghinao Proper Bauan, Batangas 4201<br>
                Phone: +63 936 382 0087<br>
                Business Hours: 10:00 AM - 8:00 PM
            </p>
        </div>
    </div>
</body>
</html>