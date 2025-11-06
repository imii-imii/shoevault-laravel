<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - ShoeVault</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #020d27 0%, #092c80 50%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Animated background particles */
        .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            animation: float-up 4s ease-in infinite;
        }
        @keyframes float-up {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }
        
        /* Success card */
        .logout-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 100px rgba(42,106,255,0.15);
            max-width: 440px;
            width: 90%;
            position: relative;
            z-index: 10;
            animation: card-entrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes card-entrance {
            0% { transform: scale(0.7) translateY(30px); opacity: 0; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }
        
        /* Animated checkmark icon */
        .icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            position: relative;
        }
        .circle-bg {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(16,185,129,0.4);
            animation: pulse-success 1.5s ease-in-out infinite;
            position: relative;
        }
        @keyframes pulse-success {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 24px rgba(16,185,129,0.4); }
            50% { transform: scale(1.05); box-shadow: 0 12px 32px rgba(16,185,129,0.6); }
        }
        .checkmark {
            font-size: 48px;
            color: #fff;
            animation: checkmark-pop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.2s both;
        }
        @keyframes checkmark-pop {
            0% { transform: scale(0) rotate(-45deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        
        /* Ripple effect */
        .ripple {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 3px solid rgba(16,185,129,0.6);
            animation: ripple-out 2s ease-out infinite;
        }
        .ripple:nth-child(2) { animation-delay: 0.5s; }
        .ripple:nth-child(3) { animation-delay: 1s; }
        @keyframes ripple-out {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        
        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            animation: fade-slide-up 0.6s ease 0.3s both;
        }
        p {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 8px;
            animation: fade-slide-up 0.6s ease 0.4s both;
        }
        @keyframes fade-slide-up {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        /* Loading bar */
        .redirect-info {
            margin-top: 32px;
            animation: fade-slide-up 0.6s ease 0.5s both;
        }
        .redirect-text {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 999px;
            animation: progress-fill 2s ease-out forwards;
            box-shadow: 0 0 12px rgba(37,99,235,0.6);
        }
        @keyframes progress-fill {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        /* Skip button */
        .skip-btn {
            margin-top: 20px;
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 10px 24px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            animation: fade-slide-up 0.6s ease 0.6s both;
        }
        .skip-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #475569;
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .logout-card { padding: 36px 28px; }
            h1 { font-size: 24px; }
            p { font-size: 14px; }
            .icon-wrapper { width: 80px; height: 80px; }
            .circle-bg { width: 80px; height: 80px; }
            .checkmark { font-size: 38px; }
        }
    </style>
</head>
<body>
    <!-- Animated particles background -->
    <div class="particles" id="particles"></div>
    
    <div class="logout-card">
        <div class="icon-wrapper">
            <div class="ripple"></div>
            <div class="ripple"></div>
            <div class="ripple"></div>
            <div class="circle-bg">
                <i class="fas fa-check checkmark"></i>
            </div>
        </div>
        
        <h1>Successfully Logged Out!</h1>
        <p>You've been safely logged out of your account.</p>
        <p style="font-size: 14px; color: #94a3b8;">Thank you for visiting ShoeVault.</p>
        
        <div class="redirect-info">
            <div class="redirect-text">Redirecting to login page...</div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <button class="skip-btn" onclick="redirectNow()">
                <i class="fas fa-arrow-right" style="margin-right: 6px;"></i>
                Skip & Go Now
            </button>
        </div>
    </div>
    
    <script>
        // Generate animated particles
        (function() {
            const container = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 4 + 's';
                particle.style.animationDuration = (3 + Math.random() * 2) + 's';
                container.appendChild(particle);
            }
        })();
        
        // Clear any stored carts (legacy and namespaced) then redirect to login
        (function clearCartStorage(){
            try {
                // Remove legacy key
                localStorage.removeItem('sv_cart');
                // Remove any keys that start with sv_cart (e.g., sv_cart_guest, sv_cart_<id>)
                const toRemove = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.indexOf('sv_cart') === 0) toRemove.push(key);
                }
                toRemove.forEach(k => localStorage.removeItem(k));
            } catch (e) {
                // ignore storage errors
            }
        })();

        function redirectNow() {
            window.location.href = '{{ route("customer.login") }}';
        }
        
                // store timer so anime.js can coordinate/clear it if needed
                window.logoutRedirectTimer = setTimeout(redirectNow, 2500);
    </script>
        <!-- anime.js for richer, coordinated animations -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js" integrity="sha512-5x8mYfC6lqQ+e6kQeYJf9D2s+fXl1KqQG8qW2G2tYJm8v6Jp6jKf5rG9y9R5Gk2zQm9e5a1h2b3c4d5e6f7g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                // pause/disable CSS particle animation to let anime.js drive motion
                document.querySelectorAll('.particle').forEach(el => el.style.animation = 'none');

                // Card entrance
                anime({
                    targets: '.logout-card',
                    translateY: [30, 0],
                    scale: [0.96, 1],
                    opacity: [0, 1],
                    duration: 700,
                    easing: 'cubicBezier(.2,1,.3,1)'
                });

                // Particles: floating with staggered delays
                const particles = Array.from(document.querySelectorAll('.particle'));
                particles.forEach((p, i) => {
                    const dur = 3800 + Math.floor(Math.random() * 1400);
                    anime({
                        targets: p,
                        translateY: [window.innerHeight * 0.6, -window.innerHeight * 0.6],
                        translateX: [0, (Math.random() - 0.5) * 240],
                        opacity: [0, 1, 0],
                        scale: [0.3, 1],
                        easing: 'linear',
                        delay: i * 120 + Math.floor(Math.random() * 300),
                        duration: dur,
                        loop: true
                    });
                });

                // Ripples
                anime({
                    targets: '.ripple',
                    scale: [1, 1.8],
                    opacity: [1, 0],
                    duration: 2000,
                    delay: anime.stagger(350),
                    easing: 'easeOutQuad',
                    loop: true
                });

                // Checkmark pop
                anime({
                    targets: '.checkmark',
                    scale: [0, 1.05, 1],
                    rotate: [-30, 0],
                    duration: 700,
                    delay: 260,
                    easing: 'spring(1, 80, 10, 0)'
                });

                // Progress bar animation - tie into existing redirect timer
                const progressEl = document.querySelector('.progress-fill');
                const totalMs = 2500; // match previous timeout

                // clear existing timer if any and use anime's complete to redirect
                if (window.logoutRedirectTimer) {
                    clearTimeout(window.logoutRedirectTimer);
                }

                const prog = anime({
                    targets: progressEl,
                    width: ['0%','100%'],
                    easing: 'linear',
                    duration: totalMs,
                    autoplay: true,
                    complete: function(){
                        // ensure redirect only happens once
                        redirectNow();
                    }
                });

                // Skip button: immediately redirect and stop progress
                const skip = document.querySelector('.skip-btn');
                if (skip) {
                    skip.addEventListener('click', function(e){
                        prog.pause();
                        if (window.logoutRedirectTimer) {
                            clearTimeout(window.logoutRedirectTimer);
                        }
                        redirectNow();
                    });
                }
            });
        </script>
</body>
</html>
