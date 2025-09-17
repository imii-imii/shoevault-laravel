@extends('layouts.app')

@section('title', 'Login - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <!-- Background Pattern -->
    <div class="background-pattern"></div>

    <!-- Login Card -->
    <div class="login-card">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-container">
                <img src="{{ asset('assets/images/logo.png') }}" alt="ShoeVault Batangas" class="logo-img">
            </div>
            <div class="brand-info">
                <h1 class="brand-name">ShoeVault Batangas</h1>
                <p class="brand-tagline">Premium Footwear & Accessories</p>
            </div>
        </div>

        <!-- Login Form -->
        <div class="login-form">
            <div class="form-header">
                <h2><i class="fas fa-user-circle"></i> Login</h2>
                <p>Enter your credentials to access the system</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" 
                           placeholder="Enter your username" 
                           value="{{ old('username') }}" 
                           required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" 
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Authenticating...</p>
        </div>
    </div>

    <!-- Error Notification -->
    <div class="error-notification" id="error-notification">
        <i class="fas fa-exclamation-circle"></i>
        <span id="error-message">Invalid credentials</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Password toggle functionality
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
}

// Enhanced form submission with loading states
document.getElementById('login-form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('login-btn');
    const loadingOverlay = document.getElementById('loading-overlay');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Logging in...</span>';
    loadingOverlay.style.display = 'flex';
});

// Auto-hide error messages
document.addEventListener('DOMContentLoaded', function() {
    const errorNotification = document.getElementById('error-notification');
    if (document.querySelector('.alert-error')) {
        setTimeout(function() {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
});
</script>
@endpush
