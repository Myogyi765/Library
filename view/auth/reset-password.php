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

$pageTitle = 'Reset Password';
$token = $token ?? '';
$email = $email ?? '';
include __DIR__ . '/../layout/header.php';
?>

<div class="auth-shell">

    <!-- ─── LEFT SIDE: Illustration ─── -->
    <div class="auth-illustration">
        <div class="auth-copy">
            <svg viewBox="0 0 200 160" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto max-h-[200px] mx-auto">
                <path d="M40 40 L40 120 Q60 100 100 120 L100 40 Q60 20 40 40Z" fill="#fbbf24" stroke="#d97706" stroke-width="2" />
                <path d="M100 40 L100 120 Q140 100 160 120 L160 40 Q140 20 100 40Z" fill="#60a5fa" stroke="#2563eb" stroke-width="2" />
                <path d="M44 44 L44 116 Q62 98 100 116 L100 44 Q62 26 44 44Z" fill="#fef3c7" />
                <path d="M100 44 L100 116 Q138 98 156 116 L156 44 Q138 26 100 44Z" fill="#dbeafe" />
                <circle cx="100" cy="30" r="14" fill="#fcd34d" />
                <circle cx="95" cy="26" r="2" fill="#1e293b" />
                <circle cx="105" cy="26" r="2" fill="#1e293b" />
                <path d="M94 34 Q100 38 106 34" stroke="#1e293b" stroke-width="2" fill="none" stroke-linecap="round" />
                <rect x="92" y="44" width="16" height="18" rx="4" fill="#f59e0b" />
                <path d="M88 48 L82 52 M112 48 L118 52" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" />
                <path d="M130 18 Q148 2 160 14 Q170 24 158 34 Q148 40 136 36 L130 44 L134 34 Q128 28 130 18Z" fill="#fff" stroke="#cbd5e1" stroke-width="1.5" />
                <text x="146" y="24" font-size="9" fill="#334155" font-family="sans-serif" text-anchor="middle">🔐</text>
            </svg>
            <h2>Create New Password</h2>
            <p>Enter your new password below to complete the reset process.</p>
            <div class="auth-benefits">
                <span><span class="benefit-icon">✓</span> Secure</span>
                <span><span class="benefit-icon">✓</span> Encrypted</span>
                <span><span class="benefit-icon">✓</span> Protected</span>
            </div>
        </div>
    </div>

    <!-- ─── RIGHT SIDE: Reset Password Card ─── -->
    <div class="auth-form-panel">
        <div class="glass-card">
            <div class="icon-wrap">
                <i class="fas fa-lock-open text-white text-2xl"></i>
            </div>
            <h2>Set New Password</h2>
            <p class="subtitle">Create a new password for your account</p>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/reset-password/update" method="POST" class="auth-form" autocomplete="on">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <!-- New Password -->
                <div class="form-group">
                    <label for="password" class="form-label"><i class="fas fa-key"></i> New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" required
                               placeholder="Enter new password (min 6 chars)"
                               minlength="6"
                               class="field-input" autocomplete="new-password">
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Minimum 6 characters</p>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirm" class="form-label"><i class="fas fa-check-circle"></i> Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-check input-icon"></i>
                        <input type="password" name="password_confirm" id="password_confirm" required
                               placeholder="Confirm your new password"
                               class="field-input" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Update Password
                </button>

                <p class="auth-footer">
                    <a href="<?= BASE_URL ?>/login" class="auth-link auth-link-strong">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </p>

            </form>
        </div>
    </div>
</div>

<!-- ─── Custom Styles ─── -->
<style>
    :root {
        --bg-body: #eef4ff;
        --bg-card: rgba(255,255,255,0.95);
        --bg-input: #f8fafc;
        --border-input: #dbe3f0;
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --shadow-card: 0 18px 45px rgba(15, 23, 42, 0.15);
        --focus-ring: rgba(59, 130, 246, 0.16);
        --border-card: rgba(255,255,255,0.75);
        --disabled-bg: #e2e8f0;
        --disabled-text: #94a3b8;
        --icon-bg: linear-gradient(135deg, #3b82f6, #1d4ed8);
        --link-color: #2563eb;
        --error-bg: #fef2f2;
        --error-border: #fecaca;
        --error-text: #dc2626;
        --success-bg: #f0fdf4;
        --success-border: #bbf7d0;
        --success-text: #16a34a;
        --btn-shadow: rgba(59, 130, 246, 0.28);
        --btn-hover-shadow: rgba(59, 130, 246, 0.4);
    }
    .dark {
        --bg-body: #0f172a;
        --bg-card: rgba(30, 41, 59, 0.95);
        --bg-input: #1e293b;
        --border-input: #334155;
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --shadow-card: 0 18px 45px rgba(0, 0, 0, 0.6);
        --focus-ring: rgba(96, 165, 250, 0.25);
        --border-card: rgba(255,255,255,0.08);
        --disabled-bg: #334155;
        --disabled-text: #64748b;
        --icon-bg: linear-gradient(135deg, #2563eb, #1d4ed8);
        --link-color: #60a5fa;
        --error-bg: rgba(220, 38, 38, 0.15);
        --error-border: #f87171;
        --error-text: #fca5a5;
        --success-bg: rgba(22, 163, 74, 0.15);
        --success-border: #4ade80;
        --success-text: #4ade80;
        --btn-shadow: rgba(59, 130, 246, 0.2);
        --btn-hover-shadow: rgba(59, 130, 246, 0.4);
    }

    /* ─── Auth Shell ─── */
    .auth-shell {
        max-width: 1180px;
        margin: 0 auto;
        padding: 2rem 1rem 3rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        min-height: 80vh;
    }

    .auth-illustration,
    .auth-form-panel {
        flex: 1 1 320px;
        min-width: 280px;
        max-width: 500px;
    }

    .auth-copy {
        text-align: center;
        padding: 1rem;
    }

    .auth-copy h2 {
        margin: 1rem 0 0.5rem;
        font-size: 2rem;
        color: #1d4ed8;
    }

    .dark .auth-copy h2 {
        color: #60a5fa;
    }

    .auth-copy p {
        margin: 0 auto;
        max-width: 420px;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .auth-benefits {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.75rem 1rem;
        margin-top: 1rem;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .benefit-icon {
        color: #16a34a;
        font-weight: 700;
    }

    .dark .benefit-icon {
        color: #4ade80;
    }

    /* ─── Glass Card ─── */
    .glass-card {
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        border-radius: 1.5rem;
        padding: 2rem 1.5rem 1.5rem;
        width: 100%;
        box-shadow: var(--shadow-card);
        border: 1px solid var(--border-card);
        transition: background 0.3s, box-shadow 0.3s, border-color 0.3s;
    }

    .glass-card h2 {
        text-align: center;
        margin: 0.35rem 0 0.25rem;
        color: var(--text-primary);
    }

    .glass-card .subtitle {
        text-align: center;
        color: var(--text-secondary);
        margin: 0 0 1.25rem;
    }

    .icon-wrap {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        margin: -3rem auto 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--icon-bg);
        color: #fff;
        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.3);
        transition: background 0.3s;
    }

    /* ─── Form ─── */
    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-label i {
        margin-right: 0.3rem;
        color: var(--text-muted);
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon,
    .input-wrapper i {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        transition: color 0.2s;
    }

    .field-input {
        width: 100%;
        box-sizing: border-box;
        padding: 0.8rem 2.8rem 0.8rem 2.4rem;
        border: 1.5px solid var(--border-input);
        border-radius: 0.8rem;
        font-size: 0.95rem;
        background: var(--bg-input);
        color: var(--text-primary);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s, color 0.2s;
    }

    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px var(--focus-ring);
    }

    .field-input::placeholder {
        color: var(--text-muted);
    }

    .submit-btn {
        width: 100%;
        border: none;
        border-radius: 0.8rem;
        padding: 0.8rem 1rem;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 22px var(--btn-shadow);
        transition: transform 0.2s, box-shadow 0.2s, background 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        font-size: 1rem;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px var(--btn-hover-shadow);
    }

    .auth-footer {
        margin-top: 0.25rem;
        text-align: center;
        color: var(--text-secondary);
    }

    .auth-link {
        color: var(--link-color);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .auth-link:hover {
        text-decoration: underline;
    }

    .auth-link-strong {
        font-weight: 700;
    }

    /* ─── Alerts ─── */
    .alert-error {
        background: var(--error-bg);
        border: 1px solid var(--error-border);
        color: var(--error-text);
        padding: 0.7rem 1rem;
        border-radius: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        margin-bottom: 1.2rem;
    }

    .alert-success {
        background: var(--success-bg);
        border: 1px solid var(--success-border);
        color: var(--success-text);
        padding: 0.7rem 1rem;
        border-radius: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        margin-bottom: 1.2rem;
    }

    /* ─── Responsive ─── */
    @media (max-width: 768px) {
        .auth-shell {
            padding-top: 1.2rem;
            gap: 1.25rem;
        }
        .glass-card {
            padding: 1.6rem 1rem 1.25rem;
        }
        .auth-copy h2 {
            font-size: 1.5rem;
        }
    }
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>