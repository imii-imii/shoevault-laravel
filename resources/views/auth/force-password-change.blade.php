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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .change-password-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .change-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-bottom: 20px;
        }

        .change-btn:hover {
            transform: translateY(-2px);
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
    </script>
</body>
</html>