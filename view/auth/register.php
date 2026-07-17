<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . '://' . $host . $scriptDir);
}

include __DIR__ . '/../layout/header.php';
?>

<!-- ─── MAIN CONTAINER (background changes with dark mode) ─── -->
<div class="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-300">
    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row items-stretch justify-center min-h-[80vh] gap-8 lg:gap-12">

        <!-- ─── LEFT SIDE: Cartoon Illustration ─── -->
        <div class="flex-1 min-h-[300px] lg:min-h-[500px] flex items-center justify-center p-6">
            <div class="text-center max-w-sm">
                <svg viewBox="0 0 200 160" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto max-h-[200px] mx-auto">
                    <path d="M40 40 L40 120 Q60 100 100 120 L100 40 Q60 20 40 40Z" fill="var(--svg-book-yellow)" stroke="var(--svg-book-stroke-yellow)" stroke-width="2" />
                    <path d="M100 40 L100 120 Q140 100 160 120 L160 40 Q140 20 100 40Z" fill="var(--svg-book-blue)" stroke="var(--svg-book-stroke-blue)" stroke-width="2" />
                    <path d="M44 44 L44 116 Q62 98 100 116 L100 44 Q62 26 44 44Z" fill="var(--svg-inner-yellow)" />
                    <path d="M100 44 L100 116 Q138 98 156 116 L156 44 Q138 26 100 44Z" fill="var(--svg-inner-blue)" />
                    <circle cx="100" cy="30" r="14" fill="var(--svg-sun)" />
                    <circle cx="95" cy="26" r="2" fill="var(--svg-eyes)" />
                    <circle cx="105" cy="26" r="2" fill="var(--svg-eyes)" />
                    <path d="M94 34 Q100 38 106 34" stroke="var(--svg-eyes)" stroke-width="2" fill="none" stroke-linecap="round" />
                    <rect x="92" y="44" width="16" height="18" rx="4" fill="var(--svg-rect)" />
                    <path d="M88 48 L82 52 M112 48 L118 52" stroke="var(--svg-arrow)" stroke-width="3" stroke-linecap="round" />
                    <path d="M130 18 Q148 2 160 14 Q170 24 158 34 Q148 40 136 36 L130 44 L134 34 Q128 28 130 18Z" fill="var(--svg-cloud)" stroke="var(--svg-cloud-stroke)" stroke-width="1.5" />
                    <text x="146" y="24" font-size="9" fill="var(--svg-text)" font-family="sans-serif" text-anchor="middle">📖</text>
                </svg>
                <h2 class="text-2xl lg:text-3xl font-bold text-blue-800 mt-4 dark:text-blue-300">Start Your Reading Journey</h2>
                <p class="text-gray-600 mt-2 text-sm lg:text-base dark:text-gray-300">
                    Create an account to borrow books, manage your loans, and explore new worlds.
                </p>
                <div class="mt-4 flex flex-wrap justify-center gap-3 text-xs lg:text-sm text-gray-700 dark:text-gray-300">
                    <span class="inline-flex items-center gap-1"><span class="text-green-500">✓</span> Free</span>
                    <span class="inline-flex items-center gap-1"><span class="text-green-500">✓</span> 24/7</span>
                    <span class="inline-flex items-center gap-1"><span class="text-green-500">✓</span> Easy</span>
                </div>
            </div>
        </div>

        <!-- ─── RIGHT SIDE: Registration Card ─── -->
        <div class="flex-1 max-w-md w-full lg:max-w-sm flex items-center">
            <div class="glass-card" id="registerCard">
                <div class="icon-wrap">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <h2 class="text-center text-2xl font-bold text-gray-800 mt-1 dark:text-white">Create Account</h2>
                <p class="text-center text-sm text-gray-500 mb-5 dark:text-gray-400">Join our library system</p>

                <?php if (!empty($_SESSION['register_errors']['general'])): ?>
                    <div class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 p-3 rounded-lg text-sm mb-4 flex items-start gap-2 border border-red-200 dark:border-red-800">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <span><?= htmlspecialchars($_SESSION['register_errors']['general']) ?></span>
                    </div>
                    <?php unset($_SESSION['register_errors']['general']); ?>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/register" method="POST" class="space-y-4" autocomplete="on">

                    <!-- Full Name -->
                    <div class="form-group <?= isset($_SESSION['register_errors']['name']) ? 'has-error' : '' ?>">
                        <label for="name" class="form-label"><i class="fas fa-user mr-1"></i> Full Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-circle"></i>
                            <input type="text" name="name" id="name" placeholder="Enter your full name"
                                   class="field-input" autocomplete="name"
                                   value="<?= htmlspecialchars($_SESSION['register_old']['name'] ?? '') ?>">
                        </div>
                        <?php if (isset($_SESSION['register_errors']['name'])): ?>
                            <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['name']) ?></div>
                            <?php unset($_SESSION['register_errors']['name']); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Contact Tabs -->
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-address-book mr-1"></i> Contact Method</label>
                        <div class="flex bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                            <button type="button" class="tab-btn active" data-method="email">
                                <i class="fas fa-envelope"></i> Email
                            </button>
                            <button type="button" class="tab-btn" data-method="phone">
                                <i class="fas fa-phone"></i> Phone
                            </button>
                        </div>
                    </div>

                    <!-- Email -->
                    <div id="email-field" class="form-group <?= isset($_SESSION['register_errors']['email']) ? 'has-error' : '' ?>">
                        <label for="email-input" class="form-label"><i class="fas fa-envelope mr-1"></i> Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email-input" placeholder="your@email.com"
                                   class="field-input" autocomplete="email"
                                   value="<?= htmlspecialchars($_SESSION['register_old']['email'] ?? '') ?>">
                        </div>
                        <?php if (isset($_SESSION['register_errors']['email'])): ?>
                            <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['email']) ?></div>
                            <?php unset($_SESSION['register_errors']['email']); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Phone -->
                    <div id="phone-field" class="form-group hidden <?= isset($_SESSION['register_errors']['phone']) ? 'has-error' : '' ?>">
                        <label for="phone-input" class="form-label"><i class="fas fa-phone mr-1"></i> Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phone" id="phone-input" placeholder="+1234567890"
                                   class="field-input" autocomplete="tel"
                                   value="<?= htmlspecialchars($_SESSION['register_old']['phone'] ?? '') ?>">
                        </div>
                        <?php if (isset($_SESSION['register_errors']['phone'])): ?>
                            <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['phone']) ?></div>
                            <?php unset($_SESSION['register_errors']['phone']); ?>
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="register_method" id="method" value="email">

                    <!-- Password -->
                    <div class="form-group <?= isset($_SESSION['register_errors']['password']) ? 'has-error' : '' ?>">
                        <label for="password-input" class="form-label"><i class="fas fa-lock mr-1"></i> Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key"></i>
                            <input type="password" name="password" id="password-input" placeholder="Create a strong password"
                                   class="field-input" autocomplete="new-password">
                            <button type="button" class="toggle-pwd" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="strength-bar" id="strength-bar"></div>
                        <div class="strength-text" id="strength-text">8+ characters recommended</div>
                        <?php if (isset($_SESSION['register_errors']['password'])): ?>
                            <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['password']) ?></div>
                            <?php unset($_SESSION['register_errors']['password']); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group <?= isset($_SESSION['register_errors']['confirm_password']) ? 'has-error' : '' ?>">
                        <label for="confirm-password-input" class="form-label"><i class="fas fa-check-circle mr-1"></i> Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-check"></i>
                            <input type="password" name="confirm_password" id="confirm-password-input" placeholder="Confirm your password"
                                   class="field-input" autocomplete="new-password">
                            <button type="button" class="toggle-pwd" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="match-feedback" class="match-feedback"></div>
                        <?php if (isset($_SESSION['register_errors']['confirm_password'])): ?>
                            <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['confirm_password']) ?></div>
                            <?php unset($_SESSION['register_errors']['confirm_password']); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" name="terms" id="terms" class="mt-1 accent-blue-600 dark:accent-blue-400" checked>
                        <label for="terms">I agree to the <a href="#" class="text-blue-600 dark:text-blue-400 font-medium">Terms</a> &amp; <a href="#" class="text-blue-600 dark:text-blue-400 font-medium">Privacy Policy</a></label>
                    </div>
                    <?php if (isset($_SESSION['register_errors']['terms'])): ?>
                        <div class="field-error"><?= htmlspecialchars($_SESSION['register_errors']['terms']) ?></div>
                        <?php unset($_SESSION['register_errors']['terms']); ?>
                    <?php endif; ?>

                    <button type="submit" class="submit-btn"><i class="fas fa-user-plus mr-2"></i> Create Account</button>

                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Already have an account?
                        <a href="<?= BASE_URL ?>/login" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Login</a>
                    </p>

                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* ─── CSS Variables for Light & Dark ─── */
    :root {
        /* Card & inputs */
        --bg-card: rgba(255,255,255,0.92);
        --bg-input: #f8fafc;
        --border-input: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --text-muted: #94a3b8;
        --shadow-card: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --focus-ring: rgba(59,130,246,0.15);
        --border-card: rgba(255,255,255,0.3);

        /* SVG illustration colors (light) */
        --svg-book-yellow: #fbbf24;
        --svg-book-blue: #60a5fa;
        --svg-book-stroke-yellow: #d97706;
        --svg-book-stroke-blue: #2563eb;
        --svg-inner-yellow: #fef3c7;
        --svg-inner-blue: #dbeafe;
        --svg-sun: #fcd34d;
        --svg-eyes: #1e293b;
        --svg-rect: #f59e0b;
        --svg-arrow: #f59e0b;
        --svg-cloud: #ffffff;
        --svg-cloud-stroke: #cbd5e1;
        --svg-text: #334155;
    }

    .dark {
        --bg-card: rgba(30, 41, 59, 0.92);
        --bg-input: #1e293b;
        --border-input: #334155;
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --shadow-card: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        --focus-ring: rgba(96, 165, 250, 0.25);
        --border-card: rgba(255,255,255,0.08);

        /* SVG illustration colors (dark) */
        --svg-book-yellow: #b45309;
        --svg-book-blue: #1d4ed8;
        --svg-book-stroke-yellow: #92400e;
        --svg-book-stroke-blue: #1e3a8a;
        --svg-inner-yellow: #fef3c7;
        --svg-inner-blue: #dbeafe;
        --svg-sun: #f59e0b;
        --svg-eyes: #e2e8f0;
        --svg-rect: #d97706;
        --svg-arrow: #d97706;
        --svg-cloud: #1e293b;
        --svg-cloud-stroke: #475569;
        --svg-text: #94a3b8;
    }

    .glass-card {
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        border-radius: 2rem;
        padding: 1.8rem 2rem 1.5rem;
        width: 100%;
        box-shadow: var(--shadow-card);
        border: 1px solid var(--border-card);
        transition: background 0.3s, box-shadow 0.3s, border-color 0.3s;
    }

    .icon-wrap {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: -2.8rem auto 0.4rem;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(59,130,246,0.4);
    }
    .dark .icon-wrap {
        border-color: var(--bg-card);
        box-shadow: 0 8px 20px rgba(59,130,246,0.3);
    }

    .form-group { margin-bottom: 1rem; }
    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
    }
    .input-wrapper {
        position: relative;
    }
    .input-wrapper i {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.9rem;
        transition: color 0.2s;
        pointer-events: none;
    }
    .field-input {
        width: 100%;
        padding: 0.6rem 3rem 0.6rem 2.4rem;
        border: 1.5px solid var(--border-input);
        border-radius: 0.8rem;
        font-size: 0.9rem;
        background: var(--bg-input);
        color: var(--text-primary);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s, color 0.2s;
    }
    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px var(--focus-ring);
        background: var(--bg-input);
    }
    .field-input::placeholder {
        color: var(--text-muted);
    }

    .toggle-pwd {
        position: absolute;
        right: 0.4rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 1.1rem;
        padding: 1rem ;
        border-radius: 0.4rem;
        transition: color 0.2s, background 0.2s;
        z-index: 2;
        line-height: 1;
    }
    .toggle-pwd:hover {
        color: #3b82f6;
        background: rgba(59,130,246,0.08);
    }
    .toggle-pwd:active {
        transform: translateY(-50%) scale(0.92);
    }
    .toggle-pwd i {
        pointer-events: none;
    }

    .strength-bar {
        height: 3px;
        border-radius: 3px;
        background: var(--border-input);
        margin-top: 0.4rem;
        transition: width 0.3s, background 0.3s;
        width: 0;
    }
    .strength-bar.weak { background: #ef4444; width: 33%; }
    .strength-bar.medium { background: #f59e0b; width: 66%; }
    .strength-bar.strong { background: #3b82f6; width: 100%; }

    .strength-text {
        font-size: 0.65rem;
        color: var(--text-secondary);
        margin-top: 0.1rem;
    }
    .match-feedback {
        font-size: 0.7rem;
        margin-top: 0.2rem;
    }
    .match-feedback.match { color: #3b82f6; }
    .match-feedback.no-match { color: #ef4444; }

    .tab-btn {
        flex: 1;
        padding: 0.4rem 0;
        border: none;
        border-radius: 0.8rem;
        background: transparent;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        font-size: 0.8rem;
    }
    .tab-btn.active {
        background: #fff;
        color: #1d4ed8;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .dark .tab-btn.active {
        background: #1e293b;
        color: #60a5fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .tab-btn:hover:not(.active) { background: rgba(255,255,255,0.4); }
    .dark .tab-btn:hover:not(.active) { background: rgba(255,255,255,0.05); }

    .submit-btn {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        padding: 0.6rem;
        border: none;
        border-radius: 0.8rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        width: 100%;
        box-shadow: 0 4px 16px rgba(59,130,246,0.35);
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(59,130,246,0.45);
    }
    .field-error {
        color: #dc2626;
        font-size: 0.7rem;
        margin-top: 0.2rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .field-error i { font-size: 0.7rem; }
    .has-error .field-input { border-color: #dc2626; }
    .has-error .field-input:focus { box-shadow: 0 0 0 4px rgba(220,38,38,0.15); }

    .dark .has-error .field-input { border-color: #f87171; }
    .dark .has-error .field-input:focus { box-shadow: 0 0 0 4px rgba(248,113,113,0.25); }

    /* ─── FIX: White backgrounds in dark mode for all inputs and contact method ─── */
    .dark .field-input {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }
    .dark .field-input::placeholder {
        color: #94a3b8 !important;
    }
    .dark .input-wrapper i {
        color: #64748b !important;
    }
    .dark .toggle-pwd {
        color: #64748b !important;
    }
    .dark .toggle-pwd:hover {
        color: #3b82f6 !important;
        background: rgba(59,130,246,0.1) !important;
    }
    .dark .strength-text {
        color: #334155 !important;
    }
    .dark .match-feedback.match {
        color: #2563eb !important;
    }
    .dark .match-feedback.no-match {
        color: #dc2626 !important;
    }
    /* Contact method tabs white background */
    .dark .flex.bg-gray-100 {
        background-color: #ffffff !important;
    }
    .dark .tab-btn {
        color: #1e293b !important;
    }
    .dark .tab-btn.active {
        background-color: #ffffff !important;
        color: #1d4ed8 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }
    .dark .tab-btn:hover:not(.active) {
        background: rgba(0,0,0,0.05) !important;
    }
    /* Focus and error states on white background */
    .dark .field-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.25) !important;
    }
    .dark .has-error .field-input {
        border-color: #dc2626 !important;
    }
    .dark .has-error .field-input:focus {
        box-shadow: 0 0 0 4px rgba(220,38,38,0.2) !important;
    }
    /* ─── End fix ─── */

    @media (max-width: 768px) {
        .container { flex-direction: column; align-items: center; }
        .glass-card { max-width: 460px; margin: 0 auto; }
        .icon-wrap { width: 60px; height: 60px; margin-top: -3rem; }
        .icon-wrap i { font-size: 1.4rem; }
        h2 { font-size: 1.4rem; }
    }
</style>

<script>
    (function() {
        'use strict';

        // ─── Tabs ───
        const tabs = document.querySelectorAll('.tab-btn');
        const emailField = document.getElementById('email-field');
        const phoneField = document.getElementById('phone-field');
        const methodInput = document.getElementById('method');
        const emailInput = document.getElementById('email-input');
        const phoneInput = document.getElementById('phone-input');

        function switchTab(method) {
            tabs.forEach(t => t.classList.toggle('active', t.dataset.method === method));
            if (method === 'email') {
                emailField.classList.remove('hidden');
                phoneField.classList.add('hidden');
                emailInput.required = true;
                phoneInput.required = false;
                phoneInput.value = '';
            } else {
                phoneField.classList.remove('hidden');
                emailField.classList.add('hidden');
                phoneInput.required = true;
                emailInput.required = false;
                emailInput.value = '';
            }
            methodInput.value = method;
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                switchTab(tab.dataset.method);
            });
        });

        emailInput.addEventListener('input', function() {
            if (this.value.trim() !== '' && !emailField.classList.contains('hidden')) {
                const active = document.querySelector('.tab-btn.active');
                if (active && active.dataset.method !== 'email') switchTab('email');
            }
        });
        phoneInput.addEventListener('input', function() {
            if (this.value.trim() !== '' && !phoneField.classList.contains('hidden')) {
                const active = document.querySelector('.tab-btn.active');
                if (active && active.dataset.method !== 'phone') switchTab('phone');
            }
        });
        switchTab('email');

        // ─── Password Toggle ───
        document.querySelectorAll('.toggle-pwd').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const input = this.closest('.input-wrapper').querySelector('input');
                if (!input) return;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
                input.focus();
            });
        });

        // ─── Password Strength & Match ───
        const pwdInput = document.getElementById('password-input');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        const confirmInput = document.getElementById('confirm-password-input');
        const matchFeedback = document.getElementById('match-feedback');

        function checkStrength(pwd) {
            let score = 0;
            if (pwd.length >= 8) score++;
            if (pwd.length >= 12) score++;
            if (/\d/.test(pwd)) score++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
            if (/[^a-zA-Z0-9]/.test(pwd)) score++;
            return score <= 2 ? 'weak' : score <= 4 ? 'medium' : 'strong';
        }

        function updateStrength() {
            const val = pwdInput.value;
            const strength = checkStrength(val);
            strengthBar.className = 'strength-bar';
            if (val.length === 0) {
                strengthText.textContent = '8+ characters recommended';
                strengthText.style.color = 'var(--text-secondary)';
                return;
            }
            if (strength === 'weak') {
                strengthBar.classList.add('weak');
                strengthText.textContent = '⚠️ Weak – add numbers & symbols';
                strengthText.style.color = '#ef4444';
            } else if (strength === 'medium') {
                strengthBar.classList.add('medium');
                strengthText.textContent = '⚡ Medium – add more characters';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = '✅ Strong password!';
                strengthText.style.color = '#3b82f6';
            }
            checkMatch();
        }

        function checkMatch() {
            if (confirmInput.value.length === 0) {
                matchFeedback.textContent = '';
                matchFeedback.className = 'match-feedback';
                confirmInput.style.borderColor = '';
                return;
            }
            if (pwdInput.value === confirmInput.value) {
                matchFeedback.textContent = '✅ Passwords match';
                matchFeedback.className = 'match-feedback match';
                confirmInput.style.borderColor = '#3b82f6';
            } else {
                matchFeedback.textContent = '❌ Passwords do not match';
                matchFeedback.className = 'match-feedback no-match';
                confirmInput.style.borderColor = '#dc2626';
            }
        }

        pwdInput.addEventListener('input', updateStrength);
        confirmInput.addEventListener('input', checkMatch);
        pwdInput.addEventListener('input', checkMatch);
    })();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>