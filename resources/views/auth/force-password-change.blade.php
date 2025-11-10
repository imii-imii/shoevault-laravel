<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - ShoeVault Batangas</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Anime.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #000e2e 0%, #2343ce 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Futuristic ambient decorations */
        .bg-decor { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
        .orb { position: absolute; border-radius: 50%; filter: blur(18px); opacity: .35; }
        .orb.one { width: 420px; height: 420px; left: -140px; top: -140px; background: radial-gradient(circle, rgba(35,67,206,0.8), rgba(35,67,206,0)); }
        .orb.two { width: 520px; height: 520px; right: -180px; bottom: -180px; background: radial-gradient(circle, rgba(0,14,46,0.85), rgba(0,14,46,0)); }
        .scanline { position:absolute; left:0; right:0; height:1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); opacity:.18; }

        .change-password-container {
            position: relative;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.18);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            z-index: 1;
            opacity: 0;
            transform: translateY(16px);
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .logo p {
            color: #718096;
            font-size: 0.9rem;
        }

        .warning-message {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2343ce;
            box-shadow: 0 0 0 3px rgba(35, 67, 206, 0.15);
        }

        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .change-btn {
            width: 100%;
            background: linear-gradient(135deg, #000e2e 0%, #2343ce 100%);
            color: #fff;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow .3s ease;
            margin-bottom: 20px;
        }

        .change-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -10px rgba(35,67,206,0.45);
        }

        .change-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .logout-link {
            color: #718096;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .logout-link:hover {
            color: #2d3748;
        }

        .password-requirements {
            background: #f7fafc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }

        .password-requirements h4 {
            color: #2d3748;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .password-requirements ul {
            list-style: none;
            font-size: 0.8rem;
            color: #4a5568;
        }

        .password-requirements li {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirement-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .requirement-icon.met {
            background: #48bb78;
            color: #fff;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="bg-decor" aria-hidden="true">
        <span class="orb one"></span>
        <span class="orb two"></span>
        <span class="scanline" style="top: 25%"></span>
        <span class="scanline" style="top: 65%"></span>
    </div>
    <div class="change-password-container">
        <div class="logo">
            <h1>ShoeVault</h1>
            <p>Batangas</p>
        </div>

        @if (session('message'))
            <div class="alert alert-error">
                {{ session('message') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="change-password-form" method="POST" action="{{ route('force-password-change.update') }}">
            @csrf
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
                <div class="error-message" id="current_password_error">
                    @error('current_password') {{ $message }} @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <div class="error-message" id="new_password_error">
                    @error('new_password') {{ $message }} @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
                <div class="error-message" id="new_password_confirmation_error">
                    @error('new_password_confirmation') {{ $message }} @enderror
                </div>
            </div>

            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>
                        <span class="requirement-icon" id="length-req"><i class="fas fa-times"></i></span>
                        At least 8 characters long
                    </li>
                    <li>
                        <span class="requirement-icon" id="letter-req"><i class="fas fa-times"></i></span>
                        Contains at least one letter
                    </li>
                    <li>
                        <span class="requirement-icon" id="number-req"><i class="fas fa-times"></i></span>
                        Contains at least one number
                    </li>
                    <li>
                        <span class="requirement-icon" id="different-req"><i class="fas fa-times"></i></span>
                        Different from current password
                    </li>
                </ul>
            </div>

            <button type="submit" class="change-btn" id="submit-btn" disabled>
                Change Password
            </button>
        </form>

        <a href="{{ route('logout') }}" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout instead
        </a>
    </div>

    <script>
        const form = document.getElementById('change-password-form');
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('new_password_confirmation');
        const submitBtn = document.getElementById('submit-btn');

        // Requirement indicators
        const lengthReq = document.getElementById('length-req');
        const letterReq = document.getElementById('letter-req');
        const numberReq = document.getElementById('number-req');
        const differentReq = document.getElementById('different-req');

        function checkRequirements() {
            const newPass = newPassword.value;
            const currentPass = currentPassword.value;
            const confirmPass = confirmPassword.value;

            // Check length
            const hasLength = newPass.length >= 8;
            updateRequirement(lengthReq, hasLength);

            // Check letter
            const hasLetter = /[a-zA-Z]/.test(newPass);
            updateRequirement(letterReq, hasLetter);

            // Check number
            const hasNumber = /\d/.test(newPass);
            updateRequirement(numberReq, hasNumber);

            // Check different from current
            const isDifferent = newPass !== currentPass && newPass.length > 0;
            updateRequirement(differentReq, isDifferent);

            // Check passwords match
            const passwordsMatch = newPass === confirmPass && newPass.length > 0;

            // Enable submit button if all requirements are met
            const allMet = hasLength && hasLetter && hasNumber && isDifferent && passwordsMatch;
            submitBtn.disabled = !allMet;
        }

        function updateRequirement(element, met) {
            if (met) {
                element.classList.add('met');
                element.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                element.classList.remove('met');
                element.innerHTML = '<i class="fas fa-times"></i>';
            }
        }

        // Add event listeners
        currentPassword.addEventListener('input', checkRequirements);
        newPassword.addEventListener('input', checkRequirements);
        confirmPassword.addEventListener('input', checkRequirements);

        // Show validation errors
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-error';
                errorDiv.textContent = '{{ $error }}';
                form.insertBefore(errorDiv, form.firstChild);
            @endforeach
        @endif

        // Use anime.js for subtle, professional animations
        if (window.anime) {
            // Card entrance
            anime({ targets: '.change-password-container', opacity: [0,1], translateY: [16,0], duration: 650, easing: 'easeOutCubic' });

            // Ambient motion
            anime({ targets: '.orb.one', translateX: [-18, 18], translateY: [-10, 10], scale: [1, 1.05], direction: 'alternate', loop: true, duration: 5200, easing: 'easeInOutSine' });
            anime({ targets: '.orb.two', translateX: [16, -16], translateY: [12, -12], scale: [1.04, 0.96], direction: 'alternate', loop: true, duration: 6000, easing: 'easeInOutSine' });
            anime({ targets: '.scanline', opacity: [{ value: .32, duration: 900 }, { value: .12, duration: 1100 }], delay: anime.stagger(650), direction: 'alternate', loop: true, easing: 'linear' });

            // Success toast animation (if exists)
            const toast = document.getElementById('success-toast');
            if (toast) {
                anime.timeline()
                    .add({ targets: toast, opacity: [0,1], translateY: [10,0], scale: [.98,1], duration: 500, easing: 'easeOutBack' })
                    .add({ targets: toast, delay: 2400, opacity: [1,0], translateY: [0,-10], duration: 500, easing: 'easeInCubic', complete: () => toast.remove() });
            }
        }
    </script>
</body>
</html>