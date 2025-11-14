<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShoeVault Verification Code</title>
    
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
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .tagline {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #4a5568;
            line-height: 1.7;
        }
        .code-container {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        .code-label {
            font-size: 14px;
            color: #718096;
            margin-bottom: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .verification-code {
            font-size: 36px;
            font-weight: 800;
            color: #2343ce;
            letter-spacing: 8px;
            margin: 0;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            font-size: 14px;
            color: #e53e3e;
            margin-top: 15px;
            font-weight: 600;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .note {
            background: #fef5e7;
            border: 1px solid #f6e05e;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #744210;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ShoeVault Batangas</div>
            <div class="tagline">Premium Footwear & Accessories</div>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $customerName }}!</div>
            
            <div class="message">
                Welcome to ShoeVault! To complete your account verification and start reserving your favorite shoes, please use the verification code below:
            </div>
            
            <div class="code-container">
                <div class="code-label">Your Verification Code</div>
                <div class="verification-code">{{ $verificationCode }}</div>
                <div class="expiry">⏰ Expires in 10 minutes</div>
            </div>
            
            <div class="note">
                <strong>Security tip:</strong> Never share this code with anyone. ShoeVault will never ask for your verification code via phone or email.
            </div>
            
            <div class="message">
                If you didn't request this code, you can safely ignore this email. Your account security is important to us.
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} ShoeVault Batangas. All rights reserved.</p>
            <p>Questions? Contact us at shoevaul2020@gmail.com</p>
        </div>
    </div>
</body>
</html>