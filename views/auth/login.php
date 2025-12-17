<?php
$title = 'Login';

// Get konfigurasi logo for login form BEFORE header.php (which may override it)
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$loginLogoUrl = $baseUrl . '/assets/images/logo.png'; // Default logo

// Get logo from konfigurasi (same logic as header.php but without Auth::check())
try {
    require_once __DIR__ . '/../../models/Konfigurasi.php';
    $konfigurasiModel = new Konfigurasi();
    $konfigurasi = $konfigurasiModel->get();
    if ($konfigurasi && !empty($konfigurasi['logo'])) {
        $logoFilePath = __DIR__ . '/../../uploads/' . $konfigurasi['logo'];
        if (file_exists($logoFilePath)) {
            $loginLogoUrl = $baseUrl . $config['upload_url'] . $konfigurasi['logo'];
        }
    }
} catch (Exception $e) {
    // Silently fail if Konfigurasi model not available
}

// Now require header (which may set its own $konfigurasiLogo, but we use $loginLogoUrl)
require __DIR__ . '/../layouts/header.php';
?>
<div class="login-container">
    <div class="login-card card">
        <div class="card-body">
            <div class="login-logo text-center mb-4">
                <img src="<?= htmlspecialchars($loginLogoUrl) ?>" alt="Logo" class="login-logo-img">
            </div>
            <h3 class="card-title text-center">Login Absensi</h3>
            <form method="POST" action="/login" class="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                        <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Toggle password visibility">
                            <?= icon('eye-slash', '', 18) ?>
                        </button>
                    </div>
                </div>
                <div class="login-buttons-wrapper d-flex gap-2">
                    <button type="submit" class="login-btn flex-grow-1">
                        <?= icon('login', '', 20) ?> Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Scroll login form to top when keyboard appears on mobile
document.addEventListener('DOMContentLoaded', function() {
    const loginCard = document.querySelector('.login-card');
    const inputs = document.querySelectorAll('.login-form input[type="text"], .login-form input[type="password"]');
    
    if (loginCard && inputs.length > 0) {
        inputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                // Check if mobile device
                const isMobile = window.innerWidth <= 991.98;
                
                if (isMobile) {
                    // Delay to ensure keyboard is shown and viewport is adjusted
                    setTimeout(function() {
                        const cardRect = loginCard.getBoundingClientRect();
                        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                        const targetScroll = currentScroll + cardRect.top - 20; // 20px offset from top
                        
                        window.scrollTo({
                            top: targetScroll,
                            behavior: 'smooth'
                        });
                    }, 300);
                }
            });
        });
    }

    // Toggle password visibility
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const usernameInput = document.getElementById('username');
    
    if (passwordInput && passwordToggle) {
        // Store base URL from PHP
        const baseUrl = <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const icon = passwordToggle.querySelector('img');
        
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle input type
            const currentType = passwordInput.type;
            const newType = currentType === 'password' ? 'text' : 'password';
            
            // Change input type immediately
            passwordInput.type = newType;
            
            // Toggle icon with cache busting
            if (icon) {
                if (newType === 'password') {
                    icon.src = baseUrl + '/assets/icons/eye-slash.svg?v=' + Date.now();
                    icon.alt = 'Show password';
                    passwordToggle.setAttribute('aria-label', 'Show password');
                } else {
                    icon.src = baseUrl + '/assets/icons/eye.svg?v=' + Date.now();
                    icon.alt = 'Hide password';
                    passwordToggle.setAttribute('aria-label', 'Hide password');
                }
            }
        });
    }


    // Save username to localStorage when form is submitted successfully
    const loginForm = document.querySelector('.login-form');
    if (loginForm && usernameInput) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            if (username.length > 0) {
                localStorage.setItem('last_username', username);
            }
        });
    }

    // Load and fill last username in input field (mobile only)
    function loadLastUsername() {
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
        if (!isMobile || !usernameInput) {
            return;
        }

        const lastUsername = localStorage.getItem('last_username');
        const currentUsername = usernameInput.value.trim();
        
        if (lastUsername && lastUsername.length > 0 && currentUsername.length === 0) {
            usernameInput.value = lastUsername;
            setTimeout(function() {
                usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }, 300);
        }
    }

    loadLastUsername();

});
</script>

<style>
.login-buttons-wrapper {
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

