<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . '://' . $host . $scriptDir);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Your Account – Library Management</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <style>
        /* ─── Background (same as login/register) ─── */
        body {
            background: linear-gradient(145deg, #60a5fa, #1d4ed8);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* ─── Glass card ─── */
        .glass-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-radius: 2rem;
            padding: 2.2rem 2.5rem 1.8rem;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255,255,255,0.3);
            transition: box-shadow 0.3s;
        }

        /* ─── Icon ─── */
        .icon-wrap {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -3.2rem auto 0.6rem;
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(59,130,246,0.4);
        }

        /* ─── Form Elements ─── */
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        /* ─── OTP Boxes ─── */
        .otp-container {
            display: flex;
            gap: 0.6rem;
            justify-content: center;
            margin: 0.5rem 0 0.75rem;
        }
        .otp-box {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            font-family: 'Inter', monospace;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #ffffff;  /* ← changed to pure white */
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.1s;
            color: #1e293b;
        }
        .otp-box:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
            background: #ffffff;
            transform: scale(1.02);
        }
        .otp-box:not(:placeholder-shown) {
            border-color: #3b82f6;
            background: #ffffff;
        }
        .otp-box::placeholder {
            color: #cbd5e1;
            font-weight: 300;
            font-size: 1rem;
        }
        /* dark mode support (optional) - keep white background even in dark mode */
        @media (prefers-color-scheme: dark) {
            .otp-box {
                background: #ffffff;
                border-color: #cbd5e1;
                color: #1e293b;
            }
            .otp-box:focus {
                background: #ffffff;
                border-color: #3b82f6;
                box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
            }
            .otp-box:not(:placeholder-shown) {
                border-color: #3b82f6;
                background: #ffffff;
            }
            .otp-box::placeholder {
                color: #94a3b8;
            }
        }

        /* ─── Submit Button ─── */
        .submit-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            padding: 0.75rem;
            border: none;
            border-radius: 0.9rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            box-shadow: 0 4px 16px rgba(59,130,246,0.35);
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(59,130,246,0.45);
        }

        /* ─── Resend Link ─── */
        .resend-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .resend-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* ─── Alert Messages ─── */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .alert i {
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        /* ─── Responsive ─── */
        @media (max-width: 480px) {
            .glass-card {
                padding: 1.5rem 1.2rem 1.2rem;
                border-radius: 1.8rem;
            }
            .icon-wrap {
                width: 56px;
                height: 56px;
                margin-top: -2.8rem;
            }
            .icon-wrap i {
                font-size: 1.2rem;
            }
            h2 {
                font-size: 1.4rem;
            }
            .otp-box {
                width: 40px;
                height: 48px;
                font-size: 1.2rem;
            }
            .otp-container {
                gap: 0.4rem;
            }
        }
    </style>
</head>
<body>

<div class="glass-card">

    <!-- Icon -->
    <div class="icon-wrap">
        <i class="fas fa-shield-alt text-white text-2xl"></i>
    </div>

    <!-- Title -->
    <h2 class="text-center text-2xl font-bold text-gray-800 mt-1">Verify Your Account</h2>
    <p class="text-center text-sm text-gray-500 mb-5">
        <?php if (isset($_SESSION['verification_email'])): ?>
            We sent a 6-digit code to <strong><?= htmlspecialchars($_SESSION['verification_email']) ?></strong>
        <?php else: ?>
            Enter the 6-digit verification code from your email
        <?php endif; ?>
    </p>

    <!-- Messages -->
    <?php if (isset($message) && $success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php elseif (isset($message) && !$success): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <!-- Session messages from previous pages -->
    <?php if (isset($_SESSION['verification_error'])): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['verification_error']) ?></span>
        </div>
        <?php unset($_SESSION['verification_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['verification_success'])): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['verification_success']) ?></span>
        </div>
        <?php unset($_SESSION['verification_success']); ?>
    <?php endif; ?>

    <!-- ===== OTP Input Form ===== -->
    <form action="<?= BASE_URL ?>/verify-email-code" method="POST" id="otp-form" autocomplete="off">

        <div class="form-group">
            <label class="form-label text-center block">
                <i class="fas fa-shield-alt mr-1"></i> Enter 6‑Digit Code
            </label>

            <!-- 6 OTP boxes -->
            <div class="otp-container" id="otp-container">
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="•" required>
            </div>

            <!-- Hidden field to combine OTP -->
            <input type="hidden" name="code" id="combined-code" value="" />

            <p class="text-xs text-gray-400 text-center mt-2">
                <i class="fas fa-info-circle"></i> Type the code or paste it directly
            </p>
        </div>

        <button type="submit" class="submit-btn" id="verify-btn">
            <i class="fas fa-check-circle mr-2"></i> Verify Account
        </button>

    </form>

    <!-- Divider -->
    <div class="flex items-center gap-3 my-4">
        <hr class="flex-1 border-gray-200" />
        <span class="text-xs text-gray-400">or</span>
        <hr class="flex-1 border-gray-200" />
    </div>

    <!-- Resend Link -->
    <div class="text-center">
        <a href="<?= BASE_URL ?>/resend-verification" class="resend-link">
            <i class="fas fa-paper-plane"></i>
            Resend verification email
        </a>
    </div>

    <!-- Login Link -->
    <p class="text-center text-sm text-gray-500 mt-3">
        Already verified?
        <a href="<?= BASE_URL ?>/login" class="text-blue-600 font-semibold hover:underline">Login</a>
    </p>

</div>

<script>
    (function() {
        'use strict';

        const otpInputs = document.querySelectorAll('.otp-box');
        const hiddenInput = document.getElementById('combined-code');
        const form = document.getElementById('otp-form');

        // ─── Auto‑advance, backspace, paste support ───
        otpInputs.forEach((input, index) => {
            // Focus first input on load
            if (index === 0) input.focus();

            // Handle input (digit entered)
            input.addEventListener('input', function(e) {
                // Allow only digits
                this.value = this.value.replace(/\D/g, '');
                // If a digit was entered, move to next
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                // Update hidden combined value
                updateCombinedCode();
            });

            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (this.value === '' && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
                // Allow arrow keys navigation
                if (e.key === 'ArrowLeft' && index > 0) {
                    e.preventDefault();
                    otpInputs[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    e.preventDefault();
                    otpInputs[index + 1].focus();
                }
            });

            // Handle paste on the first input
            if (index === 0) {
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    const digits = pasteData.replace(/\D/g, '').slice(0, 6);
                    if (digits.length > 0) {
                        // Fill the boxes
                        for (let i = 0; i < digits.length && i < otpInputs.length; i++) {
                            otpInputs[i].value = digits[i];
                        }
                        // Focus the next empty box or the last
                        const nextIndex = Math.min(digits.length, otpInputs.length - 1);
                        otpInputs[nextIndex].focus();
                        updateCombinedCode();
                    }
                });
            }
        });

        // ─── Combine values into hidden input ───
        function updateCombinedCode() {
            let code = '';
            otpInputs.forEach(input => {
                code += input.value;
            });
            hiddenInput.value = code;
        }

        // ─── Before submit, ensure hidden input is up to date ───
        form.addEventListener('submit', function(e) {
            updateCombinedCode();
            // Optional: check if all 6 digits are filled
            if (hiddenInput.value.length !== 6) {
                e.preventDefault();
                alert('Please enter the complete 6-digit verification code.');
                // Focus the first empty box
                for (let i = 0; i < otpInputs.length; i++) {
                    if (otpInputs[i].value === '') {
                        otpInputs[i].focus();
                        break;
                    }
                }
            }
        });

        updateCombinedCode();

    })();
</script>

</body>
</html>