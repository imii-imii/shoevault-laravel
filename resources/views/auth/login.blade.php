@extends('layouts.app')

@section('title', 'Login - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@push('scripts')
    @include('partials.mobile-blocker')
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
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

    // Animate eye icon
    if (window.anime && passwordIcon) {
        anime({ targets: passwordIcon, rotate: [0, 180], duration: 240, easing: 'easeOutCubic' });
        anime({ targets: '.password-input-container', scale: [1, 1.02, 1], duration: 220, easing: 'easeOutQuad' });
    }
}

// Enhanced form submission with loading states
document.getElementById('login-form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('login-btn');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loginCard = document.querySelector('.login-card');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Logging in...</span>';
    loadingOverlay.style.display = 'flex';
    if (window.anime) {
        anime.set(loadingOverlay, { opacity: 0 });
        anime({ targets: loadingOverlay, opacity: [0, 1], duration: 240, easing: 'linear' });
        if (loginCard) {
            anime({ targets: loginCard, opacity: [1, 0.9], scale: [1, 0.98], duration: 260, easing: 'easeOutQuad' });
        }
    }
});

// Auto-hide error messages
document.addEventListener('DOMContentLoaded', function() {
    const errorNotification = document.getElementById('error-notification');
    // Initial entrance animations
    if (window.anime) {
        // Subtle floating background
        const bg = document.querySelector('.background-pattern');
        if (bg) {
            anime({ targets: bg, translateY: [0, 6], translateX: [0, 6], direction: 'alternate', easing: 'easeInOutSine', duration: 5000, loop: true, autoplay: true });
            anime.set(bg, { opacity: 0 });
            anime({ targets: bg, opacity: [0, 1], duration: 600, easing: 'easeOutCubic' });
        }

        const timeline = anime.timeline({ autoplay: true });
        // Prepare initial states
        anime.set('.login-card', { opacity: 0, translateY: 16 });
        anime.set('.logo-container', { opacity: 0, scale: 0.9 });
        anime.set('.brand-info', { opacity: 0, translateY: 10 });
        anime.set('.form-header', { opacity: 0, translateY: 8 });
        anime.set('.form-group', { opacity: 0, translateY: 8 });
        anime.set('.form-options', { opacity: 0, translateY: 8 });
        anime.set('#login-btn', { opacity: 0, translateY: 8 });

        timeline
            .add({ targets: '.login-card', opacity: [0,1], translateY: [16, 0], duration: 520, easing: 'easeOutCubic' })
            .add({ targets: '.logo-container', opacity: [0,1], scale: [0.9, 1], duration: 420, easing: 'easeOutBack' }, '-=360')
            .add({ targets: '.brand-info', opacity: [0,1], translateY: [10, 0], duration: 420, easing: 'easeOutCubic' }, '-=380')
            .add({ targets: '.form-header', opacity: [0,1], translateY: [8, 0], duration: 360, easing: 'easeOutCubic' }, '-=320')
            .add({ targets: '.form-group', opacity: [0,1], translateY: [8, 0], delay: anime.stagger(70), duration: 360, easing: 'easeOutCubic' }, '-=260')
            .add({ targets: '.form-options', opacity: [0,1], translateY: [8, 0], duration: 320, easing: 'easeOutCubic' }, '-=260')
            .add({ targets: '#login-btn', opacity: [0,1], translateY: [8, 0], duration: 340, easing: 'easeOutCubic' }, '-=240');
    }

    // Animate error alert in and auto-hide with anime.js
    const alertEl = document.querySelector('.alert-error');
    if (alertEl && window.anime) {
        anime.set(alertEl, { opacity: 0, translateY: -6 });
        anime({ targets: alertEl, opacity: [0, 1], translateY: [-6, 0], duration: 380, easing: 'easeOutCubic' });
        setTimeout(() => {
            anime({ targets: alertEl, opacity: [1, 0], translateY: [0, -6], duration: 300, easing: 'easeInCubic', complete: () => alertEl.remove() });
        }, 5000);
    } else if (alertEl) {
        // Fallback if anime isn't loaded for some reason
        setTimeout(() => { alertEl.style.opacity = '0'; setTimeout(() => alertEl.remove(), 300); }, 5000);
    }

    // Input focus micro-interactions
    const inputs = document.querySelectorAll('#username, #password');
    inputs.forEach(inp => {
        inp.addEventListener('focus', () => {
            if (window.anime) anime({ targets: inp, scale: [1, 1.01], duration: 180, easing: 'easeOutQuad' });
        });
        inp.addEventListener('blur', () => {
            if (window.anime) anime({ targets: inp, scale: 1, duration: 180, easing: 'easeOutQuad' });
        });
    });

    // Button hover micro-interactions
    const loginBtn = document.getElementById('login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('mouseenter', () => {
            if (window.anime) anime({ targets: loginBtn, scale: 1.03, duration: 140, easing: 'easeOutQuad' });
        });
        loginBtn.addEventListener('mouseleave', () => {
            if (window.anime) anime({ targets: loginBtn, scale: 1, duration: 140, easing: 'easeOutQuad' });
        });
    }
});
</script>
@endpush
