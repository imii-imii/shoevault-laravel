// ===== LOGIN PAGE JAVASCRIPT =====

// ===== USER CREDENTIALS =====
const USERS = [{
        username: 'admin',
        password: 'admin123',
        role: 'admin',
        name: 'Administrator',
        permissions: ['all']
    },
    {
        username: 'cashier',
        password: 'cashier123',
        role: 'cashier',
        name: 'Lauren Smith',
        permissions: ['pos', 'reports']
    },
    {
        username: 'manager',
        password: 'manager123',
        role: 'manager',
        name: 'John Manager',
        permissions: ['pos', 'inventory', 'reports', 'settings']
    }
];

// ===== SESSION MANAGEMENT =====
function checkSession() {
    const session = localStorage.getItem('pos_session');
    const currentPage = window.location.pathname.split("/").pop(); // e.g., "login.html" or "inventory.html"

    if (session) {
        const sessionData = JSON.parse(session);
        const now = new Date().getTime();

        if (now - sessionData.timestamp < 24 * 60 * 60 * 1000) {
            // Session valid
            if (currentPage === 'login.html' || currentPage === '') {
                // If on login page and already logged in → redirect
                window.location.href = 'inventory.html';
            }
            return;
        } else {
            // Expired session
            localStorage.removeItem('pos_session');
        }
    }

    // No valid session
    if (currentPage !== 'login.html') {
        // If not on login page → redirect to login
        window.location.href = 'login.html';
    }
}


function createSession(user) {
    const session = {
        user: {
            username: user.username,
            role: user.role,
            name: user.name,
            permissions: user.permissions
        },
        timestamp: new Date().getTime()
    };
    localStorage.setItem('pos_session', JSON.stringify(session));
}

function clearSession() {
    localStorage.removeItem('pos_session');
}

// ===== FORM VALIDATION =====
function validateForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username) {
        showError('Please enter your username');
        document.getElementById('username').focus();
        return false;
    }

    if (!password) {
        showError('Please enter your password');
        document.getElementById('password').focus();
        return false;
    }

    return true;
}

// ===== AUTHENTICATION =====
function authenticateUser(username, password) {
    return USERS.find(user =>
        user.username.toLowerCase() === username.toLowerCase() &&
        user.password === password
    );
}

// ===== LOGIN HANDLER =====
async function handleLogin(event) {
    event.preventDefault();

    if (!validateForm()) {
        return;
    }

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('remember-me').checked;

    // Show loading
    showLoading();

    // Simulate authentication delay
    await new Promise(resolve => setTimeout(resolve, 1500));

    // Authenticate user
    const user = authenticateUser(username, password);

    if (user) {
        // Create session
        createSession(user);

        // Store remember me preference
        if (rememberMe) {
            localStorage.setItem('remember_me', 'true');
        } else {
            localStorage.removeItem('remember_me');
        }

        // Show success message
        showSuccess('Login successful! Redirecting...');

        // Redirect to main app after a short delay
        setTimeout(() => {
            window.location.href = 'inventory.html';

        }, 1000);

    } else {
        hideLoading();
        showError('Invalid username or password. Please try again.');

        // Clear password field
        document.getElementById('password').value = '';
        document.getElementById('password').focus();
    }
}

// ===== PASSWORD TOGGLE =====
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

// ===== LOADING STATES =====
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    const loginBtn = document.getElementById('login-btn');

    loadingOverlay.classList.add('show');
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Authenticating...</span>';
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    const loginBtn = document.getElementById('login-btn');

    loadingOverlay.classList.remove('show');
    loginBtn.disabled = false;
    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i><span>Login to POS</span>';
}

// ===== NOTIFICATIONS =====
function showError(message) {
    const errorNotification = document.getElementById('error-notification');
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = message;
    errorNotification.classList.add('show');

    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorNotification.classList.remove('show');
    }, 5000);
}

function showSuccess(message) {
    // Create success notification
    const successNotification = document.createElement('div');
    successNotification.className = 'success-notification';
    successNotification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

    // Add styles
    successNotification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #38a169 0%, #68d391 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 1001;
        animation: slideInRight 0.3s ease-out;
    `;

    document.body.appendChild(successNotification);

    // Auto-hide after 3 seconds
    setTimeout(() => {
        successNotification.remove();
    }, 3000);
}

// ===== FORM ENHANCEMENTS =====
function setupFormEnhancements() {
    const form = document.getElementById('login-form');
    const inputs = form.querySelectorAll('input');

    // Add focus effects
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // Add enter key support
    form.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleLogin(e);
        }
    });

    // Auto-focus username field
    document.getElementById('username').focus();
}

// ===== DEMO CREDENTIALS COPY =====
function setupDemoCredentials() {
    const credentialItems = document.querySelectorAll('.credential-item');

    credentialItems.forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            const text = this.textContent;
            const credentials = text.split(':')[1].trim();
            const [username, password] = credentials.split('/');

            document.getElementById('username').value = username.trim();
            document.getElementById('password').value = password.trim();

            // Show feedback
            this.style.background = '#e6fffa';
            this.style.borderColor = '#38a169';
            setTimeout(() => {
                this.style.background = '';
                this.style.borderColor = '';
            }, 1000);
        });

        // Add hover effect
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

// ===== FORGOT PASSWORD =====
function setupForgotPassword() {
    const forgotPasswordLink = document.querySelector('.forgot-password');

    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        showError('Please contact your system administrator to reset your password.');
    });
}

// ===== REMEMBER ME =====
function setupRememberMe() {
    const rememberMe = localStorage.getItem('remember_me');
    if (rememberMe === 'true') {
        document.getElementById('remember-me').checked = true;
    }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    // Check for existing session
    checkSession();

    // Setup form enhancements
    setupFormEnhancements();

    // Setup demo credentials
    setupDemoCredentials();

    // Setup forgot password
    setupForgotPassword();

    // Setup remember me
    setupRememberMe();

    // Add some visual feedback for form interactions
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
        const input = group.querySelector('input');
        if (input) {
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.style.borderColor = '#38a169';
                } else {
                    this.style.borderColor = '';
                }
            });
        }
    });
});

// ===== EXPOSE FUNCTIONS =====
window.handleLogin = handleLogin;
window.togglePassword = togglePassword;
window.showError = showError;
window.showSuccess = showSuccess;