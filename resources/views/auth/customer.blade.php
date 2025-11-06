<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>ShoeVault Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Montserrat:wght@600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <style>
    :root{
      /* Light mode, monochrome with minimal blue accent */
      --bg-gradient: linear-gradient(135deg, #f7faff 0%, #ffffff 65%);
      --card-bg: rgba(255, 255, 255, 0.88);
      --card-border: rgba(2,6,23,0.08);
      --text: #0f172a;
      --muted: #64748b;
      --white: #ffffff;
      --accent: #2343ce;
      --accent-gradient: linear-gradient(135deg, #000e2e 0%, #2343ce 100%);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
  body{margin:0;font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji';color:var(--text);background:var(--bg-gradient);overflow:hidden}
    .auth-wrap{position:relative;min-height:100%;display:grid;place-items:center;padding:24px}

    /* Blurred background orbs */
  .orb{position:absolute;filter:blur(90px);opacity:.32;pointer-events:none}
  .orb.o1{width:420px;height:420px;background:#e0e7ff;top:-80px;left:-80px;border-radius:50%}
  .orb.o2{width:520px;height:520px;background:#eff6ff;bottom:-120px;right:-140px;border-radius:50%}
  .orb.o3{width:360px;height:360px;background:#dbeafe;top:30%;left:60%;border-radius:45%}

    .card{position:relative;z-index:2;width:min(960px, 94vw);background:var(--card-bg);backdrop-filter: blur(14px) saturate(120%);-webkit-backdrop-filter: blur(14px) saturate(120%);
      border:1px solid var(--card-border);border-radius:20px;box-shadow:0 20px 60px rgba(2,6,23,.08), inset 0 1px 0 rgba(255,255,255,.6);
      display:grid;grid-template-columns:1.05fr .95fr;overflow:hidden}

  .left{padding:28px 28px 28px 28px;display:flex;flex-direction:column;justify-content:center;gap:18px;border-right:1px solid var(--card-border)}
    .brand{display:flex;align-items:center;gap:12px}
  .brand img{width:44px;height:44px;border-radius:12px;object-fit:contain;background:#ffffff;padding:6px;border:1px solid rgba(2,6,23,.06)}
    .brand h1{font-size:1.25rem;margin:0;font-weight:800;letter-spacing:.3px}
    .tag{color:var(--muted);font-size:.92rem;line-height:1.5}
  .switcher{display:flex;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:6px;gap:6px;width:max-content}
  .switcher button{border:0;background:transparent;color:#64748b;font-weight:700;padding:10px 16px;border-radius:999px;cursor:pointer;transition:all .2s ease; -webkit-tap-highlight-color: transparent;}
    .switcher button.active{
      border:1px solid transparent;
      background:
        linear-gradient(#ffffff, #f8fbff) padding-box,
        var(--accent-gradient) border-box;
      color:#0b1220;
      box-shadow:0 10px 24px -14px rgba(35,67,206,.35);
    }

    form{display:grid;gap:12px}
    .field{display:flex;flex-direction:column;gap:6px}
  .label{font-size:.85rem;color:#6b7280;font-weight:700}
  .input{height:44px;border-radius:12px;border:1px solid #e5e7eb;background:#ffffff;color:#0f172a;padding:0 12px;outline:none;transition:all .15s ease}
  .input:focus{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(147,197,253,.25)}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .actions{display:flex;align-items:center;justify-content:space-between;margin-top:6px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;padding:0 16px;border-radius:12px;border:1px solid #e5e7eb;cursor:pointer;font-weight:800;transition:transform .05s ease, filter .15s ease, box-shadow .15s ease}
    .btn:hover{filter:brightness(1.05)}
    .btn:active{transform:translateY(1px)}

  .btn-primary{
    border:1px solid transparent;
    background:
      linear-gradient(180deg, #ffffff 0%, #f7fbff 100%) padding-box,
      var(--accent-gradient) border-box;
    color:#0b1220
  }
  .btn-primary:hover{box-shadow:0 12px 28px -14px rgba(35,67,206,.28)}
  .btn-ghost{background:#f8fafc;color:#0f172a}

    .right{position:relative;display:flex;align-items:center;justify-content:center;background:var(--accent-gradient);color:#ffffff}
    .hero{padding:28px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px}
  .hero h2{margin:0;font-size:1.6rem;font-weight:800;color:#ffffff}
  .hero p{margin:0;color:rgba(255,255,255,0.85)}
  .hero-graphic{width:200px;height:200px;border-radius:20px;background:rgba(255,255,255,0.08);border:1px dashed rgba(255,255,255,0.12);display:grid;place-items:center}
  .hero-graphic i{color:#ffffff;opacity:0.95}

    /* Sliding forms */
    .forms{position:relative;overflow:hidden}
    .panel{width:100%;transition:transform .35s ease, opacity .35s ease}
    .panel.hidden{position:absolute;inset:0;opacity:0;transform:translateX(20px);pointer-events:none}
    .panel.active{opacity:1;transform:translateX(0)}

    /* Mobile-first enhancements */
    .mobile-hero{display:none}

    @media (max-width: 960px){
      /* Allow scrolling on small screens */
      body{overflow:auto}
      /* Hide decorative orbs to reduce clutter */
      .orb{display:none}
      /* Show a compact gradient hero above the form */
      .mobile-hero{display:block;width:100%;max-width:720px;margin:8px auto 6px auto;background:var(--accent-gradient);color:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,14,46,.25)}
      .mobile-hero .mh-content{display:flex;align-items:center;gap:10px;padding:12px 14px}
      .mobile-hero img{width:32px;height:32px;border-radius:8px;background:#ffffff;padding:6px}
      .mobile-hero .mh-title{font-weight:800;letter-spacing:.2px}

      /* Stack content with tight spacing (remove grid centering) */
      .auth-wrap{padding:10px 12px;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;gap:10px;min-height:100%}
      .card{grid-template-columns:1fr;width:min(560px, 100%);box-shadow:0 12px 28px rgba(2,6,23,.08);border-radius:16px}
      .left{padding:16px;border-right:0}
      .switcher{transform:scale(.98)}
      .right{display:none}

      /* Prevent overflow in 2-column rows on small screens */
      .row{grid-template-columns:1fr}
      /* Stack action buttons and make them full width to avoid overflow */
      .actions{flex-direction:column;align-items:stretch;gap:10px}
      .actions .btn{width:100%}
    }

    /* Terms & Conditions Modal */
    .t-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:4000}
    .t-modal.is-open{display:flex}
    .t-modal .t-backdrop{position:absolute;inset:0;background:rgba(2,6,23,.5);backdrop-filter:blur(4px)}
    .t-modal .t-window{position:relative;z-index:1;width:min(720px,92vw);max-height:80vh;overflow:auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 22px 60px rgba(2,6,23,.18);padding:18px 18px 16px 18px;transform:scale(.94);opacity:0;animation:t-zoom .28s ease forwards}
    .t-modal .t-header{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
    .t-modal .t-title{margin:0;font-size:1.1rem;font-weight:800;color:#0f172a}
    .t-modal .t-close{border:0;background:#f1f5f9;color:#0f172a;width:34px;height:34px;border-radius:10px;cursor:pointer}
    .t-modal .t-content{color:#475569;line-height:1.6}
    @keyframes t-zoom{to{transform:scale(1);opacity:1}}

    /* Login Success Overlay (anime.js animated) */
    .login-success-overlay {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #020d27 0%, #092c80 50%, #1e40af 100%);
      z-index: 9999;
      overflow: hidden;
    }
    .login-success-overlay.is-open { display: flex; }
    
    .success-particles {
      position: absolute;
      inset: 0;
      overflow: hidden;
      pointer-events: none;
    }
    .success-particle {
      position: absolute;
      width: 6px;
      height: 6px;
      background: rgba(255,255,255,0.6);
      border-radius: 50%;
    }
    
    .success-content {
      position: relative;
      z-index: 10;
      text-align: center;
      color: #fff;
      opacity: 0;
    }
    .success-icon-wrap {
      width: 120px;
      height: 120px;
      margin: 0 auto 24px;
      position: relative;
    }
    .success-circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 12px 32px rgba(16,185,129,0.5);
    }
    .success-check {
      font-size: 60px;
      color: #fff;
    }
    .success-ripple {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 3px solid rgba(16,185,129,0.8);
      opacity: 0;
    }
    .success-title {
      font-size: 32px;
      font-weight: 800;
      margin-bottom: 12px;
      letter-spacing: -0.5px;
    }
    .success-subtitle {
      font-size: 16px;
      color: rgba(255,255,255,0.85);
      margin-bottom: 32px;
    }
    .success-loader {
      width: 200px;
      height: 4px;
      background: rgba(255,255,255,0.2);
      border-radius: 999px;
      margin: 0 auto;
      overflow: hidden;
    }
    .success-loader-bar {
      height: 100%;
      background: linear-gradient(90deg, #ffffff 0%, #e0e7ff 100%);
      border-radius: 999px;
      box-shadow: 0 0 12px rgba(255,255,255,0.6);
      transform: translateX(-100%);
    }
    @media (max-width: 480px) {
      .success-icon-wrap { width: 90px; height: 90px; }
      .success-circle { width: 90px; height: 90px; }
      .success-check { font-size: 45px; }
      .success-title { font-size: 24px; }
      .success-subtitle { font-size: 14px; }
    }
  </style>
</head>
<body>
  <div class="auth-wrap">
    <!-- Mobile Gradient Hero (shown on small screens) -->
    <div class="mobile-hero" aria-hidden="true">
      <div class="mh-content">
        <img src="{{ asset('images/logo.png') }}" alt="ShoeVault" />
        <div class="mh-title">Welcome to ShoeVault</div>
      </div>
    </div>
    <div class="orb o1"></div>
    <div class="orb o2"></div>
    <div class="orb o3"></div>

    <div class="card">
      <div class="left">
        <div class="brand">
          <img src="{{ asset('images/logo.png') }}" alt="ShoeVault" />
          <h1>ShoeVault Account</h1>
        </div>
        <div class="tag">Sign in or create an account to manage your reservations and get seamless checkout.</div>
        <div class="switcher" role="tablist" aria-label="Auth tabs">
          <button id="tab-login" class="active" aria-controls="panel-login" aria-selected="true">Login</button>
          <button id="tab-signup" aria-controls="panel-signup" aria-selected="false">Sign Up</button>
        </div>

        <div class="forms">
          <!-- Login Panel -->
          <div id="panel-login" class="panel active" role="tabpanel" aria-labelledby="tab-login">
            <form onsubmit="event.preventDefault();">
              <div class="field">
                <label class="label" for="login-email">Email</label>
                <input class="input" type="email" id="login-email" placeholder="you@example.com" required />
              </div>
              <div class="field">
                <label class="label" for="login-password">Password</label>
                <input class="input" type="password" id="login-password" placeholder="••••••••" required />
              </div>
              <div class="actions">
                <a href="#" onclick="showForgotPasswordStep(); return false;" style="color:var(--muted);text-decoration:none">Forgot password?</a>
                <button class="btn btn-primary" type="submit">Login</button>
              </div>
            </form>
          </div>

          <!-- Signup Panel -->
          <div id="panel-signup" class="panel hidden" role="tabpanel" aria-labelledby="tab-signup">
            <form onsubmit="event.preventDefault();">
              <div class="row">
                <div class="field">
                  <label class="label" for="su-first">First Name</label>
                  <input class="input" type="text" id="su-first" placeholder="Juan" required />
                </div>
                <div class="field">
                  <label class="label" for="su-last">Last Name</label>
                  <input class="input" type="text" id="su-last" placeholder="Dela Cruz" required />
                </div>
              </div>

              <!-- Username field added for signup -->
              <div class="field">
                <label class="label" for="su-username">Username</label>
                <input class="input" type="text" id="su-username" placeholder="your-username" required />
              </div>

              <div class="field">
                <label class="label" for="su-email">Email</label>
                <input class="input" type="email" id="su-email" placeholder="you@example.com" required />
              </div>
              <div class="row">
                <div class="field">
                  <label class="label" for="su-pass">Password</label>
                  <input class="input" type="password" id="su-pass" placeholder="Create a password" required />
                </div>
                <div class="field">
                  <label class="label" for="su-confirm">Confirm Password</label>
                  <input class="input" type="password" id="su-confirm" placeholder="Re-enter password" required />
                </div>
              </div>
              <!-- Terms & Conditions consent -->
              <div class="field" style="flex-direction:row;align-items:center;gap:10px">
                <input type="checkbox" id="su-terms" style="width:18px;height:18px" required />
                <label for="su-terms" class="label" style="margin:0;color:#0f172a">
                  I agree to the
                  <a href="#" id="open-terms" class="terms-link" style="color:var(--accent);text-decoration:underline;font-weight:700">Terms & Conditions</a>
                </label>
              </div>
              <div class="actions" style="justify-content:flex-end">
                <button class="btn btn-ghost" type="button" id="go-login">Already have an account?</button>
                <button class="btn btn-primary" type="submit">Create Account</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="right">
        <div class="hero">
          <div class="hero-graphic">
            <img src="{{ asset('images/logo.png') }}" alt="ShoeVault" style="width:200;height:200px;object-fit:contain;border-radius:12px;background:rgba(255,255,255,0.06);padding:10px;" />
          </div>
          <h2>Step into seamless reservations</h2>
          <p>Keep your sizes secure, and checkout faster across devices.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Login Success Overlay -->
  <div id="loginSuccessOverlay" class="login-success-overlay">
    <div class="success-particles" id="successParticles"></div>
    <div class="success-content" id="successContent">
      <div class="success-icon-wrap">
        <div class="success-ripple" id="ripple1"></div>
        <div class="success-ripple" id="ripple2"></div>
        <div class="success-ripple" id="ripple3"></div>
        <div class="success-circle">
          <i class="fas fa-check success-check" id="successCheck"></i>
        </div>
      </div>
      <h1 class="success-title">Welcome Back!</h1>
      <p class="success-subtitle">Login successful. Redirecting to portal...</p>
      <div class="success-loader">
        <div class="success-loader-bar" id="successLoaderBar"></div>
      </div>
    </div>
  </div>

  <!-- Terms & Conditions Modal -->
  <div id="termsModal" class="t-modal" aria-hidden="true" role="dialog" aria-label="Terms and Conditions">
    <div class="t-backdrop"></div>
    <div class="t-window">
      <div class="t-header">
        <h3 class="t-title">Sample Terms & Conditions</h3>
        <button class="t-close" aria-label="Close Terms">&times;</button>
      </div>
      <div class="t-content">
        <p>Welcome to ShoeVault. These sample Terms & Conditions are for demonstration only:</p>
        <ul>
          <li>Use a valid email address and keep your password secure.</li>
          <li>Reservations may be subject to stock availability and confirmation.</li>
          <li>Your information will be processed according to our privacy practices.</li>
        </ul>
        <p>By creating an account, you confirm that you have read and agreed to these terms.</p>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const tabLogin = document.getElementById('tab-login');
      const tabSignup = document.getElementById('tab-signup');
      const panelLogin = document.getElementById('panel-login');
      const panelSignup = document.getElementById('panel-signup');
      const goLogin = document.getElementById('go-login');

      // Get forms
      const loginForm = panelLogin.querySelector('form');
      const signupForm = panelSignup.querySelector('form');

      // CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      function activate(tab){
        const loginActive = tab === 'login';
        tabLogin.classList.toggle('active', loginActive);
        tabSignup.classList.toggle('active', !loginActive);
        tabLogin.setAttribute('aria-selected', loginActive ? 'true' : 'false');
        tabSignup.setAttribute('aria-selected', loginActive ? 'false' : 'true');
        panelLogin.classList.toggle('active', loginActive);
        panelLogin.classList.toggle('hidden', !loginActive);
        panelSignup.classList.toggle('active', !loginActive);
        panelSignup.classList.toggle('hidden', loginActive);
      }

      function showMessage(message, type = 'info') {
        // Create or update message div
        let messageDiv = document.querySelector('.auth-message');
        if (!messageDiv) {
          messageDiv = document.createElement('div');
          messageDiv.className = 'auth-message';
          messageDiv.style.cssText = `
            padding: 12px 16px;
            margin: 16px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
          `;
          document.querySelector('.forms').prepend(messageDiv);
        }

        messageDiv.textContent = message;
        messageDiv.style.backgroundColor = type === 'error' ? '#fee2e2' : '#dbeafe';
        messageDiv.style.color = type === 'error' ? '#dc2626' : '#1e40af';
        messageDiv.style.borderColor = type === 'error' ? '#fca5a5' : '#93c5fd';
      }

      async function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        if (!email || !password) {
          showMessage('Please enter your email and password.', 'error');
          return;
        }

        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging in...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.login.post") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ email, password }),
          });

          const data = await response.json();

          if (data.success) {
            // Show anime.js success animation
            showLoginSuccessAnimation();
            // Redirect back to the page they came from or portal
            const returnUrl = new URLSearchParams(window.location.search).get('return') || '{{ route("reservation.portal") }}';
            setTimeout(() => {
              window.location.href = returnUrl;
            }, 2200);
          } else {
            // Check if email verification is needed
            if (data.needs_email_verification) {
              showMessage(data.message);
              showEmailVerificationStep(data.email);
            }
            // Check if password reset is needed
            else if (data.needs_password_reset) {
              showMessage(data.message);
              showPasswordResetStep(email);
            } else {
              showMessage(data.message, 'error');
            }
          }
        } catch (error) {
          showMessage('Something went wrong. Please try again.', 'error');
          console.error('Login error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      function showForgotPasswordStep() {
        // Replace login form with forgot password form
        panelLogin.innerHTML = `
          <form onsubmit="handleForgotPassword(event)">
            <div class="field">
              <label class="label" for="forgot-email">Email</label>
              <input class="input" type="email" id="forgot-email" placeholder="Enter your email address" required />
            </div>
            <div style="margin:12px 0;padding:12px;background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;font-size:0.85rem;color:#1e40af;">
              We'll send you a verification code to reset your password.
            </div>
            <div class="actions">
              <button type="button" onclick="location.reload()" class="btn btn-ghost">Back</button>
              <button class="btn btn-primary" type="submit">Send Reset Code</button>
            </div>
          </form>
        `;
      }

      window.handleForgotPassword = async function(e) {
        e.preventDefault();
        const email = document.getElementById('forgot-email').value;

        if (!email) {
          showMessage('Please enter your email address.', 'error');
          return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.send-password-reset-code") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
          });

          const data = await response.json();

          if (data.success) {
            showMessage(data.message);
            showPasswordUpdateStep(email);
          } else {
            showMessage(data.message, 'error');
          }
        } catch (error) {
          showMessage('Something went wrong. Please try again.', 'error');
          console.error('Forgot password error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      function showPasswordResetStep(email) {
        // Replace login form with password reset form
        panelLogin.innerHTML = `
          <form onsubmit="handlePasswordReset(event)">
            <div class="field">
              <label class="label">Email</label>
              <input class="input" type="email" value="${email}" readonly />
            </div>
            <div style="margin:12px 0;padding:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;font-size:0.85rem;color:#dc2626;">
              Your account needs a password update. We'll send you a verification code to reset your password.
            </div>
            <div class="actions">
              <button type="button" onclick="location.reload()" class="btn btn-ghost">Back</button>
              <button class="btn btn-primary" type="submit">Send Reset Code</button>
            </div>
          </form>
        `;
      }

      window.handlePasswordReset = async function(e) {
        e.preventDefault();
        const email = document.querySelector('input[type="email"]').value;

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.send-password-reset-code") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
          });

          const data = await response.json();

          if (data.success) {
            showMessage(data.message);
            showPasswordUpdateStep(email);
          } else {
            showMessage(data.message, 'error');
          }
        } catch (error) {
          showMessage('Something went wrong. Please try again.', 'error');
          console.error('Password reset error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      function showPasswordUpdateStep(email) {
        // Replace form with password update form
        panelLogin.innerHTML = `
          <form onsubmit="handlePasswordUpdate(event)">
            <div class="field">
              <label class="label">Email</label>
              <input class="input" type="email" value="${email}" readonly />
            </div>
            <div class="field">
              <label class="label" for="reset-verification-code">Verification Code</label>
              <input class="input" type="text" id="reset-verification-code" placeholder="Enter 6-digit code" maxlength="6" required />
            </div>
            <div class="field">
              <label class="label" for="new-password">New Password</label>
              <input class="input" type="password" id="new-password" placeholder="Create a new password" required />
            </div>
            <div class="field">
              <label class="label" for="confirm-new-password">Confirm Password</label>
              <input class="input" type="password" id="confirm-new-password" placeholder="Confirm your password" required />
            </div>
            <div class="actions">
              <button type="button" onclick="location.reload()" class="btn btn-ghost">Back</button>
              <button class="btn btn-primary" type="submit">Update Password</button>
            </div>
          </form>
        `;
      }

      window.handlePasswordUpdate = async function(e) {
        e.preventDefault();
        const email = document.querySelector('input[type="email"]').value;
        const code = document.getElementById('reset-verification-code').value;
        const password = document.getElementById('new-password').value;
        const passwordConfirmation = document.getElementById('confirm-new-password').value;

        if (!code || code.length !== 6) {
          showMessage('Please enter a valid 6-digit verification code.', 'error');
          return;
        }

        if (password !== passwordConfirmation) {
          showMessage('Passwords do not match.', 'error');
          return;
        }

        if (password.length < 6) {
          showMessage('Password must be at least 6 characters long.', 'error');
          return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.update-password") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ 
              email, 
              verification_code: code, 
              password, 
              password_confirmation: passwordConfirmation 
            }),
          });

          const data = await response.json();

          if (data.success) {
            showMessage(data.message);
            // Go back to login form
            setTimeout(() => location.reload(), 2000);
          } else {
            showMessage(data.message, 'error');
          }
        } catch (error) {
          showMessage('Something went wrong. Please try again.', 'error');
          console.error('Password update error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      function showEmailVerificationStep(email) {
        // Clear any existing message
        const existingMessage = document.querySelector('.auth-message');
        if (existingMessage) {
          existingMessage.remove();
        }
        
        const verificationForm = `
          <form id="email-verification-form">
            <div class="field">
              <label class="label">Email</label>
              <input class="input" type="email" name="email" id="verification-email" value="${email}" readonly />
            </div>
            <div class="field">
              <label class="label" for="verification-code">Verification Code</label>
              <input class="input" type="text" id="verification-code" name="code" placeholder="Enter 6-digit code" maxlength="7" required />
            </div>
            <div style="margin:12px 0;padding:12px;background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;font-size:0.85rem;color:#1e40af;">
              Please check your email for the verification code we just sent.
            </div>
            <div class="actions">
              <button type="button" onclick="location.reload()" class="btn btn-ghost">Back</button>
              <button type="button" id="resend-verification-btn" class="btn btn-ghost" onclick="handleResendVerification('${email}')" disabled style="color: #dc2626;">
                Resend Code (<span id="resend-countdown">200</span>s)
              </button>
              <button class="btn btn-primary" type="submit">Verify Email</button>
            </div>
          </form>
        `;
        
        // Always use the signup panel for verification
        activate('signup');
        panelSignup.innerHTML = verificationForm;
        
        // Attach the email verification handler to the new form
        const newForm = document.getElementById('email-verification-form');
        if (newForm) {
          newForm.addEventListener('submit', handleEmailVerification);
        }
        
        // Start the countdown timer
        startResendCountdown();
      }

      function startResendCountdown() {
        let countdown = 200;
        const countdownElement = document.getElementById('resend-countdown');
        const resendButton = document.getElementById('resend-verification-btn');
        
        // Set initial disabled state with red text
        if (resendButton) {
          resendButton.disabled = true;
          resendButton.style.color = '#dc2626';
          resendButton.style.cursor = 'not-allowed';
        }
        
        const timer = setInterval(() => {
          countdown--;
          
          if (countdownElement) {
            countdownElement.textContent = countdown;
          }
          
          if (countdown <= 0) {
            clearInterval(timer);
            if (resendButton) {
              resendButton.disabled = false;
              resendButton.innerHTML = 'Resend Code';
              resendButton.style.color = '';
              resendButton.style.cursor = 'pointer';
            }
          }
        }, 1000);
        
        // Store timer reference to clear if needed
        window.resendTimer = timer;
      }

      window.handleResendVerification = async function(email) {
        const resendButton = document.getElementById('resend-verification-btn');
        const originalText = resendButton.textContent;
        
        resendButton.textContent = 'Sending...';
        resendButton.disabled = true;
        
        try {
          const response = await fetch('{{ route("customer.resend-verification-code") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
          });

          const data = await response.json();

          if (data.success) {
            showMessage(data.message);
            // Clear existing timer if any
            if (window.resendTimer) {
              clearInterval(window.resendTimer);
            }
            // Reset button display and restart countdown
            resendButton.innerHTML = 'Resend Code (<span id="resend-countdown">200</span>s)';
            startResendCountdown();
          } else {
            showMessage(data.message, 'error');
            resendButton.textContent = originalText;
            resendButton.disabled = false;
          }
        } catch (error) {
          showMessage('Failed to resend code. Please try again.', 'error');
          resendButton.textContent = originalText;
          resendButton.disabled = false;
        }
      }

      async function handleEmailVerification(e) {
        e.preventDefault();
        const email = document.getElementById('verification-email').value;
        const codeInput = document.getElementById('verification-code');
        const code = codeInput.value.trim(); // Trim whitespace

        if (!email) {
          showMessage('Email is required.', 'error');
          return;
        }

        if (!code) {
          showMessage('Please enter the verification code.', 'error');
          codeInput.focus();
          return;
        }

        // More flexible validation - allow numeric codes
        if (!/^\d+$/.test(code) || code.length < 5 || code.length > 7) {
          showMessage('Verification code must be 5-7 digits.', 'error');
          codeInput.focus();
          codeInput.select();
          return;
        }

        console.log('Sending verification request:', { email, code }); // Debug log

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Verifying...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.verify-code") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ email, code }),
          });

          console.log('Response status:', response.status); // Debug
          
          const data = await response.json();
          console.log('Response data:', data); // Debug

          if (response.ok && data.success) {
            showMessage('Email verified successfully! You can now login with your email and password.');
            // Switch to login tab
            activate('login');
          } else {
            // Show the specific error message from the server
            const errorMessage = data.message || `Server error (${response.status})`;
            showMessage(errorMessage, 'error');
            
            // If it's a validation error, show field errors
            if (data.errors) {
              console.log('Validation errors:', data.errors);
              Object.keys(data.errors).forEach(field => {
                console.log(`${field}:`, data.errors[field]);
              });
            }
          }
        } catch (error) {
          showMessage('Network error. Please check your connection and try again.', 'error');
          console.error('Email verification error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      async function handleSignup(e) {
        e.preventDefault();
        const firstName = document.getElementById('su-first').value;
        const lastName = document.getElementById('su-last').value;
        const username = document.getElementById('su-username').value;
        const email = document.getElementById('su-email').value;
        const password = document.getElementById('su-pass').value;
        const confirmPassword = document.getElementById('su-confirm').value;
        const termsAccepted = document.getElementById('su-terms')?.checked;

        // Basic validation
        if (!firstName || !lastName || !username || !email || !password) {
          showMessage('Please fill in all required fields.', 'error');
          return;
        }

        if (password !== confirmPassword) {
          showMessage('Passwords do not match.', 'error');
          return;
        }

        if (!termsAccepted) {
          showMessage('Please accept the Terms & Conditions to continue.', 'error');
          return;
        }

        const submitBtn = signupForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creating Account...';
        submitBtn.disabled = true;

        try {
          const response = await fetch('{{ route("customer.register") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              first_name: firstName,
              last_name: lastName,
              username,
              email,
              password,
              // Include terms acceptance for server-side validation
              terms: termsAccepted,
            }),
          });

          const data = await response.json();

          // Surface validation errors (e.g., terms must be accepted)
          if (!response.ok && data?.errors) {
            const firstField = Object.keys(data.errors)[0];
            const firstMsg = Array.isArray(data.errors[firstField]) ? data.errors[firstField][0] : data.message;
            showMessage(firstMsg || 'Please check the form and try again.', 'error');
            return;
          }

          if (data.success) {
            showMessage(data.message);
            // Show verification step for email verification
            showEmailVerificationStep(email);
          } else {
            showMessage(data.message, 'error');
          }
        } catch (error) {
          showMessage('Something went wrong. Please try again.', 'error');
          console.error('Signup error:', error);
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      }

      // Event listeners
      tabLogin.addEventListener('click', ()=> activate('login'));
      tabSignup.addEventListener('click', ()=> activate('signup'));
      goLogin?.addEventListener('click', ()=> activate('login'));

      // Form event listeners
      loginForm.addEventListener('submit', handleLogin);
      signupForm.addEventListener('submit', handleSignup);

      // Terms modal events
      const termsModal = document.getElementById('termsModal');
      const openTerms = document.getElementById('open-terms');
      function openTermsModal(e){ e?.preventDefault?.(); termsModal?.classList.add('is-open'); }
      function closeTermsModal(){ termsModal?.classList.remove('is-open'); }
      openTerms?.addEventListener('click', openTermsModal);
      termsModal?.addEventListener('click', (e)=>{ if (e.target === termsModal || e.target.closest('.t-close')) closeTermsModal(); });
      document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeTermsModal(); });

      // Login success animation with anime.js
      function showLoginSuccessAnimation() {
        const overlay = document.getElementById('loginSuccessOverlay');
        const content = document.getElementById('successContent');
        const check = document.getElementById('successCheck');
        const ripple1 = document.getElementById('ripple1');
        const ripple2 = document.getElementById('ripple2');
        const ripple3 = document.getElementById('ripple3');
        const loaderBar = document.getElementById('successLoaderBar');
        const particlesContainer = document.getElementById('successParticles');

        // Show overlay
        overlay.classList.add('is-open');

        // Generate floating particles
        for (let i = 0; i < 25; i++) {
          const particle = document.createElement('div');
          particle.className = 'success-particle';
          particle.style.left = Math.random() * 100 + '%';
          particle.style.top = '100%';
          particlesContainer.appendChild(particle);

          // Animate particle floating up
          anime({
            targets: particle,
            translateY: [0, -window.innerHeight - 100],
            opacity: [0, 1, 1, 0],
            scale: [0, 1, 1, 0.5],
            delay: Math.random() * 1000,
            duration: 3000 + Math.random() * 1000,
            easing: 'easeOutCubic'
          });
        }

        // Animate content fade in
        anime({
          targets: content,
          opacity: [0, 1],
          translateY: [30, 0],
          duration: 600,
          easing: 'easeOutExpo'
        });

        // Animate checkmark pop
        anime({
          targets: check,
          scale: [0, 1],
          rotate: [-45, 0],
          duration: 500,
          delay: 200,
          easing: 'easeOutElastic(1, 0.6)'
        });

        // Animate ripples
        anime({
          targets: [ripple1, ripple2, ripple3],
          scale: [1, 1.8],
          opacity: [0.8, 0],
          duration: 2000,
          delay: anime.stagger(400, {start: 300}),
          loop: true,
          easing: 'easeOutQuad'
        });

        // Animate loader bar
        anime({
          targets: loaderBar,
          translateX: ['-100%', '0%'],
          duration: 2000,
          delay: 400,
          easing: 'easeOutQuad'
        });
      }
    })();
  </script>
</body>
</html>
